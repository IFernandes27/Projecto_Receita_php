<?php
// cliente/perfil.php
session_start();




if (!isset($_SESSION['id_utilizador'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../conexao.php'; // deve definir $conn (mysqli)

// charset seguro
if (method_exists($conn, 'set_charset')) { $conn->set_charset('utf8mb4'); }

// helper de escapagem HTML
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$userId = (int) $_SESSION['id_utilizador'];
$alerts = [];

// Flash de mensagens (após redirects)
if (!empty($_SESSION['flash'])) {
    $alerts[] = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper: buscar dados do utilizador
function fetchUser(mysqli $conn, int $userId): array {
    $stmt = $conn->prepare("SELECT nome, username, email, telefone FROM utilizador WHERE id_utilizador = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($nome, $username, $email, $telefone);
    $stmt->fetch();
    $stmt->close();
    return [
        'nome' => $nome ?? '',
        'username' => $username ?? '',
        'email' => $email ?? '',
        'telefone' => $telefone ?? ''
    ];
}

// Buscar dados iniciais
$user = fetchUser($conn, $userId);

// Processar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $alerts[] = ['type' => 'danger', 'msg' => 'Falha de segurança (CSRF). Atualize a página e tente novamente.'];
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') {
            // Editar nome/username/email/telefone
            $nome     = trim($_POST['nome'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');

            // Normalizar case (opcional: usernames e emails sem distinção de maiúsculas)
            $emailNorm    = mb_strtolower($email, 'UTF-8');
            $usernameNorm = mb_strtolower($username, 'UTF-8');

            // Validações
            if ($nome === '' || $emailNorm === '' || $usernameNorm === '') {
                $alerts[] = ['type' => 'warning', 'msg' => 'Nome, username e email são obrigatórios.'];
            } elseif (!preg_match('/^[a-z0-9._-]{3,20}$/', $usernameNorm)) {
                $alerts[] = ['type' => 'warning', 'msg' => 'O username deve ter 3–20 caracteres (letras, números, ponto, underscore ou hífen).'];
            } elseif (!filter_var($emailNorm, FILTER_VALIDATE_EMAIL)) {
                $alerts[] = ['type' => 'warning', 'msg' => 'O email não é válido.'];
            } elseif ($telefone !== '' && !preg_match('/^\+?[0-9\s\-]{6,20}$/', $telefone)) {
                $alerts[] = ['type' => 'warning', 'msg' => 'O telefone deve conter apenas dígitos, espaço ou “-” (6–20 caracteres).'];
            } else {
                // Pré-checagens de duplicado (amigáveis)
                $chkU = $conn->prepare("SELECT id_utilizador FROM utilizador WHERE LOWER(username) = ? AND id_utilizador <> ?");
                $chkU->bind_param('si', $usernameNorm, $userId);
                $chkU->execute();
                $chkU->store_result();

                $chkE = $conn->prepare("SELECT id_utilizador FROM utilizador WHERE LOWER(email) = ? AND id_utilizador <> ?");
                $chkE->bind_param('si', $emailNorm, $userId);
                $chkE->execute();
                $chkE->store_result();

                if ($chkU->num_rows > 0) {
                    $alerts[] = ['type' => 'danger', 'msg' => 'Este username já está em uso por outro utilizador.'];
                } elseif ($chkE->num_rows > 0) {
                    $alerts[] = ['type' => 'danger', 'msg' => 'Este email já está em uso por outro utilizador.'];
                } else {
                    // Update (telefone vira NULL se vazio)
                    $up = $conn->prepare("
                        UPDATE utilizador
                        SET nome = ?, username = ?, email = ?, telefone = NULLIF(?, '')
                        WHERE id_utilizador = ?
                    ");
                    // Gravaremos os valores normalizados (em minúsculas) para garantir unicidade consistente
                    $up->bind_param('ssssi', $nome, $usernameNorm, $emailNorm, $telefone, $userId);

                    if ($up->execute()) {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Dados atualizados com sucesso.'];
                        header('Location: perfil.php'); exit;
                    } else {
                        if ($up->errno == 1062) {
                            $alerts[] = ['type' => 'danger', 'msg' => 'Email ou username já está em uso.'];
                        } else {
                            $alerts[] = ['type' => 'danger', 'msg' => 'Erro ao atualizar os dados. Tente novamente.'];
                        }
                    }
                    $up->close();
                }
                $chkU->close();
                $chkE->close();
            }

            // Manter valores digitados no formulário se houve erros
            if (!empty($alerts)) {
                $user = [
                    'nome'     => $nome,
                    'username' => $usernameNorm,
                    'email'    => $emailNorm,
                    'telefone' => $telefone,
                ];
            }

        } elseif ($action === 'change_password') {
            $senhaAtual = $_POST['senha_atual'] ?? '';
            $senhaNova  = $_POST['senha_nova']  ?? '';
            $senhaConf  = $_POST['senha_confirma'] ?? '';

            if ($senhaNova !== $senhaConf) {
                $alerts[] = ['type' => 'warning', 'msg' => 'A confirmação da nova senha não coincide.'];
            } elseif (strlen($senhaNova) < 8) {
                $alerts[] = ['type' => 'warning', 'msg' => 'A nova senha deve ter pelo menos 8 caracteres.'];
            } else {
                $stmt = $conn->prepare("SELECT password FROM utilizador WHERE id_utilizador = ?");
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $stmt->bind_result($hashAtual);
                if ($stmt->fetch()) {
                    $stmt->close();
                    if (!password_verify($senhaAtual, $hashAtual)) {
                        $alerts[] = ['type' => 'danger', 'msg' => 'A senha atual está incorreta.'];
                    } else {
                        $novoHash = password_hash($senhaNova, PASSWORD_DEFAULT);
                        $up = $conn->prepare("UPDATE utilizador SET password = ? WHERE id_utilizador = ?");
                        $up->bind_param('si', $novoHash, $userId);
                        if ($up->execute()) {
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Senha alterada com sucesso!'];
                            header('Location: perfil.php'); exit;
                        } else {
                            $alerts[] = ['type' => 'danger', 'msg' => 'Erro ao atualizar a senha. Tente novamente.'];
                        }
                        $up->close();
                    }
                } else {
                    $stmt->close();
                    $alerts[] = ['type' => 'danger', 'msg' => 'Utilizador não encontrado.'];
                }
            }
        }
    }
}

// (Re)buscar dados atuais do utilizador se não há “sticky” já aplicado
if (empty($alerts) || (count($alerts) === 1 && isset($_SESSION['flash']))) {
    $user = fetchUser($conn, $userId);
}

// Inicial do avatar e página atual
$initial = mb_strtoupper(mb_substr($user['nome'] ?: 'U', 0, 1, 'UTF-8'), 'UTF-8');
$current = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <title>Sabores da CPLP — Perfil</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --brand: rgb(230, 218, 208);
      --sidebar-w: 280px;
    }
    body { background-color: #f8f9fa; }

    /* Sidebar fixa */
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

    /* Conteúdo */
    .content{ margin-left: var(--sidebar-w); min-height: 100vh; display:flex; flex-direction:column; }

    /* Topbar */
    .topbar{ position: sticky; top: 0; z-index: 1030; background:#fff; border-bottom:1px solid #eee; }
    .avatar-top{ width: 38px; height: 38px; border-radius: 50%; background:#e9ecef; display:grid; place-items:center; font-weight:700; }

    @media (max-width: 991.98px){
      .sidebar{ transform: translateX(-100%); transition: transform .3s ease; }
      .sidebar.show{ transform: translateX(0); }
      .content{ margin-left: 0; }
      .backdrop{ position: fixed; inset: 0; background: rgba(0,0,0,.25); z-index: 1035; display: none; }
      .backdrop.show{ display: block; }
    }

    .card-rounded { border-radius: 1rem; }
    .brand-color { color: var(--brand); }
  </style>
</head>
<body>

<div class="backdrop d-lg-none" id="backdrop"></div>

<!-- SIDEBAR FIXA -->
<aside class="sidebar" id="sidebar">
  <div class="brand">
    <div class="logo">SC</div>
    <div class="fw-bold">Sabores <span class="brand-color">da CPLP</span></div>
  </div>

  <div class="nav-section-title">Navegação</div>
  <nav class="nav flex-column nav-sidebar">
    <a class="nav-link <?= $current === 'perfil.php' ? 'active' : '' ?>" href="perfil.php">
      <i class="bi bi-person"></i> Perfil
    </a>
    <a class="nav-link" href="minhas_receitas.php">
      <i class="bi bi-book"></i> Minhas Receitas
    </a>
    <a class="nav-link" href="../carrinho.php">
      <i class="bi bi-cart3"></i> Carrinho
    </a>
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
        <div class="fw-semibold">Área do Cliente</div>
      </div>

      <!-- Avatar no topo direito -->
      <div class="dropdown">
        <a href="#" class="d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="d-none d-sm-inline text-muted small"><?= h($user['nome']) ?></span>
          <div class="avatar-top"><?= h($initial) ?></div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow">
          <li class="dropdown-header small">
            <div class="fw-semibold"><?= h($user['nome']) ?></div>
            <div class="text-muted"><?= h($user['email']) ?></div>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="perfil.php"><i class="bi bi-person me-2"></i>Perfil</a></li>
          <li><a class="dropdown-item" href="minhas_receitas.php"><i class="bi bi-book me-2"></i>Minhas Receitas</a></li>
          <li><a class="dropdown-item" href="../carrinho.php"><i class="bi bi-cart3 me-2"></i>Carrinho</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Terminar sessão</a></li>
        </ul>
      </div>
    </div>
  </div>

  <main class="container py-4">
    <?php foreach ($alerts as $a): ?>
      <div class="alert alert-<?= h($a['type']) ?> alert-dismissible fade show" role="alert">
        <?= h($a['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
      </div>
    <?php endforeach; ?>

    <div class="row g-4">
      <!-- Formulário: Editar dados pessoais (inclui USERNAME) -->
      <div class="col-12 col-lg-6">
        <div class="card shadow-sm card-rounded">
          <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Dados Pessoais</h5>
          </div>
          <div class="card-body">
            <form method="post" novalidate>
              <input type="hidden" name="action" value="update_profile">
              <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">

              <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" required autocomplete="name" value="<?= h($user['nome']) ?>">
              </div>

              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input
                  type="text"
                  class="form-control"
                  id="username"
                  name="username"
                  required
                  minlength="3"
                  maxlength="20"
                  pattern="[a-z0-9._-]{3,20}"
                  autocomplete="username"
                  value="<?= h($user['username']) ?>"
                >
                <div class="form-text">3–20 chars: letras, números, ponto, underscore ou hífen (case-insensitive).</div>
              </div>

              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required autocomplete="email" value="<?= h($user['email']) ?>">
              </div>

              <div class="mb-3">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="text" class="form-control" id="telefone" name="telefone" inputmode="tel" autocomplete="tel" value="<?= h($user['telefone']) ?>">
              </div>

              <button type="submit" class="btn btn-dark">Guardar alterações</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Formulário: Alterar Senha -->
      <div class="col-12 col-lg-6">
        <div class="card shadow-sm card-rounded">
          <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Alterar Senha</h5>
          </div>
          <div class="card-body">
            <form method="post" novalidate>
              <input type="hidden" name="action" value="change_password">
              <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">

              <div class="mb-3">
                <label for="senha_atual" class="form-label">Senha atual</label>
                <input type="password" class="form-control" id="senha_atual" name="senha_atual" required autocomplete="current-password">
              </div>

              <div class="mb-3">
                <label for="senha_nova" class="form-label">Nova senha</label>
                <input type="password" class="form-control" id="senha_nova" name="senha_nova" required minlength="8" autocomplete="new-password">
                <div class="form-text">Pelo menos 8 caracteres.</div>
              </div>

              <div class="mb-3">
                <label for="senha_confirma" class="form-label">Confirmar nova senha</label>
                <input type="password" class="form-control" id="senha_confirma" name="senha_confirma" required autocomplete="new-password">
              </div>

              <button type="submit" class="btn btn-dark">Guardar nova senha</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="py-4 mt-4">
    <div class="container text-center text-muted small">
      &copy; <?= date('Y') ?> Sabores da CPLP
    </div>
  </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Toggle da sidebar em ecrãs pequenos
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
