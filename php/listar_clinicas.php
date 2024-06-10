<?php
include 'decode_cred.php';

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

    $privateKeyContent = file_get_contents($privateKeyPath);

    if ($privateKeyContent === false) {
        throw new Exception('Erro ao ler o conteúdo da chave privada.');
    }

    $privateKey = openssl_pkey_get_private($privateKeyContent);

    if (!$privateKey) {
        $error = openssl_error_string();
        throw new Exception('Falha ao carregar a chave privada. Erro: ' . $error);
    }

    $encryptedTermoBusca = $_POST['termo'] ?? '';

    if (empty($encryptedTermoBusca)) {
        throw new Exception('Termo de busca é obrigatório.');
    }

    $termoBusca = '';
    if (!openssl_private_decrypt(base64_decode($encryptedTermoBusca), $termoBusca, $privateKey)) {
        throw new Exception('Erro ao decriptar o termo de busca.');
    }

    $conn = new mysqli($credentials['servername'], $credentials['username'], $credentials['password'], $credentials['database']);

    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Conexão falhou: ' . $conn->connect_error]));
    }

    $termoBusca = $conn->real_escape_string($termoBusca);

    $sql = "SELECT name, phone, endereco, email, especializacao FROM clinica";
    if ($termoBusca) {
        $sql .= " WHERE name LIKE '%$termoBusca%' OR email LIKE '%$termoBusca%' OR especializacao LIKE '%$termoBusca%'";
    }

    $result = $conn->query($sql);

    $clinicas = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $clinicas[] = $row;
        }
    }

    echo json_encode($clinicas);

    $conn->close();
} catch (Exception $e) {
    log_error($e->getMessage());
    return_json_error($e->getMessage());
}

?>