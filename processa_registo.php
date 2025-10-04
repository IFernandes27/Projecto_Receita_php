<?php
include 'conexao.php';
session_start();

$nome = $_POST['nome'];
$username = $_POST['username'];
$email = $_POST['email'];
$telefone = $_POST['telefone'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$id_tipo = 2;

$sql_check = "SELECT id_utilizador FROM utilizador WHERE username = ? OR email = ?";
$stmtc = $conn->prepare($sql_check);
$stmtc->bind_param("ss", $username, $email);
$stmtc->execute();
$stmtc->store_result();
if ($stmtc->num_rows > 0) {
    header("Location: registo.php?erro=Username ou email já existe");
    exit;
}

$sql = "INSERT INTO utilizador (nome, username, email, telefone, password, id_tipo) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $nome, $username, $email, $telefone, $password, $id_tipo);

if ($stmt->execute()) {
    header("Location: login.php?msg=registo_sucesso");
} else {
    header("Location: registo.php?erro=Erro no registo");
}
$conn->close();