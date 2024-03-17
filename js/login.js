document.addEventListener("DOMContentLoaded", function () {
    const /*banco_de_dados*/ = document.querySelector("#login form");

    /*banco_de_dados*/.addEventListener("submit", function (event) {
        event.preventDefault();

        const email = document.getElementById("email").value;
        const password = document.getElementById("password").value;

        if (!email||!password) {
            alert("Por favor, preencha todos os campos.");
            return;
        }

        const /*banco_de_dados*/ = new /*banco_de_dados*/();
        /*banco_de_dados*/.append("email", email);
        /*banco_de_dados*/.append("password", password);

        fetch("../php/login.php", {
            method: "POST",
            body: /*banco_de_dados*/
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