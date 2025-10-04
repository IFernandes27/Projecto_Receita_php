<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt">
<head>


<style>
body {
    background-color: blue;
}
 
  </style>


  <meta charset="UTF-8">
  <title>Entrar - Sabores da CPLP</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  
  
 


</style>
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card shadow-lg mx-auto" style="max-width: 500px;">
    <div class="card-body">
      <h3 class="card-title text-center mb-4">Entrar</h3>
      <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger">Credenciais inválidas</div>
      <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'registo_sucesso'): ?>
        <div class="alert alert-success">Registo concluído! Faça login.</div>
      <?php endif; ?>
      <form action="processa_login.php" method="POST">
        <div class="form-group"><label>Username ou Email</label><input type="text" name="login" class="form-control" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required></div>
        <button type="submit" class="btn btn-dark btn-block">Entrar</button>
        <p class="text-center mt-3">Não tem conta? <a href="registo.php">Registar</a></p>
      </form>
    </div>
  </div>
</div>

</body>
</html>