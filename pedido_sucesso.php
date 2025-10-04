<?php
session_start();
require 'conexao.php';

$id = isset($_GET['pedido']) ? (int)$_GET['pedido'] : 0;
if ($id <= 0) { header('Location: index.php'); exit; }

// Busca encomenda + pagamento
$sql = "SELECT e.*, p.nome AS pagamento
        FROM encomendas e
        JOIN pagamento p ON p.idPagamento = e.Pagamento_idPagamento
        WHERE e.idencomendas = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$enc = $stmt->get_result()->fetch_assoc();
if (!$enc) { header('Location: index.php'); exit; }

// Busca itens
$itens = [];
$sql2 = "SELECT r.idreceita, r.nome, r.preco, r.imagens
         FROM encomenda_receita er
         JOIN receita r ON r.idreceita = er.receita_idreceita
         WHERE er.encomendas_idencomendas = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($row = $res2->fetch_assoc()) { $itens[] = $row; }
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pedido #<?= $id ?> concluído</title>
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/templatemo-sixteen.css">
</head>
<body>

  <?php //include 'header.php'; ?>

  <div class="container my-5">
    <div class="alert alert-success">
      <strong>Obrigado!</strong> Recebemos a tua encomenda <strong>#<?= $id ?></strong>.
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <div><strong>Data:</strong> <?= htmlspecialchars($enc['data']) ?></div>
        <div><strong>Pagamento:</strong> <?= htmlspecialchars(ucfirst($enc['pagamento'])) ?></div>
        <div><strong>Total:</strong> €<?= number_format($enc['valor_total'], 2, ',', '.') ?></div>
      </div>
    </div>

    <h5>Itens</h5>
    <ul class="list-group mb-4">
      <?php foreach ($itens as $it): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span><?= htmlspecialchars($it['nome']) ?></span>
          <span>€<?= number_format((float)$it['preco'], 2, ',', '.') ?></span>
        </li>
      <?php endforeach; ?>
    </ul>

    <a href="index.php" class="filled-button">Voltar à loja</a>
    <a href="cliente/minhas_receitas.php" class="filled-button">Ver Minhas Receitas</a>

  </div>

  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
