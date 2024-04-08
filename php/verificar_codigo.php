
<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$database = "buscavet";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Conexão falhou: ' . $conn->connect_error]));
}

// Verifique se o código foi fornecido
if (empty($_POST['codigo_verificacao'])) {
    echo json_encode(['success' => false, 'message' => 'Código de verificação é necessário.']);
    exit;
}

// Pegue o código de verificação do POST e o id do usuário da sessão
$verificationCode = $_POST['codigo_verificacao'];
$userId = $_SESSION['login_user_id'] ?? '';

// Prepare a declaração para verificar o código
$stmt = $conn->prepare("SELECT * FROM usuario WHERE id = ? AND codigo_verificacao = ? AND codigo_verificacao_expira > NOW()");
$stmt->bind_param('is', $userId, $verificationCode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Código correto, permitir acesso
    echo json_encode(['success' => true, 'message' => 'Código verificado com sucesso.']);

    // Aqui você pode atualizar o banco de dados para refletir que a verificação foi bem-sucedida, se necessário
    // Você também pode definir variáveis de sessão adicionais, se necessário
} else {
    // Código incorreto ou expirado
    echo json_encode(['success' => false, 'message' => 'Código inválido ou expirado.']);
}

$stmt->close();
$conn->close();

?>
