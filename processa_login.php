<?php
session_start();
require 'conexao.php';

$login = $_POST['login'];
$password = $_POST['password'];

// Buscar pelo username OU email
$sql = "SELECT * FROM utilizador WHERE (username = ? OR email = ?) LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $login, $login);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['id_utilizador'] = $user['id_utilizador'];
    $_SESSION['nome'] = $user['nome'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['id_tipo'] = $user['id_tipo'];

    // Direciona conforme o tipo de utilizador
    if ($user['id_tipo'] == 2) {
        // Usuário comum → página de cliente
        header("Location: cliente/perfil.php");
    } else {
        // Administrador ou outros tipos → página de admin
        header("Location: adm/adm1.php");
    }
    exit;
} else {
    header("Location: login.php?erro=1");
    exit;
}
