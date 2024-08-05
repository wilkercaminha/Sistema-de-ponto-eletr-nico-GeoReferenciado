<?php
session_start();
require 'config.php'; // Arquivo de configuração para conectar ao banco de dados

// Verificar se o usuário está logado e se é um admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Obter dados do formulário
$user_id = $_POST['user_id'] ?? '';
$nome = $_POST['nome'] ?? '';
$senha = $_POST['senha'] ?? '';
$confirm_senha = $_POST['confirm_senha'] ?? '';
$tipo = $_POST['tipo'] ?? '';

// Validar dados
if (!$user_id || !$nome || !$tipo) {
    header("Location: usuarios_admin.php?status=fail&message=Dados obrigatórios não fornecidos.");
    exit();
}

if ($senha && $senha !== $confirm_senha) {
    header("Location: usuarios_admin.php?status=fail&message=Senhas não coincidem.");
    exit();
}

// Verificar se o nome de usuário já existe
$sql_check = "SELECT id FROM usuarios WHERE nome = ? AND id != ?";
if ($stmt_check = $conn->prepare($sql_check)) {
    $stmt_check->bind_param('si', $nome, $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $stmt_check->close();
        header("Location: usuarios_admin.php?status=fail&message=Nome de usuário já está em uso.");
        exit();
    }
    $stmt_check->close();
}

// Preparar consulta para atualizar o usuário
$sql = "UPDATE usuarios SET nome = ?, tipo = ?";
$params = [$nome, $tipo];

if ($senha) {
    // Hash da nova senha
    $hashed_senha = password_hash($senha, PASSWORD_DEFAULT);
    $sql .= ", senha = ?";
    $params[] = $hashed_senha;
}

$sql .= " WHERE id = ?";

// Atualizar o banco de dados
if ($stmt = $conn->prepare($sql)) {
    // Adiciona o ID do usuário no final dos parâmetros
    $params[] = $user_id;

    // Determinar o tipo de dados dos parâmetros
    $types = str_repeat('s', count($params) - 1) . 'i';

    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        header("Location: usuarios_admin.php?status=success&message=Usuário atualizado com sucesso.");
    } else {
        header("Location: usuarios_admin.php?status=fail&message=Erro ao atualizar o usuário: " . $stmt->error);
    }
    $stmt->close();
} else {
    header("Location: usuarios_admin.php?status=fail&message=Erro ao preparar a consulta: " . $conn->error);
}

// Fechar a conexão com o banco de dados
$conn->close();

// Redirecionar para a página de administração de usuários
exit();
?>
