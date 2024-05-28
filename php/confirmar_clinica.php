<?php
include 'decode_config.php';

$conn = new mysqli($servername, $username, $password, $database);

if (mysqli_connect_errno()) {
    echo "Falha na conexão com o banco de dados: " . mysqli_connect_error();
    exit();
}

$token = $_GET['token'] ?? '';

if ($token) {
    $stmt = $conn->prepare("UPDATE clinica SET confirmacao = 1 WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Clinica confirmada com sucesso!";
    } else {
        echo "Token inválido ou clinica já confirmada.";
    }

    $stmt->close();
} else {
    echo "Token não fornecido.";
}

$conn->close();

?>