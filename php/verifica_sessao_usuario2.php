<?php
session_start();
$session_lifetime = 900;

if (!isset($_SESSION['user_id']) || (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_lifetime))) {
    session_unset();
    session_destroy();
    header('Location: ../html/login_usuario.html');
    exit;
}
$userName = htmlspecialchars($_SESSION['user_name']);
$_SESSION['LAST_ACTIVITY'] = time();
setcookie("userName", htmlspecialchars($_SESSION['user_name']), time() + 30);

header('Location: ../html/pagina_segura_usuario2.html');
exit;
?>