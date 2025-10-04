<?php
session_start();
require '../conexao.php';

// Apenas administradores
if (!isset($_SESSION['id_utilizador']) || $_SESSION['id_tipo'] != 1) {
    header("Location: ../login.php?erro=acesso");
    exit;
}

// (Opcional) Carregar info do admin logado
$idAdmin = $_SESSION['id_utilizador'];
$stmtAdmin = $conn->prepare("SELECT * FROM utilizador WHERE id_utilizador = ?");
$stmtAdmin->bind_param("i", $idAdmin);
$stmtAdmin->execute();
$user = $stmtAdmin->get_result()->fetch_assoc();

// --------- Pesquisa ---------
$pesquisa = isset($_GET['pesquisa']) ? trim($_GET['pesquisa']) : "";
$like = "%" . $pesquisa . "%";

// --------- Paginação ---------
$limite = 20;
$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;

// Total de categorias (com filtro)
$stmtCount = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM categoria c
    WHERE c.nome_categoria LIKE ?
");
$stmtCount->bind_param("s", $like);
$stmtCount->execute();
$totalRegistos = (int)$stmtCount->get_result()->fetch_assoc()['total'];

$totalPaginas = max(1, (int)ceil($totalRegistos / $limite));
if ($pagina > $totalPaginas) $pagina = $totalPaginas;
$offset = ($pagina - 1) * $limite;

// Dados paginados + quantidade de receitas
$sql = "
    SELECT 
        c.idcategoria,
        c.nome_categoria,
        COALESCE(COUNT(r.idreceita), 0) AS qtd_receitas
    FROM categoria c
    LEFT JOIN receita r ON r.categoria_idcategoria = c.idcategoria
    WHERE c.nome_categoria LIKE ?
    GROUP BY c.idcategoria, c.nome_categoria
    ORDER BY c.nome_categoria ASC
    LIMIT ? OFFSET ?
";
$stmtData = $conn->prepare($sql);
$stmtData->bind_param("sii", $like, $limite, $offset);
$stmtData->execute();
$categorias = $stmtData->get_result();
?>
<!doctype html>
<html lang="pt">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - Categorias</title>
  <link rel="stylesheet" href="./css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
 <?php include 'slide_bar.php'; ?>

  <!-- Main -->
  <main class="app-main">
    <div class="container-fluid my-4">

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-tags"></i> Lista de Categorias</h2>
        <a href="adicionar_categoria.php" class="btn btn-success">
          <i class="bi bi-plus-circle"></i> Adicionar Categoria
        </a>
      </div>

      <!-- Mensagens -->
      <?php if (isset($_GET['msg']) && $_GET['msg'] === 'adicionado_sucesso'): ?>
        <div class="alert alert-success">Categoria adicionada com sucesso!</div>
      <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'editado_sucesso'): ?>
        <div class="alert alert-success">Categoria atualizada com sucesso!</div>
      <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'apagado_sucesso'): ?>
        <div class="alert alert-success">Categoria apagada com sucesso!</div>
      <?php elseif (isset($_GET['erro']) && $_GET['erro'] === 'erro_transacao'): ?>
        <div class="alert alert-danger">Não foi possível concluir a operação.</div>
      <?php endif; ?>

      <!-- Pesquisa -->
      <form method="get" class="mb-3 d-flex">
        <input type="text" name="pesquisa" class="form-control me-2" placeholder="Pesquisar categoria..." value="<?= htmlspecialchars($pesquisa) ?>">
        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
      </form>

      <div class="card shadow-sm">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Nome da Categoria</th>
                  <th>Receitas</th>
                  <th style="width: 150px;">Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($categorias && $categorias->num_rows > 0): ?>
                  <?php while ($row = $categorias->fetch_assoc()): ?>
                    <tr>
                      <td><?= $row['idcategoria'] ?></td>
                      <td><?= htmlspecialchars($row['nome_categoria']) ?></td>
                      <td>
                        <span class="badge bg-info"><?= (int)$row['qtd_receitas'] ?></span>
                      </td>
                      <td>
                        <div class="dropdown">
                          <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Ações
                          </button>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="editar_categoria.php?id=<?= $row['idcategoria'] ?>"><i class="bi bi-pencil-square"></i> Editar</a></li>
                            <li>
                              <a class="dropdown-item text-danger" 
                                 href="apagar_categoria.php?id=<?= $row['idcategoria'] ?>"
                                 onclick="return confirm('Ao apagar esta categoria, as receitas associadas ficarão sem categoria (se a FK permitir) ou a operação pode falhar (se a FK não permitir). Deseja continuar?');">
                                <i class="bi bi-trash"></i> Apagar
                              </a>
                            </li>
                          </ul>
                        </div>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="4" class="text-center">Nenhuma categoria encontrada.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Paginação -->
      <nav aria-label="Navegação de páginas">
        <ul class="pagination justify-content-center mt-3">
          <?php if ($pagina > 1): ?>
            <li class="page-item">
              <a class="page-link" href="?pagina=<?= $pagina - 1 ?>&pesquisa=<?= urlencode($pesquisa) ?>">Anterior</a>
            </li>
          <?php endif; ?>

          <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
              <a class="page-link" href="?pagina=<?= $i ?>&pesquisa=<?= urlencode($pesquisa) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>

          <?php if ($pagina < $totalPaginas): ?>
            <li class="page-item">
              <a class="page-link" href="?pagina=<?= $pagina + 1 ?>&pesquisa=<?= urlencode($pesquisa) ?>">Próximo</a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>

      <!-- Botões inferiores -->
      <div class="d-flex justify-content-between mt-3">
        <a href="adicionar_categoria.php" class="btn btn-success">
          <i class="bi bi-plus-circle"></i> Adicionar Categoria
        </a>
        <a href="adm1.php" class="btn btn-secondary">
          <i class="bi bi-arrow-left"></i> Voltar ao Painel
        </a>
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
