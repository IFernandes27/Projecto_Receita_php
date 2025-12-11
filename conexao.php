<?php
// Mostrar erros de MySQLi como exceções (ótimo para debug)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = getenv('MYSQLHOST');      // ex.: turntable.proxy.rlwy.net
$port = getenv('MYSQLPORT');      // ex.: 54238
$user = getenv('MYSQLUSER');      // ex.: root
$pass = getenv('MYSQLPASSWORD');  // senha da Railway
$db   = getenv('MYSQLDATABASE');  // ex.: railway

try {
    // Conexão com porta explícita
    $conn = new mysqli($host, $user, $pass, $db, (int)$port);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Loga no servidor (Render) e mostra mensagem genérica para o usuário
    error_log('Erro MySQL: ' . $e->getMessage());
    http_response_code(500);
    echo 'Erro ao ligar à base de dados.';
    exit;
}
