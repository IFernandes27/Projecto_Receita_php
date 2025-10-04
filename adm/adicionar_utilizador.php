<?php
session_start();
require '../conexao.php';

// Bloqueia acesso de não administradores
if (!isset($_SESSION['id_utilizador']) || $_SESSION['id_tipo'] != 1) {
    header("Location: ../login.php?erro=acesso");
    exit;
}

// Se o formulário for submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $id_tipo = intval($_POST['id_tipo']); // 1 = Admin, 2 = Cliente

    $sql = "INSERT INTO utilizador (nome, username, email, telefone, password, id_tipo) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $nome, $username, $email, $telefone, $password, $id_tipo);

    if ($stmt->execute()) {
        header("Location: ver_utilizadores.php?msg=adicionado_sucesso");
        exit;
    } else {
        $erro = "Erro ao adicionar utilizador.";
    }
}
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Adicionar Utilizador</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./css/adminlte.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
  <!--end::Head-->
   <?php include 'slide_bar.php'; ?>

    <!-- Conteúdo -->
    <main class="app-main">
      <div class="container my-4">
        <h2 class="mb-4"><i class="bi bi-person-plus"></i> Adicionar Utilizador</h2>

        <?php if (isset($erro)): ?>
          <div class="alert alert-danger"><?= $erro ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
          <div class="card-body">
            <form method="POST">
              <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" class="form-control">
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Tipo de Utilizador</label>
                <select name="id_tipo" class="form-select" required>
                  <option value="2" selected>Cliente</option>
                  <option value="1">Administrador</option>
                </select>
              </div>
              <div class="d-flex justify-content-between">
                <a href="ver_utilizadores.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Guardar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>

    <footer class="app-footer">
      <div class="float-end d-none d-sm-inline">Área do administrador</div>
    </footer>
  </div>

  <!-- Bootstrap -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
