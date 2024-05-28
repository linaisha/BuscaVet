<?php
include 'decode_config.php';

ob_start();
ini_set('display_errors', 0);
error_reporting(0);

session_start();
header('Content-Type: application/json');

$conn = new mysqli($servername, $username, $password, $database);

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

$query = "SELECT id, name, email, password, confirmacao, phone FROM usuario WHERE email = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Erro na consulta: ' . $conn->error]);
    exit;
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
        exit;
    }

    if ($user['password'] === $hashedPassword) {
        $_SESSION['login_user_id'] = $user['id'];
        $_SESSION['login_user_email'] = $user['email'];

        $codigo_verificacao = rand(100000, 999999);
        $codigo_verificacao_expira = (new DateTime())->add(new DateInterval('PT10M'))->format('Y-m-d H:i:s');
        $updateStmt = $conn->prepare("UPDATE usuario SET codigo_verificacao = ?, codigo_verificacao_expira = ? WHERE id = ?");
        $updateStmt->bind_param('ssi', $codigo_verificacao, $codigo_verificacao_expira, $user['id']);
        $updateStmt->execute();
        $updateStmt->close();

        require_once '../twilio/vendor/autoload.php';
        $twilioSid = 'AC986807cad58fd6a8134f2a3f8c80a9c7';
        $twilioToken = '38c444a3f2399d7a98794f627becf25b';
        $twilioPhoneNumber = '14793485734';

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
ob_end_flush();
?>