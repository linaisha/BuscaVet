<?php
include 'decode_cred.php';

session_start();
header('Content-Type: application/json');

$certPath = '../chaves/certificate.pem';
$privateKeyPath = '../chaves/private_key.pem';
$privateKeyPassword = 'TotalmenteOnline#69';

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

    $privateKey = openssl_pkey_get_private($privateKeyContent, $privateKeyPassword);

    if (!$privateKey) {
        $error = openssl_error_string();
        throw new Exception('Falha ao carregar a chave privada. Erro: ' . $error);
    }

    if (!isset($_SESSION['clinica_id'])) {
        throw new Exception('Clínica não autenticada');
    }

    $clinica_id = $_SESSION['clinica_id'];

    $conn = new mysqli($credentials['servername'], $credentials['username'], $credentials['password'], $credentials['database']);

    if ($conn->connect_error) {
        throw new Exception('Conexão falhou: ' . $conn->connect_error);
    }

    $encryptedName = $_POST['name'];
    $encryptedEspecializacao = $_POST['especializacao'];
    $encryptedEmail = $_POST['email'];
    $encryptedPhone = $_POST['phone'];
    $encryptedEndereco = $_POST['endereco'];

    $name = '';
    $especializacao = '';
    $email = '';
    $phone = '';
    $endereco = '';

    if (!openssl_private_decrypt(base64_decode($encryptedName), $name, $privateKey)) {
        throw new Exception('Erro ao decriptar o nome.');
    }

    if (!openssl_private_decrypt(base64_decode($encryptedEspecializacao), $especializacao, $privateKey)) {
        throw new Exception('Erro ao decriptar a especialização.');
    }

    if (!openssl_private_decrypt(base64_decode($encryptedEmail), $email, $privateKey)) {
        throw new Exception('Erro ao decriptar o e-mail.');
    }

    if (!openssl_private_decrypt(base64_decode($encryptedPhone), $phone, $privateKey)) {
        throw new Exception('Erro ao decriptar o telefone.');
    }

    if (!openssl_private_decrypt(base64_decode($encryptedEndereco), $endereco, $privateKey)) {
        throw new Exception('Erro ao decriptar o endereço.');
    }

    $sql = "UPDATE clinica SET name = ?, especializacao = ?, email = ?, phone = ?, endereco = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Erro ao preparar a query: ' . $conn->error);
    }

    $stmt->bind_param("sssssi", $name, $especializacao, $email, $phone, $endereco, $clinica_id);

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