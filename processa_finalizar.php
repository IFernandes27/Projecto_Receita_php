<?php
session_start();
require 'conexao.php';

// Regras básicas
if (empty($_SESSION['id_utilizador'])) { header('Location: login.php?msg=precisa_login'); exit; }
if (empty($_SESSION['carrinho'])) { header('Location: carrinho.php'); exit; }

$idPagamento = isset($_POST['id_pagamento']) ? (int)$_POST['id_pagamento'] : 0;
if ($idPagamento <= 0) {
  $_SESSION['msg'] = ['tipo'=>'danger','texto'=>'Escolhe um método de pagamento.'];
  header('Location: finalizar.php'); exit;
}

// Confirma se o método existe
$check = $conn->prepare("SELECT 1 FROM pagamento WHERE idPagamento = ?");
$check->bind_param("i", $idPagamento);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
  $_SESSION['msg'] = ['tipo'=>'danger','texto'=>'Método de pagamento inválido.'];
  header('Location: finalizar.php'); exit;
}

// Calcula total
$total = 0.0;
foreach ($_SESSION['carrinho'] as $it) { $total += (float)($it['preco'] ?? 0); }

$conn->begin_transaction();
try {
  // Insere em encomendas
  $now = date('Y-m-d H:i:s');
  $uid = (int)$_SESSION['id_utilizador'];

  $ins = $conn->prepare("INSERT INTO encomendas (`data`, `valor_total`, `utilizador_id_utilizador`, `Pagamento_idPagamento`) VALUES (?,?,?,?)");
  $ins->bind_param("sdii", $now, $total, $uid, $idPagamento);
  if (!$ins->execute()) { throw new Exception($ins->error); }
  $idEncomenda = $conn->insert_id;

  // Insere itens na encomenda_receita (Observacoes fica NULL)
  $itemStmt = $conn->prepare("INSERT INTO encomenda_receita (`receita_idreceita`, `encomendas_idencomendas`, `Observacoes`) VALUES (?,?,NULL)");
  foreach ($_SESSION['carrinho'] as $idReceita => $item) {
    $rid = (int)$idReceita;
    $itemStmt->bind_param("ii", $rid, $idEncomenda);
    if (!$itemStmt->execute()) { throw new Exception($itemStmt->error); }
  }

  $conn->commit();

  // Limpa carrinho e redireciona p/ sucesso
  $_SESSION['carrinho'] = [];
  header("Location: pedido_sucesso.php?pedido=".$idEncomenda);
  exit;

} catch (Exception $e) {
  $conn->rollback();
  $_SESSION['msg'] = ['tipo'=>'danger','texto'=>'Ocorreu um erro ao finalizar: '.$e->getMessage()];
  header('Location: finalizar.php');
  exit;
}
