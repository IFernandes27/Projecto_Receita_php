<?php
session_start();
require '../conexao.php';

// Apenas administradores
if (!isset($_SESSION['id_utilizador']) || $_SESSION['id_tipo'] != 1) {
  header("Location: ../login.php?erro=acesso");
  exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: ver_encomendas.php?erro=id_invalido");
  exit;
}
$idencomendas = (int)$_GET['id'];

// Verificar existência
$st = $conn->prepare("SELECT idencomendas FROM encomendas WHERE idencomendas = ?");
$st->bind_param("i", $idencomendas);
$st->execute();
$r = $st->get_result();
if ($r->num_rows === 0) {
  header("Location: ver_encomendas.php?erro=nao_encontrada");
  exit;
}

$conn->begin_transaction();
try {
  // 1) Apagar itens (caso a FK não seja ON DELETE CASCADE)
  $st1 = $conn->prepare("DELETE FROM encomenda_receita WHERE encomendas_idencomendas = ?");
  $st1->bind_param("i", $idencomendas);
  if (!$st1->execute()) {
    throw new Exception("Falha ao remover itens da encomenda.");
  }

  // 2) Apagar encomenda
  $st2 = $conn->prepare("DELETE FROM encomendas WHERE idencomendas = ?");
  $st2->bind_param("i", $idencomendas);
  if (!$st2->execute()) {
    throw new Exception("Falha ao remover encomenda.");
  }

  $conn->commit();
  header("Location: ver_encomendas.php?msg=apagado_sucesso");
  exit;

} catch (Exception $e) {
  $conn->rollback();
  header("Location: ver_encomendas.php?erro=erro_transacao");
  exit;
}
