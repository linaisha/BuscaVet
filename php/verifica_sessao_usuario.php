<?php
session_start();
$session_lifetime = 30; // Tempo de sessão em segundos

if (!isset($_SESSION['user_id']) || (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_lifetime))) {
    session_unset();
    session_destroy();
    header('Location: ../html/login_usuario.html');
    exit;
}
$userName = htmlspecialchars($_SESSION['user_name']);
$_SESSION['LAST_ACTIVITY'] = time();
// Armazenar o nome do usuário em um cookie por um curto período (exemplo simples)
setcookie("userName", htmlspecialchars($_SESSION['user_name']), time()+30); // expira em 30 segundos

// Redireciona para a página HTML
header('Location: ../html/pagina_segura_usuario.html');
exit;
?>
