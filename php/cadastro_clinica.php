<?php
include 'decode_cred.php';

ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Funções de validação
function validarSenha($senha)
{
    $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($regex, $senha);
}

function validarEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validarCRMV($crmv)
{
    $regex = '/^[A-Z]{2}\/\d+$/';
    return preg_match($regex, $crmv);
}

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

    // Conexão ao banco de dados
    $conn = new mysqli($credentials['servername'], $credentials['username'], $credentials['password'], $credentials['database']);

    if ($conn->connect_error) {
        throw new Exception('Conexão falhou: ' . $conn->connect_error);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $conn->real_escape_string($_POST['name']);
        $login = $conn->real_escape_string($_POST['login']);
        $email = $conn->real_escape_string($_POST['email']);
        $especializacao = $conn->real_escape_string($_POST['especializacao']);
        $endereco = $conn->real_escape_string($_POST['endereco']);
        $crmv = $conn->real_escape_string($_POST['crmv']);
        $encryptedPassword = $_POST['password'];
        $phone = $conn->real_escape_string($_POST['phone']);

        if (!validarEmail($email) || !validarCRMV($crmv)) {
            echo json_encode(['success' => false, 'message' => 'Validação falhou']);
            exit;
        }

        if (empty($encryptedPassword)) {
            throw new Exception('Senha é obrigatória.');
        }

        // Decriptar a senha recebida
        $decryptedPassword = '';
        if (!openssl_private_decrypt(base64_decode($encryptedPassword), $decryptedPassword, $privateKey)) {
            throw new Exception('Erro ao decriptar a senha.');
        }

        if (!validarSenha($decryptedPassword)) {
            echo json_encode(['success' => false, 'message' => 'A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma letra minúscula, um número e um caractere especial.']);
            exit;
        }

        $hashedPassword = password_hash($decryptedPassword, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO clinica (name, login, email, especializacao, endereco, crmv, password, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Erro ao preparar a consulta: ' . $conn->error);
        }
        $stmt->bind_param("ssssssss", $name, $login, $email, $especializacao, $endereco, $crmv, $hashedPassword, $phone);

        if ($stmt->execute()) {
            $token = bin2hex(random_bytes(16));
            $expira = date("Y-m-d H:i:s", strtotime('+1 day'));

            $stmtUpdate = $conn->prepare("UPDATE clinica SET token = ?, token_expira = ? WHERE email = ?");
            if (!$stmtUpdate) {
                throw new Exception('Erro ao preparar a consulta de atualização: ' . $conn->error);
            }
            $stmtUpdate->bind_param("sss", $token, $expira, $email);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'buscavetpucpr@gmail.com';
            $mail->Password = 'emdy mihd aoeo pxut';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('buscavetpucpr@gmail.com', 'BuscaVet');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Confirmação de Cadastro';
            $mail->Body = "Clique aqui para confirmar seu cadastro: <a href='http://localhost/php/confirmar_clinica.php?token={$token}'>Confirmar Cadastro</a>";

            try {
                $mail->send();
                echo json_encode(['success' => true, 'message' => 'Clínica cadastrada e e-mail de confirmação enviado.']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Clínica cadastrada, mas houve um erro ao enviar o e-mail de confirmação. Erro: ' . $mail->ErrorInfo]);
            }
        } else {
            throw new Exception('Erro ao cadastrar a clínica: ' . $stmt->error);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Método de requisição inválido']);
    }

    $conn->close();
} catch (Exception $e) {
    log_error($e->getMessage());
    return_json_error($e->getMessage());
}

ob_end_flush();
?>