<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'organizador'){
    header("Location: index.php");
    exit;
}

$organizador_id = $_SESSION['user_id'];
$evento_id = isset($_GET['evento_id']) ? intval($_GET['evento_id']) : null;

// Pega apenas o evento do organizador
$stmt = $conn->prepare("SELECT * FROM eventos WHERE organizador_id = ? AND id = ?");
$stmt->bind_param("ii", $organizador_id, $evento_id);
$stmt->execute();
$eventos_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="logoaba.png">
<title>Inscritos do Evento - ConectaRed</title>
<link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
<script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
<style>
body { font-family:'Poppins', sans-serif; background:#f7f3fa; padding:50px; }
.mdl-card { padding:20px; border-radius:15px; background:white; margin-bottom:20px; transition: 0.3s; }
.mdl-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(0,0,0,0.2); }
.botao-voltar { display:inline-block; padding:10px 20px; border-radius:20px; background-color:#7b1fa2; color:white; text-decoration:none; margin-bottom:20px; }
.botao-voltar:hover { background-color:#9c27b0; }
</style>
</head>
<body>

<a href="index.php" class="botao-voltar">← Voltar</a>

<?php if($eventos_result->num_rows > 0): ?>
    <?php while($evento = $eventos_result->fetch_assoc()): ?>
        <h2><?php echo htmlspecialchars($evento['nome']); ?></h2>
        <p><strong>Data:</strong> <?php echo htmlspecialchars($evento['data_evento']); ?> | <strong>Local:</strong> <?php echo htmlspecialchars($evento['local']); ?></p>

        <?php
        $stmt2 = $conn->prepare("SELECT u.nome, u.email FROM inscricoes i JOIN usuarios u ON i.usuario_id = u.id WHERE i.evento_id = ?");
        $stmt2->bind_param("i", $evento['id']);
        $stmt2->execute();
        $inscritos = $stmt2->get_result();
        ?>

        <?php if($inscritos->num_rows > 0): ?>
            <?php while($user = $inscritos->fetch_assoc()): ?>
                <div class="mdl-card">
                    <h4><?php echo htmlspecialchars($user['nome']); ?></h4>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhum inscrito ainda.</p>
        <?php endif; ?>
    <?php endwhile; ?>
<?php else: ?>
    <p>Nenhum evento encontrado.</p>
<?php endif; ?>

</body>
</html>
