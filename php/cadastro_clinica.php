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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $con = mysqli_connect("localhost", "root", "", "buscavet");

    if ($con) {
        $name = $_POST['name'];
        $login = $_POST['login'];
        $email = $_POST['email'];
        $cnpj = $_POST['cnpj'];
        $password = $_POST['password'];

        if (!validarEmail($email)) {
            ob_end_clean();
            echo json_encode(["mensagem" => "E-mail inválido."]);
            exit;
        }

        if (!validarCnpj($cnpj)) {
            ob_end_clean();
            echo json_encode(["mensagem" => "CNPJ inválido."]);
            exit;
        }

        if (!validarSenha($password)) {
            ob_end_clean();
            echo json_encode(["mensagem" => "A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúsculo, uma letra minúscula, um número e um caractere especial."]);
            exit;
        }

        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($con, "INSERT INTO clinica (name, login, email, cnpj, data_nasc, password) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssssss', $name, $login, $email, $cnpj, $data_nasc, $passwordHashed);

        if (mysqli_stmt_execute($stmt)) {
            ob_end_clean();
            echo json_encode(["mensagem" => "Clínica cadastrada com sucesso!"]);
        } else {
            ob_end_clean();
            echo json_encode(["mensagem" => "Erro ao cadastrar a clínica: " . mysqli_stmt_error($stmt)]);
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
