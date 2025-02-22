<?php
include 'decode_cred.php';

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

function validarSenha($senha) {
    $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($regex, $senha);
}

function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validarCpfCnpj($cpfCnpj) {
    $cleaned = preg_replace('/\D/', '', $cpfCnpj);
    return strlen($cleaned) === 11 || strlen($cleaned) === 14;
}

function validarDataNasc($data_nasc) {
    $regexData = '/^\d{4}-\d{2}-\d{2}$/';
    return preg_match($regexData, $data_nasc);
}

function enviarEmailConfirmacao($email, $token) {
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

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $conn = new mysqli($credentials['servername'], $credentials['username'], $credentials['password'], $credentials['database']);
        if ($conn) {
            $encryptedName = $_POST['name'];
            $encryptedLogin = $_POST['login'];
            $encryptedEmail = $_POST['email'];
            $encryptedCpf = $_POST['cpf'];
            $encryptedDataNasc = $_POST['data_nasc'];
            $encryptedPassword = $_POST['password'];
            $encryptedPhone = $_POST['phone'];

            $name = '';
            $login = '';
            $email = '';
            $cpf = '';
            $data_nasc = '';
            $password = '';
            $phone = '';

            if (!openssl_private_decrypt(base64_decode($encryptedName), $name, $privateKey)) {
                throw new Exception('Erro ao decriptar o nome.');
            }
            if (!openssl_private_decrypt(base64_decode($encryptedLogin), $login, $privateKey)) {
                throw new Exception('Erro ao decriptar o login.');
            }
            if (!openssl_private_decrypt(base64_decode($encryptedEmail), $email, $privateKey)) {
                throw new Exception('Erro ao decriptar o email.');
            }
            if (!openssl_private_decrypt(base64_decode($encryptedCpf), $cpf, $privateKey)) {
                throw new Exception('Erro ao decriptar o CPF.');
            }
            if (!openssl_private_decrypt(base64_decode($encryptedDataNasc), $data_nasc, $privateKey)) {
                throw new Exception('Erro ao decriptar a data de nascimento.');
            }
            if (!openssl_private_decrypt(base64_decode($encryptedPassword), $password, $privateKey)) {
                throw new Exception('Erro ao decriptar a senha.');
            }
            if (!openssl_private_decrypt(base64_decode($encryptedPhone), $phone, $privateKey)) {
                throw new Exception('Erro ao decriptar o telefone.');
            }

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

            if (empty($password)) {
                throw new Exception('Senha é obrigatória.');
            }

            if (!validarSenha($password)) {
                echo json_encode(['success' => false, 'message' => 'A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma letra minúscula, um número e um caractere especial.']);
                exit;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO usuario (name, login, email, data_nasc, cpf, password, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssss', $name, $login, $email, $data_nasc, $cpf, $hashedPassword, $phone);

            if ($stmt->execute()) {
                $token = bin2hex(random_bytes(16));
                $expira = date("Y-m-d H:i:s", strtotime('+1 day'));

                $stmtUpdate = $conn->prepare("UPDATE usuario SET token = ?, token_expira = ? WHERE email = ?");
                $stmtUpdate->bind_param("sss", $token, $expira, $email);
                $stmtUpdate->execute();
                $stmtUpdate->close();

                if (enviarEmailConfirmacao($email, $token)) {
                    echo json_encode(['success' => true, 'message' => 'Usuário cadastrado com sucesso. Por favor, verifique seu e-mail para confirmar o cadastro.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Usuário cadastrado, mas houve um erro ao enviar o e-mail de confirmação.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar o usuário.']);
            }

            $stmt->close();
        } else {
            throw new Exception('Erro ao conectar ao banco de dados.');
        }

        $conn->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
    }
} catch (Exception $e) {
    log_error($e->getMessage());
    return_json_error($e->getMessage());
}

ob_end_flush();
?>
