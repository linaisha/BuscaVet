<?php
include 'decode_cred.php';

ob_start();
session_start();

header('Content-Type: application/json');

$conn = new mysqli($credentials['servername'], $credentials['username'], $credentials['password'], $credentials['database']);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Conexão falhou: ' . $conn->connect_error]);
    exit;
}

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
ob_end_flush();
?>
