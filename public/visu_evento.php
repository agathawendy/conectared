<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['id'])) {
    die('ID do evento não fornecido.');
}

$evento_id = intval($_GET['id']);

// Buscar dados do evento
$stmt = $conn->prepare("SELECT * FROM eventos WHERE id = ?");
$stmt->bind_param("i", $evento_id);
$stmt->execute();
$result = $stmt->get_result();
$evento = $result->fetch_assoc();

if (!$evento) {
    die('Evento não encontrado.');
}

// Buscar detalhes do evento
$stmt2 = $conn->prepare("SELECT * FROM detalhes_evento WHERE evento_id = ?");
$stmt2->bind_param("i", $evento_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$detalhes = $result2->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($evento['nome']); ?> - Detalhes</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
body { font-family:'Poppins',sans-serif; background:linear-gradient(180deg,#f7f3fa 0%,#fefcff 100%); color:#333; margin:0; }
.container { max-width:900px; margin:80px auto 50px; background:white; padding:40px; border-radius:25px; box-shadow:0 8px 25px rgba(0,0,0,0.15); }
h2 { color:#6a1b9a; text-align:center; margin-bottom:20px; font-weight:600; }
.section { margin-bottom:30px; }
.section h3 { color:#4a148c; margin-bottom:15px; }
p, li { font-size:15px; line-height:1.6em; }
ul { padding-left:20px; }
img { max-width:100%; border-radius:15px; margin-bottom:15px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }

.botoes { display:flex; justify-content:center; margin-top:20px; flex-wrap:wrap; gap:15px; }
.botoes a { background:#7b1fa2; color:white; text-decoration:none; padding:12px 25px; border-radius:25px; transition:all 0.3s ease; font-weight:500; }
.botoes a:hover { background:#9c27b0; transform:scale(1.05); }

.galeria { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:15px; margin-top:15px; }
.galeria img { width:100%; border-radius:15px; box-shadow:0 4px 15px rgba(0,0,0,0.2); }

.info-card { background:#f3e5f5; padding:15px; border-radius:15px; margin-bottom:10px; }
.info-card strong { color:#4a148c; }
</style>
</head>
<body>

<div class="container">
<h2><?php echo htmlspecialchars($evento['nome']); ?></h2>

<?php if(!empty($evento['imagem_capa'])): ?>
<img src="uploads/<?php echo htmlspecialchars($evento['imagem_capa']); ?>" alt="Imagem do evento">
<?php endif; ?>

<div class="section">
    <h3>Descrição</h3>
    <p><?php echo nl2br(htmlspecialchars($evento['descricao'])); ?></p>
</div>

<?php if($detalhes): ?>
<div class="section">
    <h3>Detalhes do Evento</h3>
    
    <?php if(!empty($detalhes['descricao_detalhada'])): ?>
    <p><?php echo nl2br(htmlspecialchars($detalhes['descricao_detalhada'])); ?></p>
    <?php endif; ?>

    <?php
    $campos = [
        'Tipo de inscrição' => $detalhes['tipo_inscricao'] ?? '',
        'Pagamentos aceitos' => $detalhes['pagamentos_aceitos'] ?? '',
        'Endereço' => $detalhes['endereco'] ?? '',
        'Ponto de referência' => $detalhes['ponto_referencia'] ?? '',
        'Link no Maps' => !empty($detalhes['link_maps']) ? "<a href='".htmlspecialchars($detalhes['link_maps'])."' target='_blank'>Abrir no Google Maps</a>" : '',
        'WhatsApp' => $detalhes['contato_whatsapp'] ?? '',
        'Email' => $detalhes['contato_email'] ?? '',
        'Redes sociais' => $detalhes['redes_sociais'] ?? ''
    ];
    foreach($campos as $titulo => $valor){
        if(!empty($valor)){
            echo "<div class='info-card'><strong>$titulo:</strong> $valor</div>";
        }
    }
    ?>
</div>

<?php if(!empty($detalhes['programacao'])): ?>
<div class="section">
    <h3>Programação</h3>
    <p><?php echo nl2br(htmlspecialchars($detalhes['programacao'])); ?></p>
</div>
<?php endif; ?>

<?php if(!empty($detalhes['imagens_adicionais'])): 
$imagens = explode(',', $detalhes['imagens_adicionais']);
?>
<div class="section">
    <h3>Galeria</h3>
    <div class="galeria">
    <?php foreach($imagens as $img): ?>
        <img src="uploads/<?php echo htmlspecialchars($img); ?>" alt="Imagem adicional">
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<div class="botoes">
    <a href="index.php"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
</div>
</div>

</body>
</html>
