<?php
include 'decode_cred.php';

ob_start();
error_reporting(0);
ini_set('display_errors', 0);

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

function validarSenha($senha)
{
    $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($regex, $senha);
}

function validarEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validarCpfCnpj($cpfCnpj)
{
    $cleaned = preg_replace('/\D/', '', $cpfCnpj);
    return strlen($cleaned) === 11 || strlen($cleaned) === 14;
}

function validarDataNasc($data_nasc)
{
    $regexData = '/^\d{4}-\d{2}-\d{2}$/';
    return preg_match($regexData, $data_nasc);
}

function enviarEmailConfirmacao($email, $token)
{
    $mail = new PHPMailer(true);
    try {
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
        $mail->Body = "Clique aqui para confirmar seu cadastro: <a href='http://localhost/php/confirmar_usuario.php?token={$token}'>Confirmar Cadastro</a>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail: {$mail->ErrorInfo}");
        return false;
    }
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

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $conn = new mysqli($credentials['servername'], $credentials['username'], $credentials['password'], $credentials['database']);
        if ($conn) {
            $name = $conn->real_escape_string($_POST['name']);
            $login = $conn->real_escape_string($_POST['login']);
            $email = $conn->real_escape_string($_POST['email']);
            $cpf = $conn->real_escape_string($_POST['cpf']);
            $data_nasc = $conn->real_escape_string($_POST['data_nasc']);
            $encryptedPassword = $_POST['password'];
            $phone = $conn->real_escape_string($_POST['phone']);

            if (!validarEmail($email)) {
                echo json_encode(['success' => false, 'message' => 'E-mail inválido.']);
                exit;
            }

            if (!validarCpfCnpj($cpf)) {
                echo json_encode(['success' => false, 'message' => 'CPF/CNPJ inválido.']);
                exit;
            }

            if (!validarDataNasc($data_nasc)) {
                echo json_encode(['success' => false, 'message' => 'Data de nascimento inválida.']);
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

            $stmt = $conn->prepare("INSERT INTO usuario (name, login, email, data_nasc, cpf, password, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssss', $name, $login, $email, $data_nasc, $cpf, $hashedPassword, $phone);

            if ($stmt->execute()) {
                $token = bin2hex(random_bytes(50));
                $updateTokenStmt = $conn->prepare("UPDATE usuario SET token = ? WHERE email = ?");
                $updateTokenStmt->bind_param('ss', $token, $email);
                $updateTokenStmt->execute();
                $updateTokenStmt->close();

                if (enviarEmailConfirmacao($email, $token)) {
                    echo json_encode(["mensagem" => "Usuário cadastrado com sucesso! E-mail de confirmação enviado."]);
                } else {
                    echo json_encode(["mensagem" => "Usuário cadastrado. Erro ao enviar e-mail de confirmação."]);
                }
            } else {
                echo json_encode(["mensagem" => "Erro ao cadastrar o usuário: " . $stmt->error]);
            }

            $stmt->close();
            $conn->close();
        } else {
            echo json_encode(["mensagem" => "Erro na conexão com o banco de dados: " . $conn->connect_error]);
        }
    } else {
        echo json_encode(["mensagem" => "Método de requisição inválido."]);
    }
} catch (Exception $e) {
    log_error($e->getMessage());
    return_json_error($e->getMessage());
}

ob_end_flush();
?>