<?php
require_once 'bootstrap.php';
include 'header.php';
?>

  <!-- Cabeçalho da página -->
  <div class="page-heading products-heading header-text">
    <div class="container">
      <div class="row"><div class="col-md-12">
        <h1><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if ($categoriaAtual): ?>
          <span>Mostrando receitas da categoria <strong><?= htmlspecialchars($categoriaAtual['nome_categoria'], ENT_QUOTES, 'UTF-8') ?></strong></span>
        <?php else: ?>
          <span>Selecione uma categoria no menu acima</span>
        <?php endif; ?>
      </div></div>
    </div>
  </div>





<?php
// Buscar receitas da categoria
$receitas = null; $total = 0; $pages = 1;
if ($categoriaAtual) {
  // total
  $sqlC = "SELECT COUNT(*) AS c FROM receita WHERE categoria_idcategoria = ?";
  $stc = $conn->prepare($sqlC);
  $stc->bind_param('i', $categoriaAtual['idcategoria']);
  $stc->execute();
  $rc = $stc->get_result()->fetch_assoc();
  $total = (int)($rc['c'] ?? 0);
  $pages = max(1, (int)ceil($total / $perPage));

  // lista
  $sql = "SELECT idreceita, nome, descricao, preco, imagens
          FROM receita
          WHERE categoria_idcategoria = ?
          ORDER BY idreceita DESC
          LIMIT ? OFFSET ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('iii', $categoriaAtual['idcategoria'], $perPage, $offset);
  $stmt->execute();
  $receitas = $stmt->get_result();
}


?>




  <!-- Lista de receitas por categoria -->
  <div class="latest-products">
    <div class="container-fluid">
      <div class="row">
        <?php if (!$categoriaAtual): ?>
          <div class="col-12 col-md-10 col-lg-8 mx-auto">
            <div class="alert alert-info mt-4">Categoria inválida ou não informada. Escolha uma categoria no menu <strong>Categorias</strong>.</div>
          </div>
        <?php else: ?>
          <?php if ($receitas && $receitas->num_rows > 0): ?>
            <?php while ($r = $receitas->fetch_assoc()):
              $id    = (int)($r['idreceita'] ?? 0);
              $nome  = htmlspecialchars($r['nome'] ?? '', ENT_QUOTES, 'UTF-8');
              $desc  = htmlspecialchars($r['descricao'] ?? '', ENT_QUOTES, 'UTF-8');
              $preco = (float)($r['preco'] ?? 0);

              $imgFile = basename($r['imagens'] ?? '');
              $imgFile = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $imgFile);
              $imgPath = "assets/images/" . ($imgFile !== '' ? $imgFile : 'no-image.png');

              $gratis = $preco <= 0;
            ?>
              <div class="col-6 col-sm-6 col-md-4 col-lg-3 mb-4">
                <div class="product-item h-100 position-relative">
                  <?php if ($gratis): ?>
                    <span class="badge badge-brand position-absolute" style="top:8px; right:8px;">Grátis</span>
                    <a href="product-details.php?id=<?= $id ?>">
                      <img src="<?= $imgPath ?>" alt="<?= $nome ?>" loading="lazy"
                           onerror="this.src='assets/images/placeholder.jpg';"
                           style="width:100%;height:200px;object-fit:cover;">
                    </a>
                  <?php else: ?>
                    <img src="<?= $imgPath ?>" alt="<?= $nome ?>" loading="lazy"
                         onerror="this.src='assets/images/placeholder.jpg';"
                         style="width:100%;height:200px;object-fit:cover;">
                  <?php endif; ?>

                  <div class="down-content">
                   <!-- linha: Nome + Preço à direita -->
                  <div class="pi-header-line">


                    <a  <?php if ($gratis): ?>   href="product-details.php?id=<?= $id ?>     <?php endif; ?>    " class="pi-title">
                      
                    
                    <?= $nome ?></a>


                    <span class="pi-price"><?= $gratis ? 'Grátis' : formatPrice($preco) ?></span>
                  </div>

                    <p class="pi-desc"><?= $desc ?></p>

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

            <!-- Paginação -->
            <div class="col-12">
              <nav aria-label="Paginação" class="mt-3">
                <ul class="pagination justify-content-center">
                  <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                      <a class="page-link" href="?cat=<?= urlencode($slug) ?>&p=<?= $i ?>"><?= $i ?></a>
                    </li>
                  <?php endfor; ?>
                </ul>
              </nav>
            </div>

          <?php else: ?>
            <div class="col-12 col-md-10 col-lg-8 mx-auto">
              <div class="alert alert-warning mt-4">
                Não encontramos receitas para a categoria <strong><?= htmlspecialchars($categoriaAtual['nome_categoria'], ENT_QUOTES, 'UTF-8') ?></strong>.
              </div>
            </div>
          <?php endif; ?>
        <?php endif; ?>
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
