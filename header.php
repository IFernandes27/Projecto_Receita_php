
<?php


require_once 'bootstrap.php'; 


function is_logged_in(): bool {
  return !empty($_SESSION['user_id']);
}
$username = htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8');
$id_tipo  = (int)($_SESSION['id_tipo'] ?? 0);








/* Inicia sessão (evitando múltiplos session_start)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}*/

require_once 'conexao.php';

/** CSRF */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf'];

/** Helpers */
function slugify(string $str): string {
    $s = $str;
    if (function_exists('transliterator_transliterate')) {
        $s = transliterator_transliterate('Any-Latin; Latin-ASCII', $s);
    } else {
        $tmp = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($tmp !== false) $s = $tmp;
    }
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    $s = trim($s, '-');
    return $s ?: 'pais';
}
function formatPrice(float $v): string {
    return '€ ' . number_format($v, 2, ',', '.');
}

// ------- ler slug do país -------
$slug = trim($_GET['pais'] ?? '');

/** --------- Dados de listagem/paginação --------- */
$perPage = 32;
$page    = max(1, (int)($_GET['p'] ?? 1));
$offset  = ($page - 1) * $perPage;



/** Total p/ paginação */
$total = 0;
if ($res = $conn->query("SELECT COUNT(*) AS c FROM receita")) {
    $row   = $res->fetch_assoc();
    $total = (int)($row['c'] ?? 0);
}
$pages = max(1, (int)ceil($total / $perPage));

// ------- obter lista de países para navbar e para mapear slug -> idpais -------
$lista_paises = [];
$slug2pais = []; // slug => ['idpais'=>.., 'Pais'=>..]
if ($rs = $conn->query("SELECT idpais, Pais FROM pais ORDER BY Pais ASC")) {
  $lista_paises = $rs->fetch_all(MYSQLI_ASSOC);
  foreach ($lista_paises as $row) {
    $slug2pais[slugify($row['Pais'])] = ['idpais' => (int)$row['idpais'], 'Pais' => $row['Pais']];
  }
}


// ------- validar slug -------
$paisAtual = null;
if ($slug !== '' && isset($slug2pais[$slug])) {
  $paisAtual = $slug2pais[$slug];
}

// Se o slug não for válido, mostrar mensagem amigável
if (!$paisAtual) {
  $tituloPagina2 = "Receitas por País";
} else {
  $tituloPagina2 = "Receitas de " . htmlspecialchars($paisAtual['Pais'], ENT_QUOTES, 'UTF-8');
}



// Ler slug da categoria
$slug = trim($_GET['cat'] ?? '');

// Categorias (para dropdown e mapear slug->id)
$lista_categorias = [];
$slug2cat = [];
if ($rs = $conn->query("SELECT idcategoria, nome_categoria FROM categoria ORDER BY nome_categoria ASC")) {
  $lista_categorias = $rs->fetch_all(MYSQLI_ASSOC);
  foreach ($lista_categorias as $c) {
    $slug2cat[slugify($c['nome_categoria'])] = [
      'idcategoria'   => (int)$c['idcategoria'],
      'nome_categoria'=> $c['nome_categoria']
    ];
  }
}


// Categoria atual (se slug válido)
$categoriaAtual = null;
if ($slug !== '' && isset($slug2cat[$slug])) {
  $categoriaAtual = $slug2cat[$slug];
}
$tituloPagina = $categoriaAtual
  ? 'Receitas de ' . htmlspecialchars($categoriaAtual['nome_categoria'], ENT_QUOTES, 'UTF-8')
  : 'Receitas por Categoria';




// Carrinho (contador simples para badge)
$cartCount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $it) {
    $cartCount += (int)($it['qty'] ?? 1);
  }
}





// === NORMALIZADOR DO CARRINHO (suporta 'cart' e 'carrinho' e chaves diversas) ===
$cartCount = 0;
$cartItems = [];
$cartTotal = 0.0;

// fonte: aceita $_SESSION['cart'] OU $_SESSION['carrinho']
$cartSession = [];
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
  $cartSession = $_SESSION['cart'];
} elseif (!empty($_SESSION['carrinho']) && is_array($_SESSION['carrinho'])) {
  $cartSession = $_SESSION['carrinho'];
}

// alguns projetos guardam como array indexado por ID (associativo)
foreach ($cartSession as $key => $raw) {
  // normalizar chaves
  $id      = (int)($raw['id'] ?? $raw['idreceita'] ?? $key);
  $nome    = (string)($raw['nome'] ?? $raw['titulo'] ?? '');
  $preco   = (float)($raw['preco'] ?? $raw['price'] ?? 0);
  $qty     = (int)($raw['qty'] ?? $raw['quantidade'] ?? 1);
  $imagem  = (string)($raw['imagens'] ?? $raw['imagem'] ?? '');

  // sanear imagem para não quebrar o src
  $imagem  = basename($imagem);

  if ($id <= 0 || $qty <= 0) { continue; }

  $line = $preco * $qty;

  $cartItems[] = [
    'id'      => $id,
    'nome'    => htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'),
    'imagens' => $imagem,
    'qty'     => $qty,
    'preco'   => $preco,
    'line'    => $line,
  ];

  $cartCount += $qty;
  $cartTotal += $line;
}




//vê se esta logado e guarda na variavel logado para icone da conta
if (session_status() === PHP_SESSION_NONE) session_start();
$logado = !empty($_SESSION['id_tipo']);

?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Sabores da CPLP — Receitas Lusófonas</title>
  <meta name="description" content="Descubra receitas tradicionais dos países da CPLP. Explore e compre receitas autênticas dos sabores lusófonos.">
  <!-- <link rel="canonical" href="https://o-teu-dominio.tld/"> -->

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,600,700&display=swap" rel="stylesheet">



<!-- OwlCarousel2 CSS (CDN confiável) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">


  <!-- Bootstrap core CSS -->

  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!-- Additional CSS Files -->
  <link rel="stylesheet" href="assets/css/fontawesome.css">
  <link rel="stylesheet" href="assets/css/templatemo-sixteen.css">
  <link rel="stylesheet" href="assets/css/owl.css">

  <!-- Font Awesome CDN (ícone de conta) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* Cor principal da marca */
    :root{ --brand: rgb(230, 218, 208); }
    .filled-button{ background: var(--brand) !important; color:#333 !important; border-color: var(--brand) !important; }
    .filled-button:hover{ filter:brightness(0.95); color:#111 !important; }
    .badge-brand{ background: var(--brand); color:#333; }

    /* Grid dos produtos */
   .product-item .down-content{ position:relative; padding-right:30px; }
    .product-item .down-content .pi-header-line{ display:flex; align-items:flex-start; justify-content:space-between; gap:.5rem; margin-bottom:.25rem; }
    .product-item .down-content .pi-title{ flex:1; min-width:0; display:block; font-size:1.05rem; font-weight:600; color:inherit; text-decoration:none; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .product-item .down-content .pi-price{ white-space:nowrap; font-weight:700; color:#28a745; }
    .product-item .down-content .pi-desc{ display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; line-height:1.3em; min-height:calc(1.3em * 3); margin:0; }
    .product-item .down-content h6{ display:none !important; }

    /* Botão Add to Cart só no hover e apenas quando existir */
    .product-item .add-to-cart-btn{ opacity:0; transform:translateY(6px); transition:opacity .15s ease, transform .15s ease; pointer-events:none; }
    .product-item:hover .add-to-cart-btn{ opacity:1; transform:translateY(0); pointer-events:auto; }

    /* Navbar carrinho */
    .cart-badge{ position:relative; display:inline-block; }
    .cart-badge .badge{ position:absolute; top:-8px; right:-12px; font-size:.65rem; }

    /* Dropdown do carrinho */
    .dropdown-menu.cart-dropdown{ min-width:320px; }
    .cart-items .media img{ width:40px; height:40px; object-fit:cover; }
  </style>
<style>
/* user-menu hover dropdown */
.user-menu .dropdown-menu{display:none; margin-top:.25rem;}
@media (hover:hover){
  .user-menu:hover > .dropdown-menu,
  .user-menu:focus-within > .dropdown-menu{display:block;}
}
/* optional: hide default caret */
.user-menu .dropdown-toggle::after{display:none;}



/* Remove seta apenas no dropdown do carrinho */
#cartDropdown::after {
  display: none !important;
}

/* Remove seta apenas no dropdown do ícone de conta */
#userMenu::after {
  display: none !important;
}


</style>
</head>

<body>
  <!-- ***** Preloader Start ***** -->
  <div id="preloader"><div class="jumper"><div></div><div></div><div></div></div></div>
  <!-- ***** Preloader End ***** -->

  <!-- Header -->
  <header>
    <nav class="navbar navbar-expand-lg">
      <div class="container">

  <a class="navbar-brand" href="index.php"><h2>Sabores <em>da CPLP</em></h2></a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive"
          aria-controls="navbarResponsive" aria-expanded="false" aria-label="Alternar navegação">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarResponsive">
          <ul class="navbar-nav ml-auto">
            <li class="nav-item active">
 <a class="nav-link" href="index.php">Início <span class="sr-only">(current)</span></a>
    </li>

          <!-- Drop-list dos Países (uma única query) -->
<li class="nav-item dropdown">
  <a class="nav-link dropdown-toggle" href="#" id="paisDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    Países
  </a>
  <div class="dropdown-menu" aria-labelledby="paisDropdown">
    <?php if (!empty($lista_paises)): ?>
      <?php foreach ($lista_paises as $linha):
          $paisRaw = $linha['Pais'] ?? '';
          $Pais = htmlspecialchars($paisRaw, ENT_QUOTES, 'UTF-8');
          $slug = slugify($paisRaw); // função que já tens
      ?>
        <a class="dropdown-item" href="receita_pais.php?pais=<?= urlencode($slug) ?>"><?= $Pais ?></a>
      <?php endforeach; ?>
    <?php else: ?>
      <a class="dropdown-item disabled" href="#">Nenhum país encontrado</a>
    <?php endif; ?>
  </div>
</li>







<!-- Drop-list de Categorias -->
<li class="nav-item dropdown">
  <a class="nav-link dropdown-toggle" href="#" id="catDropdown" role="button"
     data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    Categorias
  </a>
  <div class="dropdown-menu" aria-labelledby="catDropdown">
    <?php if (!empty($lista_categorias)): ?>
      <?php foreach ($lista_categorias as $c):
        $nomeRaw = $c['nome_categoria'] ?? '';
        $NomeCat = htmlspecialchars($nomeRaw, ENT_QUOTES, 'UTF-8');
        $slugCat = slugify($nomeRaw);
      ?>
        <a class="dropdown-item"
           href="categoria.php?cat=<?= urlencode($slugCat) ?>">
          <?= $NomeCat ?>
        </a>
      <?php endforeach; ?>
    <?php else: ?>
      <a class="dropdown-item disabled" href="#">Nenhuma categoria encontrada</a>
    <?php endif; ?>
  </div>
</li>



            <li class="nav-item"><a class="nav-link" href="contact.php">Contacte-nos</a></li>







         


 <!-- Verificação para não mostra carrinho caso seja administrador -->
<?php  if (!($logado) || ( $_SESSION['id_tipo'] === 2)) { ?>



     <!-- Ícone do carrinho -->

            <li class="nav-item dropdown">
  <a class="nav-link dropdown-toggle cart-badge" href="#" id="cartDropdown"
     role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <i class="fa fa-shopping-cart"></i> Carrinho
    <?php if ($cartCount > 0): ?>
      <span class="badge badge-pill badge-danger"><?= (int)$cartCount ?></span>
    <?php endif; ?>
  </a>

  <div class="dropdown-menu dropdown-menu-right p-3 cart-dropdown" aria-labelledby="cartDropdown" style="min-width:320px">
    <?php if ($cartCount === 0): ?>
      <p class="mb-0 text-muted">O seu carrinho está vazio.</p>
    <?php else: ?>
      <div class="cart-items">
        <?php foreach ($cartItems as $ci): ?>
          <div class="media mb-2">
            <img src="assets/images/<?= $ci['imagens'] !== '' ? $ci['imagens'] : 'placeholder.jpg' ?>"
                 alt="<?= $ci['nome'] ?>" class="mr-2" style="width:40px;height:40px;object-fit:cover;">
            <div class="media-body">
              <div class="d-flex justify-content-between">
                <small class="text-truncate" style="max-width:170px;"><?= $ci['nome'] ?></small>
                <small>x<?= (int)$ci['qty'] ?></small>
              </div>
              <small class="text-muted">
                <?= formatPrice((float)$ci['preco']) ?> • <strong><?= formatPrice((float)$ci['line']) ?></strong>
              </small>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <hr class="my-2">
      <div class="d-flex justify-content-between align-items-center">
        <strong>Total:</strong>
        <strong><?= formatPrice($cartTotal) ?></strong>
      </div>
      <div class="mt-2 d-flex">
        <a href="carrinho.php" class="btn btn-sm btn-outline-secondary flex-fill mr-2">Ver carrinho</a>
        <a href="finalizar.php" class="btn btn-sm btn-primary flex-fill">Checkout</a>
      </div>
    <?php endif; ?>
  </div>
</li>

   

 <!-- Fim de Verificação para não mostra carrinho caso seja administrador -->
<?php } ?>

           
           





          





 
 <!-- Ícone de conta -->

<?php

$perfilHref = 'login.php';
if ($logado) {
  $perfilHref = ((int)$_SESSION['id_tipo'] === 1) ? 'adm/adm1.php' : 'cliente/perfil.php';
}
$username = htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<?php if ($logado): ?>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="userMenu" data-toggle="dropdown" aria-expanded="false">
      <i class="fa fa-user"></i> <?= $username ?: 'Perfil' ?>
    </a>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userMenu">
      <a class="dropdown-item" href="<?= $perfilHref ?>">Perfil</a>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="logout.php">Sair</a>
    </div>
  </li>
<?php else: ?>
  <li class="nav-item">
    <a class="nav-link" href="login.php"><i class="fa fa-user"></i> Login</a>
  </li>
<?php endif; ?>

 <!-- Fim do Ícone de conta -->


                 
          </ul>


        </div>
      </div>
    </nav>
  </header>