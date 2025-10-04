<?php
session_start();
require '../conexao.php';

if (!isset($_SESSION['id_utilizador'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $senha_nova = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);
    $id = $_SESSION['id_utilizador'];

    $sql = "UPDATE utilizador SET password = ? WHERE id_utilizador = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $senha_nova, $id);
    $stmt->execute();

    header("Location: perfil.php?msg=senha_alterada");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Alterar Senha</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Alterar Senha</h2>
    <form method="post">
        <div class="form-group">
            <label>Nova Senha:</label>
            <input type="password" name="nova_senha" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Alterar</button>
    </form>
</div>
</body>
</html>
