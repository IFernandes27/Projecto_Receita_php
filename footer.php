<!-- ===================== FOOTER ===================== -->
<footer class="site-footer bg-dark text-white pt-5 mt-5">
  <div class="container">
    <div class="row">

      <!-- Coluna: Marca / Sobre -->
      <div class="col-12 col-md-6 col-lg-3 mb-4">
        <h5 class="fw-bold mb-3">Sabores <em>da CPLP</em></h5>
        <p class="text-white-50 mb-3">
          Receitas tradicionais dos países lusófonos. Explore, aprenda e saboreie em casa.
        </p>
        <div class="d-flex gap-3 fs-5">
          <a class="text-white-50 hover-white" href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
          <a class="text-white-50 hover-white" href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a class="text-white-50 hover-white" href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
          <a class="text-white-50 hover-white" href="#" aria-label="X"><i class="fab fa-x-twitter"></i></a>
        </div>
      </div>

      <!-- Coluna: Links Rápidos -->
      <div class="col-6 col-lg-3 mb-4">
        <h6 class="text-uppercase text-white-50 mb-3">Links</h6>
        <ul class="list-unstyled m-0">
          <li class="mb-2"><a class="footer-link" href="index.php">Início</a></li>
          <li class="mb-2"><a class="footer-link" href="products.php">Receitas</a></li>
          <li class="mb-2"><a class="footer-link" href="about.php">Contacte-nos</a></li>

          
<?php if (!($logado) || ($_SESSION['id_tipo'] == 2) ){ ?>

          <li class="mb-2"><a class="footer-link" href="carrinho.php">Carrinho</a></li>
          
          <?php } ?>
        
          
        </ul>
      </div>

      <!-- Coluna: Newsletter -->
      <div class="col-12 col-md-6 col-lg-3 mb-4">
        <h6 class="text-uppercase text-white-50 mb-3">Newsletter</h6>
        <p class="text-white-50">Receba novidades e receitas no seu email.</p>
        <form action="newsletter_subscribe.php" method="post" class="needs-validation" novalidate>
          <?php if (!empty($_SESSION['csrf'])): ?>
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8'); ?>">
          <?php endif; ?>
          <div class="input-group mb-2">
            <input type="email" name="email" class="form-control form-control-sm bg-dark text-white border-secondary"
                   placeholder="O seu email" required>
            <button class="btn btn-sm btn-light" type="submit">Subscrever</button>
          </div>
          <small class="text-white-50">Pode cancelar a qualquer momento.</small>
        </form>
      </div>

      <!-- Coluna: Contactar -->
      <div class="col-12 col-md-6 col-lg-3 mb-4">
        <h6 class="text-uppercase text-white-50 mb-3">Contactar</h6>
        <ul class="list-unstyled text-white-50 mb-3">
          <li class="mb-2"><i class="fa fa-envelope me-2"></i> suporte@saborescplp.tld</li>
          <li class="mb-2"><i class="fa fa-phone me-2"></i> +351 210 000 000</li>
          <li class="mb-2"><i class="fa fa-map-marker-alt me-2"></i> Lisboa, Portugal</li>
        </ul>
        <form action="contact_submit.php" method="post" class="needs-validation" novalidate>
          <?php if (!empty($_SESSION['csrf'])): ?>
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8'); ?>">
          <?php endif; ?>
          <div class="mb-2">
            <input type="text" name="nome" class="form-control form-control-sm bg-dark text-white border-secondary"
                   placeholder="O seu nome" required>
          </div>
          <div class="mb-2">
            <input type="email" name="email" class="form-control form-control-sm bg-dark text-white border-secondary"
                   placeholder="O seu email" required>
          </div>
          <div class="mb-2">
            <textarea name="mensagem" rows="2" class="form-control form-control-sm bg-dark text-white border-secondary"
                      placeholder="Mensagem" required></textarea>
          </div>
          <button class="btn btn-sm btn-outline-light" type="submit">Enviar</button>
        </form>
      </div>

    </div>

    <hr class="border-secondary opacity-50">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center py-3">
      <div class="small text-white-50">
        &copy; <?= date('Y') ?> Sabores da CPLP. Todos os direitos reservados.
      </div>
      <div class="small">
        <a class="footer-link" href="#">Privacidade</a>
        <span class="text-white-50 mx-2">•</span>
        <a class="footer-link" href="#">Termos</a>
        <span class="text-white-50 mx-2">•</span>
        <a class="footer-link" href="contact.php">Suporte</a>
      </div>
    </div>
  </div>
</footer>

<!-- Estilos do rodapé -->
<style>
  .site-footer a.footer-link{ color:#fff; text-decoration:none; opacity:.8; }
  .site-footer a.footer-link:hover{ opacity:1; text-decoration:underline; }
  .site-footer .hover-white:hover{ color:#fff !important; }

  /* Inputs escuros */
  .site-footer .form-control.bg-dark{
    background-color:#111 !important;
    color:#fff !important;
  }
  .site-footer .form-control.bg-dark::placeholder{ color:#bbb; }
  .site-footer .form-control.bg-dark:focus{
    border-color:#fff !important;
    box-shadow:none;
  }

  /* Linhas e separadores */
  .site-footer hr{ border-color:#444; }

  /* Ajuste do <em> no título */
  .site-footer em{ font-style:normal; color:#fff; opacity:.85; }
</style>
