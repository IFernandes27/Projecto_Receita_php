  <!-- Header -->
  <?php include 'header.php'; ?>

 



<?php





if (!isset($_SESSION['carrinho'])) {
  $_SESSION['carrinho'] = [];
}

/* Ações: remover item / limpar carrinho */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
  $acao = $_POST['acao'];

  if ($acao === 'remover' && isset($_POST['id'])) {
    $id = preg_replace('/\D/', '', (string)$_POST['id']);
    unset($_SESSION['carrinho'][$id]);
  }

  if ($acao === 'limpar') {
    $_SESSION['carrinho'] = [];
  }

  header('Location: carrinho.php');
  exit;
}



  /* Dados para apresentação */
$itens = $_SESSION['carrinho'];
$total = 0.0;
foreach ($itens as $it) {
  $total += (float)($it['preco'] ?? 0); // quantidade fixa = 1
}



/* Ações: remover item / limpar carrinho */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
  $acao = $_POST['acao'];

  if ($acao === 'remover' && isset($_POST['id'])) {
    $id = preg_replace('/\D/', '', (string)$_POST['id']);
    unset($_SESSION['carrinho'][$id]);
  }

  if ($acao === 'limpar') {
    $_SESSION['carrinho'] = [];
  }

  header('Location: carrinho.php');
  exit;
}




// Total
$total = 0;
if ($res = $conn->query("SELECT COUNT(*) AS c FROM receita")) {
  $row   = $res->fetch_assoc();
  $total = (int)($row['c'] ?? 0);
}
$pages = max(1, (int)ceil($total / $perPage));




// Receitas (todas)
$sql = "SELECT idreceita, nome, preco, imagens, descricao
        FROM receita
        ORDER BY idreceita DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $perPage, $offset);
$stmt->execute();
$receitas = $stmt->get_result();

?>

  <div class="page-heading products-heading header-text">
    <div class="container">
      <div class="row"><div class="col-md-12">
        <h1>Meu Carrinho</h1>
        <span>Revê as tuas receitas antes de finalizar</span>
      </div></div>
    </div>
  </div>

  <div class="container my-4">
    <?php if (!empty($_SESSION['msg'])): $m = $_SESSION['msg']; unset($_SESSION['msg']); ?>
      <div class="alert alert-<?= htmlspecialchars($m['tipo'] ?? 'info') ?>"><?= htmlspecialchars($m['texto'] ?? '') ?></div>
    <?php endif; ?>

    <?php if (empty($itens)): ?>
      <div class="cart-empty">
        <h5>O teu carrinho está vazio.</h5>
        <p class="mb-3">Explora as receitas e adiciona as tuas favoritas!</p>
        <a href="index.php" class="filled-button">Voltar à loja</a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="thead-light">
            <tr>
              <th style="width:60%">Produto</th>
              <th style="width:15%">Preço</th>
              <th style="width:10%">Quantidade</th>
              <th style="width:10%">Subtotal</th>
              <th style="width:5%"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($itens as $id => $item):
              $nome  = htmlspecialchars($item['nome'] ?? '', ENT_QUOTES, 'UTF-8');
              $preco = (float)($item['preco'] ?? 0);
              $img   = htmlspecialchars($item['imagem'] ?? '', ENT_QUOTES, 'UTF-8');
              $line  = $preco; // 1 unidade
            ?>
            <tr>
              <td>
                <div class="media align-items-center">
                  <?php if (!empty($img)): ?>
                    <img src="<?= $img ?>" alt="<?= $nome ?>" class="mr-3 cart-item-img">
                  <?php endif; ?>
                  <div class="media-body">
                    <strong><?= $nome ?></strong>
                    <div><small class="text-muted">ID: <?= (int)$id ?></small></div>
                  </div>
                </div>
              </td>
              <td>€<?= number_format($preco, 2, ',', '.') ?></td>
              <td>1</td>
              <td><strong>€<?= number_format($line, 2, ',', '.') ?></strong></td>
              <td class="text-center">
                <form method="post" class="m-0 p-0 d-inline">
                  <input type="hidden" name="acao" value="remover">
                  <input type="hidden" name="id" value="<?= (int)$id ?>">
                  <button class="btn btn-sm btn-outline-danger" title="Remover">&times;</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" class="text-right"><strong>Total:</strong></td>
              <td colspan="2"><strong><?= formatPrice($cartTotal) ?></strong></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="d-flex justify-content-between">
        <div>
          <form method="post" class="d-inline" onsubmit="return confirm('Tens a certeza que queres limpar o carrinho?');">
            <input type="hidden" name="acao" value="limpar">
            <button type="submit" class="border-button">Limpar carrinho</button>
          </form>
          <a href="index.php" class="border-button ml-2">Continuar a comprar</a>
        </div>
        <a href="finalizar.php" class="filled-button<?= $total <= 0 ? ' disabled' : '' ?>">Finalizar compra</a>

      </div>
    <?php endif; ?>
  </div>

  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/custom.js"></script>
  <script src="assets/js/owl.js"></script>
  <script src="assets/js/slick.js"></script>
  <script src="assets/js/isotope.js"></script>
  <script src="assets/js/accordions.js"></script>
</body>
</html>
