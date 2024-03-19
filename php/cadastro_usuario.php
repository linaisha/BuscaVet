<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

$mensagem = "";

function validarSenha($senha){
    $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($regex, $senha);
}

function validarEmail($email){
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validarCpfCnpj($cpfCnpj){
    $cleaned = preg_replace('/\D/','',$cpfCnpj);
    return strlen($cleaned) === 11 || strlen($cleaned) === 14;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $con = mysqli_connect("localhost", "root", "", "buscavet");

    if ($con) {
        // Real_escape_string já é chamado abaixo antes da inserção no SQL.
        $name = $_POST['name'];
        $login = $_POST['login'];
        $email = $_POST['email'];
        $cpf = $_POST['cpf'];
        $password = $_POST['password'];

        // Sua lógica de validação...

        // Se alguma validação falhar, configure a mensagem e responda com JSON.
        if(!validarEmail($email)){
            echo "E-mail inválido.";
            exit;
        }

        // validação do CPF/CNPJ
        if (!validarCpfCnpj($cpf)){
            echo "CPF/CNPJ inválido.";
            exit;
        }

        // validação de senha
        if (!validarSenha($password)){
            echo "A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúsculo, uma letra minúscula, um número e um caractere especial.";
            exit;
        }

        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($con, "INSERT INTO usuario (name, login, email, cpf, password) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssss', 
            mysqli_real_escape_string($con, $name), 
            mysqli_real_escape_string($con, $login), 
            mysqli_real_escape_string($con, $email), 
            mysqli_real_escape_string($con, $cpf), 
            $passwordHashed);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["mensagem" => "Usuário cadastrado com sucesso!"]);
        } else {
            echo json_encode(["mensagem" => "Erro ao cadastrar o usuário: " . mysqli_stmt_error($stmt)]);
        }

        mysqli_stmt_close($stmt);
        mysqli_close($con);
    } else {
        echo json_encode(["mensagem" => "Erro na conexão com o banco de dados: " . mysqli_connect_error()]);
    }
} else {
    echo json_encode(["mensagem" => "Método de requisição inválido."]);
}
?>
