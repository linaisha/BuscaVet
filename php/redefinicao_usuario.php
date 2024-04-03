<!-- redefinicao.php -->
<?php
$token = $_GET['token'] ?? '';
?>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../css/style.css">
    <title> BuscaVet - Redefinição de Senha </title>
    <link rel="icon" href="/img/Logo.png">
</head>
<body style="background-image: url('../img/BuscaVetLogo.png');">

<header class="cabecalho">
    <div class="juntos">
    <div class="logo">
        <a href="/img/imagem.png">
            <img src="../img/Logo.png" width="120" height="120">
        </a>

        <ul>
            <li><a href="index.html" class="link">HOME</a></li>
            <li><a href="login.html" class="link">LOGIN</a></li>
            <li><a href="cadastro_usuario.html" class="link">CADASTRE-SE</a></li>
            <li><a href="autenticacao.html" class="link">AUTENTICACAO</a></li>
        </ul>
</header>
    <h1 id="titulo_geral">Redefinição de Senha</h1>
    <div id="nova-senha" class="nova-senha">
        <form id="form-nova-senha" action="criar_nova_senha.php" method="post" class="form-nova-senha">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="input-container">
                <label for="new_password">Nova Senha:</label>
                <input type="password" name="new_password" id="new_password" required>
            </div>
            <div class="input-container">
                <label for="confirm_password">Confirmar Nova Senha:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit">Redefinir Senha</button>
        </form>
    </div>
</body>
</html>
