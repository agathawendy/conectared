<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id_evento'])) {
    $id_evento = $_GET['id_evento'];
    $usuario_id = $_SESSION['user_id'];

    $sql = "DELETE FROM inscricoes WHERE evento_id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_evento, $usuario_id);

    if ($stmt->execute()) {
        header("Location: minhas_inscricoes.php?msg=cancelado");
        exit;
    } else {
        echo "Erro ao cancelar inscrição.";
    }
} else {
    echo "Evento não especificado.";
}
?>
