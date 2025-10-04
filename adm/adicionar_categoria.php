<?php
session_start();
require '../conexao.php';

// Apenas administradores
if (!isset($_SESSION['id_utilizador']) || $_SESSION['id_tipo'] != 1) {
  header("Location: ../login.php?erro=acesso");
  exit;
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nome = trim($_POST['nome_categoria'] ?? '');
  if ($nome === '') {
    $erro = "Indique o nome da categoria.";
  } else {
    $stmt = $conn->prepare("INSERT INTO categoria (nome_categoria) VALUES (?)");
    $stmt->bind_param("s", $nome);
    if ($stmt->execute()) {
      header("Location: ver_categorias.php?msg=adicionado_sucesso");
      exit;
    } else {
      $erro = "Erro ao adicionar categoria.";
    }
  }
}
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Adicionar Categoria</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="./css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
<div class="app-wrapper">
  <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
      <a href="adm1.php" class="brand-link">
        <img src="./assets/img/AdminLTELogo.png" class="brand-image opacity-75 shadow" alt="Logo">
        <span class="brand-text fw-light">Área do Administrador</span>
      </a>
    </div>
    <div class="sidebar-wrapper">
      <nav class="mt-2">
        <ul class="nav sidebar-menu flex-column">
          <li class="nav-item"><a href="adm1.php" class="nav-link"><i class="bi bi-speedometer2"></i> <p>Painel</p></a></li>
          <li class="nav-item"><a href="ver_categorias.php" class="nav-link active"><i class="bi bi-tags"></i> <p>Categorias</p></a></li>
          <li class="nav-item"><a href="ver_receitas.php" class="nav-link"><i class="bi bi-journal-text"></i> <p>Receitas</p></a></li>
          <li class="nav-item"><a href="../index.php" class="nav-link"><i class="bi bi-house"></i> <p>Sabores da CPLP</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <main class="app-main">
    <div class="container my-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-plus-circle"></i> Adicionar Categoria</h2>
        <a href="ver_categorias.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
      </div>

      <?php if ($erro): ?><div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

      <div class="card shadow-sm">
        <div class="card-body">
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Nome da Categoria *</label>
              <input type="text" name="nome_categoria" class="form-control" required>
            </div>
            <div class="d-flex justify-content-end">
              <button class="btn btn-success" type="submit"><i class="bi bi-save"></i> Guardar</button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </main>

  <footer class="app-footer">
    <div class="float-end d-none d-sm-inline">Área Administrativa</div>
  </footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
