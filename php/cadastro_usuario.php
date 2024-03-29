<?php

ob_start();

error_reporting(0);
ini_set('display_errors', 0);

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
            ob_end_clean(); // Clean any other output in the buffer
            echo json_encode(["mensagem" => "E-mail inválido."]);
            exit;
        }

        if (!validarCpfCnpj($cpf)) {
            ob_end_clean(); // Clean any other output in the buffer
            echo json_encode(["mensagem" => "CPF/CNPJ inválido."]);
            exit;
        }

        
        error_log("Validating date of birth: " . $data_nasc);

        if (!validarDataNasc($data_nasc)) {
            ob_end_clean(); // Clean any other output in the buffer
            echo json_encode(["mensagem" => "Data de nascimento inválida."]);
            exit;
        }

        if (!validarSenha($password)) {
            ob_end_clean(); // Clean any other output in the buffer
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
            ob_end_clean(); // Clean any other output in the buffer
            echo json_encode(["mensagem" => "Usuário cadastrado com sucesso!"]);
        } else {
            ob_end_clean(); // Clean any other output in the buffer
            echo json_encode(["mensagem" => "Erro ao cadastrar o usuário: " . mysqli_stmt_error($stmt)]);
        }

        mysqli_stmt_close($stmt);
        mysqli_close($con);
    } else {
        ob_end_clean(); // Clean any other output in the buffer
        echo json_encode(["mensagem" => "Erro na conexão com o banco de dados: " . mysqli_connect_error()]);
    }
} else {
    ob_end_clean(); // Clean any other output in the buffer
    echo json_encode(["mensagem" => "Método de requisição inválido."]);
}

ob_end_flush(); // Flush the buffer here if the end is reached without exiting

?>
