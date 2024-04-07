<?php

ob_start();

error_reporting(0);
ini_set('display_errors', 0);

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

// como se valida um cnpj? link: https://blog.dbins.com.br/como-funciona-a-logica-da-validacao-do-cnpj#google_vignette
function validarCnpj($cnpj){
    $cnpj = preg_replace('/\D/', '', $cnpj);
    if (strlen($cnpj) !== 14) return false;

    $calculo = 0;
    $calculo2 = 0;
    $regra = [6,5,4,3,2,9,8,7,6,5,4,3,2];
    
    for ($i = 0; $i < 12; $i++) {
        $calculo = $calculo + ($cnpj[$i] * $regra[$i+1]);
    }
    
    $calculo = ($calculo % 11 < 2) ? 0 : 11 - ($calculo % 11);
    
    for ($i = 0; $i < 13; $i++) {
        $calculo2 = $calculo2 + ($cnpj[$i] * $regra[$i]);
    }
    
    $calculo2 = ($calculo2 % 11 < 2) ? 0 : 11 - ($calculo2 % 11);
    
    if ($calculo != $cnpj[12] || $calculo2 != $cnpj[13]) {
        return false;
    } else {
        return true;
    }
}

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
        $mail->Body    = "Clique aqui para confirmar seu cadastro: <a href='http://localhost/php/confirmar_clinica.php?token={$token}'>Confirmar Cadastro</a>";

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
        $cnpj = $_POST['cnpj'];
        $endereco = $_POST['endereco'];
        $crmv = $_POST['crmv'];
        $password = $_POST['password'];

        if (!validarEmail($email)) {
            echo json_encode(["mensagem" => "E-mail inválido."]);
            exit;
        }

        if (!validarCnpj($cnpj)) {
            echo json_encode(["mensagem" => "CNPJ inválido."]);
            exit;
        }

        if (!validarSenha($password)) {
            echo json_encode(["mensagem" => "A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma letra minúscula, um número e um caractere especial."]);
            exit;
        }

        $crmv = strtoupper($_POST['crmv']);

        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($con, "INSERT INTO clinica (name, login, email, cnpj, endereco, crmv, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssssss',
         $name, 
         $login, 
         $email, 
         $cnpj, 
         $endereco, 
         $crmv, 
         $passwordHashed);

         if (mysqli_stmt_execute($stmt)) {
            $token = bin2hex(random_bytes(50)); // Gera um token seguro
            // Salva o token na base de dados
            $updateTokenStmt = mysqli_prepare($con, "UPDATE clinica SET token = ? WHERE email = ?");
            mysqli_stmt_bind_param($updateTokenStmt, 'ss', $token, $email);
            mysqli_stmt_execute($updateTokenStmt);
            mysqli_stmt_close($updateTokenStmt);

            // Enviar e-mail de confirmação
            if (enviarEmailConfirmacao($email, $token)) {
                ob_end_clean();
                echo json_encode(["mensagem" => "Clinica cadastrado com sucesso! E-mail de confirmação enviado."]);
            } else {
                ob_end_clean();
                echo json_encode(["mensagem" => "Clinica cadastrado. Erro ao enviar e-mail de confirmação."]);
            }
        } else {
            ob_end_clean();
            echo json_encode(["mensagem" => "Erro ao cadastrar o Clinica: " . mysqli_stmt_error($stmt)]);
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

