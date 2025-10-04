<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Registar - Sabores da CPLP</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card shadow-lg mx-auto" style="max-width: 500px;">
    <div class="card-body">
      <h3 class="card-title text-center mb-4">Registar Conta</h3>
      <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger">Erro: <?php echo htmlspecialchars($_GET['erro']); ?></div>
      <?php endif; ?>
      <form action="processa_registo.php" method="POST">
        <div class="form-group"><label>Nome</label><input type="text" name="nome" class="form-control" required></div>
        <div class="form-group"><label>Username</label><input type="text" name="username" class="form-control" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="form-group"><label>Telefone</label><input type="text" name="telefone" class="form-control" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required></div>
        <button type="submit" class="btn btn-dark btn-block">Registar</button>
        <p class="text-center mt-3">Já tem conta? <a href="login.php">Entrar</a></p>
      </form>
    </div>
  </div>
</div>

</body>
</html>