<?php

 require_once 'bootstrap.php';
include 'header.php'; 
?>


    <!-- Cabeçalho da página -->
  <div class="page-heading products-heading header-text">
    <div class="container">
      <div class="row"><div class="col-md-12">
        <h1><?= htmlspecialchars($tituloPagina2, ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if ($paisAtual): ?>
          <span>Mostrando receitas do pais <strong><?= htmlspecialchars($paisAtual['Pais'], ENT_QUOTES, 'UTF-8') ?></strong></span>
        <?php else: ?>
          <span>Selecione uma pais no menu acima</span>
        <?php endif; ?>
      </div></div>
    </div>
  </div>

  






<?php

// ------- buscar receitas do país (se válido) -------
$receitas = null; $total = 0; $pages = 1;

if ($paisAtual) {
  // total
  $sqlCount = "SELECT COUNT(*) AS c FROM receita WHERE pais_idpais = ?";
  $stc = $conn->prepare($sqlCount);
  $stc->bind_param('i', $paisAtual['idpais']);
  $stc->execute();
  $rc = $stc->get_result()->fetch_assoc();
  $total = (int)($rc['c'] ?? 0);
  $pages = max(1, (int)ceil($total / $perPage));

  // lista
  $sql = "SELECT idreceita, nome, descricao, preco, imagens
          FROM receita
          WHERE pais_idpais = ?
          ORDER BY idreceita DESC
          LIMIT ? OFFSET ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('iii', $paisAtual['idpais'], $perPage, $offset);
  $stmt->execute();
  $receitas = $stmt->get_result();
}


?>

  <!-- Lista de receitas por país -->
 

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

                <a     <?php if ($gratis): ?>   href="product-details.php?id=<?= $id ?>   <?php endif; ?> ">
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



 <?php include 'footer.php'; ?>
 <!-- Bootstrap core JavaScript -->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Additional Scripts -->
  <script src="assets/js/custom.js"></script>
  <script src="assets/js/owl.js"></script>
  <script src="assets/js/slick.js"></script>
  <script src="assets/js/isotope.js"></script>
  <script src="assets/js/accordions.js"></script>
</body>
</html>
