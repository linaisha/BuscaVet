<?php
include 'config.php';

ob_start();
ini_set('display_errors', 0);
error_reporting(0);

session_start();
header('Content-Type: application/json');

$conn = new mysqli(servername, username, password, database);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Falha na conexão: ' . $conn->connect_error]);
    exit;
}

$email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
$hashedPassword = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($hashedPassword)) {
    echo json_encode(['success' => false, 'message' => 'Email e senha são obrigatórios.']);
    exit;
}

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

    if ($clinica['password'] === $hashedPassword) {
        $_SESSION['clinica_id'] = $clinica['id'];
        $_SESSION['login_clinica_email'] = $clinica['email'];

        $codigo_verificacao = rand(100000, 999999);
        $codigo_verificacao_expira = (new DateTime())->add(new DateInterval('PT10M'))->format('Y-m-d H:i:s');
        $updateStmt = $conn->prepare("UPDATE clinica SET codigo_verificacao = ?, codigo_verificacao_expira = ? WHERE id = ?");
        $updateStmt->bind_param('ssi', $codigo_verificacao, $codigo_verificacao_expira, $clinica['id']);
        $updateStmt->execute();
        $updateStmt->close();

        require_once '../twilio/vendor/autoload.php';
        $twilioSid = 'AC067590a3b1cdf1a1f05ff3cf7d41ed36';
        $twilioToken = '7c0f1096a52587bf580db980bf56ffe9';
        $twilioPhoneNumber = '13203968435';

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
