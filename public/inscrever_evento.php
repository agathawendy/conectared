<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Verifica se o usuário está logado
if(!isset($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'participante'){
    $_SESSION['mensagem'] = "Você precisa estar logado como participante para se inscrever.";
    header("Location: index.php");
    exit;
}

// Verifica se o evento foi enviado
if(!isset($_GET['evento_id'])){
    $_SESSION['mensagem'] = "Evento inválido.";
    header("Location: index.php");
    exit;
}

$evento_id = intval($_GET['evento_id']);
$usuario_id = intval($_SESSION['user_id']); // vem do login

// Verifica se o usuário já está inscrito
$sql = "SELECT * FROM inscricoes WHERE usuario_id = ? AND evento_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $usuario_id, $evento_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $_SESSION['mensagem'] = "Você já está inscrito neste evento.";
    header("Location: index.php");
    exit;
}

// Faz a inscrição
$sql = "INSERT INTO inscricoes (usuario_id, evento_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $usuario_id, $evento_id);

if($stmt->execute()){
    $_SESSION['mensagem'] = "Inscrição realizada com sucesso!";
} else {
    $_SESSION['mensagem'] = "Erro ao realizar inscrição.";
}

header("Location: index.php");
exit;
?>
