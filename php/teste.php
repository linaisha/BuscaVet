<?php
include 'config.php';

$conn = new mysqli(servername, username, password, database);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

echo "Conexão bem-sucedida!";
?>