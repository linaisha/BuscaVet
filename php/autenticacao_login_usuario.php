<?php
include 'decode_cred.php';

ob_start();
ini_set('display_errors', 0);
error_reporting(0);

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
    ob_end_flush();
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

    $encryptedEmail = $_POST['email'] ?? '';
    $encryptedPassword = $_POST['password'] ?? '';

    if (empty($encryptedEmail) || empty($encryptedPassword)) {
        throw new Exception('Email e senha são obrigatórios.');
    }

    $email = '';
    $decryptedPassword = '';

    if (!openssl_private_decrypt(base64_decode($encryptedEmail), $email, $privateKey)) {
        throw new Exception('Erro ao decriptar o email.');
    }

    if (!openssl_private_decrypt(base64_decode($encryptedPassword), $decryptedPassword, $privateKey)) {
        throw new Exception('Erro ao decriptar a senha.');
    }

    $conn = new mysqli($credentials['servername'], $credentials['username'], $credentials['password'], $credentials['database']);

    if ($conn->connect_error) {
        throw new Exception('Falha na conexão: ' . $conn->connect_error);
    }

    $query = "SELECT id, name, email, password, confirmacao, phone FROM usuario WHERE email = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception('Erro na consulta: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($user['confirmacao'] != 1) {
            echo json_encode(['success' => false, 'message' => 'Conta não confirmada. Por favor, verifique seu e-mail.']);
            $stmt->close();
            $conn->close();
            ob_end_flush();
            exit;
        }

        if (password_verify($decryptedPassword, $user['password'])) {
            $_SESSION['login_user_id'] = $user['id'];
            $_SESSION['login_user_email'] = $user['email'];

            $codigo_verificacao = rand(100000, 999999);
            $codigo_verificacao_expira = (new DateTime())->add(new DateInterval('PT10M'))->format('Y-m-d H:i:s');
            $updateStmt = $conn->prepare("UPDATE usuario SET codigo_verificacao = ?, codigo_verificacao_expira = ? WHERE id = ?");
            $updateStmt->bind_param('ssi', $codigo_verificacao, $codigo_verificacao_expira, $user['id']);
            $updateStmt->execute();
            $updateStmt->close();

            require_once '../twilio/vendor/autoload.php';
            $twilioSid = 'AC6fdfece6c25c3b5788e700f63e2f6c2f';
            $twilioToken = '0b1ee83dccf90fcea079401ece7234a6';
            $twilioPhoneNumber = '15306841566';

            $client = new Twilio\Rest\Client($twilioSid, $twilioToken);

            try {
                $message = $client->messages->create(
                    $user['phone'],
                    [
                        'from' => $twilioPhoneNumber,
                        'body' => "Seu código de verificação é: {$codigo_verificacao}"
                    ]
                );

                echo json_encode(['success' => true, 'message' => 'Login bem-sucedido e SMS enviado.']);
            } catch (Exception $e) {
                log_error('Erro ao enviar código de verificação: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Erro ao enviar código de verificação: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Senha incorreta.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado.']);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    log_error($e->getMessage());
    return_json_error($e->getMessage());
}

ob_end_flush();

?>