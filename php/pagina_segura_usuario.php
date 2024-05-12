<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header('Location: ../html/login_usuario.html'); // Direciona de volta ao login se não estiver logado.
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Página Segura</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="cabecalho">
        <h2>Bem-vindo à Página Segura, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
    </header>
    <main>
        <p>Esta é uma página segura. Seu login foi bem-sucedido!</p>
        <p><a href="logout_usuario.php">Sair</a></p>
    </main>
</body>
</html>
