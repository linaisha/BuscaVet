<?php
include 'decode_cred.php';

header('Content-Type: application/json');

$conn = new mysqli($credentials['servername'], $credentials['username'], $credentials['password'], $credentials['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Conexão falhou: ' . $conn->connect_error]));
}

$termoBusca = isset($_GET['termo']) ? $conn->real_escape_string($_GET['termo']) : '';

$sql = "SELECT name, phone, endereco, email, especializacao FROM clinica";
if ($termoBusca) {
    $sql .= " WHERE name LIKE '%$termoBusca%' OR email LIKE '%$termoBusca%' OR especializacao LIKE '%$termoBusca%'";
}

$result = $conn->query($sql);

$clinicas = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clinicas[] = $row;
    }
}

echo json_encode($clinicas);

$conn->close();
?>
