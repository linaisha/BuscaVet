<?php
include 'decode_config.php';

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

echo "Conexão bem-sucedida!";


echo json_encode(['success' => true, 'message' => 'Teste bem-sucedido']);


?>