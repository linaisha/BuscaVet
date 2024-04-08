document.addEventListener("DOMContentLoaded", function () {
    const formLogin = document.querySelector("#login form");

    formLogin.addEventListener("submit", function (event) {
        event.preventDefault();

        const formData = new FormData(formLogin);

        fetch("../php/autenticacao_login.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = "pagina_segura.php";
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error("Erro na requisição: ", error);
            alert("Erro ao processar o login. Por favor, tente novamente.");
        });
    });
});
