<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    // Se não estiver logado, redireciona para a página de login
    header('Location: login.html');
    exit;
}

// Se chegou aqui, o usuário está logado
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Página Segura</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Ajuste o caminho conforme necessário -->
</head>
<body>
    <header class="cabecalho">
        <h2>Bem-vindo à Página Segura</h2>
    </header>
    <main>
        <p>Olá, <?php echo htmlspecialchars($_SESSION['user_name']); ?>. Seu login foi bem-sucedido!</p>
        <p><a href="logout.php">Sair</a></p> <!-- Link para logout (precisa ser implementado) -->
    </main>
</body>
</html>
