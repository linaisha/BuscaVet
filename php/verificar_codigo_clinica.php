<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$database = "buscavet";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Conexão falhou: ' . $conn->connect_error]));
}

if (empty($_POST['codigo_verificacao'])) {
    echo json_encode(['success' => false, 'message' => 'Código de verificação é necessário.']);
    exit;
}

$verificationCode = $_POST['codigo_verificacao'];
$userId = $_SESSION['login_user_id'] ?? '';

$stmt = $conn->prepare("SELECT * FROM clinica WHERE id = ? AND codigo_verificacao = ? AND codigo_verificacao_expira > NOW()");
$stmt->bind_param('is', $userId, $verificationCode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Código verificado com sucesso.']);

} else {
    echo json_encode(['success' => false, 'message' => 'Código inválido ou expirado.']);
}

$stmt->close();
$conn->close();

?>