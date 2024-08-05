<?php
// register.php
session_start();
require 'config.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = htmlspecialchars(trim($_POST['nome']));
    $senha = htmlspecialchars(trim($_POST['senha']));
    $tipo = htmlspecialchars(trim($_POST['tipo']));

    if ($senha !== $_POST['confirmar_senha']) {
        $error = "As senhas não coincidem.";
    } else {
        $hashed_password = password_hash($senha, PASSWORD_DEFAULT);

        $sql = $conn->prepare("INSERT INTO usuarios (nome, senha, tipo) VALUES (?, ?, ?)");
        $sql->bind_param("sss", $nome, $hashed_password, $tipo);

        if ($sql->execute()) {
            $success = "Cadastro realizado com sucesso. Você pode <a href='index.php'>fazer login</a>.";
        } else {
            $error = "Erro: " . $sql->error;
        }

        $sql->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .register-container {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        .register-container h2 {
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }
        .register-container .btn-primary {
            background-color: #25D366; /* Cor do WhatsApp */
            border-color: #25D366;
        }
        .register-container .btn-primary:hover {
            background-color: #1EBE6F; /* Cor um pouco mais escura para hover */
            border-color: #1EBE6F;
        }
        .register-container .btn-link {
            color: #007bff;
        }
        .register-container .btn-link:hover {
            color: #0056b3;
        }
        .alert {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="register-container">
         <center><img src="logo.png" height="150px" width="150px"><center>
        <center><h2>Cadastro</h2></center>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="nome">Nome de Usuário:</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha:</label>
                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
            </div>
            <div class="form-group">
                <label for="tipo">Tipo:</label>
                <select class="form-control" id="tipo" name="tipo" required>
                    <option value="usuario">Usuário</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Cadastrar</button>
        </form>
        <a href="index.php" class="btn btn-link btn-block mt-3">Já possui uma conta? Faça login</a>
    </div>
</body>
</html>
