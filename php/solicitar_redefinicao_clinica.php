<?php

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$con = mysqli_connect("localhost", "root", "", "buscavet");
if (!$con) {
    die("Conexão falhou: " . mysqli_connect_error());
}

function emailExiste($con, $email)
{
    $stmt = $con->prepare("SELECT id FROM clinica WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function validarEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function enviarEmailRedefinicao($email, $token)
{
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
    $mail->Subject = 'Redefinição de Senha';
    $urlRedefinicao = "http://localhost/php/redefinicao_clinica.php?token=$token";
    $mail->Body = "Clique no seguinte link para redefinir sua senha: <a href='$urlRedefinicao'>Redefinir Senha</a>";

    try {
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail: " . $mail->ErrorInfo);
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    if (validarEmail($email) && emailExiste($con, $email)) {
        $token = bin2hex(random_bytes(50));
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $stmt = $con->prepare("UPDATE clinica SET token = ?, token_expira = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expira, $email);
        if ($stmt->execute()) {
            if (enviarEmailRedefinicao($email, $token)) {
                echo json_encode(["mensagem" => "E-mail de redefinição de senha enviado com sucesso."]);
            } else {
                echo json_encode(["mensagem" => "Erro ao enviar e-mail de redefinição."]);
            }
        } else {
            echo json_encode(["mensagem" => "Erro ao salvar o token no banco de dados."]);
        }
        $stmt->close();
    } else {
        echo json_encode(["mensagem" => "E-mail não encontrado no banco de dados."]);
    }
}

mysqli_close($con);
?>