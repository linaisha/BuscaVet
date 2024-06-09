<?php
include 'decode_cred.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['clinica_id'])) {
    echo json_encode(['success' => false, 'message' => 'Clínica não autenticada']);
    exit;
}

$clinica_id = $_SESSION['clinica_id'];

$conn = new mysqli($credentials['servername'], $credentials['username'], $credentials['password'], $credentials['database']);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Conexão falhou: ' . $conn->connect_error]);
    exit;
}

$name = $conn->real_escape_string($_POST['name']);
$especializacao = $conn->real_escape_string($_POST['especializacao']);
$email = $conn->real_escape_string($_POST['email']);
$phone = $conn->real_escape_string($_POST['phone']);
$endereco = $conn->real_escape_string($_POST['endereco']);

$sql = "UPDATE clinica SET name = ?, especializacao = ?, email = ?, phone = ?, endereco = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Erro ao preparar a query: ' . $conn->error]);
    exit;
}

$stmt->bind_param("sssssi", $name, $especializacao, $email, $phone, $endereco, $clinica_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar perfil: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>