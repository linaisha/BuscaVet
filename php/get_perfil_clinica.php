<?php
session_start();
include 'decode_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['clinica_id'])) {
    echo json_encode(['success' => false, 'message' => 'Clínica não autenticada']);
    exit;
}

$clinica_id = $_SESSION['clinica_id'];

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Conexão falhou: ' . $conn->connect_error]);
    exit;
}

$sql = "SELECT name, especializacao, email, phone FROM clinica WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Erro ao preparar a query: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $clinica_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $clinica = $result->fetch_assoc();
    echo json_encode(['success' => true, 'data' => $clinica]);
} else {
    echo json_encode(['success' => false, 'message' => 'Clínica não encontrada']);
}

$stmt->close();
$conn->close();
?>
