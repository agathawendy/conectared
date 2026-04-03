<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/database.php';

// Apenas organizadores podem excluir
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'organizador') {
    header('Location: index.php');
    exit;
}

// Verifica se o ID foi enviado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id']);

// Busca evento para deletar a imagem
$stmt = $conn->prepare("SELECT imagem_capa FROM eventos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$evento = $result->fetch_assoc();
$stmt->close();

if ($evento) {
    // Deleta imagem da pasta uploads, se existir
    if (!empty($evento['imagem_capa']) && file_exists(__DIR__ . '/../public/uploads/' . $evento['imagem_capa'])) {
        unlink(__DIR__ . '/../public/uploads/' . $evento['imagem_capa']);
    }

    // Deleta evento do banco
    $stmtDel = $conn->prepare("DELETE FROM eventos WHERE id = ?");
    $stmtDel->bind_param("i", $id);
    $stmtDel->execute();
    $stmtDel->close();
}

header('Location: index.php');
exit;
