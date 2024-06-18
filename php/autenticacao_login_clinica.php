<?php
include 'decode_cred.php';

session_start();
header('Content-Type: application/json');

$privateKeyPath = '../chaves/private_key.pem';

function log_error($message) {
    error_log($message, 3, '../logs/php-error.log');
}

function return_json_error($message) {
    echo json_encode(['success' => false, 'message' => $message]);
    ob_end_flush();
    exit;
}

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

    $encryptedFormData = $_POST['formData'] ?? '';
    $encryptedAesKey = $_POST['aesKey'] ?? '';
    $encryptedIv = $_POST['iv'] ?? '';

    if (empty($encryptedFormData) || empty($encryptedAesKey) || empty($encryptedIv)) {
        throw new Exception('Dados do formulário, chave AES e IV são obrigatórios.');
    }

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

    $email = $formData['email'];
    $password = $formData['password'];

    $conn = new mysqli($credentials['servername'], $credentials['username'], $credentials['password'], $credentials['database']);

    if ($conn->connect_error) {
        throw new Exception('Falha na conexão: ' . $conn->connect_error);
    }

    $query = "SELECT id, name, email, password, confirmacao, phone FROM clinica WHERE email = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception('Erro na consulta: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $clinica = $result->fetch_assoc();

        if ($clinica['confirmacao'] != 1) {
            echo json_encode(['success' => false, 'message' => 'Conta não confirmada. Por favor, verifique seu e-mail.']);
            $stmt->close();
            $conn->close();
            ob_end_flush();
            exit;
        }

        if (password_verify($password, $clinica['password'])) {
            $_SESSION['clinica_id'] = $clinica['id'];
            $_SESSION['login_clinica_email'] = $clinica['email'];

            $codigo_verificacao = rand(100000, 999999);
            $codigo_verificacao_expira = (new DateTime())->add(new DateInterval('PT10M'))->format('Y-m-d H:i:s');
            $updateStmt = $conn->prepare("UPDATE clinica SET codigo_verificacao = ?, codigo_verificacao_expira = ? WHERE id = ?");
            $updateStmt->bind_param('ssi', $codigo_verificacao, $codigo_verificacao_expira, $clinica['id']);
            $updateStmt->execute();
            $updateStmt->close();

            require_once '../twilio/vendor/autoload.php';
            $twilioSid = 'AC6fdfece6c25c3b5788e700f63e2f6c2f';
            $twilioToken = '0b1ee83dccf90fcea079401ece7234a6';
            $twilioPhoneNumber = '15306841566';

            $client = new Twilio\Rest\Client($twilioSid, $twilioToken);

            try {
                $message = $client->messages->create(
                    $clinica['phone'],
                    [
                        'from' => $twilioPhoneNumber,
                        'body' => "Seu código de verificação é: {$codigo_verificacao}"
                    ]
                );

                echo json_encode(['success' => true, 'message' => 'Login bem-sucedido e SMS enviado.', 'redirect' => '../html/verificar_codigo_clinica.html']);
            } catch (Exception $e) {
                log_error('Erro ao enviar código de verificação: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Erro ao enviar código de verificação: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Senha incorreta.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Clínica não encontrada.']);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    log_error($e->getMessage());
    return_json_error($e->getMessage());
}

ob_end_flush();
?>
