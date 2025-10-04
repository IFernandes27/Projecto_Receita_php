<?php
session_start();
require '../conexao.php';

// Bloqueia acesso de não administradores
if (!isset($_SESSION['id_utilizador']) || $_SESSION['id_tipo'] != 1) {
    header("Location: ../login.php?erro=acesso");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "DELETE FROM utilizador WHERE id_utilizador = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: ver_utilizadores.php?msg=apagado_sucesso");
        exit;
    } else {
        header("Location: ver_utilizadores.php?erro=nao_apagado");
        exit;
    }
} else {
    header("Location: ver_utilizadores.php?erro=id_invalido");
    exit;
}
