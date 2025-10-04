<?php
session_start();
require '../conexao.php';

// Bloqueia acesso de não administradores
if (!isset($_SESSION['id_utilizador']) || $_SESSION['id_tipo'] != 1) {
    header("Location: ../login.php?erro=acesso");
    exit;
}

// Verifica se recebeu o ID do utilizador
if (!isset($_GET['id'])) {
    header("Location: ver_utilizadores.php?erro=sem_id");
    exit;
}

$id = intval($_GET['id']);

// Buscar dados do utilizador a editar
$sql = "SELECT id_utilizador, nome, username, email, telefone, id_tipo FROM utilizador WHERE id_utilizador = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$utilizador = $result->fetch_assoc();

if (!$utilizador) {
    header("Location: ver_utilizadores.php?erro=nao_encontrado");
    exit;
}

// Atualizar dados se formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $id_tipo = $_POST['id_tipo'];

    $sql_update = "UPDATE utilizador SET nome=?, username=?, email=?, telefone=?, id_tipo=? WHERE id_utilizador=?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssssii", $nome, $username, $email, $telefone, $id_tipo, $id);

    if ($stmt_update->execute()) {
        header("Location: ver_utilizadores.php?msg=atualizado");
        exit;
    } else {
        $erro = "Erro ao atualizar utilizador.";
    }
}
?>

<!doctype html>
<html lang="pt">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Editar Utilizador</title>

    <!-- AdminLTE + Bootstrap -->
    <link rel="stylesheet" href="./css/adminlte.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
  </head>
  <?php include 'slide_bar.php'; ?>
      <!-- Sidebar End -->

      <!-- Main -->
      <main class="app-main">
        <div class="container my-4">
          <h2 class="mb-4"><i class="bi bi-pencil-square"></i> Editar Utilizador</h2>

          <div class="card shadow-sm">
            <div class="card-body">
              <?php if (!empty($erro)): ?>
                <div class="alert alert-danger"><?= $erro ?></div>
              <?php endif; ?>

              <form method="POST">
                <div class="mb-3">
                  <label class="form-label">Nome</label>
                  <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($utilizador['nome']) ?>" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Username</label>
                  <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($utilizador['username']) ?>" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($utilizador['email']) ?>" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Telefone</label>
                  <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($utilizador['telefone']) ?>">
                </div>
                <div class="mb-3">
                  <label class="form-label">Tipo de Utilizador</label>
                  <select name="id_tipo" class="form-select">
                    <option value="1" <?= $utilizador['id_tipo'] == 1 ? 'selected' : '' ?>>Administrador</option>
                    <option value="2" <?= $utilizador['id_tipo'] == 2 ? 'selected' : '' ?>>Cliente</option>
                  </select>
                </div>

                <div class="d-flex justify-content-between">
                  <a href="ver_utilizadores.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
                  <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar Alterações</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </main>
      <!-- Main End -->

      <!-- Footer -->
      <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">Administração Sabores CPLP</div>
      </footer>
    </div>
  </body>
</html>
