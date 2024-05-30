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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli($credentials['servername'], $credentials['username'], $credentials['password'], $credentials['database']);
    if ($conn) {
        $name = $_POST['name'];
        $login = $_POST['login'];
        $email = $_POST['email'];
        $cpf = $_POST['cpf'];
        $data_nasc = $_POST['data_nasc'];
        $password = $_POST['password'];
        $phone = $_POST['phone'] ?? '';

        if (!validarEmail($email)) {
            ob_end_clean();
            echo json_encode(["mensagem" => "E-mail inválido."]);
            exit;
        }

        if (!validarCpfCnpj($cpf)) {
            ob_end_clean();
            echo json_encode(["mensagem" => "CPF/CNPJ inválido."]);
            exit;
        }

        if (!validarDataNasc($data_nasc)) {
            ob_end_clean();
            echo json_encode(["mensagem" => "Data de nascimento inválida."]);
            exit;
        }

        if (!validarSenha($password)) {
            ob_end_clean();
            echo json_encode(["mensagem" => "A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma letra minúscula, um número e um caractere especial."]);
            exit;
        }

        // SHA-256 Hashing
        $passwordHashed = hash('sha256', $password);
        $stmt = $conn->prepare("INSERT INTO usuario (name, login, email, data_nasc, cpf, password, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssssss', $name, $login, $email, $data_nasc, $cpf, $passwordHashed, $phone);

        if (mysqli_stmt_execute($stmt)) {
            $token = bin2hex(random_bytes(50));
            $updateTokenStmt = mysqli_prepare($conn, "UPDATE usuario SET token = ? WHERE email = ?");
            mysqli_stmt_bind_param($updateTokenStmt, 'ss', $token, $email);
            mysqli_stmt_execute($updateTokenStmt);
            mysqli_stmt_close($updateTokenStmt);

            if (enviarEmailConfirmacao($email, $token)) {
                ob_end_clean();
                echo json_encode(["mensagem" => "Usuário cadastrado com sucesso! E-mail de confirmação enviado."]);
            } else {
                ob_end_clean();
                echo json_encode(["mensagem" => "Usuário cadastrado. Erro ao enviar e-mail de confirmação."]);
            }
        } else {
            ob_end_clean();
            echo json_encode(["mensagem" => "Erro ao cadastrar o usuário: " . mysqli_stmt_error($stmt)]);
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    } else {
        ob_end_clean();
        echo json_encode(["mensagem" => "Erro na conexão com o banco de dados: " . mysqli_connect_error()]);
    }
} else {
    ob_end_clean();
    echo json_encode(["mensagem" => "Método de requisição inválido."]);
}

ob_end_flush();
?>
