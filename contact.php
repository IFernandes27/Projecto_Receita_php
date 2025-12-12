<?php
require_once 'bootstrap.php';
include 'header.php';
?>

<!-- Banner -->
<div class="page-heading contact-heading header-text">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <h1>Contacte-nos</h1>
        <span>Estamos prontos para ouvir você</span>
      </div>
    </div>
  </div>
</div>

<!-- Contact Page -->
<div class="contact-information">
  <div class="container">
    <div class="row">

      <!-- Contact Info -->
      <div class="col-md-4">
        <div class="contact-item">
          <i class="fa fa-phone"></i>
          <h4>Telefone</h4>
          <p>Ligue para nós de segunda a sexta-feira.</p>
          <a href="tel:+351912345678">+351 912 345 678</a>
        </div>
      </div>

      <div class="col-md-4">
        <div class="contact-item">
          <i class="fa fa-envelope"></i>
          <h4>Email</h4>
          <p>Envie-nos a sua questão a qualquer hora.</p>
          <a href="mailto:info@saboresdacplp.com">info@saboresdacplp.com</a>
        </div>
      </div>

      <div class="col-md-4">
        <div class="contact-item">
          <i class="fa fa-map-marker"></i>
          <h4>Localização</h4>
          <p>Lisboa, Portugal</p>
          <a href="https://goo.gl/maps/7x7bCZUqz9E2" target="_blank">Ver no mapa</a>
        </div>
      </div>
    </div>

    <!-- Contact Form -->
    <div class="row mt-5">
      <div class="col-md-8">
        <div class="contact-form">
          <form id="contact" action="enviar_mensagem.php" method="post">
            <div class="row">
              <div class="col-lg-6 col-md-12 col-sm-12">
                <fieldset>
                  <input name="nome" type="text" class="form-control" id="nome" placeholder="Seu nome..." required="">
                </fieldset>
              </div>
              <div class="col-lg-6 col-md-12 col-sm-12">
                <fieldset>
                  <input name="email" type="email" class="form-control" id="email" placeholder="Seu email..." required="">
                </fieldset>
              </div>
              <div class="col-lg-12">
                <fieldset>
                  <input name="assunto" type="text" class="form-control" id="assunto" placeholder="Assunto..." required="">
                </fieldset>
              </div>
              <div class="col-lg-12">
                <fieldset>
                  <textarea name="mensagem" rows="6" class="form-control" id="mensagem" placeholder="A sua mensagem..." required=""></textarea>
                </fieldset>
              </div>
              <div class="col-lg-12 mt-3">
                <fieldset>
                  <button type="submit" id="form-submit" class="filled-button">Enviar Mensagem</button>
                </fieldset>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Mapa -->
      <div class="col-md-4">
        <div id="map" style="width:100%; height: 100%; min-height:300px;">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3111.3123!2d-9.1426852!3d38.7168919!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd19338123456789%3A0x123456789abcd!2sLisboa!5e0!3m2!1spt-PT!2spt!4v1680000000000"
            width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>

<!-- Scripts -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/custom.js"></script>

</body>
</html>
