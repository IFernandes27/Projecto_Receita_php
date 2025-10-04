<?php
session_start();
require '../conexao.php';

// Apenas administradores
if (!isset($_SESSION['id_utilizador']) || $_SESSION['id_tipo'] != 1) {
  header("Location: ../login.php?erro=acesso");
  exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: ver_categorias.php?erro=id_invalido");
  exit;
}
$id = (int)$_GET['id'];

$erro = null;

// Buscar categoria
$stmt = $conn->prepare("SELECT idcategoria, nome_categoria FROM categoria WHERE idcategoria = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$categoria = $stmt->get_result()->fetch_assoc();
if (!$categoria) {
  header("Location: ver_categorias.php?erro=nao_encontrada");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nome = trim($_POST['nome_categoria'] ?? '');
  if ($nome === '') {
    $erro = "Indique o nome da categoria.";
  } else {
    $stmtUp = $conn->prepare("UPDATE categoria SET nome_categoria = ? WHERE idcategoria = ?");
    $stmtUp->bind_param("si", $nome, $id);
    if ($stmtUp->execute()) {
      header("Location: ver_categorias.php?msg=editado_sucesso");
      exit;
    } else {
      $erro = "Erro ao atualizar categoria.";
    }
  }
  // manter valor no form em caso de erro
  $categoria['nome_categoria'] = $nome;
}
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Editar Categoria</title>
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
        <h2><i class="bi bi-pencil-square"></i> Editar Categoria #<?= $categoria['idcategoria'] ?></h2>
        <a href="ver_categorias.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
      </div>

      <?php if ($erro): ?><div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

      <div class="card shadow-sm">
        <div class="card-body">
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Nome da Categoria *</label>
              <input type="text" name="nome_categoria" class="form-control" value="<?= htmlspecialchars($categoria['nome_categoria']) ?>" required>
            </div>
            <div class="d-flex justify-content-end">
              <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Guardar alterações</button>
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
