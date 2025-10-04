<?php
session_start();
require '../conexao.php';

// Bloqueia acesso de não administradores
if (!isset($_SESSION['id_utilizador']) || $_SESSION['id_tipo'] != 1) {
    header("Location: ../login.php?erro=acesso");
    exit;
}

// Puxa dados do admin logado (opcional para mostrar nome, etc.)
$idAdmin = $_SESSION['id_utilizador'];
$stmtAdmin = $conn->prepare("SELECT * FROM utilizador WHERE id_utilizador = ?");
$stmtAdmin->bind_param("i", $idAdmin);
$stmtAdmin->execute();
$user = $stmtAdmin->get_result()->fetch_assoc();

// ---------- Pesquisa ----------
$pesquisa = isset($_GET['pesquisa']) ? trim($_GET['pesquisa']) : "";
$like = "%" . $pesquisa . "%";

// ---------- Paginação ----------
$limite = 20;
$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;

// Contar total de registos (com filtro de pesquisa)
$stmtCount = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM receita r
    LEFT JOIN pais p ON r.pais_idpais = p.idpais
    WHERE r.nome LIKE ?
");
$stmtCount->bind_param("s", $like);
$stmtCount->execute();
$totalRegistos = (int)$stmtCount->get_result()->fetch_assoc()['total'];

// Calcular páginas (garante pelo menos 1 para evitar warnings)
$totalPaginas = max(1, (int)ceil($totalRegistos / $limite));

// Se a página pedida for maior que o total, ajusta
if ($pagina > $totalPaginas) $pagina = $totalPaginas;

$offset = ($pagina - 1) * $limite;

// Buscar dados paginados com pesquisa
$sql = "
    SELECT r.idreceita, r.nome, r.descricao, r.preco, r.imagens, p.Pais
    FROM receita r
    LEFT JOIN pais p ON r.pais_idpais = p.idpais
    WHERE r.nome LIKE ?
    ORDER BY r.idreceita DESC
    LIMIT ? OFFSET ?
";
$stmtData = $conn->prepare($sql);
$stmtData->bind_param("sii", $like, $limite, $offset);
$stmtData->execute();
$resultado = $stmtData->get_result();
?>
<!doctype html>
<html lang="pt">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - Receitas</title>
  <link rel="stylesheet" href="./css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
  <?php include 'slide_bar.php'; ?>

  <!-- Main -->
  <main class="app-main">
    <div class="container-fluid my-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-journal-text"></i> Lista de Receitas</h2>
     
      
        </a>
      </div>

      <!-- Pesquisa -->
      <form method="get" class="mb-3 d-flex">
        <input type="text" name="pesquisa" class="form-control me-2" placeholder="Pesquisar receita..." value="<?= htmlspecialchars($pesquisa) ?>">
        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
      </form>

      <div class="card shadow-sm">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Imagem</th>
                  <th>Nome</th>
                  <th>Descrição</th>
                  <th>Preço</th>
                  <th>País</th>
                  <th style="width: 150px;">Ações</th>
                </tr>
              </thead>
              <tbody>
              <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($row = $resultado->fetch_assoc()): ?>
                  <tr>
                    <td><?= $row['idreceita'] ?></td>
                    <td>
                      <?php if (!empty($row['imagens'])): ?>
                        <img src="../assets/images/<?= htmlspecialchars($row['imagens']) ?>" alt="Imagem" style="width:70px; height:70px; object-fit:cover;">
                      <?php else: ?>
                        <img src="../assets/images/no-image.png" alt="Sem imagem" style="width:70px; height:70px; object-fit:cover;">
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['nome']) ?></td>
                    <td><?= htmlspecialchars(function_exists('mb_strimwidth') ? mb_strimwidth($row['descricao'], 0, 60, "...") : substr($row['descricao'], 0, 60) . "...") ?></td>
                    <td><?= number_format($row['preco'], 2, ',', '.') ?> €</td>
                    <td><?= htmlspecialchars($row['Pais'] ?? '---') ?></td>
                    <td>
                      <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                          Ações
                        </button>
                        <ul class="dropdown-menu">
                          <li><a class="dropdown-item" href="editar_receita.php?id=<?= $row['idreceita'] ?>"><i class="bi bi-pencil-square"></i> Editar</a></li>
                          <li><a class="dropdown-item text-danger" href="apagar_receita.php?id=<?= $row['idreceita'] ?>" onclick="return confirm('Tem certeza que deseja apagar esta receita?');"><i class="bi bi-trash"></i> Apagar</a></li>
                        </ul>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="7" class="text-center">Nenhuma receita encontrada.</td></tr>
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
        <a href="adicionar_receita.php" class="btn btn-success">
          <i class="bi bi-plus-circle"></i> Adicionar Receita
        </a>
        <a href="adm1.php" class="btn btn-secondary">
          <i class="bi bi-arrow-left"></i> Voltar ao Painel
        </a>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="app-footer">
    <div class="float-end d-none d-sm-inline">Área Administrativa</div>
  </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
