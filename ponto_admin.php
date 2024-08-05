<?php
session_start();
require 'config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obter registros para o usuário logado, incluindo o nome do usuário
$sql = "
    SELECT ponto.id, usuarios.nome AS usuario, ponto.entrada, ponto.almoco_saida, ponto.almoco_retorno, ponto.saida, ponto.latitude, ponto.longitude
    FROM ponto
    JOIN usuarios ON ponto.usuario_id = usuarios.id
    WHERE ponto.usuario_id = ?
";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        echo "<p>Erro ao executar a consulta: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Erro ao preparar a consulta: " . $conn->error . "</p>";
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Ponto</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
        #map {
            height: 400px;
            width: 100%;
            display: none;
        }
         .btn-home {
            background-color: #2F4F4F; /* Cor do WhatsApp */
            border-color: #2F4F4F;
            color: #fff;
        }
        .btn-home:hover {
            background-color: #1EBE6F; /* Cor um pouco mais escura para hover */
            border-color: #1EBE6F;
            color: #fff;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#">Ponto Sys</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="btn btn-home" href="administrador.php"><i class="fas fa-home"></i> Home</a>
                </li>
                <li class="nav-item">
                    <button class="btn btn-success" onclick="saveRecord('entrada')">Entrada</button>
                </li>
                <li class="nav-item">
                    <button class="btn btn-warning" onclick="saveRecord('almoco_saida')">Saída para Almoço</button>
                </li>
                <li class="nav-item">
                    <button class="btn btn-info" onclick="saveRecord('almoco_retorno')">Retorno do Almoço</button>
                </li>
                <li class="nav-item">
                    <button class="btn btn-danger" onclick="saveRecord('saida')">Saída</button>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary" href="registro_ponto.php"><i class="fas fa-file-alt"></i> Relatório</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-secondary" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Registros de Ponto</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuário</th>
                        <th>Entrada</th>
                        <th>Saída para Almoço</th>
                        <th>Retorno do Almoço</th>
                        <th>Saída</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($result)): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                                <td><?php echo htmlspecialchars($row['entrada']); ?></td>
                                <td><?php echo htmlspecialchars($row['almoco_saida']); ?></td>
                                <td><?php echo htmlspecialchars($row['almoco_retorno']); ?></td>
                                <td><?php echo htmlspecialchars($row['saida']); ?></td>
                                <td><?php echo htmlspecialchars($row['latitude']); ?></td>
                                <td><?php echo htmlspecialchars($row['longitude']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">Nenhum registro encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div id="map"></div>
    </div>

    <!-- Modal de Confirmação -->
    <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Registro Efetuado</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Seu registro foi salvo com sucesso.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        function saveRecord(action) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const formData = new FormData();
                    formData.append('latitude', position.coords.latitude);
                    formData.append('longitude', position.coords.longitude);
                    formData.append('action', action);

                    $.ajax({
                        type: 'POST',
                        url: 'save_record.php',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('#successModal').modal('show');
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                            alert('Ocorreu um erro ao salvar o registro.');
                        }
                    });
                });
            } else {
                alert("Geolocalização não é suportada por este navegador.");
            }
        }

        $('#successModal').on('hidden.bs.modal', function (e) {
            location.reload(); // Recarregar a página após fechar o modal
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
