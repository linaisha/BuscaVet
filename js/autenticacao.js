document.addEventListener("DOMContentLoaded", function () {
    const /*banco_de_dados*/ = document.querySelector("#rec-senha form");
    /*banco_de_dados*/.addEventListener("submit", function (event) {
        event.preventDefault();

        const email = document.getElementById("email").value;

        if (!email) {
            alert("Por favor, preencha todos os campos.");
            return;
        }

        const /*banco_de_dados*/ = new /*banco_de_dados*/();
        /*banco_de_dados*/.append("email", email);
        
        

        fetch("../php/autenticacao.php", {
            method: "POST",
            body: /*banco_de_dados*/
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