<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Verifica se o usuário está logado e é participante
if(!isset($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'participante'){
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Busca eventos em que o usuário está inscrito
$sql = "SELECT e.* 
        FROM inscricoes i
        JOIN eventos e ON i.evento_id = e.id
        WHERE i.usuario_id = ?
        ORDER BY e.data_evento ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="logoaba.png">
<title>Minhas Inscrições - ConectaRed</title>
<link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
<script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body { margin:0; font-family:'Poppins', sans-serif; background:linear-gradient(180deg,#f7f3fa 0%,#fefcff 100%); color:#333; }
.top-bar { position:fixed; top:0; left:0; right:0; height:70px; display:flex; align-items:center; justify-content:space-between; background:rgba(123,31,162,0.95); color:white; padding:0 40px; box-shadow:0 8px 25px rgba(0,0,0,0.15); z-index:1000; }
.nav-links a { color:white; text-decoration:none; margin-left:25px; font-weight:500; }
.nav-links a:hover { text-decoration:underline; }
main { padding:100px 20px 60px; max-width:1000px; margin:auto; }
.evento-card { background:white; border-radius:15px; padding:20px; margin-bottom:20px; box-shadow:0 4px 12px rgba(0,0,0,0.1); display:flex; align-items:center; gap:20px; }
.evento-card img { width:150px; height:100px; border-radius:10px; object-fit:cover; }
footer { background:linear-gradient(90deg,#7b1fa2,#4a148c); color:white; text-align:center; padding:20px; margin-top:50px; }
.botao-voltar { display:inline-block; padding:10px 20px; border-radius:20px; background-color:#7b1fa2; color:white; text-decoration:none; margin-bottom:20px; transition:0.3s; }
.botao-voltar:hover { background-color:#9c27b0; }
</style>
</head>

<body>
<header class="top-bar">
   <div class="logo"><img src="logoindex.png" alt="ConectaRed" style="height:140px; width:auto;"></div>
   <nav class="nav-links">
      <a href="index.php">Início</a>
      <a href="minhas_inscricoes.php">Minhas Inscrições</a>
      <a href="logout.php" class="logout">Sair</a>
   </nav>
</header>

<main>
    <a href="index.php" class="botao-voltar"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
    <h2>Minhas Inscrições</h2>

    <?php if($result->num_rows > 0): ?>
        <?php while($evento = $result->fetch_assoc()): ?>
           <div class="evento-card">
    <img src="uploads/<?php echo htmlspecialchars($evento['imagem_capa'] ?? 'default.jpg'); ?>" alt="Capa do evento">
    <div style="flex:1;">
        <h3 style="color:#6a1b9a;"><?php echo htmlspecialchars($evento['nome']); ?></h3>
        <p><strong>Data:</strong> <?php echo htmlspecialchars($evento['data_evento']); ?></p>
        <p><strong>Local:</strong> <?php echo htmlspecialchars($evento['local']); ?></p>
        <p><?php echo htmlspecialchars($evento['descricao']); ?></p>

        <!-- Botão de cancelar inscrição -->
        <a href="cancelar_inscricao.php?id_evento=<?php echo $evento['id']; ?>"
           onclick="return confirm('Tem certeza que deseja cancelar sua inscrição neste evento?');"
           class="btn-cancelar">
           Cancelar inscrição
        </a>
    </div>
</div>

        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center; color:#6a1b9a;">Você ainda não está inscrito em nenhum evento.</p>
    <?php endif; ?>
</main>

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