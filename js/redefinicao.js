document.addEventListener("DOMContentLoaded", function () {
    const buscavet = document.querySelector("#redefinicao form");

    buscavet.addEventListener("submit", function (event) {
        event.preventDefault();

        const new_password = document.getElementById("new_password").value;
        const check_password = document.getElementById("check_password").value;


        if (!new_password || !check_password) {
            alert("Por favor, preencha todos os campos.");
            return;
        /*FAZER UMA COMPARACAO PARA VER SE ELES SAO IGUAIS*/
        }

        const buscavet = new buscavet();
        buscavet.append("new_password", new_password);
        buscavet.append("check_password", check_password);


        fetch("../php/redefinicao.php", {
            method: "POST",
            body: buscavet
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