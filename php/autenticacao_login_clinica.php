<?php
include 'decode_cred.php';

ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

// Configurações do caminho para os arquivos de certificado e chave privada
$certPath = '../chaves/certificate.pem';
$privateKeyPath = '../chaves/private_key.pem';
$privateKeyPassword = 'TotalmenteOnline#69'; // Senha da chave privada

// Função para registrar erros
function log_error($message)
{
    error_log($message, 3, '../logs/php-error.log');
}

// Função para retornar erros como JSON
function return_json_error($message)
{
    echo json_encode(['success' => false, 'message' => $message]);
    ob_end_flush();
    exit;
}

try {
    // Verificar se os arquivos existem
    if (!file_exists($certPath)) {
        throw new Exception('Certificado não encontrado no caminho especificado: ' . $certPath);
    }

    if (!file_exists($privateKeyPath)) {
        throw new Exception('Chave privada não encontrada no caminho especificado: ' . $privateKeyPath);
    }

    // Leitura do certificado e da chave privada
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

    // Conectar ao banco de dados
    $conn = new mysqli($credentials['servername'], $credentials['username'], $credentials['password'], $credentials['database']);

    if ($conn->connect_error) {
        throw new Exception('Falha na conexão: ' . $conn->connect_error);
    }

    // Receber os dados do formulário
    $email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
    $encryptedPassword = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($encryptedPassword)) {
        throw new Exception('Email e senha são obrigatórios.');
    }

    // Decriptar a senha recebida
    $decryptedPassword = '';
    if (!openssl_private_decrypt(base64_decode($encryptedPassword), $decryptedPassword, $privateKey)) {
        throw new Exception('Erro ao decriptar a senha.');
    }

    // Consultar a clínica no banco de dados
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

        // Verificar a senha descriptografada com o hash armazenado
        if (password_verify($decryptedPassword, $clinica['password'])) {
            $_SESSION['clinica_id'] = $clinica['id'];
            $_SESSION['login_clinica_email'] = $clinica['email'];

            $codigo_verificacao = rand(100000, 999999);
            $codigo_verificacao_expira = (new DateTime())->add(new DateInterval('PT10M'))->format('Y-m-d H:i:s');
            $updateStmt = $conn->prepare("UPDATE clinica SET codigo_verificacao = ?, codigo_verificacao_expira = ? WHERE id = ?");
            $updateStmt->bind_param('ssi', $codigo_verificacao, $codigo_verificacao_expira, $clinica['id']);
            $updateStmt->execute();
            $updateStmt->close();

            require_once '../twilio/vendor/autoload.php';
            $twilioSid = 'AC986807cad58fd6a8134f2a3f8c80a9c7';
            $twilioToken = '38c444a3f2399d7a98794f627becf25b';
            $twilioPhoneNumber = '14793485734';

            $client = new Twilio\Rest\Client($twilioSid, $twilioToken);

            try {
                $message = $client->messages->create(
                    $clinica['phone'],
                    [
                        'from' => $twilioPhoneNumber,
                        'body' => "Seu código de verificação é: {$codigo_verificacao}"
                    ]
                );

                echo json_encode(['success' => true, 'message' => 'Login bem-sucedido e SMS enviado.', 'redirect' => '../html/pagina_inicial.html']);
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