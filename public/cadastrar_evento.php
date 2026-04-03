<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/database.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Cadastrar evento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $data_evento = $_POST['data_evento'];
    $local = $_POST['local'];
    $vagas = $_POST['vagas'];
    $link = $_POST['link'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $categoria = $_POST['categoria']; // Novo campo de categoria
    $organizador_id = $_SESSION['user_id'];

    // Variável da imagem
    $imagem_capa = null;

    // Verifica se o arquivo foi enviado
    if (isset($_FILES['imagem_capa']) && $_FILES['imagem_capa']['error'] === UPLOAD_ERR_OK) {
        $arquivoTmp = $_FILES['imagem_capa']['tmp_name'];
        $nomeOriginal = basename($_FILES['imagem_capa']['name']);
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

        // Extensões permitidas
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extensao, $extensoesPermitidas)) {
            $mensagem = "❌ Tipo de arquivo não permitido. Envie JPG, PNG, GIF ou WEBP.";
        } elseif ($_FILES['imagem_capa']['size'] > 2 * 1024 * 1024) { // 2MB
            $mensagem = "❌ O arquivo excede o tamanho máximo de 2MB.";
        } else {
            // Gera nome único e move arquivo
            $novoNome = uniqid('evento_', true) . '.' . $extensao;
            $destino = __DIR__ . '/../public/uploads/' . $novoNome;

            if (move_uploaded_file($arquivoTmp, $destino)) {
                $imagem_capa = $novoNome;
            } else {
                $mensagem = "⚠️ Falha ao salvar o arquivo.";
            }
        }
    }

    // Insere no banco (adicionando categoria)
    $stmt = $conn->prepare("INSERT INTO eventos (nome, descricao, data_evento, local, vagas, link, imagem_capa, organizador_id, latitude, longitude, categoria) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssisssdds", $nome, $descricao, $data_evento, $local, $vagas, $link, $imagem_capa, $organizador_id, $latitude, $longitude, $categoria);

    if ($stmt->execute()) {
        echo "<script>alert('Evento cadastrado com sucesso!'); window.location.href='index.php';</script>";
        exit;
    } else {
        echo "<script>alert('Erro ao cadastrar o evento.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="logoaba.png">
    <title>Cadastrar Evento - ConectaRed</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Navbar -->
<header style="position:fixed; top:0; left:0; right:0; height:70px; display:flex; align-items:center; justify-content:space-between; background:rgba(123,31,162,0.95); backdrop-filter:blur(15px); color:white; padding:0 40px; box-shadow:0 8px 25px rgba(0,0,0,0.15); z-index:1000;">
    <div class="logo"><img src="logoindex.png" alt="ConectaRed" style="height:140px; width:auto;"></div>
    <nav id="navLinks" style="display:flex; align-items:center; gap:25px;">
        <a href="index.php" style="color:white; text-decoration:none; font-weight:500;"><i class="fa-solid fa-house"></i> Início</a>
        <?php if($_SESSION['tipo_usuario']==='participante'): ?>
            <a href="minhas_inscricoes.php" style="color:white; text-decoration:none; font-weight:500;">Minhas Inscrições</a>
        <?php endif; ?>
        <a href="logout.php" style="padding:6px 15px; border-radius:20px; background:rgba(255,255,255,0.15); color:white; text-decoration:none;">Sair</a>
    </nav>
    <div id="hamburger" style="display:none; flex-direction:column; cursor:pointer; gap:5px; padding:5px;">
        <div style="width:28px; height:3px; background:white; border-radius:3px;"></div>
        <div style="width:28px; height:3px; background:white; border-radius:3px;"></div>
        <div style="width:28px; height:3px; background:white; border-radius:3px;"></div>
    </div>
</header>
    <style>
body { font-family: 'Poppins', sans-serif; background: linear-gradient(180deg, #f7f3fa 0%, #fefcff 100%); margin: 0; color: #333; }
.container { max-width: 600px; margin: 100px auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
h2 { color: #6a1b9a; text-align: center; margin-bottom: 30px; }
input[type="text"], input[type="number"], input[type="date"], textarea, input[type="file"], select { width: 100%; padding: 12px; margin: 8px 0 16px; border-radius: 8px; border: 1px solid #ccc; font-family: 'Poppins', sans-serif; font-size: 14px; }
button { background-color: #7b1fa2; color: white; border: none; border-radius: 25px; padding: 12px 25px; font-size: 15px; font-weight: 500; cursor: pointer; width: 100%; transition: all 0.3s ease, box-shadow 0.3s ease; }
button:hover { background-color: #9c27b0; transform: scale(1.05); box-shadow: 0 8px 20px rgba(156,39,176,0.5); }
.mensagem { text-align: center; margin-bottom: 15px; font-weight: 500; }
.voltar { display: block; text-align: center; margin-top: 20px; color: #6a1b9a; text-decoration: none; }
.voltar:hover { text-decoration: underline; }
.preview { text-align: center; margin-bottom: 15px; }
.preview img { max-width: 100%; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
#map { height:300px; border-radius:15px; margin-bottom:15px; }
.coord-field { display:flex; gap:10px; }
.coord-field input { width:100%; }
footer { background:linear-gradient(90deg,#7b1fa2,#4a148c); color:white; text-align:center; padding:25px; font-size:0.9em; margin-top:50px; }
footer a { color:#d1b3ff; text-decoration:none; font-weight:500; }
footer a:hover { text-decoration:underline; }
    </style>
</head>

<main>
    <div class="container">
    <h2>Cadastrar Novo Evento</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Nome do Evento:</label>
        <input type="text" name="nome" required>

        <label>Descrição breve:</label>
        <textarea name="descricao" rows="3" required></textarea>

        <label>Data do Evento:</label>
        <input type="date" name="data_evento" required>

        <label>Vagas:</label>
        <input type="number" name="vagas" required>

        <p><label>Imagem de Capa:</label></p>
        <input type="file" name="imagem_capa">

        <label>Nome do Local do Evento:</label>
        <input type="text" name="local">

        <label>Selecione a Localização no Mapa:</label>
        <div id="map"></div>

        <div class="coord-field">
            <input type="text" name="latitude" id="latitude" placeholder="Latitude" readonly required>
            <input type="text" name="longitude" id="longitude" placeholder="Longitude" readonly required>
        </div>

        <label>Categoria do Evento:</label>
        <select name="categoria" required>
            <option value="">Selecione a categoria</option>
            <option value="Show / Música ao Vivo">Show / Música ao Vivo</option>
            <option value="Workshop / Oficinas Criativas">Workshop / Oficinas Criativas</option>
            <option value="Palestra / Talk Inspirador">Palestra / Talk Inspirador</option>
            <option value="Esportes / Atividades Físicas">Esportes / Atividades Físicas</option>
            <option value="Culinária / Gastronomia">Culinária / Gastronomia</option>
            <option value="Artes / Exposições">Artes / Exposições</option>
            <option value="Cinema / Sessões de Filme">Cinema / Sessões de Filme</option>
            <option value="Tecnologia / Hackathons">Tecnologia / Hackathons</option>
            <option value="Educação / Cursos e Treinamentos">Educação / Cursos e Treinamentos</option>
            <option value="Bem-estar / Yoga & Meditação">Bem-estar / Yoga & Meditação</option>
            <option value="Networking / Encontros Profissionais">Networking / Encontros Profissionais</option>
            <option value="Feiras / Mercados Locais">Feiras / Mercados Locais</option>
            <option value="Aventura / Turismo e Natureza">Aventura / Turismo e Natureza</option>
            <option value="Jogos / eSports & Competições">Jogos / eSports & Competições</option>
            <option value="Solidariedade / Ações Comunitárias">Solidariedade / Ações Comunitárias</option>
            <option value="Cosplay / Eventos Temáticos">Cosplay / Eventos Temáticos</option>
            <option value="Outro">Outro</option>
        </select>

        <div class="preview" id="preview"></div>

        <button type="submit">Cadastrar Evento</button>
    </form>

    <a href="index.php" class="voltar">← Voltar para a lista</a>
</div>
</main>

<footer>
    <p>© 2025 ConectaRed. Todos os direitos reservados.</p>
    <p>Entre em contato: <a href="mailto:contato@conectared.com">contato@conectared.com</a></p>
    <p>Siga-nos nas redes sociais: <a href="#"><i class="fa-brands fa-instagram"></i> Instagram</a> | <a href="#"><i class="fa-brands fa-facebook"></i> Facebook</a> | <a href="#"><i class="fa-brands fa-linkedin"></i> LinkedIn</a></p>
</footer>

<script>
document.addEventListener('DOMContentLoaded',()=>{
    const hamburger=document.getElementById('hamburger');
    const navLinks=document.getElementById('navLinks');
    hamburger.addEventListener('click',()=>{ navLinks.classList.toggle('active'); hamburger.classList.toggle('active'); });
    window.addEventListener('scroll',()=>{ document.body.classList.toggle('scrolled', window.scrollY>10); });

    // Mapa Leaflet
    var map = L.map('map').setView([-14.2350, -51.9253], 4);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'&copy; OpenStreetMap contributors' }).addTo(map);
    var marker;
    map.on('click', function(e){
        var lat = e.latlng.lat.toFixed(6);
        var lng = e.latlng.lng.toFixed(6);
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        if(marker) map.removeLayer(marker);
        marker = L.marker([lat,lng]).addTo(map).bindPopup("Local Selecionado:<br>Lat: "+lat+"<br>Lon: "+lng).openPopup();
    });
});
</script>
</body>
</html>
