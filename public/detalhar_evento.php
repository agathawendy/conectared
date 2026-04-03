<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/database.php';

// Verifica login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Verifica ID do evento
if (!isset($_GET['id'])) {
    die('ID do evento não fornecido.');
}

$evento_id = intval($_GET['id']);
$mensagem = "";
$pesquisa = "";

// Busca dados do evento
$stmt = $conn->prepare("SELECT * FROM eventos WHERE id = ?");
$stmt->bind_param("i", $evento_id);
$stmt->execute();
$result = $stmt->get_result();
$evento = $result->fetch_assoc();

if (!$evento) {
    die('Evento não encontrado.');
}

// Busca detalhes do evento
$stmt = $conn->prepare("SELECT * FROM detalhes_evento WHERE evento_id = ?");
$stmt->bind_param("i", $evento_id);
$stmt->execute();
$resultDetalhes = $stmt->get_result();
$detalhes = $resultDetalhes->fetch_assoc();

// Upload e atualização para organizador
if ($_SESSION['tipo_usuario'] === 'organizador' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao_detalhada = $_POST['descricao_detalhada'];
    $tipo_inscricao = $_POST['tipo_inscricao'];
    $pagamentos_aceitos = isset($_POST['pagamentos_aceitos']) ? implode(', ', $_POST['pagamentos_aceitos']) : '';
    $ponto_referencia = $_POST['ponto_referencia'];
    $link_maps = $_POST['link_maps'];
    $contato_whatsapp = $_POST['contato_whatsapp'];
    $contato_email = $_POST['contato_email'];
    $redes_sociais = $_POST['redes_sociais'];

    // Monta a programação em JSON
    $programacao_items = [];
    if (isset($_POST['prog_data'])) {
        foreach ($_POST['prog_data'] as $i => $data) {
            $programacao_items[] = [
                'data' => trim($data),
                'hora' => trim($_POST['prog_hora'][$i] ?? ''),
                'atividade' => trim($_POST['prog_atividade'][$i] ?? '')
            ];
        }
    }
    $programacao_json = json_encode($programacao_items, JSON_UNESCAPED_UNICODE);

    // Upload de imagens
    $nomesImagens = [];
    if (!empty($_FILES['imagens']['name'][0])) {
        $pastaDestino = __DIR__ . '/../uploads/';
        if (!is_dir($pastaDestino)) mkdir($pastaDestino, 0755, true);
        foreach ($_FILES['imagens']['tmp_name'] as $key => $tmpName) {
            $nomeArquivo = uniqid() . '_' . basename($_FILES['imagens']['name'][$key]);
            move_uploaded_file($tmpName, $pastaDestino . $nomeArquivo);
            $nomesImagens[] = $nomeArquivo;
        }
    }

    // Se tiver imagens novas, adiciona ao campo existente
    $imagens_existentes = $detalhes['imagens_adicionais'] ?? '';
    $imagens_adicionais = trim($imagens_existentes . ',' . implode(',', $nomesImagens), ',');

    if ($detalhes) {
        $sql = "UPDATE detalhes_evento 
                SET descricao_detalhada=?, tipo_inscricao=?, pagamentos_aceitos=?, 
                    ponto_referencia=?, link_maps=?, imagens_adicionais=?, 
                    contato_whatsapp=?, contato_email=?, redes_sociais=?, programacao=? 
                WHERE evento_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssi", $descricao_detalhada, $tipo_inscricao, $pagamentos_aceitos, 
            $ponto_referencia, $link_maps, $imagens_adicionais, 
            $contato_whatsapp, $contato_email, $redes_sociais, $programacao_json, $evento_id);
        $stmt->execute();
        $mensagem = "Detalhes do evento atualizados com sucesso!";
    } else {
        $sql = "INSERT INTO detalhes_evento 
                (evento_id, descricao_detalhada, tipo_inscricao, pagamentos_aceitos, 
                 ponto_referencia, link_maps, imagens_adicionais, 
                 contato_whatsapp, contato_email, redes_sociais, programacao)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssssss", $evento_id, $descricao_detalhada, $tipo_inscricao, $pagamentos_aceitos,
            $ponto_referencia, $link_maps, $imagens_adicionais, 
            $contato_whatsapp, $contato_email, $redes_sociais, $programacao_json);
        $stmt->execute();
        $mensagem = "Detalhes do evento adicionados com sucesso!";
    }

    // Atualiza dados
    $stmt = $conn->prepare("SELECT * FROM detalhes_evento WHERE evento_id = ?");
    $stmt->bind_param("i", $evento_id);
    $stmt->execute();
    $resultDetalhes = $stmt->get_result();
    $detalhes = $resultDetalhes->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>ConectaRed - <?php echo htmlspecialchars($evento['nome']); ?></title>
<link rel="icon" type="image/png" href="logoaba.png">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script>
function adicionarProgramacao(){
    const container = document.getElementById('programacao-container');
    const item = document.createElement('div');
    item.innerHTML = `
        <div style="display:flex; gap:10px; align-items:center; margin-bottom:8px;">
            <input type="date" name="prog_data[]" style="flex:1; padding:8px; border-radius:10px; border:1px solid #ccc;">
            <input type="time" name="prog_hora[]" style="flex:1; padding:8px; border-radius:10px; border:1px solid #ccc;">
            <input type="text" name="prog_atividade[]" placeholder="Atividade" style="flex:2; padding:8px; border-radius:10px; border:1px solid #ccc;">
            <button type="button" onclick="this.parentElement.remove()" style="background:#e53935; color:white; border:none; border-radius:8px; padding:6px 10px; cursor:pointer;">X</button>
        </div>`;
    container.appendChild(item);
}
</script>
</head>

<body style="margin:0; font-family:'Poppins',sans-serif; background:linear-gradient(180deg,#f7f3fa 0%,#fefcff 100%); color:#333;">

<header style="position:fixed; top:0; left:0; right:0; height:70px; display:flex; align-items:center; justify-content:space-between; background:rgba(123,31,162,0.95); color:white; padding:0 40px; box-shadow:0 8px 25px rgba(0,0,0,0.15); z-index:1000;">
    <div class="logo"><img src="logoindex.png" alt="ConectaRed" style="height:140px;"></div>
    <nav style="display:flex; align-items:center; gap:25px;">
        <a href="index.php" style="color:white; text-decoration:none;"><i class="fa-solid fa-house"></i> Início</a>
        <a href="logout.php" style="padding:6px 15px; border-radius:20px; background:rgba(255,255,255,0.15); color:white;">Sair</a>
    </nav>
</header>

<main style="padding:120px 20px 60px; max-width:1200px; margin:auto;">
    <h2 style="text-align:center; color:#6a1b9a;"><?php echo htmlspecialchars($evento['nome']); ?></h2>

    <?php if($mensagem): ?>
        <p style="text-align:center; color:green; font-weight:600;"><?php echo htmlspecialchars($mensagem); ?></p>
    <?php endif; ?>

<?php if($_SESSION['tipo_usuario']==='organizador'): ?>
<form method="POST" enctype="multipart/form-data" style="background:white; border-radius:20px; padding:25px; box-shadow:0 8px 25px rgba(0,0,0,0.15);">
    <label>Descrição detalhada:</label>
    <textarea name="descricao_detalhada" rows="5" style="width:100%; border-radius:10px; border:1px solid #ccc; padding:10px;"><?php echo htmlspecialchars($detalhes['descricao_detalhada'] ?? ''); ?></textarea>

    <label>Tipo de inscrição:</label>
    <select name="tipo_inscricao" style="padding:10px; width:100%; border-radius:10px; border:1px solid #ccc;">
        <option value="Gratuita" <?php if(($detalhes['tipo_inscricao'] ?? '')==='Gratuita') echo 'selected'; ?>>Gratuita</option>
        <option value="Paga" <?php if(($detalhes['tipo_inscricao'] ?? '')==='Paga') echo 'selected'; ?>>Paga</option>
        <option value="Convite" <?php if(($detalhes['tipo_inscricao'] ?? '')==='Convite') echo 'selected'; ?>>Social</option>
    </select>

    <label>Pagamentos aceitos:</label>
    <div>
        <?php 
        $pagamentos = explode(', ', $detalhes['pagamentos_aceitos'] ?? '');
        foreach(['Pix','Cartão','Boleto'] as $op): ?>
            <label><input type="checkbox" name="pagamentos_aceitos[]" value="<?php echo $op; ?>" <?php if(in_array($op,$pagamentos)) echo 'checked'; ?>> <?php echo $op; ?></label>
        <?php endforeach; ?>
    </div>

    <label>Ponto de referência:</label>
    <input type="text" name="ponto_referencia" value="<?php echo htmlspecialchars($detalhes['ponto_referencia'] ?? ''); ?>" style="width:100%; padding:10px; border-radius:10px; border:1px solid #ccc;">

    <label>Link do Google Maps:</label>
    <input type="text" name="link_maps" value="<?php echo htmlspecialchars($detalhes['link_maps'] ?? ''); ?>" style="width:100%; padding:10px; border-radius:10px; border:1px solid #ccc;">

    <label>Imagens adicionais:</label>
    <input type="file" name="imagens[]" multiple>

    <label>WhatsApp:</label>
    <input type="text" name="contato_whatsapp" value="<?php echo htmlspecialchars($detalhes['contato_whatsapp'] ?? ''); ?>" style="width:100%; padding:10px; border-radius:10px; border:1px solid #ccc;">

    <label>Email:</label>
    <input type="email" name="contato_email" value="<?php echo htmlspecialchars($detalhes['contato_email'] ?? ''); ?>" style="width:100%; padding:10px; border-radius:10px; border:1px solid #ccc;">

    <label>Redes sociais:</label>
    <input type="text" name="redes_sociais" value="<?php echo htmlspecialchars($detalhes['redes_sociais'] ?? ''); ?>" style="width:100%; padding:10px; border-radius:10px; border:1px solid #ccc;">

    <label>Programação:</label>
    <div id="programacao-container" style="margin-bottom:10px;">
        <?php
        $programacao = json_decode($detalhes['programacao'] ?? '[]', true);
        if(!empty($programacao)):
            foreach($programacao as $item): ?>
                <div style="display:flex; gap:10px; align-items:center; margin-bottom:8px;">
                    <input type="date" name="prog_data[]" value="<?php echo htmlspecialchars($item['data']); ?>" style="flex:1; padding:8px; border-radius:10px; border:1px solid #ccc;">
                    <input type="time" name="prog_hora[]" value="<?php echo htmlspecialchars($item['hora']); ?>" style="flex:1; padding:8px; border-radius:10px; border:1px solid #ccc;">
                    <input type="text" name="prog_atividade[]" value="<?php echo htmlspecialchars($item['atividade']); ?>" placeholder="Atividade" style="flex:2; padding:8px; border-radius:10px; border:1px solid #ccc;">
                    <button type="button" onclick="this.parentElement.remove()" style="background:#e53935; color:white; border:none; border-radius:8px; padding:6px 10px;">X</button>
                </div>
            <?php endforeach;
        endif; ?>
    </div>
    <button type="button" onclick="adicionarProgramacao()" style="background:#7b1fa2; color:white; border:none; padding:8px 15px; border-radius:10px; cursor:pointer;">+ Adicionar atividade</button>

    <button type="submit" style="margin-top:20px; padding:12px; width:100%; border:none; border-radius:25px; background:#7b1fa2; color:white; cursor:pointer;">Salvar Detalhes</button>
</form>
<?php endif; ?>
<?php if($_SESSION['tipo_usuario']==='participante'): ?>
    <?php if($detalhes): ?>
        <div style="display:flex; flex-direction:column; gap:15px;">
            <?php
            $info = [
                ['icon'=>'fas fa-align-left','text'=>$detalhes['descricao_detalhada']??''],
                ['icon'=>'fas fa-ticket-alt','text'=>'Tipo de inscrição: '.$detalhes['tipo_inscricao']??''],
                ['icon'=>'fas fa-credit-card','text'=>'Pagamentos: '.$detalhes['pagamentos_aceitos']??''],
                ['icon'=>'fas fa-street-view','text'=>'Ponto de referência: '.$detalhes['ponto_referencia']??''],
                ['icon'=>'fas fa-map','text'=>'<a href="'.$detalhes['link_maps'].'" target="_blank">Ver no mapa</a>'],
                ['icon'=>'fab fa-whatsapp','text'=>$detalhes['contato_whatsapp']??''],
                ['icon'=>'fas fa-envelope','text'=>$detalhes['contato_email']??''],
                ['icon'=>'fas fa-share-alt','text'=>$detalhes['redes_sociais']??'']
            ];

            foreach($info as $item){
                if(!empty(strip_tags($item['text']))){
                    echo '<div style="display:flex; gap:10px; background:#fff; padding:12px; border-radius:12px; box-shadow:0 6px 12px rgba(0,0,0,0.08);">
                        <i class="'.$item['icon'].'" style="color:#7b1fa2;"></i>
                        <div>'.$item['text'].'</div>
                    </div>';
                }
            }

            // Programação JSON
            $programacao = json_decode($detalhes['programacao'] ?? '[]', true);
            if(!empty($programacao)):
                echo '<h3 style="margin-top:20px; color:#4a148c;">Programação</h3>';
                foreach($programacao as $item):
                    echo '<div style="background:#ede7f6; border-left:5px solid #7b1fa2; padding:10px; border-radius:10px; margin-bottom:8px;">
                            <strong>'.$item['atividade'].'</strong><br>
                            Data: '.$item['data'].' | Hora: '.$item['hora'].'
                        </div>';
                endforeach;
            endif;

            // Imagens adicionais
        
if(!empty($detalhes['imagens_adicionais'])):
    $imagens = explode(',', $detalhes['imagens_adicionais']);
?>
<div class="carousel-container" style="position:relative; overflow:hidden; border-radius:12px; margin-top:20px; box-shadow:0 4px 15px rgba(0,0,0,0.2);">
    <div class="carousel-track" style="display:flex; transition:transform 0.4s ease-in-out;">
        <?php foreach($imagens as $img): ?>
            <div class="carousel-slide" style="min-width:100%; flex-shrink:0;">
<img src="../uploads/<?php echo htmlspecialchars($img); ?>" 
     style="width:100%; height:700px; object-fit: cover; display:block; border-radius:12px;">            </div>
        <?php endforeach; ?>
    </div>

    <!-- Setas -->
    <button class="carousel-btn prev" style="position:absolute; top:50%; left:10px; transform:translateY(-50%); background:rgba(0,0,0,0.4); border:none; color:white; padding:20px; font-size:24px; border-radius:50%; cursor:pointer; z-index:10;">
        &#10094;
    </button>
    <button class="carousel-btn next" style="position:absolute; top:50%; right:10px; transform:translateY(-50%); background:rgba(0,0,0,0.4); border:none; color:white; padding:20px; font-size:24px; border-radius:50%; cursor:pointer; z-index:10;">
        &#10095;
    </button>

    <!-- Pontos indicadores -->
    <div class="carousel-dots" style="position:absolute; bottom:10px; left:50%; transform:translateX(-50%); display:flex; gap:6px;">
        <?php foreach($imagens as $index => $img): ?>
            <span class="dot" data-index="<?php echo $index; ?>" style="width:10px; height:10px; background:rgba(255,255,255,0.7); border-radius:50%; display:inline-block; cursor:pointer;"></span>
        <?php endforeach; ?>
    </div>
</div>

<script>
const track = document.querySelector('.carousel-track');
const slides = Array.from(track.children);
const dots = document.querySelectorAll('.dot');
let currentIndex = 0;

function showSlide(index){
    if(index < 0) index = slides.length - 1;
    if(index >= slides.length) index = 0;
    track.style.transform = 'translateX(-'+(100*index)+'%)';
    currentIndex = index;

    dots.forEach(dot => dot.style.background = 'rgba(255,255,255,0.7)');
    dots[currentIndex].style.background = 'white';
}

// Inicializa primeiro ponto ativo
showSlide(0);

// Setas
document.querySelector('.prev').addEventListener('click', () => showSlide(currentIndex-1));
document.querySelector('.next').addEventListener('click', () => showSlide(currentIndex+1));

// Pontos clicáveis
dots.forEach(dot => {
    dot.addEventListener('click', e => showSlide(parseInt(e.target.dataset.index)));
});

// Arrastar com mouse
let startX = 0;
let isDragging = false;

track.addEventListener('mousedown', e => { isDragging = true; startX = e.pageX; });
track.addEventListener('mouseup', e => {
    if(!isDragging) return;
    isDragging = false;
    const diff = e.pageX - startX;
    if(diff > 50) showSlide(currentIndex-1);
    if(diff < -50) showSlide(currentIndex+1);
});
track.addEventListener('mouseleave', () => isDragging = false);
track.addEventListener('mousemove', e => { if(!isDragging) return; });

// Toque para mobile
track.addEventListener('touchstart', e => startX = e.touches[0].clientX);
track.addEventListener('touchend', e => {
    const diff = e.changedTouches[0].clientX - startX;
    if(diff > 50) showSlide(currentIndex-1);
    if(diff < -50) showSlide(currentIndex+1);
});
</script>
<?php endif; ?>
            
        </div>
    <?php else: ?>
        <p>Este evento ainda não possui detalhes adicionais.</p>
    <?php endif; ?>
<?php endif; ?>


<a href="index.php" style="display:block; margin-top:40px; text-align:center; color:#6a1b9a; font-weight:600;">← Voltar</a>
</main>
</body>
</html>
