<?php
session_start();
require '../conexao.php';

// Bloqueia acesso de não administradores
if (!isset($_SESSION['id_utilizador']) || $_SESSION['id_tipo'] == 2) {
    header("Location: ../login.php?erro=acesso");
    exit;
}

// Total de usuários
$sqlUsuarios = "SELECT COUNT(*) AS total FROM utilizador";
$resUsuarios = $conn->query($sqlUsuarios);
$totalUsuarios = ($resUsuarios && $row = $resUsuarios->fetch_assoc()) ? $row['total'] : 0;

// Total de receitas
$sqlReceitas = "SELECT COUNT(*) AS total FROM receita";
$resReceitas = $conn->query($sqlReceitas);
$totalReceitas = ($resReceitas && $row = $resReceitas->fetch_assoc()) ? $row['total'] : 0;

// Total de países
$sqlPaises = "SELECT COUNT(*) AS total FROM pais";
$resPaises = $conn->query($sqlPaises);
$totalPaises = ($resPaises && $row = $resPaises->fetch_assoc()) ? $row['total'] : 0;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="mb-4">Painel Administrativo</h1>

    <div class="row">
        <!-- Card: Usuários -->
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3 shadow">
                <div class="card-body">
                    <h5 class="card-title">Usuários</h5>
                    <p class="card-text display-4"><?php echo $totalUsuarios; ?></p>
                </div>
            </div>
        </div>

        <!-- Card: Receitas -->
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3 shadow">
                <div class="card-body">
                    <h5 class="card-title">Receitas</h5>
                    <p class="card-text display-4"><?php echo $totalReceitas; ?></p>
                </div>
            </div>
        </div>

        <!-- Card: Países -->
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3 shadow">
                <div class="card-body">
                    <h5 class="card-title">Países</h5>
                    <p class="card-text display-4"><?php echo $totalPaises; ?></p>
                </div>
            </div>
        </div>
    </div>

    <a href="../logout.php" class="btn btn-danger mt-4">Sair</a>
</div>

</body>
</html>
