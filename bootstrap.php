<?php
// NADA de HTML/echo/espacos antes disto
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_set_cookie_params([
    'lifetime' => 0,                    // sessão até fechar o browser
    'path'     => '/',
    'domain'   => '',                   // se usas subdomínios, põe '.teudominio.tld'
    'secure'   => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
  ]);
  session_start();
}
