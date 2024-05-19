<?php
include 'config.php';

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

function validarSenha($senha) {
    $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($regex, $senha);
}

function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validarCnpj($cnpj) {
    $cnpj = preg_replace('/\D/', '', $cnpj);
    if (strlen($cnpj) !== 14)
        return false;

    $calculo = 0;
    $calculo2 = 0;
    $regra = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    for ($i = 0; $i < 12; $i++) {
        $calculo = $calculo + ($cnpj[$i] * $regra[$i + 1]);
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

$conn = new mysqli(servername, username, password, database);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Conexão falhou: ' . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $login = $conn->real_escape_string($_POST['login']);
    $email = $conn->real_escape_string($_POST['email']);
    $cnpj = $conn->real_escape_string($_POST['cnpj']);
    $endereco = $conn->real_escape_string($_POST['endereco']);
    $crmv = $conn->real_escape_string($_POST['crmv']);
    $password = $_POST['password'];
    $phone = $conn->real_escape_string($_POST['phone']);

    if (!validarEmail($email) || !validarSenha($password) || !validarCnpj($cnpj)) {
        echo json_encode(['success' => false, 'message' => 'Validação falhou']);
        exit;
    }

    $passwordHashed = hash('sha256', $password);

    $stmt = $conn->prepare("INSERT INTO clinica (name, login, email, cnpj, endereco, crmv, password, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $name, $login, $email, $cnpj, $endereco, $crmv, $passwordHashed, $phone);

    if ($stmt->execute()) {
        $token = bin2hex(random_bytes(16));
        $expira = date("Y-m-d H:i:s", strtotime('+1 day'));

        $stmtUpdate = $conn->prepare("UPDATE clinica SET token = ?, token_expira = ? WHERE email = ?");
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

        if ($mail->send()) {
            echo json_encode(['success' => true, 'message' => 'Clínica cadastrada e e-mail de confirmação enviado.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Clínica cadastrada, mas houve um erro ao enviar o e-mail de confirmação.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar a clínica: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido']);
}

$conn->close();
ob_end_flush();
?>