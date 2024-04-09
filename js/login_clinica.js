document.addEventListener("DOMContentLoaded", function () {
  const formLogin = document.querySelector("#login form");

  formLogin.addEventListener("submit", function (event) {
    event.preventDefault();

    const formData = new FormData(formLogin);

    fetch("../php/autenticacao_login_clinica.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (response.ok) {
          return response.json();
        } else {
          throw new Error("Algo deu errado no servidor");
        }
      })
      .then((data) => {
        if (data.success) {
          window.location.href = "verificar_codigo_clinica.html";
        } else {
          throw new Error(data.message);
        }
      })
      .catch((error) => {
        console.error("Erro na requisição: ", error);
        alert("Erro ao processar o login: " + error);
      });
  });
});
