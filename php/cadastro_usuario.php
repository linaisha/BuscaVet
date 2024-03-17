<?php
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $con = mysqli_connect("localhost", "root", "", "buscavet");

    if ($con) {
        $name = mysqli_real_escape_string($con, $_POST['name']);
        $login = mysqli_real_escape_string($con, $_POST['login']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $cpf = mysqli_real_escape_string($con, $_POST['cpf']);
        $password = mysqli_real_escape_string($con, $_POST['password']); // Aqui estamos usando o campo 'password'

        // A query SQL deve usar os nomes das colunas correspondentes à tabela do banco de dados
        // Supondo que a sua tabela se chame 'usuarios'
        $sql = "INSERT INTO usuario (name, login, email, cpf, password) VALUES ('$name', '$login', '$email', '$cpf', '$password')";

        if (mysqli_query($con, $sql)) {
            $mensagem = "Usuário cadastrado com sucesso!";
        } else {
            $mensagem = "Erro ao cadastrar o usuário: " . mysqli_error($con);
        }

        mysqli_close($con);
    } else {
        $mensagem = "Erro na conexão com o banco de dados: " . mysqli_connect_error();
    }
} else {
    $mensagem = "Método de requisição inválido.";
}

echo $mensagem;
?>