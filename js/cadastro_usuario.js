document.addEventListener("DOMContentLoaded", function () {
    const usuarioForm = document.querySelector("#form-cadastro");

    usuarioForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const name = document.getElementById("name").value;
        const login = document.getElementById("login").value;
        const email = document.getElementById("email").value;
        const cpf = document.getElementById("cpf").value;
        const password = document.getElementById("password").value;
        const check_password = document.getElementById("check_password").value;

        if (!name || !login || !email || !cpf || !password || !check_password) {
            alert("Por favor, preencha todos os campos.");
            return;
        }

        const formData = new FormData();
        formData.append("name", name);
        formData.append("login", login);
        formData.append("email", email);
        formData.append("cpf", cpf);
        formData.append("password", password);
        formData.append("check_password", check_password);

        fetch("../php/cadastro_usuario.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert("Cadastrado com sucesso!");
        })
        .catch(error => {
            alert("Erro ao cadastrar o usuario: " + error);
        });
    });
});