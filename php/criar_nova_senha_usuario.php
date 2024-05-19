<?php
include '../../TotalmenteSeguro/decode_config.php';

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = new mysqli($servername, $username, $password, $database);
if (!$conn) {
    die("Falha na conexão: " . mysqli_connect_error());
}

function validarSenha($senha)
{
    $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($regex, $senha);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo json_encode(["mensagem" => "As senhas não coincidem."]);
        exit;
    }

    if (!validarSenha($new_password)) {
        echo json_encode(["mensagem" => "A senha não atende aos requisitos de segurança."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM usuario WHERE token = ? AND token_expira > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();
        $passwordHashed = hash('sha256', $password);

        $updateStmt = $conn->prepare("UPDATE usuario SET password = ?, token = '', token_expira = NULL WHERE id = ?");
        $updateStmt->bind_param("si", $passwordHashed, $usuario['id']);
        $updateStmt->execute();

        if ($updateStmt->affected_rows == 1) {
            echo json_encode(["mensagem" => "Senha atualizada com sucesso."]);
        } else {
            echo json_encode(["mensagem" => "Erro ao atualizar a senha."]);
        }
        $updateStmt->close();
    } else {
        echo json_encode(["mensagem" => "Token inválido ou expirado."]);
    }

    $stmt->close();
}

mysqli_close($conn);

?>