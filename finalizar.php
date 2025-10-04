<?php
session_start();
require 'conexao.php';

// Só continua se estiver logado
if (empty($_SESSION['id_utilizador'])) {
  header('Location: login.php?msg=precisa_login&redir=finalizar.php');
  exit;
}

// Carrinho não pode estar vazio
if (empty($_SESSION['carrinho'])) {
  $_SESSION['msg'] = ['tipo'=>'warning','texto'=>'O carrinho está vazio.'];
  header('Location: carrinho.php'); exit;
}

// Busca métodos de pagamento da BD
$metodos = [];
$res = $conn->query("SELECT idPagamento, nome FROM pagamento ORDER BY idPagamento");
while ($row = $res->fetch_assoc()) { $metodos[] = $row; }

// Calcula total
$total = 0.0;
foreach ($_SESSION['carrinho'] as $it) { $total += (float)($it['preco'] ?? 0); }
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Finalizar compra</title>
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/fontawesome.css">
  <link rel="stylesheet" href="assets/css/templatemo-sixteen.css">
  <link rel="stylesheet" href="assets/css/owl.css">
  <style>
    .table td, .table th { vertical-align: middle }
    .cart-item-img{width:60px;height:60px;object-fit:cover;border-radius:6px}
  </style>
</head>
<body>

  <?php //include 'header.php'; ?>

  <div class="page-heading products-heading header-text">
    <div class="container"><div class="row"><div class="col-md-12">
      <h1>Finalizar compra</h1>
      <span>Escolhe o método de pagamento e confirma a encomenda</span>
    </div></div></div>
  </div>

  <div class="container my-4">
    <div class="row">
      <div class="col-lg-7 mb-4">
        <div class="card">
          <div class="card-header"><strong>Método de pagamento</strong></div>
          <div class="card-body">
            <form action="processa_finalizar.php" method="post" id="form-finalizar">
              <?php foreach ($metodos as $i => $m): 
                $id = (int)$m['idPagamento']; $nome = htmlspecialchars($m['nome']);
              ?>
                <div class="custom-control custom-radio mb-2">
                  <input type="radio" id="pg<?= $id ?>" name="id_pagamento" value="<?= $id ?>" class="custom-control-input" <?= $i===0?'checked':'' ?>>
                  <label class="custom-control-label" for="pg<?= $id ?>"><?= ucfirst($nome) ?></label>
                </div>
              <?php endforeach; ?>
              <hr>
              <button type="submit" class="filled-button">Confirmar encomenda</button>
              <a href="carrinho.php" class="border-button ml-2">Voltar ao carrinho</a>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card">
          <div class="card-header"><strong>Resumo</strong></div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table mb-0">
                <thead class="thead-light">
                  <tr><th>Produto</th><th class="text-right">Preço</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($_SESSION['carrinho'] as $id => $item):
                    $nome = htmlspecialchars($item['nome'] ?? '');
                    $preco = (float)($item['preco'] ?? 0);
                    $img = htmlspecialchars($item['imagem'] ?? '');
                  ?>
                  <tr>
                    <td>
                      <?php if ($img): ?><img src="<?= $img ?>" alt="<?= $nome ?>" class="cart-item-img mr-2"><?php endif; ?>
                      <?= $nome ?>
                    </td>
                    <td class="text-right">€<?= number_format($preco, 2, ',', '.') ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <th>Total</th>
                    <th class="text-right">€<?= number_format($total, 2, ',', '.') ?></th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
        <small class="text-muted d-block mt-2">* Quantidade fixa: 1 por receita</small>
      </div>
    </div>
  </div>

  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
