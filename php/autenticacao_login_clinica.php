<?php
include 'decode_cred.php';

ob_start();
ini_set('display_errors', 0);
error_reporting(0);

session_start();
header('Content-Type: application/json');

// Configurações do caminho para os arquivos de certificado e chave privada
$certPath = '../chavesTeste/certificate.pem';
$privateKeyPath = '../chavesTeste/private_key.pem';

// Verificar se os arquivos existem
if (!file_exists($certPath) || !file_exists($privateKeyPath)) {
    echo json_encode(['success' => false, 'message' => 'Certificado ou chave privada não encontrados.']);
    exit;
}

// Leitura do certificado e da chave privada
$publicKey = file_get_contents($certPath);
$privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath));

if (!$privateKey) {
    echo json_encode(['success' => false, 'message' => 'Falha ao carregar a chave privada.']);
    exit;
}

// Conectar ao banco de dados
$conn = new mysqli($credentials['servername'], $credentials['username'], $credentials['password'], $credentials['database']);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Falha na conexão: ' . $conn->connect_error]);
    exit;
}

// Receber os dados do formulário
$email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
$encryptedPassword = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($encryptedPassword)) {
    echo json_encode(['success' => false, 'message' => 'Email e senha são obrigatórios.']);
    exit;
}

// Decriptar a senha recebida
$decryptedPassword = '';
if (!openssl_private_decrypt(base64_decode($encryptedPassword), $decryptedPassword, $privateKey)) {
    echo json_encode(['success' => false, 'message' => 'Erro ao decriptar a senha.']);
    exit;
}

// Consultar a clínica no banco de dados
$query = "SELECT id, name, email, password, confirmacao, phone FROM clinica WHERE email = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Erro na consulta: ' . $conn->error]);
    exit;
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
        exit;
    }

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

            echo json_encode(['success' => true, 'message' => 'Login bem-sucedido e SMS enviado.']);
        } catch (Exception $e) {
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
ob_end_flush();
?>
