document.addEventListener("DOMContentLoaded", function () {
    const /*banco_de_dados*/ = document.querySelector("#redefinicao form");

    /*banco_de_dados*/.addEventListener("submit", function (event) {
        event.preventDefault();

        const new_password = document.getElementById("new_password").value;
        const check_password = document.getElementById("check_password").value;


        if (!new_password || !check_password) {
            alert("Por favor, preencha todos os campos.");
            return;
        /*FAZER UMA COMPARACAO PARA VER SE ELES SAO IGUAIS*/
        }

        const /*banco_de_dados*/ = new /*banco_de_dados*/();
        /*banco_de_dados*/.append("new_password", new_password);
        /*banco_de_dados*/.append("check_password", check_password);


        fetch("../php/redefinicao.php", {
            method: "POST",
            body: /*banco_de_dados*/
        })
        .then(response => response.text())
        .then(data => {
            alert("Nova senha registrada!");
        })
        .catch(error => {
            alert("Erro ao cadastrar a senha: " + error);
        });
    });
});