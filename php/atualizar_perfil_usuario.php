<?php
include 'decode_cred.php';

session_start();
header('Content-Type: application/json');

$certPath = '../chaves/certificate.pem';
$privateKeyPath = '../chaves/private_key.pem';

function log_error($message)
{
    error_log($message, 3, '../logs/php-error.log');
}

function return_json_error($message)
{
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

try {
    if (!file_exists($certPath)) {
        throw new Exception('Certificado não encontrado no caminho especificado: ' . $certPath);
    }

    if (!file_exists($privateKeyPath)) {
        throw new Exception('Chave privada não encontrada no caminho especificado: ' . $privateKeyPath);
    }

    $publicKey = file_get_contents($certPath);
    $privateKeyContent = file_get_contents($privateKeyPath);

    if ($privateKeyContent === false) {
        throw new Exception('Erro ao ler o conteúdo da chave privada.');
    }

    $privateKey = openssl_pkey_get_private($privateKeyContent);

    if (!$privateKey) {
        $error = openssl_error_string();
        throw new Exception('Falha ao carregar a chave privada. Erro: ' . $error);
    }

    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception('Usuário não autenticado');
    }

    $usuario_id = $_SESSION['usuario_id'];

    $conn = new mysqli($credentials['servername'], $credentials['username'], $credentials['password'], $credentials['database']);

    if ($conn->connect_error) {
        throw new Exception('Conexão falhou: ' . $conn->connect_error);
    }

    $encryptedName = $_POST['name'];
    $encryptedEmail = $_POST['email'];
    $encryptedPhone = $_POST['phone'];
    $encryptedDataNasc = $_POST['data_nasc'];

    $name = '';
    $email = '';
    $phone = '';
    $data_nasc = '';

    if (!openssl_private_decrypt(base64_decode($encryptedName), $name, $privateKey)) {
        throw new Exception('Erro ao decriptar o nome.');
    }

    if (!openssl_private_decrypt(base64_decode($encryptedEmail), $email, $privateKey)) {
        throw new Exception('Erro ao decriptar o e-mail.');
    }

    if (!openssl_private_decrypt(base64_decode($encryptedPhone), $phone, $privateKey)) {
        throw new Exception('Erro ao decriptar o telefone.');
    }

    if (!openssl_private_decrypt(base64_decode($encryptedDataNasc), $data_nasc, $privateKey)) {
        throw new Exception('Erro ao decriptar a data de nascimento.');
    }

    $sql = "UPDATE usuario SET name = ?, email = ?, phone = ?, data_nasc = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Erro ao preparar a query: ' . $conn->error);
    }

    $stmt->bind_param("ssssi", $name, $email, $phone, $data_nasc, $usuario_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso']);
    } else {
        throw new Exception('Erro ao atualizar perfil: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

ob_end_flush();
?>
