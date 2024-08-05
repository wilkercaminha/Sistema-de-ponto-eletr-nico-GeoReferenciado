<?php
// config.php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ponto";

// Criação da conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificação da conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
?>
