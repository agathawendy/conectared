<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/database.php';

// Apenas organizadores podem acessar
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'organizador') {
    header('Location: index.php');
    exit;
}

// Verifica se o ID do evento foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id']);
$mensagem = "";

// Busca os dados do evento
$stmt = $conn->prepare("SELECT * FROM eventos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$evento = $result->fetch_assoc();
$stmt->close();

if (!$evento) {
    header('Location: index.php');
    exit;
}

// Lista de categorias
$categorias = [
    "Show / Música ao Vivo",
    "Workshop / Oficinas Criativas",
    "Palestra / Talk Inspirador",
    "Esportes / Atividades Físicas",
    "Culinária / Gastronomia",
    "Artes / Exposições",
    "Cinema / Sessões de Filme",
    "Tecnologia / Hackathons",
    "Educação / Cursos e Treinamentos",
    "Bem-estar / Yoga & Meditação",
    "Networking / Encontros Profissionais",
    "Feiras / Mercados Locais",
    "Aventura / Turismo e Natureza",
    "Jogos / eSports & Competições",
    "Solidariedade / Ações Comunitárias",
    "Cosplay / Eventos Temáticos",
    "Outro"
];

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $data_evento = $_POST['data_evento'];
    $local = trim($_POST['local']);
    $vagas = intval($_POST['vagas']);
    $categoria = trim($_POST['categoria']); // Nova categoria
    $imagem_capa = $evento['imagem_capa']; // Mantém a antiga

    // Se enviou nova imagem
    if (isset($_FILES['imagem_capa']) && $_FILES['imagem_capa']['error'] === UPLOAD_ERR_OK) {
        $arquivoTmp = $_FILES['imagem_capa']['tmp_name'];
        $nomeOriginal = basename($_FILES['imagem_capa']['name']);
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extensao, $extensoesPermitidas)) {
            $mensagem = "❌ Tipo de arquivo não permitido. Envie JPG, PNG, GIF ou WEBP.";
        } elseif ($_FILES['imagem_capa']['size'] > 2 * 1024 * 1024) {
            $mensagem = "❌ O arquivo excede o tamanho máximo de 2MB.";
        } else {
            $novoNome = uniqid('evento_', true) . '.' . $extensao;
            $destino = __DIR__ . '/../public/uploads/' . $novoNome;

            if (move_uploaded_file($arquivoTmp, $destino)) {
                // Apaga a imagem antiga
                if (!empty($imagem_capa) && file_exists(__DIR__ . '/../public/uploads/' . $imagem_capa)) {
                    unlink(__DIR__ . '/../public/uploads/' . $imagem_capa);
                }
                $imagem_capa = $novoNome;
            } else {
                $mensagem = "⚠️ Falha ao salvar o arquivo.";
            }
        }
    }

    // Se não houve erros
    if (empty($mensagem)) {
        $stmtUp = $conn->prepare("UPDATE eventos SET nome = ?, descricao = ?, data_evento = ?, local = ?, vagas = ?, imagem_capa = ?, categoria = ? WHERE id = ?");
$stmtUp->bind_param("ssssissi", $nome, $descricao, $data_evento, $local, $vagas, $imagem_capa, $categoria, $id);

        if ($stmtUp->execute()) {
            $mensagem = "✅ Evento atualizado com sucesso!";
            // Atualiza $evento para exibir os novos valores
            $evento['nome'] = $nome;
            $evento['descricao'] = $descricao;
            $evento['data_evento'] = $data_evento;
            $evento['local'] = $local;
            $evento['vagas'] = $vagas;
            $evento['imagem_capa'] = $imagem_capa;
            $evento['categoria'] = $categoria;
        } else {
            $mensagem = "❌ Erro ao atualizar evento: " . $stmtUp->error;
        }
        $stmtUp->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="logoaba.png">
<title>Editar Evento - ConectaRed</title>
<link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
<script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
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
body { font-family: 'Poppins', sans-serif; background: linear-gradient(180deg, #f7f3fa 0%, #fefcff 100%); margin:0; color:#333; }
.container { max-width: 600px; margin: 100px auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
h2 { color:#6a1b9a; text-align:center; margin-bottom:30px; }
input[type="text"], input[type="number"], input[type="date"], textarea, input[type="file"], select {
    width: 100%; padding:12px; margin:8px 0 16px; border-radius:8px; border:1px solid #ccc; font-family:'Poppins', sans-serif; font-size:14px;
}
button { background-color:#7b1fa2; color:white; border:none; border-radius:25px; padding:12px 25px; font-size:15px; font-weight:500; cursor:pointer; width:100%; transition:all 0.3s ease, box-shadow 0.3s ease; }
button:hover { background-color:#9c27b0; transform: scale(1.05); box-shadow:0 8px 20px rgba(156,39,176,0.5); }
.mensagem { text-align:center; margin-bottom:15px; font-weight:500; }
.voltar { display:block; text-align:center; margin-top:20px; color:#6a1b9a; text-decoration:none; }
.voltar:hover { text-decoration:underline; }
.preview { text-align:center; margin-bottom:15px; }
.preview img { max-width:100%; border-radius:15px; box-shadow:0 4px 12px rgba(0,0,0,0.15); }
</style>
</head>
<body>

<div class="container">
    <h2>Editar Evento</h2>

    <?php if ($mensagem): ?>
        <p class="mensagem"><?php echo $mensagem; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Nome do Evento</label>
        <input type="text" name="nome" required value="<?php echo htmlspecialchars($evento['nome']); ?>">

        <label>Descrição</label>
        <textarea name="descricao" rows="4" required><?php echo htmlspecialchars($evento['descricao']); ?></textarea>

        <label>Data do Evento</label>
        <input type="date" name="data_evento" required value="<?php echo htmlspecialchars($evento['data_evento']); ?>">

        <label>Local</label>
        <input type="text" name="local" required value="<?php echo htmlspecialchars($evento['local']); ?>">

        <label>Vagas</label>
        <input type="number" name="vagas" min="1" required value="<?php echo htmlspecialchars($evento['vagas']); ?>">

        <label>Categoria</label>
        <select name="categoria" required>
            <?php foreach($categorias as $cat): ?>
                <option value="<?php echo $cat; ?>" <?php echo ($evento['categoria'] === $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
            <?php endforeach; ?>
        </select>

        <label>Imagem de Capa</label>
        <input type="file" name="imagem_capa" accept="image/*" onchange="previewImagem(event)">

        <div class="preview" id="preview">
            <?php if(!empty($evento['imagem_capa'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($evento['imagem_capa']); ?>" alt="Imagem atual">
            <?php endif; ?>
        </div>

        <button type="submit">Atualizar Evento</button>
    </form>

    <a href="index.php" class="voltar">← Voltar para a lista</a>
</div>

<script>
function previewImagem(event) {
    const preview = document.getElementById('preview');
    const arquivo = event.target.files[0];

    if (arquivo) {
        const leitor = new FileReader();
        leitor.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Prévia da imagem">`;
        }
        leitor.readAsDataURL(arquivo);
    } else {
        preview.innerHTML = '';
    }
}
</script>

</body>
</html>
