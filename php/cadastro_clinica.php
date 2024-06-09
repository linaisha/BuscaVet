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

function validarSenha($senha) {
    $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($regex, $senha);
}

function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validarCRMV($crmv) {
    $regex = '/^[A-Z]{2}\/\d+$/';
    return preg_match($regex, $crmv);
}

$conn = new mysqli($credentials['servername'], $credentials['username'], $credentials['password'], $credentials['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Conexão falhou: ' . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $privateKeyPath = '/caminho/para/o/seu/private_key.pem';
    $privateKey = file_get_contents($privateKeyPath);

    $encryptedData = file_get_contents('php://input');
    openssl_private_decrypt(base64_decode($encryptedData), $decryptedData, openssl_pkey_get_private($privateKey));

    $data = json_decode($decryptedData, true);

    $name = $conn->real_escape_string($data['name']);
    $login = $conn->real_escape_string($data['login']);
    $email = $conn->real_escape_string($data['email']);
    $especializacao = $conn->real_escape_string($data['especializacao']);
    $endereco = $conn->real_escape_string($data['endereco']);
    $crmv = $conn->real_escape_string($data['crmv']);
    $password = $data['password'];
    $phone = $conn->real_escape_string($data['phone']);

    if (!validarEmail($email) || !validarSenha($password) || !validarCRMV($crmv)) {
        echo json_encode(['success' => false, 'message' => 'Validação falhou']);
        exit;
    }

    $passwordHashed = hash('sha256', $password);

    $stmt = $conn->prepare("INSERT INTO clinica (name, login, email, especializacao, endereco, crmv, password, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $name, $login, $email, $especializacao, $endereco, $crmv, $passwordHashed, $phone);

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
