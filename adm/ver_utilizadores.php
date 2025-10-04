<?php
session_start();
require '../conexao.php';

// Bloqueia acesso de não administradores
if (!isset($_SESSION['id_utilizador']) || $_SESSION['id_tipo'] != 1) {
    header("Location: ../login.php?erro=acesso");
    exit;
}

// Puxa dados do utilizador logado
$id = $_SESSION['id_utilizador'];
$sql = "SELECT * FROM utilizador WHERE id_utilizador = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Buscar utilizadores (sem mostrar password)
$sql = "SELECT id_utilizador, nome, username, email, telefone, id_tipo FROM utilizador";
$resultado = $conn->query($sql);
?>
<!doctype html>
<html lang="pt">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />

    <link rel="stylesheet" href="./css/adminlte.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

    <title>Utilizadores - Área do Administrador</title>
  </head>
  
 <?php include 'slide_bar.php'; ?>

      <!-- Conteúdo -->
      <main class="app-main">
        <div class="container-fluid my-4">
          <h2 class="mb-4"><i class="fa fa-users"></i> Lista de Utilizadores</h2>

          <?php if (isset($_GET['msg']) && $_GET['msg'] == 'apagado_sucesso'): ?>
            <div class="alert alert-success">Utilizador apagado com sucesso!</div>
          <?php elseif (isset($_GET['erro']) && $_GET['erro'] == 'nao_apagado'): ?>
            <div class="alert alert-danger">Erro ao apagar utilizador.</div>
          <?php endif; ?>

          <div class="card shadow-sm">
            <div class="card-body">
              <div class="table-responsive">
                <table id="tabela-utilizadores" class="table table-hover table-bordered align-middle">
                  <thead class="table-dark">
                    <tr>
                      <th>ID</th>
                      <th>Nome</th>
                      <th>Username</th>
                      <th>Email</th>
                      <th>Telefone</th>
                      <th>Tipo</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($resultado && $resultado->num_rows > 0): ?>
                      <?php while ($row = $resultado->fetch_assoc()): ?>
                        <tr>
                          <td><?= $row['id_utilizador'] ?></td>
                          <td><?= htmlspecialchars($row['nome']) ?></td>
                          <td><?= htmlspecialchars($row['username']) ?></td>
                          <td><?= htmlspecialchars($row['email']) ?></td>
                          <td><?= htmlspecialchars($row['telefone']) ?></td>
                          <td>
                            <?= $row['id_tipo'] == 1 
                              ? '<span class="badge bg-danger">Administrador</span>' 
                              : '<span class="badge bg-primary">Cliente</span>' ?>
                          </td>
                          <td>
                            <div class="btn-group">
                              <button class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                Ações
                              </button>
                              <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="editar_utilizador.php?id=<?= $row['id_utilizador'] ?>">Editar</a></li>
                                <li><a class="dropdown-item text-danger" href="apagar_utilizador.php?id=<?= $row['id_utilizador'] ?>" onclick="return confirm('Tem certeza que deseja apagar este utilizador?');">Apagar</a></li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    <?php else: ?>
                      <tr><td colspan="7" class="text-center">Nenhum utilizador encontrado.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Botões abaixo da tabela -->
          <div class="d-flex justify-content-between mt-3">
            <a href="adicionar_utilizador.php" class="btn btn-success">
              <i class="fa fa-plus"></i> Adicionar Utilizador
            </a>
            <a href="adm1.php" class="btn btn-secondary">
              <i class="fa fa-arrow-left"></i> Voltar ao Painel
            </a>
          </div>
        </div>
      </main>

      <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">Área do administrador</div>
      </footer>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
      $(document).ready(function () {
        $('#tabela-utilizadores').DataTable({
          "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-PT.json"
          }
        });
      });
    </script>
  </body>
</html>
