<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/database.php';

$erro = "";
$input_erro = false;

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if($user && password_verify($senha, $user['senha'])){
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['tipo_usuario'] = $user['tipo'];
        header('Location: index.php');
        exit;
    } else {
        $erro = "Email ou senha inválidos!";
        $input_erro = true;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="logoaba.png">
    <meta charset="UTF-8">
    <title>Login ConectaRed</title>
    <!-- MDL -->
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <style>
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #242021, #521a74);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Card central com animação e hover */
        .card-login {
            background-color: #fff;
            border-radius: 15px;
            padding: 40px 30px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 12px 30px rgba(0,0,0,0.4);
            animation: zoomIn 0.6s ease forwards;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-login:hover {
            transform: scale(1.02);
            box-shadow: 0 18px 40px rgba(0,0,0,0.5);
        }
        @keyframes zoomIn {
            0% {opacity: 0; transform: scale(0.8);}
            100% {opacity: 1; transform: scale(1);}
        }

        /* Logo maior */
        .logo {
            display: block;
            margin: 0 auto 35px auto;
            width: 220px;
            height: auto;
            transition: transform 0.4s ease;
        }
        .logo:hover { transform: scale(1.1); }

        /* Inputs com ícones e foco animado */
        .input-wrapper {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #521a74;
            transition: border-color 0.3s ease;
            padding: 5px 0;
        }
        .input-wrapper .material-icons {
            color: #521a74;
            margin-right: 10px;
            transition: color 0.3s ease;
        }
        .input-wrapper input {
            border: none;
            width: 100%;
            padding: 8px 0;
            font-size: 16px;
            color: #242021;
            outline: none;
            transition: border-bottom 0.3s ease;
        }
        .input-wrapper input::placeholder { color: #242021; transition: color 0.3s ease; }
        .input-wrapper input:focus {
            border-bottom: 2px solid #7a28a0;
        }
        .input-wrapper input:focus + .material-icons {
            color: #7a28a0;
        }
        .erro-input { border-bottom: 2px solid red !important; }

        /* Botão roxo premium compatível com MDL */
        .btn-roxa.mdl-button--colored {
            background: linear-gradient(145deg, #521a74, #7a28a0) !important;
            color: #fff !important;
            border-radius: 12px !important;
            box-shadow: 0 6px 15px rgba(0,0,0,0.3) !important;
            transition: all 0.3s ease, transform 0.2s ease !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2) !important;
        }
        .btn-roxa.mdl-button--colored:hover {
            background: linear-gradient(145deg, #7a28a0, #9f4dd1) !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.4) !important;
            transform: translateY(-3px) scale(1.02) !important;
        }

        /* Mensagem de erro */
        .erro-msg {
            color: red;
            text-align: center;
            margin-bottom: 15px;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn { 0% {opacity: 0;} 100% {opacity: 1;} }

        /* Link cadastro */
        p { text-align: center; margin-top: 15px; }
        a { color: #521a74; text-decoration: none; font-weight: bold; transition: color 0.3s ease; }
        a:hover { color: #7a28a0; text-decoration: underline; }

    </style>
</head>
<body>

    <div class="mdl-card mdl-shadow--2dp card-login">
        <!-- Logo -->
        <img src="logo.png" alt="ConectaRed Logo" class="logo" width="100%">
<h3 style="text-align:left; color:#521a74; font-family: 'Poppins', sans-serif; font-weight:500; margin-bottom:20px;">
    Entrar na conta
</h3>


        <?php if($erro) echo "<p class='erro-msg'>$erro</p>"; ?>

        <form method="POST" action="">
            <div class="input-wrapper <?php if($input_erro) echo 'erro-input'; ?>">
                <i class="material-icons">email</i>
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <div class="input-wrapper <?php if($input_erro) echo 'erro-input'; ?>">
                <i class="material-icons">lock</i>
                <input type="password" name="senha" placeholder="Senha" required>
            </div>

            
            <button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored btn-roxa" type="submit">
                Entrar
            </button>
        </form>
        <p>Não tem conta? <a href="cadastro.php">Cadastre-se</a></p>
    </div>

</body>
</html>
