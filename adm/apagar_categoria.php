<?php
session_start();
require '../conexao.php';

// Apenas administradores
if (!isset($_SESSION['id_utilizador']) || $_SESSION['id_tipo'] != 1) {
  header("Location: ../login.php?erro=acesso");
  exit;
}

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: ver_categorias.php?erro=id_invalido");
  exit;
}
$idCategoriaApagar = (int)$_GET['id'];

// 1) Buscar a categoria a apagar (e evitar apagar a própria "Sem categoria")
$stmtSel = $conn->prepare("SELECT idcategoria, nome_categoria FROM categoria WHERE idcategoria = ?");
$stmtSel->bind_param("i", $idCategoriaApagar);
$stmtSel->execute();
$resCat = $stmtSel->get_result();
if ($resCat->num_rows === 0) {
  header("Location: ver_categorias.php?erro=nao_encontrada");
  exit;
}
$categoria = $resCat->fetch_assoc();
if (mb_strtolower(trim($categoria['nome_categoria'])) === mb_strtolower('Sem categoria')) {
  // por segurança, não permitir apagar a "Sem categoria"
  header("Location: ver_categorias.php?erro=proibido_apagar_sem_categoria");
  exit;
}

// 2) Garantir que existe a categoria "Sem categoria" (cria se não existir)
$nomeSem = 'Sem categoria';

// tenta encontrar uma existente (case-insensitive)
$stmtFind = $conn->prepare("SELECT idcategoria FROM categoria WHERE LOWER(TRIM(nome_categoria)) = LOWER(TRIM(?)) LIMIT 1");
$stmtFind->bind_param("s", $nomeSem);
$stmtFind->execute();
$resFind = $stmtFind->get_result();

if ($resFind->num_rows > 0) {
  $row = $resFind->fetch_assoc();
  $idSemCategoria = (int)$row['idcategoria'];
} else {
  // cria uma nova
  $stmtIns = $conn->prepare("INSERT INTO categoria (nome_categoria) VALUES (?)");
  $stmtIns->bind_param("s", $nomeSem);
  if (!$stmtIns->execute()) {
    header("Location: ver_categorias.php?erro=nao_criou_sem_categoria");
    exit;
  }
  $idSemCategoria = $stmtIns->insert_id;
}

// 3) Transação: mover receitas -> apagar categoria
$conn->begin_transaction();
try {
  // Reatribuir receitas para "Sem categoria"
  $stmtUpd = $conn->prepare("UPDATE receita SET categoria_idcategoria = ? WHERE categoria_idcategoria = ?");
  $stmtUpd->bind_param("ii", $idSemCategoria, $idCategoriaApagar);
  if (!$stmtUpd->execute()) {
    throw new Exception("Falha ao reatribuir receitas.");
  }

  // Apagar a categoria original
  $stmtDel = $conn->prepare("DELETE FROM categoria WHERE idcategoria = ?");
  $stmtDel->bind_param("i", $idCategoriaApagar);
  if (!$stmtDel->execute()) {
    throw new Exception("Falha ao apagar a categoria.");
  }

  $conn->commit();
  header("Location: ver_categorias.php?msg=apagado_sucesso");
  exit;

} catch (Exception $e) {
  $conn->rollback();
  // Opcional: log do erro $e->getMessage()
  header("Location: ver_categorias.php?erro=erro_transacao");
  exit;
}
