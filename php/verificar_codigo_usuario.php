<?php
include 'config.php';
ob_start(); // Inicia controle de buffer
session_start();

header('Content-Type: application/json'); // Define header para JSON

$conn = new mysqli(servername, username, password, database);

// Verifica conexão
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Conexão falhou: ' . $conn->connect_error]);
    exit;
}

// Verifica se código foi enviado
if (empty($_POST['verification_code'])) {
    echo json_encode(['success' => false, 'message' => 'Código de verificação é necessário.']);
    exit;
}

$verificationCode = $_POST['verification_code'];
$userId = $_SESSION['login_user_id'] ?? '';

$stmt = $conn->prepare("SELECT * FROM usuario WHERE id = ? AND codigo_verificacao = ? AND codigo_verificacao_expira > NOW()");
$stmt->bind_param('is', $userId, $verificationCode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    echo json_encode(['success' => true, 'message' => 'Código verificado com sucesso.', 'redirect' => '../php/verifica_sessao_usuario.php']);
} else {
    echo json_encode(['success' => false, 'message' => 'Código inválido ou expirado.']);
}

$stmt->close();
$conn->close();
ob_end_flush(); // Envia o buffer e desativa
?>
