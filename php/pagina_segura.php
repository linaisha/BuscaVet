<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header('Location: ../html/login.html');
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
        <h2>Bem-vindo à Página Segura</h2>
    </header>
    <main>
        <p>Olá, <?php echo htmlspecialchars($_SESSION['user_name']); ?>. Seu login foi bem-sucedido!</p>
        <p><a href="index.html">Sair</a></p>
    </main>
</body>

</html>