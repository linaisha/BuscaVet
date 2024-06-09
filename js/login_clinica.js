document.addEventListener("DOMContentLoaded", function () {
  const formLogin = document.querySelector("#login form");

  // Carregar o certificado
  fetch('../chaves/certificate.pem')
    .then(response => response.text())
    .then(certificate => {
      const publicKey = certificate;

      formLogin.addEventListener("submit", function (event) {
        event.preventDefault();

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        // Criptografar a senha com a chave pública
        const encrypt = new JSEncrypt();
        encrypt.setPublicKey(publicKey);
        const encryptedPassword = encrypt.encrypt(password);

        const formData = new FormData();
        formData.append('email', email);
        formData.append('password', encryptedPassword);

        fetch("../php/autenticacao_login_clinica.php", {
          method: "POST",
          body: formData,
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              window.location.href = "verificar_codigo_clinica.html";
            } else {
              alert(data.message);
            }
          })
          .catch(error => {
            console.error("Erro na requisição: ", error);
            alert("Erro ao processar o login: " + error.message);
          });
      });
    })
    .catch(error => {
      console.error("Erro ao carregar o certificado: ", error);
    });
});
