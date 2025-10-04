<?php


//não é valido
include 'conexao.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
  die("ID inválido.");
}

$sql = "SELECT r.*, p.Pais AS nome_pais
        FROM receita r
        JOIN pais p ON r.pais_idpais = p.idpais
        WHERE r.idreceita = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
  die("Receita não encontrada.");
}

$receita = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($receita['nome']) ?> - Detalhes da Receita</title>
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/fontawesome.css">
  <link rel="stylesheet" href="assets/css/templatemo-sixteen.css">
  <link rel="stylesheet" href="assets/css/owl.css">
  <style>
    .detalhes-container {
      padding: 50px 0;
    }

    .img-receita {
      width: 100%;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }

    .info-receita h2 {
      font-weight: bold;
      margin-top: 10px;
    }

    .info-receita h6 {
      color: #888;
      margin-bottom: 20px;
    }

    .info-receita p {
      line-height: 1.6;
    }

    .filled-button {
      margin-top: 20px;
    }
  </style>
</head>

<body>

  <!-- Header -->
  <header>
    <nav class="navbar navbar-expand-lg">
      <div class="container">
        <a class="navbar-brand" href="index.php">
          <h2>Sabores <em>Lusófonos</em></h2>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
          <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="index.php">Início</a></li>
            <li class="nav-item"><a class="nav-link" href="recipes.php">Receitas</a></li>
            <li class="nav-item"><a class="nav-link" href="about.php">Sobre</a></li>
            <li class="nav-item"><a class="nav-link" href="contact.php">Contato</a></li>

  <!-- Carrinho -->
<?php
session_start();
$totalItens = 0;
if (isset($_SESSION['carrinho'])) {
  foreach ($_SESSION['carrinho'] as $item) {
    $totalItens += $item['quantidade'];
  }
}
?>

<li class="nav-item">
  <a class="nav-link" href="carrinho.php">
    <i class="fa fa-shopping-cart"></i>
    Carrinho
    <?php if ($totalItens > 0): ?>
      <span class="badge badge-pill badge-danger"><?php echo $totalItens; ?></span>
    <?php endif; ?>
  </a>
</li>

  <!-- Fim do carrinho -->


          </ul>
        </div>
      </div>
    </nav>
  </header>

  <!-- Título da Página -->
  <div class="page-heading products-heading header-text">
    <div class="container text-center">
      <h1><?= htmlspecialchars($receita['nome']) ?></h1>
      <span>Receita típica de <?= htmlspecialchars($receita['nome_pais']) ?></span>
    </div>
  </div>

  <!-- Detalhes da Receita -->
  <div class="detalhes-container">
    <div class="container">
      <div class="row">
        <!-- Imagem -->
        <div class="col-md-5">
          <img src="assets/images/<?= htmlspecialchars($receita['imagens']) ?>" alt="<?= htmlspecialchars($receita['nome']) ?>" class="img-fluid img-receita">
        </div>

        <!-- Informações -->
        <div class="col-md-7 info-receita">
          <h2><?= htmlspecialchars($receita['nome']) ?></h2>
          <h6>Preço: €<?= number_format($receita['preco'], 2, ',', '.') ?></h6>

          <h5 class="mt-4">Ingredientes:</h5>
          <p><?= nl2br(htmlspecialchars($receita['ingredientes'])) ?></p>

          <h5 class="mt-4">Modo de Preparo:</h5>
          <p><?= nl2br(htmlspecialchars($receita['preparacao'])) ?></p>

          
           <!-- Botão adicionar ao carrinho -->
          <form action="adicionar_carrinho.php" method="post">
  <input type="hidden" name="idreceita" value="<?php echo $receita['idreceita']; ?>">
  <input type="hidden" name="nome" value="<?php echo htmlspecialchars($receita['nome']); ?>">
  <input type="hidden" name="preco" value="<?php echo $receita['preco']; ?>">
  <input type="hidden" name="imagem" value="<?php echo htmlspecialchars($receita['imagens']); ?>">
  <button type="submit" class="filled-button mt-3">Adicionar ao Carrinho</button>
</form>

        </div>
      </div>
    </div>
  </div>







<!-- Outras Receitas -->
  <div class="latest-products">
    <div class="container">
      <div class="section-heading">
        <h2>Receitas Relacionadas</h2>
        <a href="recipes.html">ver todas <i class="fa fa-angle-right"></i></a>
      </div>
      <div class="row">
        <div class="col-md-4">
          <div class="product-item">
            <a href="#"><img src="assets/images/moqueca.jpg" alt="Moqueca Baiana"></a>
            <div class="down-content">
              <a href="#">
                <h4>Moqueca Baiana</h4>
              </a>
              <h6>R$ 18,00</h6>
              <p>Peixe cozido com leite de coco, azeite de dendê e temperos frescos.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="product-item">
            <a href="#"><img src="assets/images/bobó.jpg" alt="Bobó de Camarão"></a>
            <div class="down-content">
              <a href="#">
                <h4>Bobó de Camarão</h4>
              </a>
              <h6>R$ 20,00</h6>
              <p>Delicioso prato de camarão com purê de mandioca e temperos típicos.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="product-item">
            <a href="#"><img src="assets/images/bolo-fuba.jpg" alt="Bolo de Fubá"></a>
            <div class="down-content">
              <a href="#">
                <h4>Bolo de Fubá</h4>
              </a>
              <h6>R$ 10,00</h6>
              <p>Bolo simples e saboroso, ideal para o café da tarde.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>




  <!-- Rodapé -->
  <footer>
    <div class="container">
      <div class="inner-content text-center py-4">
        <p>&copy; <script>document.write(new Date().getFullYear())</script> Sabores Lusófonos - Design: <a href="https://templatemo.com" target="_blank">TemplateMo</a></p>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/custom.js"></script>
  <script src="assets/js/owl.js"></script>

</body>

</html>
