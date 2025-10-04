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
    header("Location: ver_receitas.php?erro=id_invalido");
    exit;
}
$idreceita = (int)$_GET['id'];

// Buscar dados da receita (para validar existência e capturar nome da imagem)
$stmtSel = $conn->prepare("SELECT imagens FROM receita WHERE idreceita = ?");
$stmtSel->bind_param("i", $idreceita);
$stmtSel->execute();
$res = $stmtSel->get_result();
if ($res->num_rows === 0) {
    header("Location: ver_receitas.php?erro=nao_encontrada");
    exit;
}
$receita = $res->fetch_assoc();
$imagemArmazenada = $receita['imagens'] ?? '';

// Caminho base para imagens
$uploadDir = dirname(__DIR__) . '/assets/images';

$conn->begin_transaction();
try {
    // 1) Apagar possíveis linhas em encomenda_receita (se a tua FK não for CASCADE)
    // Se já estiver CASCADE, este DELETE será inócuo
    $stmtDelER = $conn->prepare("DELETE FROM encomenda_receita WHERE receita_idreceita = ?");
    $stmtDelER->bind_param("i", $idreceita);
    if (!$stmtDelER->execute()) {
        throw new Exception("Falha ao remover itens associados em encomenda_receita.");
    }

    // 2) Apagar a receita
    $stmtDelRec = $conn->prepare("DELETE FROM receita WHERE idreceita = ?");
    $stmtDelRec->bind_param("i", $idreceita);
    if (!$stmtDelRec->execute()) {
        throw new Exception("Falha ao remover a receita.");
    }

    $conn->commit();

    // 3) Apagar a imagem do disco (se existir e se houve remoção no BD)
    if (!empty($imagemArmazenada)) {
        $caminho = $uploadDir . '/' . $imagemArmazenada;
        if (is_file($caminho)) {
            @unlink($caminho);
        }
    }

    header("Location: ver_receitas.php?msg=apagado_sucesso");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    // Opcional: log do erro $e->getMessage()
    header("Location: ver_receitas.php?erro=erro_transacao");
    exit;
}
