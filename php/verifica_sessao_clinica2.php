<?php
session_start();
$session_lifetime = 900;

if (!isset($_SESSION['clinica_id']) || (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_lifetime))) {
    session_unset();
    session_destroy();
    header('Location: ../html/login_clinica.html');
    exit;
}
$userName = htmlspecialchars($_SESSION['clinica_name']);
$_SESSION['LAST_ACTIVITY'] = time();
setcookie("userName", htmlspecialchars($_SESSION['clinica_name']), time()+30);

header('Location:../html/pagina_segura_clinica2.html');
exit;
?>
