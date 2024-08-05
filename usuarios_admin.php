<?php
session_start();
require 'config.php'; // Arquivo de configuração para conectar ao banco de dados

// Verificar se o usuário está logado e se é um admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Inicializar variáveis
$usuarios = [];
$status = $_GET['status'] ?? '';
$message = $_GET['message'] ?? '';

// Pesquisa de usuários
$pesquisa = $_GET['pesquisa'] ?? '';

// Obter os 10 primeiros usuários ou usuários que correspondam à pesquisa
$sql = "SELECT id, nome, tipo FROM usuarios";
if ($pesquisa) {
    $sql .= " WHERE nome LIKE ?";
}

$sql .= " ORDER BY id ASC LIMIT 10";

if ($stmt = $conn->prepare($sql)) {
    if ($pesquisa) {
        $pesquisa_param = '%' . $pesquisa . '%';
        $stmt->bind_param("s", $pesquisa_param);
    }
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $usuarios = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "Erro ao executar a consulta: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Erro ao preparar a consulta: " . $conn->error;
}

// Fechar a conexão com o banco de dados
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários Admin</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #000;
        }
        .navbar-nav .nav-item {
            margin-left: 10px;
        }
        .navbar-nav .nav-item .btn {
            color: #fff;
        }
        .navbar-nav .nav-item .btn:hover {
            background-color: #6c757d; /* Cinza claro */
            color: #fff;
        }
        .modal-dialog {
            max-width: 90%; /* Tornar o modal responsivo */
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
                    <a class="btn btn-secondary" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Usuários</h2>
        <form method="GET" class="form-inline mb-3">
            <input class="form-control mr-sm-2" type="search" name="pesquisa" placeholder="Pesquisar usuário" aria-label="Pesquisar" value="<?php echo htmlspecialchars($pesquisa); ?>">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Pesquisar</button>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['tipo']); ?></td>
                            <td>
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal" data-id="<?php echo htmlspecialchars($usuario['id']); ?>" data-nome="<?php echo htmlspecialchars($usuario['nome']); ?>" data-tipo="<?php echo htmlspecialchars($usuario['tipo']); ?>"><i class="fas fa-edit"></i> Editar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de Mensagem -->
    <div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">
                        <?php if ($status === 'success'): ?>
                            <i class="fas fa-check-circle"></i> Sucesso
                        <?php else: ?>
                            <i class="fas fa-exclamation-circle"></i> Erro
                        <?php endif; ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel"><i class="fas fa-edit"></i> Editar Usuário</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editForm" method="POST" action="editar_usuario.php">
                    <div class="modal-body">
                        <input type="hidden" id="user_id" name="user_id">
                        <div class="form-group">
                            <label for="user_nome">Nome</label>
                            <input type="text" class="form-control" id="user_nome" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="user_senha">Nova Senha <small>(deixe em branco para manter a atual)</small></label>
                            <input type="password" class="form-control" id="user_senha" name="senha">
                        </div>
                        <div class="form-group">
                            <label for="user_tipo">Tipo</label>
                            <select class="form-control" id="user_tipo" name="tipo" required>
                                <option value="usuario">Usuário</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="confirm_senha">Confirme a Senha (se alterada)</label>
                            <input type="password" class="form-control" id="confirm_senha" name="confirm_senha">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $('#editModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var userId = button.data('id');
            var userNome = button.data('nome');
            var userTipo = button.data('tipo');

            var modal = $(this);
            modal.find('#user_id').val(userId);
            modal.find('#user_nome').val(userNome);
            modal.find('#user_tipo').val(userTipo);
        });

        $(document).ready(function() {
            <?php if ($status && $message): ?>
                $('#messageModal').modal('show');
            <?php endif; ?>
        });
    </script>
</body>
</html>
