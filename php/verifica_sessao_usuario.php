<?php
session_start();
$session_lifetime = 900;

function log_error($message) {
    error_log($message, 3, '../logs/php-error.log');
}

log_error("Verificando sessão do usuário...");

if (!isset($_SESSION['user_id']) || (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_lifetime))) {
    log_error("Sessão expirada ou usuário não autenticado.");
    session_unset();
    session_destroy();
    header('Location: ../html/login_usuario.html');
    exit;
}

$userName = htmlspecialchars($_SESSION['user_name']);
log_error("Sessão ativa para o usuário: $userName");

$_SESSION['LAST_ACTIVITY'] = time();
setcookie("userName", $userName, time() + 30);

header('Location: ../html/pagina_segura_usuario.html');
exit;
?>
