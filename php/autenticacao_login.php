<?php
session_start();
header('Content-Type: application/json');

// Configuração do banco de dados
$host = "localhost";
$username = "root";
$password = "";
$database = "buscavet";

// Conectando ao banco de dados
$conn = new mysqli($host, $username, $password, $database);

// Checar a conexão
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Falha na conexão: ' . $conn->connect_error]);
    exit;
}

// Pegar email e senha do POST
$email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email e senha são obrigatórios.']);
    exit;
}

// Preparar a consulta SQL
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

    // Verificar se a conta foi confirmada
    if ($user['confirmacao'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Conta não confirmada. Por favor, verifique seu e-mail.']);
        $stmt->close();
        $conn->close();
        exit;
    }

    // Verificando a senha
    if (password_verify($password, $user['password'])) {
        // Senha correta, gerar código de verificação
        $codigo_verificacao = rand(100000, 999999);
        $codigo_verificacao_expira = new DateTime('+10 minutes'); // O código expira em 10 minutos

        // Atualiza o banco de dados com o código de verificação e a data de expiração
        $updateStmt = $conn->prepare("UPDATE usuario SET codigo_verificacao = ?, codigo_verificacao_expira = ? WHERE id = ?");
        $updateStmt->bind_param('ssi', $codigo_verificacao, $codigo_verificacao_expira->format('Y-m-d H:i:s'), $user['id']);
        $updateStmt->execute();
        $updateStmt->close();

        // Enviar código via SMS usando Twilio
        require_once '../twilio/vendor/autoload.php'; // Ajuste o caminho para o autoload do Composer
        $twilioSid = 'AC067590a3b1cdf1a1f05ff3cf7d41ed36';
        $twilioToken = '7c0f1096a52587bf580db980bf56ffe9';
        $twilioPhoneNumber = '13203968435';

        $client = new Twilio\Rest\Client($twilioSid, $twilioToken);
        
        try {
            $message = $client->messages->create(
                $user['phone'], // Número de telefone do usuário
                [
                    'from' => $twilioPhoneNumber,
                    'body' => "Seu código de verificação é: {$codigo_verificacao}"
                ]
            );
            
            // Se a mensagem foi enviada, guarda o ID do usuário e o email na sessão e solicita o código de verificação
            $_SESSION['login_user_id'] = $user['id'];
            $_SESSION['login_user_email'] = $user['email'];
            echo json_encode(['success' => true, 'message' => 'Código de verificação enviado.', 'next_step' => 'verify_code']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao enviar código de verificação: ' . $e->getMessage()]);
        }
    } else {
        // Senha incorreta
        echo json_encode(['success' => false, 'message' => 'Senha incorreta.']);
    }
} else {
    // Usuário não encontrado
    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado.']);
}

$stmt->close();
$conn->close();
?>
