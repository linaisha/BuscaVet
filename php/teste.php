<?php
$servername = "localhost"; // Endereço do servidor do BD, geralmente é localhost
$username = "root"; // Seu nome de usuário do BD
$password = ""; // Sua senha do BD
$database = "buscavet"; // Nome do seu banco de dados

// Criando a conexão
$conn = new mysqli($servername, $username, $password, $database);

// Verificando a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

echo "Conexão bem-sucedida!";
?>
