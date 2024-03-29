<?php

ob_start();

error_reporting(0);
ini_set('display_errors', 0);

// Inclui os arquivos do PHPMailer
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

function validarSenha($senha){
    $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($regex, $senha);
}

function validarEmail($email){
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validarCpfCnpj($cpfCnpj){
    $cleaned = preg_replace('/\D/', '', $cpfCnpj);
    return strlen($cleaned) === 11 || strlen($cleaned) === 14;
}

function validarDataNasc($data_nasc){
    $regexData = '/^\d{4}-\d{2}-\d{2}$/'; 
    return preg_match($regexData, $data_nasc);
}
//EMAILLLLL
function enviarEmailConfirmacao($email, $token) {
    $mail = new PHPMailer(true);
    try {
        // Configuração do servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'buscavetpucpr@gmail.com'; // Substitua pelo seu e-mail
        $mail->Password = 'emdy mihd aoeo pxut';           // Substitua pela sua senha
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Definir remetente e destinatário
        $mail->setFrom('buscavetpucpr@gmail.com', 'BuscaVet');
        $mail->addAddress($email);

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = 'Confirmação de Cadastro';
        $mail->Body    = "Clique aqui para confirmar seu cadastro: <a href='http://localhost/php/confirmar_usuario.php?token={$token}'>Confirmar Cadastro</a>";

        // Enviar o e-mail
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail: {$mail->ErrorInfo}");
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $con = mysqli_connect("localhost", "root", "", "buscavet");

    if ($con) {
        $name = $_POST['name'];
        $login = $_POST['login'];
        $email = $_POST['email'];
        $cpf = $_POST['cpf'];
        $data_nasc = $_POST['data_nasc'];
        $password = $_POST['password'];

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

        
        error_log("Validating date of birth: " . $data_nasc);

        if (!validarDataNasc($data_nasc)) {
            ob_end_clean();
            echo json_encode(["mensagem" => "Data de nascimento inválida."]);
            exit;
        }

        if (!validarSenha($password)) {
            ob_end_clean();
            echo json_encode(["mensagem" => "A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúsculo, uma letra minúscula, um número e um caractere especial."]);
            exit;
        }

        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($con, "INSERT INTO usuario (name, login, email, data_nasc, cpf, password) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssssss', 
            $name, 
            $login, 
            $email, 
            $data_nasc,
            $cpf,
            $passwordHashed);

            if (mysqli_stmt_execute($stmt)) {
                $token = bin2hex(random_bytes(50)); // Gera um token seguro
                // Salva o token na base de dados
                $updateTokenStmt = mysqli_prepare($con, "UPDATE usuario SET token = ? WHERE email = ?");
                mysqli_stmt_bind_param($updateTokenStmt, 'ss', $token, $email);
                mysqli_stmt_execute($updateTokenStmt);
                mysqli_stmt_close($updateTokenStmt);
    
                // Enviar e-mail de confirmação
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
            mysqli_close($con);
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

?>
