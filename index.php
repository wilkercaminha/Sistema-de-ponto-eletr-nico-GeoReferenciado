<?php
// login.php
session_start();
require 'config.php';

// Mensagem de erro
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = htmlspecialchars(trim($_POST['nome']));
    $senha = htmlspecialchars(trim($_POST['senha']));

    $sql = $conn->prepare("SELECT id, senha, tipo FROM usuarios WHERE nome = ?");
    $sql->bind_param("s", $nome);
    $sql->execute();
    $result = $sql->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($senha, $row['senha'])) {
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_tipo'] = $row['tipo'];

            if ($row['tipo'] == 'admin') {
                header("Location: administrador.php");
            } else {
                header("Location: ponto.php");
            }
            exit();
        } else {
            $error = "Nome de usuário ou senha incorretos.";
        }
    } else {
        $error = "Nome de usuário ou senha incorretos.";
    }

    $sql->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        .login-container h2 {
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }
        .login-container .btn-primary {
            background-color: #25D366; /* Cor do WhatsApp */
            border-color: #25D366;
        }
        .login-container .btn-primary:hover {
            background-color: #1EBE6F; /* Cor um pouco mais escura para hover */
            border-color: #1EBE6F;
        }
        .login-container .btn-link {
            color: #007bff;
        }
        .login-container .btn-link:hover {
            color: #0056b3;
        }
        .alert {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <center><img src="logo.png" height="150px" width="150px"><center>
       <center> <h2>Login</h2></center>
       
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
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
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>
        <a href="register.php" class="btn btn-link btn-block mt-3">Cadastrar-se</a>
    </div>
</body>
</html>
