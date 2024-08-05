<?php
// administrador.php
session_start();
require 'config.php'; // Arquivo de configuração para conectar ao banco de dados

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nome_usuario = '';
$registros = [];
$data_inicio = '';
$data_fim = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search'])) {
        $nome_usuario = trim($_POST['nome_usuario']);
        $data_inicio = $_POST['data_inicio'] ?? '';
        $data_fim = $_POST['data_fim'] ?? '';

        // Obter o ID do usuário com base no nome fornecido
        $sql_user = "SELECT id FROM usuarios WHERE nome = ?";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("s", $nome_usuario);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();

        if ($user_row = $result_user->fetch_assoc()) {
            $user_id = $user_row['id'];

            // Obter os registros para o usuário encontrado e limitar a 30 resultados mais recentes
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
                ORDER BY ponto.entrada DESC
                LIMIT 30
            ";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $user_id);
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
    } elseif (isset($_POST['clear'])) {
        // Limpar a consulta e os resultados
        $nome_usuario = '';
        $registros = [];
    } elseif (isset($_POST['update_record'])) {
        // Atualizar entrada e saída
        $ponto_id = $_POST['ponto_id'];
        $entrada = $_POST['entrada'] ?? '';
        $saida = $_POST['saida'] ?? '';

        $sql_update = "UPDATE ponto SET entrada = ?, saida = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql_update)) {
            $stmt->bind_param("ssi", $entrada, $saida, $ponto_id);
            if ($stmt->execute()) {
                header("Location: administrador.php?status=success&message=Registro atualizado com sucesso.");
                exit();
            } else {
                echo "Erro ao atualizar o registro: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Erro ao preparar a consulta: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisar Registros de Ponto</title>
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
        <a class="navbar-brand" href="administrador.php">Ponto Sys</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="btn btn-home" href="administrador.php"><i class="fas fa-home"></i> Home</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-success" href="administrador.php"><i class="fas fa-search"></i> Buscar Usuário</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary" href="ponto_admin.php"><i class="fas fa-cog"></i> Configurações</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary" href="usuarios_admin.php"><i class="fas fa-users"></i> Usuários</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-warning" href="frequencia_mensal.php?nome_usuario=<?php echo urlencode($nome_usuario); ?>&data_inicio=<?php echo urlencode($data_inicio); ?>&data_fim=<?php echo urlencode($data_fim); ?>" target="_blank"><i class="fas fa-calendar-alt"></i> Frequência Mensal</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-secondary" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Pesquisar Registros de Ponto</h2>
        <form method="post" class="mb-4">
            <div class="form-group">
                <label for="nome_usuario">Nome do Usuário:</label>
                <input type="text" id="nome_usuario" name="nome_usuario" class="form-control" value="<?php echo htmlspecialchars($nome_usuario); ?>" required>
            </div>
            <div class="form-group">
                <label for="data_inicio">Data Início:</label>
                <input type="date" id="data_inicio" name="data_inicio" class="form-control" value="<?php echo htmlspecialchars($data_inicio); ?>" required>
            </div>
            <div class="form-group">
                <label for="data_fim">Data Fim:</label>
                <input type="date" id="data_fim" name="data_fim" class="form-control" value="<?php echo htmlspecialchars($data_fim); ?>" required>
            </div>
            <button type="submit" name="search" class="btn btn-primary">Pesquisar</button>
            <button type="submit" name="clear" class="btn btn-secondary">Nova Consulta</button>
        </form>

        <?php if (!empty($registros)): ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Entrada</th>
                            <th>Almoço</th>
                            <th>Retorno</th>
                            <th>Saída</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Mapa</th>
                            <th>Editar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registros as $row): ?>
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
                                <td>
                                    <button id="edit-btn-<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-warning" data-toggle="modal" data-target="#editModal" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-entrada="<?php echo htmlspecialchars($row['entrada']); ?>" data-saida="<?php echo htmlspecialchars($row['saida']); ?>">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal de Edição -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Editar Registro de Ponto</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit-ponto-id" name="ponto_id">
                        <div class="form-group">
                            <label for="edit-entrada">Nova Entrada:</label>
                            <input type="datetime-local" id="edit-entrada" name="entrada" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="edit-saida">Nova Saída:</label>
                            <input type="datetime-local" id="edit-saida" name="saida" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" name="update_record" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        $(document).ready(function() {
            $('#editModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Botão que acionou o modal
                var ponto_id = button.data('id'); // Extrai informação dos atributos data-*

                // Preenche os campos do formulário do modal com os dados extraídos
                var modal = $(this);
                modal.find('.modal-title').text('Editar Registro de Ponto #' + ponto_id);
                modal.find('#edit-ponto-id').val(ponto_id);
                modal.find('#edit-entrada').val(button.data('entrada'));
                modal.find('#edit-saida').val(button.data('saida'));
            });
        });
    </script>
</body>
</html>
