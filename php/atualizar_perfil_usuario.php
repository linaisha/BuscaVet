<?php
session_start();
include 'decode_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$user_id = $_SESSION['user_id'];

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Conexão falhou: ' . $conn->connect_error]);
    exit;
}

$name = $conn->real_escape_string($_POST['name']);
$email = $conn->real_escape_string($_POST['email']);
$phone = $conn->real_escape_string($_POST['phone']);

$sql = "UPDATE usuario SET name = ?, email = ?, phone = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Erro ao preparar a query: ' . $conn->error]);
    exit;
}

$stmt->bind_param("sssi", $name, $email, $phone, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar perfil: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
