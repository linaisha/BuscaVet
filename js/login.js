document.addEventListener("DOMContentLoaded", function () {
    const buscavet = document.querySelector("#login form");

    buscavet.addEventListener("submit", function (event) {
        event.preventDefault();

        const email = document.getElementById("email").value;
        const password = document.getElementById("password").value;

        if (!email||!password) {
            alert("Por favor, preencha todos os campos.");
            return;
        }

        const buscavet = new buscavet();
        buscavet.append("email", email);
        buscavet.append("password", password);

        fetch("../php/login.php", {
            method: "POST",
            body: buscavet
        })
        .then(response => response.text())
        .then(data => {
            alert("Login efetuado com sucesso!");
        })
        .catch(error => {
            alert("Erro ao logar na sua conta, confira seu e-mail/senha e tente novamente: " + error);
        });
    });
});