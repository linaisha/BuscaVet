document.addEventListener("DOMContentLoaded", function () {
    const buscavet = document.querySelector("#rec-senha form");
    buscavet.addEventListener("submit", function (event) {
        event.preventDefault();

        const email = document.getElementById("email").value;

        if (!email) {
            alert("Por favor, preencha todos os campos.");
            return;
        }

        const buscavet = new buscavet();
        buscavet.append("email", email);
        
        

        fetch("../php/autenticacao.php", {
            method: "POST",
            body: buscavet
        })
        .then(response => response.text())
        .then(data => {
            alert("Token enviado com sucesso!");
        })
        .catch(error => {
            alert("Erro ao enviar o token ao e-mail, confira seu e-mail e tente novamente: " + error);
        });
    });
});