<?php
session_start();
require_once '../config/database.php';

$erro = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $tipo = $_POST['tipo']; // Agora o tipo vem do formulário

    // Verifica se email já existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0){
        $erro = "Email já cadastrado!";
    } else {
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nome, $email, $senha, $tipo);
        if($stmt->execute()){
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['tipo_usuario'] = $tipo;
            header("Location: login.php");
            exit;
        } else {
            $erro = "Erro ao cadastrar, tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="logoaba.png">
<title>Cadastro ConectaRed</title>
<link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
<script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #2e1f2b, #7a28a0);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.card-cadastro {
    background-color: #fff;
    border-radius: 15px;
    padding: 40px 30px;
    max-width: 420px;
    width: 100%;
    box-shadow: 0 12px 30px rgba(0,0,0,0.4);
    animation: cardSlideFade 0.6s ease forwards;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card-cadastro:hover {
    transform: scale(1.02);
    box-shadow: 0 18px 40px rgba(0,0,0,0.5);
}
@keyframes cardSlideFade {
    0% {opacity: 0; transform: translateY(30px);}
    100% {opacity: 1; transform: translateY(0);}
}

.logo {
    display: block;
    margin: 0 auto 20px auto;
    width: 200px;
    height: auto;
    transition: transform 0.4s ease;
}
.logo:hover { transform: scale(1.1); }

h3 {
    text-align: left;
    color: #521a74;
    font-weight: 500;
    margin-bottom: 20px;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 2px solid #521a74;
    padding: 5px 0;
    transition: border-color 0.3s ease;
}
.input-wrapper .material-icons {
    color: #521a74;
    margin-right: 10px;
    transition: color 0.3s ease, transform 0.3s ease;
}
.input-wrapper input, .input-wrapper select {
    border: none;
    width: 100%;
    padding: 8px 0;
    font-size: 16px;
    color: #242021;
    outline: none;
    background: transparent;
}

.input-wrapper input::placeholder {
    color: #242021;
    opacity: 0.7;
}

.btn-roxa.mdl-button--colored {
    background: linear-gradient(145deg, #521a74, #8e3cd8) !important;
    color: #fff !important;
    border-radius: 12px !important;
    box-shadow: 0 6px 15px rgba(0,0,0,0.3) !important;
    transition: all 0.3s ease, transform 0.2s ease !important;
}
.btn-roxa.mdl-button--colored:hover {
    background: linear-gradient(145deg, #7a28a0, #b066f2) !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.4) !important;
    transform: translateY(-3px) scale(1.02) !important;
}

.erro-msg {
    color: red;
    text-align: center;
    margin-bottom: 15px;
    animation: fadeIn 0.5s ease;
}
@keyframes fadeIn { 0% {opacity: 0;} 100% {opacity: 1;} }

p { text-align: center; margin-top: 15px; }
a { color: #521a74; text-decoration: none; font-weight: bold; transition: color 0.3s ease; }
a:hover { color: #7a28a0; text-decoration: underline; }
</style>
</head>
<body>

<div class="mdl-card mdl-shadow--2dp card-cadastro">
    <img src="logo.png" alt="ConectaRed Logo" class="logo">
    <h3>Criar conta</h3>

    <?php if($erro) echo "<p class='erro-msg'>$erro</p>"; ?>

    <form method="POST" action="">
        <div class="input-wrapper">
            <i class="material-icons">person_add</i>
            <input type="text" name="nome" placeholder="Nome" required>
        </div>

        <div class="input-wrapper">
            <i class="material-icons">email</i>
            <input type="email" name="email" placeholder="Email" required>
        </div>

        <div class="input-wrapper">
            <i class="material-icons">lock</i>
            <input type="password" name="senha" placeholder="Senha" required>
        </div>

        <!-- NOVO CAMPO: Tipo de usuário -->
        <div class="input-wrapper">
            <i class="material-icons">group</i>
            <select name="tipo" required>
                <option value="">Selecione o tipo de usuário</option>
                <option value="participante">Participante</option>
                <option value="organizador">Organizador</option>
            </select>
        </div>

        <br>
        <button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored btn-roxa" type="submit">
            Cadastrar
        </button>
    </form>

    <p>Já tem conta? <a href="login.php">Entre</a></p>
</div>

</body>
</html>
