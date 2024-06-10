<?php
include 'decode_cred.php';

ob_start();
session_start();

header('Content-Type: application/json');

function log_error($message)
{
    error_log($message, 3, '../logs/php-error.log');
}

function return_json_error($message)
{
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

$conn = new mysqli($credentials['servername'], $credentials['username'], $credentials['password'], $credentials['database']);

if ($conn->connect_error) {
    log_error('Conexão falhou: ' . $conn->connect_error);
    return_json_error('Conexão falhou: ' . $conn->connect_error);
}

if (empty($_POST['verification_code'])) {
    return_json_error('Código de verificação é necessário.');
}

$verificationCode = $_POST['verification_code'];
$clinicId = $_SESSION['clinica_id'] ?? '';

if (empty($clinicId)) {
    return_json_error('ID da clínica não encontrado na sessão.');
}

$stmt = $conn->prepare("SELECT * FROM clinica WHERE id = ? AND codigo_verificacao = ? AND codigo_verificacao_expira > NOW()");
if (!$stmt) {
    log_error('Erro ao preparar a query: ' . $conn->error);
    return_json_error('Erro ao preparar a query: ' . $conn->error);
}
$stmt->bind_param('is', $clinicId, $verificationCode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $clinic = $result->fetch_assoc();
    $_SESSION['clinica_id'] = $clinic['id'];
    $_SESSION['clinica_name'] = $clinic['name'];
    echo json_encode(['success' => true, 'message' => 'Código verificado com sucesso.', 'redirect' => '../php/verifica_sessao_clinica.php']);
} else {
    return_json_error('Código inválido ou expirado.');
}

$stmt->close();
$conn->close();
ob_end_flush();
?>