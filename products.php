  <!-- Header -->
  <?php require_once 'bootstrap.php';
include 'header.php'; ?>






  <!-- Cabeçalho da página -->
  <div class="page-heading products-heading header-text">
    <div class="container">
      <div class="row"><div class="col-md-12">
        <h1>Todas as Receitas</h1>
        <span>Explore todos os sabores disponíveis</span>
      </div></div>
    </div>
  </div>


<?php


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

  <!-- Lista de receitas -->
 





 <div class="latest-products">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <div class="section-heading d-flex justify-content-between align-items-center">
            <h2>Receitas recentes</h2>
            <a href="products.php">ver todas as receitas <i class="fa fa-angle-right"></i></a>
          </div>
        </div>

        <?php if ($receitas && $receitas->num_rows > 0): ?>
          <?php while ($r = $receitas->fetch_assoc()): ?>
            <?php
              $id   = (int)($r['idreceita'] ?? 0);
              $nome = htmlspecialchars($r['nome'] ?? '', ENT_QUOTES, 'UTF-8');
              $descricao = htmlspecialchars($r['descricao'] ?? '', ENT_QUOTES, 'UTF-8');

              // filename seguro
              $imgFile = basename($r['imagens'] ?? '');
              $imgFile = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $imgFile);
              $imgPath = "assets/images/" . $imgFile;

              $preco  = isset($r['preco']) ? (float)$r['preco'] : 0.0;
              $gratis = ($preco <= 0);
            ?>
            <div class="col-6 col-sm-6 col-md-4 col-lg-3 mb-4">
              <div class="product-item h-100 position-relative">
                <?php if ($gratis): ?>
                  <span class="badge badge-brand position-absolute" style="top:8px; right:8px;">Grátis</span>
                <?php endif; ?>



<?php if ($logado && ($_SESSION['id_tipo'] == 1) ){{ ?>



                <a    href="product-details.php?id=<?= $id ?>   ">
<?php } }else{ ?>


  <a     <?php if ($gratis): ?>   href="product-details.php?id=<?= $id ?>   <?php endif; ?> ">


  <?php } ?>

                  <img
                    src="<?= $imgPath ?>"
                    alt="<?= $nome ?>"
                    loading="lazy"
                    onerror="this.src='assets/images/placeholder.jpg';"
                    style="width:100%;height:200px;object-fit:cover;">
                </a>








                <div class="down-content">
                  <!-- linha: Nome + Preço à direita -->
                  <div class="pi-header-line">


                    <a  <?php if ($gratis): ?>   href="product-details.php?id=<?= $id ?>     <?php endif; ?>    " class="pi-title">
                      
                    
                    <?= $nome ?></a>


                    <span class="pi-price"><?= $gratis ? 'Grátis' : formatPrice($preco) ?></span>
                  </div>

                  <!-- descrição -->
                  <p class="pi-desc"><?= $descricao ?></p>


 <?php if ($logado && ($_SESSION['id_tipo'] == 1) ){ ?>

                  <!-- Botão para ver a receita só aparece no hover e só se preço = 0 -->

                
  <form method="get" action="product-details.php" class="mt-2 mb-0">
    <input type="hidden" name="id" value="<?= $id ?>">
    <button type="submit" class="btn btn-sm btn-outline-primary btn-block add-to-cart-btn">
      Ver receita
    </button>
  </form>

<?php  }else{ ?>

  <!-- Botão Adicionar ao Carrinho: só aparece no hover e só se preço > 0 -->
                  <?php if (!$gratis): ?>
                    <form method="post" action="adicionar_carrinho.php" class="mt-2 mb-0">
                       <input type="hidden" name="idreceita" value="<?= $id ?>">
    <input type="hidden" name="nome" value="<?= $nome ?>">
    <input type="hidden" name="preco" value="<?= $preco ?>">
    <input type="hidden" name="imagem" value="<?= $imgPath ?>">
                      <button type="submit" class="btn btn-sm btn-outline-primary btn-block add-to-cart-btn">
                        Adicionar ao Carrinho
                      </button>
                    </form>
                  <?php endif; ?>



<!-- Botão para ver a receita só aparece no hover e só se preço = 0 -->

                  <?php if ($gratis): ?>
  <form method="get" action="product-details.php" class="mt-2 mb-0">
    <input type="hidden" name="id" value="<?= $id ?>">
    <button type="submit" class="btn btn-sm btn-outline-primary btn-block add-to-cart-btn">
      Ver receita
    </button>
  </form>
<?php endif; ?>


    <?php } ?>



                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="col-12"><p>Nenhuma receita encontrada.</p></div>
        <?php endif; ?>

        <!-- Paginação -->
        <div class="col-12">
          <nav aria-label="Paginação" class="mt-3">
            <ul class="pagination justify-content-center">
              <?php for ($i = 1; $i <= $pages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                  <a class="page-link" href="?p=<?= $i ?>"><?= $i ?></a>
                </li>
              <?php endfor; ?>
            </ul>
          </nav>
        </div>

      </div>
    </div>
  </div>


  <!-- Rodapé -->
<?php include 'footer.php'; ?>

  <!-- JS -->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/custom.js"></script>
  <script src="assets/js/owl.js"></script>
</body>
</html>
