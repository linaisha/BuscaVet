<?php

// Iniciar a conexão com o banco de dados
$con = mysqli_connect("localhost", "root", "", "buscavet");

// Verificar a conexão
if (mysqli_connect_errno()) {
    echo "Falha na conexão com o banco de dados: " . mysqli_connect_error();
    exit();
}

// Suponhamos que você receba o token via método GET
$token = $_GET['token'] ?? '';

if ($token) {
    // Prepare a query para verificar o token e atualizar a confirmação
    $stmt = $con->prepare("UPDATE usuario SET confirmacao = 1 WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Usuário confirmado com sucesso!";
    } else {
        echo "Token inválido ou usuário já confirmado.";
    }

    $stmt->close();
} else {
    echo "Token não fornecido.";
}

$con->close();

?>
