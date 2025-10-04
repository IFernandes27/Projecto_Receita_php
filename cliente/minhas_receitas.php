<?php
// cliente/minhas_receitas.php
session_start();
if (!isset($_SESSION['id_utilizador'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../conexao.php'; // expõe $conn (mysqli)
if (method_exists($conn, 'set_charset')) { $conn->set_charset('utf8mb4'); }

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

/**
 * Constrói um src de imagem robusto a partir do valor guardado em receita.imagens.
 * - Aceita JSON (array/obj) e pega o primeiro URL/valor.
 * - Aceita lista separada por vírgula/; ou | e pega o primeiro.
 * - Mantém http(s)://, //cdn e data:
 * - Mantém caminhos que começam com '/' ou '../'
 * - Para caminhos relativos, tenta localizar o ficheiro em vários diretórios comuns;
 *   se existir, retorna o caminho web correspondente; senão, prefixa ../ e retorna.
 * Compatível com PHP 7 (sem str_starts_with).
 */
function buildImageSrc($raw){
    if (!$raw) return null;
    $raw = trim($raw);

    // Se vier JSON (ex.: ["uploads/img.jpg"] ou [{"url":"..."}])
    if ($raw !== '' && ($raw[0] === '[' || $raw[0] === '{')) {
        $j = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($j)) {
            $cand = null;
            if (isset($j[0])) {
                $first = $j[0];
                if (is_array($first) && isset($first['url'])) $cand = $first['url'];
                elseif (is_string($first)) $cand = $first;
            } elseif (isset($j['url'])) {
                $cand = $j['url'];
            }
            if ($cand) $raw = $cand;
        }
    }

    // Se vier lista separada por , ; |
    $parts = preg_split('/\s*[,;|]\s*/', $raw);
    $first = trim($parts[0], " \t\n\r\0\x0B\"'");

    if ($first === '') return null;

    // URL absoluta, protocolo relativo //, ou data:
    if (preg_match('#^(https?:)?//#i', $first) || substr($first, 0, 5) === 'data:') {
        return $first;
    }

    // Já é caminho root-relative ou com ../
    if ($first[0] === '/' || substr($first, 0, 3) === '../') {
        return str_replace(' ', '%20', $first);
    }

    // Tentar diretórios comuns partindo da raiz do site (este ficheiro está em /cliente/)
    $candidates = [
        '',                 // exatamente como está guardado
        'imagens/',
        'uploads/',
        'images/',
        'assets/images/',
        'img/',
        'admin/uploads/',
        'admin/imagens/',
    ];

    foreach ($candidates as $prefix) {
        $fsPath = __DIR__ . '/../' . $prefix . $first; // caminho no sistema de ficheiros
        if (file_exists($fsPath)) {
            $webPath = '../' . $prefix . $first;       // caminho web (a partir de /cliente/)
            return str_replace(' ', '%20', $webPath);
        }
    }

    // Fallback: prefixa ../
    return '../' . ltrim(str_replace(' ', '%20', $first), '/');
}

// placeholder SVG (data URI) quando não há imagem válida
function placeholderSvg($w = 640, $h = 480, $text = 'sem imagem'){
    $svg = sprintf(
        '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%2$d" viewBox="0 0 %1$d %2$d">
            <rect width="100%%" height="100%%" fill="#e9ecef"/>
            <text x="50%%" y="50%%" dominant-baseline="middle" text-anchor="middle" fill="#6c757d" font-family="Arial, sans-serif" font-size="20">%3$s</text>
         </svg>',
        $w, $h, htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
    );
    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}

// ------------------- Dados do utilizador (avatar/topbar) -------------------
$userId = (int) $_SESSION['id_utilizador'];
$usr = ['nome'=>'Utilizador','email'=>''];
$st = $conn->prepare("SELECT nome, email FROM utilizador WHERE id_utilizador = ?");
$st->bind_param('i', $userId);
$st->execute();
$st->bind_result($usrNome, $usrEmail);
if ($st->fetch()) { $usr = ['nome'=>$usrNome, 'email'=>$usrEmail]; }
$st->close();
$initial = mb_strtoupper(mb_substr($usr['nome'] ?: 'U', 0, 1, 'UTF-8'), 'UTF-8');

// ------------------- Paginação -------------------
$perPage = max(1, min(96, (int)($_GET['per_page'] ?? 32)));
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

// Total de receitas distintas nas encomendas do utilizador
$countSql = "
  SELECT COUNT(*) FROM (
    SELECT r.idreceita
    FROM encomendas e
    JOIN encomenda_receita er ON er.encomendas_idencomendas = e.idencomendas
    JOIN receita r ON r.idreceita = er.receita_idreceita
    WHERE e.utilizador_id_utilizador = ?
    GROUP BY r.idreceita
  ) t
";
$cstmt = $conn->prepare($countSql);
$cstmt->bind_param('i', $userId);
$cstmt->execute();
$cstmt->bind_result($totalRows);
$cstmt->fetch();
$cstmt->close();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

// Lista agregada por receita
$listSql = "
  SELECT
    r.idreceita,
    r.nome,
    r.preco,
    r.imagens,
    r.descricao AS descricao,
    COUNT(*)                        AS vezes_comprada,
    COUNT(DISTINCT e.idencomendas)  AS num_encomendas,
    MAX(e.`data`)                   AS last_data
  FROM encomendas e
  JOIN encomenda_receita er ON er.encomendas_idencomendas = e.idencomendas
  JOIN receita r ON r.idreceita = er.receita_idreceita
  WHERE e.utilizador_id_utilizador = ?
  GROUP BY r.idreceita, r.nome, r.preco, r.imagens, r.descricao
  ORDER BY last_data DESC
  LIMIT ? OFFSET ?
";
$lstmt = $conn->prepare($listSql);
$lstmt->bind_param('iii', $userId, $perPage, $offset);
$lstmt->execute();
$res = $lstmt->get_result();
$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$lstmt->close();

$current = 'minhas_receitas.php';
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <title>Sabores da CPLP — Minhas Receitas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{ --brand: rgb(230, 218, 208); --sidebar-w: 280px; }
    body { background-color: #f8f9fa; }
    .sidebar {
      position: fixed; inset: 0 auto 0 0; width: var(--sidebar-w);
      background: linear-gradient(180deg, rgba(230,218,208,.8), rgba(255,255,255,.95)), #ffffff;
      box-shadow: 2px 0 18px rgba(0,0,0,.08);
      padding: 1.25rem 1rem; z-index: 1040; display: flex; flex-direction: column; gap: 1rem;
    }
    .brand { display:flex; align-items:center; gap:.75rem; padding:.5rem .75rem; border-radius:1rem; background:#fff; box-shadow:0 1px 6px rgba(0,0,0,.05); }
    .brand .logo { width:40px; height:40px; border-radius:50%; background: var(--brand); display:grid; place-items:center; font-weight:700; }
    .nav-section-title{ font-size:.8rem; text-transform:uppercase; letter-spacing:.06em; color:#6c757d; margin:.25rem .5rem; }
    .nav-sidebar .nav-link{ display:flex; align-items:center; gap:.6rem; padding:.6rem .85rem; border-radius:.85rem; color:#333; }
    .nav-sidebar .nav-link i{ font-size:1.1rem; }
    .nav-sidebar .nav-link:hover{ background: rgba(230,218,208,.5); text-decoration:none; }
    .nav-sidebar .nav-link.active{ background: var(--brand); font-weight:600; }
    .content{ margin-left: var(--sidebar-w); min-height: 100vh; display:flex; flex-direction:column; }
    .topbar{ position: sticky; top: 0; z-index: 1030; background:#fff; border-bottom:1px solid #eee; }
    .avatar-top{ width: 38px; height: 38px; border-radius: 50%; background:#e9ecef; display:grid; place-items:center; font-weight:700; }
    .card-rounded { border-radius: 1rem; overflow: hidden; }
    .card-img-top { aspect-ratio: 4/3; object-fit: cover; }
    .badge-soft{ background: rgba(0,0,0,.06); }
    @media (max-width: 991.98px){
      .sidebar{ transform: translateX(-100%); transition: transform .3s ease; }
      .sidebar.show{ transform: translateX(0); }
      .content{ margin-left: 0; }
      .backdrop{ position: fixed; inset: 0; background: rgba(0,0,0,.25); z-index: 1035; display: none; }
      .backdrop.show{ display: block; }
    }
  </style>
</head>
<body>

<div class="backdrop d-lg-none" id="backdrop"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="brand">
    <div class="logo">SC</div>
    <div class="fw-bold">Sabores <span class="text-muted">da CPLP</span></div>
  </div>

  <div class="nav-section-title">Navegação</div>
  <nav class="nav flex-column nav-sidebar">
    <a class="nav-link" href="perfil.php"><i class="bi bi-person"></i> Perfil</a>
    <a class="nav-link <?= $current === 'minhas_receitas.php' ? 'active' : '' ?>" href="minhas_receitas.php">
      <i class="bi bi-book"></i> Minhas Receitas
    </a>
    <a class="nav-link" href="../carrinho.php"><i class="bi bi-cart3"></i> Carrinho</a>
  </nav>

  <div class="mt-auto">
    <div class="nav-section-title">Geral</div>
    <nav class="nav flex-column nav-sidebar">
      <a class="nav-link" href="../index.php"><i class="bi bi-house"></i> Início</a>
      <a class="nav-link" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Terminar sessão</a>
    </nav>
  </div>
</aside>

<!-- CONTEÚDO -->
<div class="content">
  <!-- TOPBAR -->
  <div class="topbar">
    <div class="container py-2 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-dark d-lg-none menu-toggle" type="button" aria-label="Abrir menu">
          <i class="bi bi-list"></i>
        </button>
        <div class="fw-semibold">Minhas Receitas</div>
      </div>

      <div class="dropdown">
        <a href="#" class="d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown">
          <span class="d-none d-sm-inline text-muted small"><?= h($usr['nome']) ?></span>
          <div class="avatar-top"><?= h($initial) ?></div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow">
          <li class="dropdown-header small">
            <div class="fw-semibold"><?= h($usr['nome']) ?></div>
            <div class="text-muted"><?= h($usr['email']) ?></div>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="perfil.php"><i class="bi bi-person me-2"></i>Perfil</a></li>
          <li><a class="dropdown-item" href="../carrinho.php"><i class="bi bi-cart3 me-2"></i>Carrinho</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Terminar sessão</a></li>
        </ul>
      </div>
    </div>
  </div>

  <main class="container py-4">

    <?php if ((int)$totalRows === 0): ?>
      <div class="text-center text-muted my-5">
        <i class="bi bi-emoji-neutral" style="font-size:2rem;"></i>
        <p class="mt-3">Ainda não há receitas nas suas encomendas.</p>
        <a href="../index.php" class="btn btn-dark">Explorar receitas</a>
      </div>
    <?php else: ?>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="small text-muted">
          Encontradas <strong><?= (int)$totalRows ?></strong> receitas nas suas encomendas.
        </div>
        <form class="d-flex align-items-center gap-2" method="get">
          <label class="small text-muted">por página</label>
          <select class="form-select form-select-sm" name="per_page" onchange="this.form.submit()">
            <?php foreach ([16,32,48,64,96] as $n): ?>
              <option value="<?= $n ?>" <?= $n==$perPage?'selected':'' ?>><?= $n ?></option>
            <?php endforeach; ?>
          </select>
          <input type="hidden" name="page" value="1">
        </form>
      </div>

      <div class="row g-3">
        <?php foreach ($rows as $r): ?>
          <div class="col-12 col-md-6 col-lg-3">
            <div class="card card-rounded h-100">
              <?php
                $imgSrc = buildImageSrc(isset($r['imagens']) ? $r['imagens'] : null);
                if (!$imgSrc) { $imgSrc = placeholderSvg(800, 600, 'sem imagem'); }
              ?>
              <img class="card-img-top" src="<?= h($imgSrc) ?>" alt="<?= h($r['nome']) ?>">
              <div class="card-body d-flex flex-column">
                <h6 class="card-title mb-1"><?= h($r['nome']) ?></h6>
                <div class="mb-2 text-muted small">
                  <?php if (isset($r['preco'])): ?>
                    <span class="badge badge-soft">€ <?= number_format((float)$r['preco'], 2, ',', ' ') ?></span>
                  <?php endif; ?>
                </div>
                <?php if (isset($r['descricao']) && $r['descricao'] !== null && $r['descricao'] !== ''): ?>
                  <p class="card-text small flex-grow-1"><?= h(mb_strimwidth($r['descricao'], 0, 140, '…', 'UTF-8')) ?></p>
                <?php else: ?>
                  <div class="flex-grow-1"></div>
                <?php endif; ?>

                <div class="mt-2 small">
                  <div>Vezes comprada: <strong><?= (int)$r['vezes_comprada'] ?></strong></div>
                  <div>Em encomendas: <strong><?= (int)$r['num_encomendas'] ?></strong></div>
                  <div>Última compra:
                    <strong>
                      <?php
                        if (!empty($r['last_data'])) {
                          $ts = strtotime($r['last_data']);
                          echo $ts ? date('d/m/Y', $ts) : h($r['last_data']);
                        } else {
                          echo '-';
                        }
                      ?>
                    </strong>
                  </div>
                </div>

                <div class="mt-3 d-grid">
                  <a class="btn btn-outline-dark" href="../product-details.php?id=<?= (int)$r['idreceita'] ?>">
                    Ver receita
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
          <ul class="pagination justify-content-center">
            <?php
              $queryBase = $_GET; unset($queryBase['page']);
              $buildUrl = function($p) use ($queryBase){ $q=$queryBase; $q['page']=$p; return 'minhas_receitas.php?'.http_build_query($q); };
            ?>
            <li class="page-item <?= $page<=1?'disabled':'' ?>">
              <a class="page-link" href="<?= $buildUrl(max(1,$page-1)) ?>" aria-label="Anterior">&laquo;</a>
            </li>
            <?php
              $start = max(1, $page-2);
              $end   = min($totalPages, $page+2);
              if ($start > 1) {
                  echo '<li class="page-item"><a class="page-link" href="'.$buildUrl(1).'">1</a></li>';
                  if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
              }
              for ($p=$start; $p<=$end; $p++){
                  echo '<li class="page-item '.($p==$page?'active':'').'"><a class="page-link" href="'.$buildUrl($p).'">'.$p.'</a></li>';
              }
              if ($end < $totalPages) {
                  if ($end < $totalPages-1) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                  echo '<li class="page-item"><a class="page-link" href="'.$buildUrl($totalPages).'">'.$totalPages.'</a></li>';
              }
            ?>
            <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>">
              <a class="page-link" href="<?= $buildUrl(min($totalPages,$page+1)) ?>" aria-label="Seguinte">&raquo;</a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>

    <?php endif; ?>
  </main>

  <footer class="py-4 mt-4">
    <div class="container text-center text-muted small">
      &copy; <?= date('Y') ?> Sabores da CPLP
    </div>
  </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const sidebar = document.getElementById('sidebar');
  const toggle = document.querySelector('.menu-toggle');
  const backdrop = document.getElementById('backdrop');
  if (toggle){
    toggle.addEventListener('click', () => {
      sidebar.classList.toggle('show');
      backdrop.classList.toggle('show');
    });
  }
  if (backdrop){
    backdrop.addEventListener('click', () => {
      sidebar.classList.remove('show');
      backdrop.classList.remove('show');
    });
  }
</script>
</body>
</html>
