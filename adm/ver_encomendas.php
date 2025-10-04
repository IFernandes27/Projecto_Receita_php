<?php
session_start();
require '../conexao.php';

// Apenas administradores
if (!isset($_SESSION['id_utilizador']) || $_SESSION['id_tipo'] != 1) {
  header("Location: ../login.php?erro=acesso");
  exit;
}

// (opcional) info admin
$idAdmin = $_SESSION['id_utilizador'];
$stmtAdmin = $conn->prepare("SELECT * FROM utilizador WHERE id_utilizador = ?");
$stmtAdmin->bind_param("i", $idAdmin);
$stmtAdmin->execute();
$user = $stmtAdmin->get_result()->fetch_assoc();

// --------- Pesquisa (por id, nome, email) ---------
$pesq = isset($_GET['q']) ? trim($_GET['q']) : "";
$qLike = "%".$pesq."%";

// --------- Paginação ---------
$limite = 20;
$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;

// Total de encomendas com filtro
$sqlCount = "
  SELECT COUNT(*) AS total
  FROM encomendas e
  LEFT JOIN utilizador u ON u.id_utilizador = e.utilizador_id_utilizador
  WHERE (? = '' OR CAST(e.idencomendas AS CHAR) LIKE ? OR u.nome LIKE ? OR u.email LIKE ?)
";
$stmtCount = $conn->prepare($sqlCount);
$stmtCount->bind_param("ssss", $pesq, $qLike, $qLike, $qLike);
$stmtCount->execute();
$totalRegistos = (int)$stmtCount->get_result()->fetch_assoc()['total'];

$totalPaginas = max(1, (int)ceil($totalRegistos / $limite));
if ($pagina > $totalPaginas) $pagina = $totalPaginas;
$offset = ($pagina - 1) * $limite;

// Dados paginados (usa valor_total da tabela encomendas; total_itens = nº de receitas associadas)
$sql = "
  SELECT 
    e.idencomendas,
    e.data,
    e.valor_total,
    u.nome  AS cliente,
    u.email AS email_cliente,
    (
      SELECT COUNT(*) 
      FROM encomenda_receita er 
      WHERE er.encomendas_idencomendas = e.idencomendas
    ) AS total_itens
  FROM encomendas e
  LEFT JOIN utilizador u ON u.id_utilizador = e.utilizador_id_utilizador
  WHERE (? = '' OR CAST(e.idencomendas AS CHAR) LIKE ? OR u.nome LIKE ? OR u.email LIKE ?)
  ORDER BY e.idencomendas DESC
  LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssii", $pesq, $qLike, $qLike, $qLike, $limite, $offset);
$stmt->execute();
$encomendas = $stmt->get_result();

// Itens de uma encomenda (lista de receitas)
function obter_itens_encomenda(mysqli $conn, int $idencomendas) {
  $sqlItens = "
    SELECT 
      r.idreceita,
      r.nome AS receita
    FROM encomenda_receita er
    INNER JOIN receita r ON r.idreceita = er.receita_idreceita
    WHERE er.encomendas_idencomendas = ?
    ORDER BY r.nome ASC
  ";
  $st = $conn->prepare($sqlItens);
  $st->bind_param("i", $idencomendas);
  $st->execute();
  return $st->get_result();
}
?>
<!doctype html>
<html lang="pt">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - Encomendas</title>
  <link rel="stylesheet" href="./css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
 <?php include 'slide_bar.php'; ?>

  <!-- Main -->
  <main class="app-main">
    <div class="container-fluid my-4">

      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-bag"></i> Lista de Encomendas</h2>
        <div class="text-muted">Apenas listar e apagar encomendas</div>
      </div>

      <!-- Mensagens -->
      <?php if (isset($_GET['msg']) && $_GET['msg'] === 'apagado_sucesso'): ?>
        <div class="alert alert-success">Encomenda apagada com sucesso.</div>
      <?php elseif (isset($_GET['erro']) && $_GET['erro'] === 'erro_transacao'): ?>
        <div class="alert alert-danger">Não foi possível apagar a encomenda.</div>
      <?php elseif (isset($_GET['erro']) && $_GET['erro'] === 'nao_encontrada'): ?>
        <div class="alert alert-warning">Encomenda não encontrada.</div>
      <?php endif; ?>

      <!-- Pesquisa -->
      <form method="get" class="mb-3 d-flex">
        <input type="text" name="q" class="form-control me-2" placeholder="Procurar por ID, cliente ou email..." value="<?= htmlspecialchars($pesq) ?>">
        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
      </form>

      <div class="card shadow-sm">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
              <thead class="table-dark">
                <tr>
                  <th>#</th>
                  <th>Data</th>
                  <th>Cliente</th>
                  <th>Email</th>
                  <th>Itens</th>
                  <th>Total (€)</th>
                  <th style="width:140px;">Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($encomendas && $encomendas->num_rows > 0): ?>
                  <?php while ($e = $encomendas->fetch_assoc()): ?>
                    <?php
                      $id = (int)$e['idencomendas'];
                      $itens = obter_itens_encomenda($conn, $id);
                    ?>
                    <tr>
                      <td><?= $id ?></td>
                      <td><?= htmlspecialchars($e['data'] ?? '') ?></td>
                      <td><?= htmlspecialchars($e['cliente'] ?? '---') ?></td>
                      <td><?= htmlspecialchars($e['email_cliente'] ?? '---') ?></td>
                      <td><span class="badge bg-info"><?= (int)$e['total_itens'] ?></span></td>
                      <td><?= number_format((float)$e['valor_total'], 2, ',', '.') ?></td>
                      <td>
                        <div class="dropdown">
                          <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Ações
                          </button>
                          <ul class="dropdown-menu">
                            <li>
                              <a class="dropdown-item text-danger"
                                 href="apagar_encomenda.php?id=<?= $id ?>"
                                 onclick="return confirm('Esta ação apagará a encomenda e os itens associados. Continuar?');">
                                <i class="bi bi-trash"></i> Apagar encomenda
                              </a>
                            </li>
                          </ul>
                        </div>
                      </td>
                    </tr>
                    <!-- Linha com os itens (receitas) desta encomenda -->
                    <tr class="table-light">
                      <td colspan="7">
                        <?php if ($itens && $itens->num_rows > 0): ?>
                          <div class="table-responsive">
                            <table class="table table-sm mb-0">
                              <thead>
                                <tr>
                                  <th style="width:100px;">ID Receita</th>
                                  <th>Receita</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php while ($it = $itens->fetch_assoc()): ?>
                                  <tr>
                                    <td><?= (int)$it['idreceita'] ?></td>
                                    <td><?= htmlspecialchars($it['receita']) ?></td>
                                  </tr>
                                <?php endwhile; ?>
                              </tbody>
                            </table>
                          </div>
                        <?php else: ?>
                          <em class="text-muted">Sem receitas associadas.</em>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="7" class="text-center">Nenhuma encomenda encontrada.</td></tr>
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
              <a class="page-link" href="?pagina=<?= $pagina - 1 ?>&q=<?= urlencode($pesq) ?>">Anterior</a>
            </li>
          <?php endif; ?>

          <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
              <a class="page-link" href="?pagina=<?= $i ?>&q=<?= urlencode($pesq) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>

          <?php if ($pagina < $totalPaginas): ?>
            <li class="page-item">
              <a class="page-link" href="?pagina=<?= $pagina + 1 ?>&q=<?= urlencode($pesq) ?>">Próximo</a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>

      <div class="d-flex justify-content-end mt-3">
        <a href="adm1.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar ao Painel</a>
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
