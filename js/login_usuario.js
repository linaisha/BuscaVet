document.addEventListener("DOMContentLoaded", function () {
    const formLogin = document.querySelector("#login form");

    formLogin.addEventListener("submit", function (event) {
        event.preventDefault(); // Impede o envio padrão do formulário

        // Preparação dos dados do formulário para envio
        const formData = new FormData(formLogin);

        // Realiza a requisição fetch ao arquivo PHP de autenticação
        fetch("../php/autenticacao_login.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json()) // Espera uma resposta JSON do servidor
        .then(data => {
            if (data.success) {
                // Se o login for bem-sucedido
                alert(data.message); // Mostra a mensagem de sucesso
                window.location.href = "pagina_segura.php"; // Redireciona para a página segura
            } else {
                // Se houver falha no login, exibe a mensagem de erro
                alert(data.message);
            }
        })
        .catch(error => {
            // Captura erros na requisição ou no processamento da resposta
            console.error("Erro na requisição: ", error);
            alert("Erro ao processar o login. Por favor, tente novamente.");
        });
    });
});
