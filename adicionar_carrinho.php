<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.php');
  exit;
}

$id     = isset($_POST['idreceita']) ? (int)$_POST['idreceita'] : 0;
$nome   = trim($_POST['nome']   ?? '');
$preco  = (float)($_POST['preco']  ?? 0);
$imagem = trim($_POST['imagem'] ?? '');

if ($id <= 0 || $nome === '') {
  $_SESSION['msg'] = ['tipo' => 'danger', 'texto' => 'Dados inválidos para adicionar ao carrinho.'];
  header('Location: carrinho.php');
  exit;
}

if (!isset($_SESSION['carrinho'])) {
  $_SESSION['carrinho'] = [];
}

if (!isset($_SESSION['carrinho'][$id])) {
  // Adiciona 1x apenas
  $_SESSION['carrinho'][$id] = [
    'nome'       => $nome,
    'preco'      => $preco,
    'quantidade' => 1,
    'imagem'     => $imagem
  ];
  $_SESSION['msg'] = ['tipo' => 'success', 'texto' => 'Receita adicionada ao carrinho.'];
} else {
  // Já existe: mantém em 1 e avisa
  $_SESSION['carrinho'][$id]['quantidade'] = 1;
  $_SESSION['msg'] = ['tipo' => 'info', 'texto' => 'Esta receita já está no carrinho.'];
}

header('Location: carrinho.php');
exit;
