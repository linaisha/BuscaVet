document.addEventListener("DOMContentLoaded", function () {
    const formVerification = document.getElementById("verification-form");

    formVerification.addEventListener("submit", function (event) {
        event.preventDefault();

        const code = document.getElementById("verification-code").value;
        const formData = new FormData();
        formData.append("verification_code", code);

        fetch("../php/verificar_codigo.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = "pagina_segura.php"; // Redireciona para a página segura após verificação
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error("Erro na verificação: ", error);
            alert("Erro ao processar a verificação. Por favor, tente novamente.");
        });
    });
});
