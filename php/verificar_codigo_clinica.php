<?php
include 'config.php';

ob_start();
session_start();

header('Content-Type: application/json');

$conn = new mysqli(servername, username, password, database);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Conexão falhou: ' . $conn->connect_error]);
    exit;
}

if (empty($_POST['verification_code'])) {
    echo json_encode(['success' => false, 'message' => 'Código de verificação é necessário.']);
    exit;
}

$verificationCode = $_POST['verification_code'];
$clinicId = $_SESSION['clinica_id'] ?? '';

$stmt = $conn->prepare("SELECT * FROM clinica WHERE id = ? AND codigo_verificacao = ? AND codigo_verificacao_expira > NOW()");
$stmt->bind_param('is', $clinicId, $verificationCode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $clinic = $result->fetch_assoc();
    $_SESSION['clinica_name'] = $clinic['name'];
    echo json_encode(['success' => true, 'message' => 'Código verificado com sucesso.', 'redirect' => 'pagina_segura_clinica.php']);
} else {
    echo json_encode(['success' => false, 'message' => 'Código inválido ou expirado.']);
}

$stmt->close();
$conn->close();
ob_end_flush();
?>
