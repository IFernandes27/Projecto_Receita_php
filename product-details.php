<?php include 'header.php'; 


  if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idreceita = intval($_GET['id']);

    $sql = "SELECT * FROM receita WHERE idreceita = $idreceita LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $receita = $result->fetch_assoc();
    } else {
        die("Receita não encontrada.");
    }
} else {
    die("ID da receita inválido.");
}
?>
  <!-- Page Title -->
  <div class="page-heading products-heading header-text">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h1>Detalhes da Receita</h1>
          <span>Aprenda a preparar pratos tradicionais com facilidade</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Receita -->
  <div class="single-product mt-5 mb-5">
    <div class="container">
      <div class="row">
        <div class="col-md-4">
          <img src="assets/images/<?php echo htmlspecialchars($receita['imagens']); ?>" alt="<?php echo htmlspecialchars($receita['nome']); ?>" class="img-fluid">

        </div>
        <div class="col-md-6">
         <h2><?php echo htmlspecialchars($receita['nome']); ?></h2>
          <h6>
<?php
if ($receita['preco'] == 0) {
    echo "Grátis";
} else {
    echo "€ " . number_format($receita['preco'], 2, ',', '.');
}
?>

          <h5 class="mt-4">Ingredientes:</h5><br>
<p><?php echo nl2br(htmlspecialchars($receita['ingredientes'])); ?></p>

          <h5 class="mt-4">Modo de Preparo:</h5><br>
<p><?php echo nl2br(htmlspecialchars($receita['preparacao'])); ?></p>

          
        </div>
      </div>
    </div>
  </div>







  

  <!-- Outras Receitas -->
<div class="latest-products mt-5">
  <div class="container">
    <div class="section-heading">
      <h2>Receitas Relacionadas</h2>
      <a href="products.php">ver todas <i class="fa fa-angle-right"></i></a>
    </div>
    <div class="row">
      <?php
      // id da receita atual
      $idAtual = (int)$receita['idreceita'];
      $catAtual = (int)$receita['categoria_idcategoria'];

      // Buscar até 3 receitas da mesma categoria (exceto a atual)
      $sql_rel = "SELECT idreceita, nome, preco, imagens, descricao
                  FROM receita
                  WHERE categoria_idcategoria = ? AND idreceita != ?
                  AND preco = 0
                  ORDER BY RAND()
                  LIMIT 3";
      $stmt_rel = $conn->prepare($sql_rel);
      $stmt_rel->bind_param("ii", $catAtual, $idAtual);
      $stmt_rel->execute();
      $relacionadas = $stmt_rel->get_result();

      if ($relacionadas && $relacionadas->num_rows > 0):
        while ($r = $relacionadas->fetch_assoc()):
          $id   = (int)$r['idreceita'];
          $nome = htmlspecialchars($r['nome'], ENT_QUOTES, 'UTF-8');
          $descricao = htmlspecialchars($r['descricao'], ENT_QUOTES, 'UTF-8');
          $img = "assets/images/" . basename($r['imagens']);
          $preco = (float)$r['preco'];
          $gratis = ($preco <= 0);
      ?>
        <div class="col-md-4">
          <div class="product-item">
            <a href="product-details.php?id=<?= $id ?>">
              <img src="<?= $img ?>" alt="<?= $nome ?>" style="width:100%;height:200px;object-fit:cover;">
            </a>
            <div class="down-content">
              <a href="product-details.php?id=<?= $id ?>">
                <h4><?= $nome ?></h4>
              </a>
              <h6><?= $gratis ? 'Grátis' : '€ ' . number_format($preco, 2, ',', '.') ?></h6>
              <p><?= mb_strimwidth($descricao, 0, 90, '...') ?></p>
            </div>
          </div>
        </div>
      <?php
        endwhile;
      else:
        echo "<p class='col-12 text-muted'>Nenhuma receita relacionada encontrada.</p>";
      endif;
      ?>
    </div>
  </div>
</div>


   <!-- Rodapé -->
<?php include 'footer.php'; ?>

  <!-- Scripts -->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/custom.js"></script>
  <script src="assets/js/owl.js"></script>

</body>

</html>