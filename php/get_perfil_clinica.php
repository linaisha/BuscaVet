<?php
session_start();
include 'decode_cred.php';

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

$sql = "SELECT name, especializacao, email, phone, endereco FROM clinica WHERE id = ?";
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

    $certPath = '../chaves/certificate.pem';
    $publicKey = file_get_contents($certPath);
    $encrypt = new JSEncrypt();
    $encrypt->setPublicKey($publicKey);

    $encryptedClinica = [];
    foreach ($clinica as $key => $value) {
        $encryptedClinica[$key] = base64_encode($encrypt->encrypt($value));
    }

    echo json_encode(['success' => true, 'data' => $encryptedClinica]);
} else {
    echo json_encode(['success' => false, 'message' => 'Clínica não encontrada']);
}

$stmt->close();
$conn->close();
?>