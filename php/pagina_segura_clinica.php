<?php
session_start();

if (!isset($_SESSION['clinica_id']) || !isset($_SESSION['clinica_name'])) {
    header('Location: login_clinica.html'); // Redireciona de volta ao login se não estiver logado.
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Página Segura da Clínica</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="cabecalho">
        <h2>Bem-vindo à Página Segura da Clínica, <?php echo htmlspecialchars($_SESSION['clinica_name']); ?>!</h2>
    </header>
    <main>
        <p>Esta é uma página segura. Seu login foi bem-sucedido!</p>
        <p><a href="logout_clinica.php">Sair</a></p>
    </main>
</body>
</html>
