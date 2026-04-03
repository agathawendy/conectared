<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/database.php';

// Função para ícones de categoria
function iconeCategoria($categoria) {
    switch(strtolower($categoria)) {
        case 'show / música ao vivo': return '<i class="fa-solid fa-music"></i>';
        case 'workshop / oficinas': return '<i class="fa-solid fa-hammer"></i>';
        case 'criativenas / palestra / talk inspirador': return '<i class="fa-solid fa-microphone"></i>';
        case 'esportes / atividades físicas': return '<i class="fa-solid fa-person-running"></i>';
        case 'culinária / gastronomia': return '<i class="fa-solid fa-utensils"></i>';
        case 'artes / exposições': return '<i class="fa-solid fa-palette"></i>';
        case 'cinema / sessões de filme': return '<i class="fa-solid fa-film"></i>';
        case 'tecnologia / hackathons': return '<i class="fa-solid fa-laptop-code"></i>';
        case 'educação / cursos e treinamentos': return '<i class="fa-solid fa-book"></i>';
        case 'bem-estar / yoga & meditação': return '<i class="fa-solid fa-spa"></i>';
        case 'networking / encontros profissionais': return '<i class="fa-solid fa-users"></i>';
        case 'feiras / mercados locais': return '<i class="fa-solid fa-shop"></i>';
        case 'aventura / turismo e natureza': return '<i class="fa-solid fa-tree"></i>';
        case 'jogos / esports & competições': return '<i class="fa-solid fa-gamepad"></i>';
        case 'solidariedade / ações comunitárias': return '<i class="fa-solid fa-hand-holding-heart"></i>';
        case 'cosplay / eventos temáticos': return '<i class="fa-solid fa-mask"></i>';
        default: return '<i class="fa-solid fa-calendar-check"></i>';
    }
}

// Campo de pesquisa
$pesquisa = isset($_GET['busca']) ? trim($_GET['busca']) : '';

// Busca eventos (com filtro opcional)
if (!empty($pesquisa)) {
    $stmt = $conn->prepare("SELECT * FROM eventos 
                            WHERE nome LIKE ? 
                               OR descricao LIKE ? 
                               OR local LIKE ? 
                            ORDER BY data_evento ASC");
    $busca = "%" . $pesquisa . "%";
    $stmt->bind_param("sss", $busca, $busca, $busca);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM eventos ORDER BY data_evento ASC");
}

// Agrupar eventos por categoria
$eventosPorCategoria = [];
while($evento = $result->fetch_assoc()) {
    $cat = $evento['categoria'] ?? 'Outro';
    $eventosPorCategoria[$cat][] = $evento;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="logoaba.png">
<title>ConectaRed - Lista de Eventos</title>
<link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
<script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<style>
body { margin:0; font-family:'Poppins', sans-serif; background:linear-gradient(180deg,#f7f3fa 0%,#fefcff 100%); color:#333; }
.top-bar { position:fixed; top:0; left:0; right:0; height:70px; display:flex; align-items:center; justify-content:space-between; background:rgba(123,31,162,0.95); backdrop-filter:blur(15px); color:white; padding:0 40px; box-shadow:0 8px 25px rgba(0,0,0,0.15); z-index:1000; transition:all 0.4s ease; }
body.scrolled .top-bar { background:rgba(123,31,162,0.98); box-shadow:0 12px 30px rgba(0,0,0,0.25); }
.top-bar .logo img { height:60px; width:auto; object-fit:contain; transition: transform 0.3s ease; }
.top-bar .logo img:hover { transform:scale(1.08) rotate(-2deg); }
.nav-links { display:flex; align-items:center; gap:25px; }
.nav-links a { color:white; text-decoration:none; margin-left:25px; font-weight:500; position:relative; transition: all 0.3s ease; }
.nav-links a::after { content:''; position:absolute; width:0; height:3px; bottom:-4px; left:0; border-radius:2px; background:linear-gradient(to right,#d1b3ff,#7b1fa2); transition: width 0.4s ease; }
.nav-links a:hover::after { width:100%; }
.nav-links a:hover { color:#d1b3ff; }
.nav-links a.logout { padding:6px 15px; border-radius:20px; background-color:rgba(255,255,255,0.15); transition:background-color 0.3s ease; }
.nav-links a.logout:hover { background-color:rgba(255,255,255,0.3); }
.search-container { display:flex; align-items:center; gap:8px; }
.search-container input { padding:8px 15px; border-radius:20px; border:1px solid #ccc; font-family:'Poppins', sans-serif; transition: all 0.3s ease; }
.search-container input:focus { outline:none; border-color:#9c27b0; box-shadow:0 0 8px rgba(156,39,176,0.4); }
.search-container button { border:none; border-radius:20px; padding:8px 15px; font-weight:500; cursor:pointer; transition: all 0.3s ease; }
.search-container button[type="submit"] { background-color:#7b1fa2; color:white; }
.search-container button[type="submit"]:hover { background-color:#9c27b0; }
.search-container button[type="button"] { background-color:#c62828; color:white; }
.search-container button[type="button"]:hover { background-color:#e53935; }
.hamburger { display:none; flex-direction:column; cursor:pointer; gap:5px; padding:5px; }
.hamburger div { width:28px; height:3px; background:white; border-radius:3px; transition: all 0.4s ease; }
@media (max-width:768px) {
    .nav-links { position:fixed; top:70px; right:-100%; width:220px; height:calc(100% - 70px); background:rgba(123,31,162,0.98); flex-direction:column; align-items:center; justify-content:flex-start; padding-top:40px; gap:25px; transition:right 0.4s ease; z-index:999; border-left:2px solid rgba(255,255,255,0.2); backdrop-filter:blur(12px); }
    .nav-links a { font-size:1.2em; margin:0; }
    .hamburger { display:flex; }
    .nav-links.active { right:0; }
    .hamburger.active div:nth-child(1) { transform:rotate(45deg) translate(6px,6px); }
    .hamburger.active div:nth-child(2) { opacity:0; }
    .hamburger.active div:nth-child(3) { transform:rotate(-45deg) translate(6px,-6px); }
}
main { padding:120px 20px 60px; max-width:1200px; margin:auto; }
.botao-cadastrar { background-color:#7b1fa2; color:white; border:none; border-radius:25px; padding:12px 25px; font-size:15px; font-weight:500; cursor:pointer; transition: all 0.3s ease, box-shadow 0.3s ease; margin:0 auto 30px; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 4px 12px rgba(123,31,162,0.3); }
.botao-cadastrar:hover { background-color:#9c27b0; transform:scale(1.05); box-shadow:0 8px 20px rgba(156,39,176,0.5); }
.eventos-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:25px; }
.evento-card { background:rgba(255,255,255,0.85); backdrop-filter:blur(10px); border-radius:20px; padding:25px; box-shadow:0 8px 20px rgba(0,0,0,0.1); transition:transform 0.3s ease, box-shadow 0.3s ease; opacity:0; transform:translateY(30px); animation:fadeUp 0.6s ease forwards; animation-delay:calc(var(--delay)*0.1s); }
.evento-card:hover { transform:translateY(-5px) scale(1.02); box-shadow:0 12px 30px rgba(0,0,0,0.15); }
.evento-card h4 { color:#6a1b9a; margin-bottom:10px; }
.evento-card p { margin:6px 0; font-size:0.95em; }
.evento-card a { text-decoration:none; }
.evento-card button { margin-top:15px; display:flex; align-items:center; gap:6px; transition: all 0.3s ease; }
.evento-card button:hover { box-shadow:0 6px 15px rgba(123,31,176,0.5); transform:scale(1.05); }
.botoes-organizador { display:flex; justify-content:space-between; gap:10px; margin-top:15px; }
.botao-editar, .botao-excluir { flex:1; text-align:center; padding:10px; border-radius:20px; color:white; text-decoration:none; font-size:14px; transition:0.3s ease; }
.botao-editar { background-color:#6a1b9a; }
.botao-editar:hover { background-color:#8e24aa; }
.botao-excluir { background-color:#c62828; }
.botao-excluir:hover { background-color:#e53935; }
.imagem-capa { width:100%; height:180px; object-fit:cover; border-radius:15px; margin-bottom:15px; box-shadow:0 4px 12px rgba(0,0,0,0.1); transition:transform 0.3s ease; }
.imagem-capa:hover { transform:scale(1.03); }
.inscrito-card { padding:10px; margin-top:10px; border-radius:15px; background:white; box-shadow:0 4px 12px rgba(0,0,0,0.1); font-size:0.9em; }

@keyframes fadeUp { from {opacity:0; transform:translateY(30px);} to {opacity:1; transform:translateY(0);} }

footer { background:linear-gradient(90deg,#7b1fa2,#4a148c); color:white; text-align:center; padding:25px; font-size:0.9em; margin-top:50px; }
footer a { color:#d1b3ff; text-decoration:none; font-weight:500; }
footer a:hover { text-decoration:underline; }

#mapModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; }
#mapContainer { width:90%; max-width:800px; height:450px; background:white; border-radius:15px; overflow:hidden; position:relative; display:flex; flex-direction:column; }
#map { flex:1; }
#closeMap { position:absolute; top:10px; right:15px; font-size:25px; font-weight:bold; color:#333; cursor:pointer; z-index:10000; }
#closeMap:hover { color:#7b1fa2; }
</style>

<script>
document.addEventListener('DOMContentLoaded',()=>{
    const hamburger=document.getElementById('hamburger');
    const navLinks=document.getElementById('navLinks');
    hamburger.addEventListener('click',()=>{
        navLinks.classList.toggle('active');
        hamburger.classList.toggle('active');
    });
    window.addEventListener('scroll',()=>{ document.body.classList.toggle('scrolled', window.scrollY>10); });
});
</script>
</head>
<body>

<header class="top-bar">
    <div class="logo"><img src="logoindex.png" alt="ConectaRed" style="height:140px; width:auto;"></div>
    <form method="GET" action="index.php" class="search-container">
        <input type="text" name="busca" placeholder=" Pesquisar eventos..." value="<?php echo htmlspecialchars($pesquisa); ?>">
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        <?php if(!empty($pesquisa)): ?><button type="button" onclick="window.location.href='index.php'">Limpar</button><?php endif; ?>
    </form>
    <nav class="nav-links" id="navLinks">
        <a href="index.php"><i class="fa-solid fa-house"></i> Início</a>
        <?php if($_SESSION['tipo_usuario'] === 'participante'): ?>
            <a href="minhas_inscricoes.php">Minhas Inscrições</a>
        <?php endif; ?>
        <a href="logout.php" class="logout">Sair</a>
    </nav>
    <div class="hamburger" id="hamburger"><div></div><div></div><div></div></div>
</header>

<main>
<h2>Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['tipo_usuario']); ?>!</h2>
<?php if(isset($_SESSION['mensagem'])){ echo '<p style="text-align:center; color:#4a148c; font-weight:600; margin-bottom:20px;">' . $_SESSION['mensagem'] . '</p>'; unset($_SESSION['mensagem']); } ?>

<?php if($_SESSION['tipo_usuario'] === 'organizador'): ?>
    <button class="botao-cadastrar" onclick="window.location.href='cadastrar_evento.php'"><i class="fa-solid fa-plus"></i> Cadastrar Novo Evento</button>
<?php endif; ?>

<?php if(count($eventosPorCategoria) > 0): ?>
    <?php foreach($eventosPorCategoria as $categoria => $eventos): ?>
        <h4 style="margin-top:40px; color: grey;"><?php echo iconeCategoria($categoria) . ' ' . htmlspecialchars($categoria); ?></h4>
        <div class="eventos-grid">
        <?php $delay=0; ?>
        <?php foreach($eventos as $evento): $delay++; ?>
            <div class="evento-card" style="--delay:<?php echo $delay; ?>;">
                <?php if(!empty($evento['imagem_capa'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($evento['imagem_capa']); ?>" alt="Capa do evento <?php echo htmlspecialchars($evento['nome']); ?>" class="imagem-capa">
                <?php else: ?>
                    <img src="uploads/default.jpg" alt="Sem imagem disponível" class="imagem-capa">
                <?php endif; ?>

                <h4><?php echo htmlspecialchars($evento['nome']); ?></h4>
                <p><?php echo htmlspecialchars($evento['descricao']); ?></p>
                <?php if(!empty($evento['link'])): ?>
                    <p><strong>Link:</strong> <a href="<?php echo htmlspecialchars($evento['link']); ?>" target="_blank" style="color:#7b1fa2; font-weight:600; text-decoration:underline;">Acessar</a></p>
                <?php endif; ?>
                <p><strong>Data:</strong> <?php echo htmlspecialchars($evento['data_evento']); ?> |
                   <strong>Vagas:</strong> <?php echo htmlspecialchars($evento['vagas']); ?></p>

                <!-- Botões Ver Local e Detalhes -->
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <a href="#" class="botao-ver-mapa"
                       data-lat="<?php echo $evento['latitude']; ?>" 
                       data-lng="<?php echo $evento['longitude']; ?>" 
                       data-nome="<?php echo htmlspecialchars($evento['nome']); ?>"
                       style="flex:1; display:flex; align-items:center; justify-content:center; gap:6px; background-color:#7b1fa2; color:white; padding:8px 15px; border-radius:20px; text-decoration:none; cursor:pointer;">
                       <i class="fa-solid fa-map-location-dot"></i> Ver Local
                    </a>

                    <a href="detalhar_evento.php?id=<?php echo $evento['id']; ?>"
                       style="flex:1; display:flex; align-items:center; justify-content:center; gap:6px; background-color:#4a148c; color:white; padding:8px 15px; border-radius:20px; text-decoration:none; cursor:pointer;">
                       <i class="fa-solid fa-circle-info"></i> Detalhes
                    </a>
                </div>

                <?php if($_SESSION['tipo_usuario'] === 'participante'): ?>
                    <a href="inscrever_evento.php?evento_id=<?php echo $evento['id']; ?>" class="botao-cadastrar" style="background-color:#4a148c; margin-top:10px;"><i class="fa-solid fa-calendar-check"></i> Inscrever-se</a>
                <?php endif; ?>

                <?php if($_SESSION['tipo_usuario'] === 'organizador'): ?>
                    <div class="botoes-organizador">
                        <a href="editar_evento.php?id=<?php echo $evento['id']; ?>" class="botao-editar"><i class="fa-solid fa-pen-to-square"></i> Editar</a>
                        <a href="excluir_evento.php?id=<?php echo $evento['id']; ?>" class="botao-excluir" onclick="return confirm('Tem certeza que deseja excluir este evento?');"><i class="fa-solid fa-trash-can"></i> Excluir</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
<p style="text-align:center; color:#4a148c;">Nenhum evento encontrado.</p>
<?php endif; ?>
</main>

<!-- Modal Mapa -->
<div id="mapModal">
    <div id="mapContainer">
        <span id="closeMap">&times;</span>
        <div id="map" style="flex:1;"></div>
        <div style="padding:10px; display:flex; gap:10px; align-items:center; justify-content:space-between; background:#fafafa;">
            <div id="mapCoords" style="font-weight:600;color:#4a148c;">Local do evento</div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
let mapModal = document.getElementById('mapModal');
let mapContainer = document.getElementById('mapContainer');
let closeMap = document.getElementById('closeMap');
let map, marker;

document.querySelectorAll('.botao-ver-mapa').forEach(btn => {
    btn.addEventListener('click', () => {
        const lat = parseFloat(btn.dataset.lat);
        const lng = parseFloat(btn.dataset.lng);
        const nome = btn.dataset.nome;

        mapModal.style.display = 'flex';

        setTimeout(() => {
            if(!map){
                map = L.map('map').setView([lat,lng],13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution:'© OpenStreetMap contributors'
                }).addTo(map);
                marker = L.marker([lat,lng]).addTo(map).bindPopup(nome).openPopup();
            } else {
                map.setView([lat,lng],13);
                marker.setLatLng([lat,lng]).setPopupContent(nome).openPopup();
            }
            map.invalidateSize();
        }, 200);
    });
});

closeMap.addEventListener('click', () => { mapModal.style.display = 'none'; });
mapModal.addEventListener('click', (e) => { if(!mapContainer.contains(e.target)){ mapModal.style.display='none'; } });
</script>

<footer>
<p>© 2025 ConectaRed. Todos os direitos reservados.</p>
<p>Entre em contato: <a href="mailto:contato@conectared.com">contato@conectared.com</a></p>
<p>Siga-nos nas redes sociais:
    <a href="#"><i class="fa-brands fa-instagram"></i> Instagram</a> |
    <a href="#"><i class="fa-brands fa-facebook"></i> Facebook</a> |
    <a href="#"><i class="fa-brands fa-linkedin"></i> LinkedIn</a>
</p>
</footer>

</body>
</html>
