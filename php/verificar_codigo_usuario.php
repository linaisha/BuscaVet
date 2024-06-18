<?php
include 'decode_cred.php';

session_start();
header('Content-Type: application/json');

function log_error($message) {
    error_log($message, 3, '../logs/php-error.log');
}

function return_json_error($message) {
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

$conn = new mysqli($credentials['servername'], $credentials['username'], $credentials['password'], $credentials['database']);

if ($conn->connect_error) {
    log_error('Conexão falhou: ' . $conn->connect_error);
    return_json_error('Conexão falhou: ' . $conn->connect_error);
}

if (empty($_POST['formData']) || empty($_POST['aesKey']) || empty($_POST['iv'])) {
    return_json_error('Dados são obrigatórios.');
}

$privateKeyPath = '../chaves/private_key.pem';

try {
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

    $encryptedFormData = $_POST['formData'];
    $encryptedAesKey = $_POST['aesKey'];
    $encryptedIv = $_POST['iv'];

    $aesKey = '';
    $iv = '';

    if (!openssl_private_decrypt(base64_decode($encryptedAesKey), $aesKey, $privateKey)) {
        throw new Exception('Erro ao decriptar a chave AES.');
    }

    if (!openssl_private_decrypt(base64_decode($encryptedIv), $iv, $privateKey)) {
        throw new Exception('Erro ao decriptar o IV.');
    }

    $aesKey = base64_decode($aesKey);
    $iv = base64_decode($iv);

    $decryptedFormData = openssl_decrypt(base64_decode($encryptedFormData), 'aes-256-cbc', $aesKey, OPENSSL_RAW_DATA, $iv);

    if ($decryptedFormData === false) {
        throw new Exception('Erro ao decriptar os dados do formulário com AES.');
    }

    $formData = json_decode($decryptedFormData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Erro ao decodificar os dados do formulário JSON.');
    }

    $verificationCode = $formData['verification_code'];

    $userId = $_SESSION['login_user_id'] ?? '';

    if (empty($userId)) {
        return_json_error('ID do usuário não encontrado na sessão.');
    }

    $stmt = $conn->prepare("SELECT * FROM usuario WHERE id = ? AND codigo_verificacao = ? AND codigo_verificacao_expira > NOW()");
    if (!$stmt) {
        log_error('Erro ao preparar a query: ' . $conn->error);
        return_json_error('Erro ao preparar a query: ' . $conn->error);
    }
    $stmt->bind_param('is', $userId, $verificationCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        echo json_encode(['success' => true, 'message' => 'Código verificado com sucesso.', 'redirect' => '../php/verifica_sessao_usuario.php']);
    } else {
        return_json_error('Código inválido ou expirado.');
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    log_error($e->getMessage());
    return_json_error($e->getMessage());
}

ob_end_flush();
?>
