<?php
// save_record.php
session_start();
require 'config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;
$action = $_POST['action'] ?? null;

if ($action !== 'entrada' && $action !== 'saida') {
    die("Ação inválida.");
}

// Verificar se a latitude e longitude estão definidos
if ($latitude === null || $longitude === null) {
    die("Latitude e longitude são necessários.");
}

// Preparar o SQL para inserir ou atualizar o ponto
if ($action === 'entrada') {
    // Registrar a entrada
    $sql = "INSERT INTO ponto (usuario_id, entrada, latitude, longitude) VALUES (?, NOW(), ?, ?)";
} else {
    // Atualizar a saída
    $sql = "UPDATE ponto SET saida = NOW(), latitude = ?, longitude = ? WHERE usuario_id = ? AND saida IS NULL";
}

$stmt = $conn->prepare($sql);

if ($action === 'entrada') {
    $stmt->bind_param("idd", $usuario_id, $latitude, $longitude);
} else {
    $stmt->bind_param("ddi", $latitude, $longitude, $usuario_id);
}

if ($stmt->execute()) {
    header("Location: ponto_admin.php");
} else {
    echo "Erro ao salvar o registro: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
