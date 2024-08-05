<?php
// registro_ponto.php
session_start();
require 'config.php'; // Arquivo de configuração para conectar ao banco de dados

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obter registros apenas para o usuário logado
$sql = "
    SELECT 
        ponto.id, 
        usuarios.nome AS usuario, 
        ponto.entrada, 
        ponto.saida, 
        ponto.latitude, 
        ponto.longitude,
        ponto.almoco_saida,
        ponto.almoco_retorno
    FROM ponto
    JOIN usuarios ON ponto.usuario_id = usuarios.id
    WHERE ponto.usuario_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Ponto</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet"> <!-- FontAwesome for icons -->
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
        .btn-map {
            transition: background-color 0.3s;
        }
        .btn-map.open {
            background-color: red;
            color: white;
        }
        .btn-map.closed {
            background-color: #17a2b8;
            color: white;
        }
        @media (max-width: 576px) {
            .btn-map {
                font-size: 0.75rem;
                padding: 0.5rem;
            }
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
                        <th>Almoço</th>
                        <th>Retorno</th>
                        <th>Saída </th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Mapa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                                <td><?php echo htmlspecialchars($row['entrada']); ?></td>
                                <td><?php echo isset($row['almoco_saida']) ? htmlspecialchars($row['almoco_saida']) : ''; ?></td>
                                <td><?php echo isset($row['almoco_retorno']) ? htmlspecialchars($row['almoco_retorno']) : ''; ?></td>
                                <td><?php echo htmlspecialchars($row['saida']); ?></td>
                                <td><?php echo htmlspecialchars($row['latitude']); ?></td>
                                <td><?php echo htmlspecialchars($row['longitude']); ?></td>
                                <td>
                                    <a href="https://earth.google.com/web/search/<?php echo htmlspecialchars($row['latitude']); ?>%09<?php echo htmlspecialchars($row['longitude']); ?>" class="btn btn-info" target="_blank" rel="noopener noreferrer">
                                        <i class="fas fa-globe"></i> Google Earth
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9">Nenhum registro encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div id="map"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        let map = null;
        let currentBtn = null;

        function toggleMap(lat, lng, button) {
            const mapDiv = document.getElementById('map');
            const isMapVisible = mapDiv.style.display === 'block';

            if (!isMapVisible) {
                // Remove the existing map if it exists
                if (map) {
                    map.remove();
                    map = null;
                }

                // Create a new map
                map = L.map('map').setView([lat, lng], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                L.marker([lat, lng]).addTo(map)
                    .bindPopup('Localização')
                    .openPopup();

                mapDiv.style.display = 'block';
                
                // Alterar o botão para "Fechar Mapa"
                button.classList.remove('closed');
                button.classList.add('open');
                button.innerHTML = '<i class="fas fa-times"></i> Fechar Mapa';
                
                // Atualizar o botão atual
                currentBtn = button;
                
                // Buscar endereço usando Geocod.io
                fetch(`https://api.geocod.io/v1.7/reverse?q=${lat},${lng}&api_key=bab1000eabbf401a1b0a6b4c66e66e66f40f6b6`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.results.length > 0) {
                            const address = data.results[0].formatted_address;
                            L.popup().setLatLng([lat, lng]).setContent(`Endereço: ${address}`).openOn(map);
                        }
                    })
                    .catch(error => console.error('Erro ao buscar endereço:', error));
            } else {
                mapDiv.style.display = 'none';
                
                // Alterar o botão para "Abrir Mapa"
                button.classList.remove('open');
                button.classList.add('closed');
                button.innerHTML = '<i class="fas fa-map"></i> Abrir Mapa';
                
                // Remover o mapa atual
                if (map) {
                    map.remove();
                    map = null;
                }
            }
        }
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
