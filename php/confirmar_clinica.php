<?php
// confirmar.php    AJUSTAR PARA CLINICA
$token = $_GET['token'];

$servername = "localhost";
$username = "root";
$password = "";
$database = "buscavet";

$conn = new mysqli($servername, $username, $password, $database);
$stmt = $con->prepare("UPDATE usuario SET confirmacao = 1 WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Cadastro confirmado com sucesso!";
} else {
    echo "Token inválido ou cadastro já confirmado.";
}
$stmt->close();
$con->close();

?>
