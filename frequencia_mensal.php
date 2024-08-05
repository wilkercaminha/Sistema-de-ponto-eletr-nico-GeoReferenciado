<?php
// frequencia_mensal.php
session_start();
require 'config.php'; // Arquivo de configuração para conectar ao banco de dados

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obter parâmetros da URL
$nome_usuario = $_GET['nome_usuario'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

// Inicializar variáveis
$registros = [];
$mes_ano = '';

if ($nome_usuario && $data_inicio && $data_fim) {
    // Obter o ID do usuário com base no nome fornecido
    $sql_user = "SELECT id FROM usuarios WHERE nome = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("s", $nome_usuario);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($user_row = $result_user->fetch_assoc()) {
        $user_id = $user_row['id'];

        // Obter os registros para o usuário encontrado e dentro do intervalo de datas
        $sql = "
            SELECT ponto.id, usuarios.nome AS usuario, ponto.entrada, ponto.saida, ponto.latitude, ponto.longitude
            FROM ponto
            JOIN usuarios ON ponto.usuario_id = usuarios.id
            WHERE ponto.usuario_id = ?
            AND (ponto.entrada BETWEEN ? AND ?)
            ORDER BY ponto.entrada DESC
        ";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iss", $user_id, $data_inicio, $data_fim);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $registros = $result->fetch_all(MYSQLI_ASSOC);
            } else {
                echo "Erro ao executar a consulta: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Erro ao preparar a consulta: " . $conn->error;
        }
    } else {
        echo "Usuário não encontrado.";
    }
    $stmt_user->close();

    // Calcular o mês e ano a partir da data inicial
    $data_inicio_formatada = new DateTime($data_inicio);
    $mes_ano = $data_inicio_formatada->format('F Y'); // Nome do mês e ano (ex: August 2024)
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frequência Mensal</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #000;
        }
        .navbar-nav .nav-item .btn {
            margin-left: 10px;
            color: #fff;
        }
        .navbar-nav .nav-item .btn:hover {
            background-color: #6c757d; /* Cinza claro */
            color: #fff;
        }
        .signature-container {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .signature-field {
            border: 1px solid #ddd;
            padding: 10px;
            height: 100px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <center><h2>Frequência Mensal</h2></center>

        <?php if ($mes_ano): ?>
            <p><strong>Mês/Ano:</strong> <?php echo htmlspecialchars($mes_ano); ?></p>
        <?php endif; ?>

        <?php if (!empty($registros)): ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Entrada</th>
                            <th>Saída</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registros as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                                <td><?php echo htmlspecialchars($row['entrada']); ?></td>
                                <td><?php echo htmlspecialchars($row['saida']); ?></td>
                                <td><?php echo htmlspecialchars($row['latitude']); ?></td>
                                <td><?php echo htmlspecialchars($row['longitude']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
           <center> <p>Nenhum registro encontrado!  Realize uma pesquisa com data inicial e final para exibir os registros.</p></center>
        <?php endif; ?>

        <div class="signature-container">
          <br>
          <br>
          <center> _________________________________________________________</center>
           <center> <h4>Assinatura</h4></center>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Fechar a conexão com o banco de dados
$conn->close();
?>
