<?php
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['latitude'], $_POST['longitude'], $_POST['action'])) {
        $usuario_id = $_SESSION['usuario_id'];
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
        $action = $_POST['action'];

        // Determinar o campo de data/hora correspondente ao tipo de evento
        $dateField = '';
        switch ($action) {
            case 'entrada':
                $dateField = 'entrada';
                break;
            case 'saida':
                $dateField = 'saida';
                break;
            case 'almoco_saida':
                $dateField = 'almoco_saida';
                break;
            case 'almoco_retorno':
                $dateField = 'almoco_retorno';
                break;
            default:
                echo "Tipo de evento inválido.";
                exit();
        }

        // Verificar se já existe um registro com o mesmo tipo de evento
        $sql = "SELECT id FROM ponto WHERE usuario_id = ? AND $dateField IS NULL";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $stmt->store_result();
            $rowCount = $stmt->num_rows;

            if ($rowCount > 0) {
                // Atualizar o registro existente
                $updateSql = "UPDATE ponto SET $dateField = NOW(), latitude = ?, longitude = ? WHERE usuario_id = ? AND $dateField IS NULL";
                $updateStmt = $conn->prepare($updateSql);

                if ($updateStmt) {
                    $updateStmt->bind_param("ddi", $latitude, $longitude, $usuario_id);
                    if ($updateStmt->execute()) {
                        echo "Registro atualizado com sucesso.";
                    } else {
                        echo "Erro ao atualizar o registro: " . $conn->error;
                    }
                } else {
                    echo "Erro ao preparar a atualização: " . $conn->error;
                }

                $updateStmt->close();
            } else {
                // Inserir um novo registro
                $insertSql = "INSERT INTO ponto (usuario_id, $dateField, latitude, longitude) VALUES (?, NOW(), ?, ?)";
                $insertStmt = $conn->prepare($insertSql);

                if ($insertStmt) {
                    $insertStmt->bind_param("idd", $usuario_id, $latitude, $longitude);
                    if ($insertStmt->execute()) {
                        echo "Registro inserido com sucesso.";
                    } else {
                        echo "Erro ao inserir o registro: " . $conn->error;
                    }
                } else {
                    echo "Erro ao preparar a inserção: " . $conn->error;
                }

                $insertStmt->close();
            }
        } else {
            echo "Erro ao preparar a consulta: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Parâmetros incompletos.";
    }
} else {
    echo "Método de requisição inválido.";
}

$conn->close();
?>
