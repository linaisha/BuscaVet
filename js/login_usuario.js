document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("form-login");

  form.addEventListener("submit", function (event) {
      event.preventDefault();

      const email = document.getElementById("email").value;
      const password = document.getElementById("password").value;

      fetch("../chaves/public_key.pem")
          .then((response) => {
              if (!response.ok) {
                  throw new Error("Erro ao carregar a chave pública.");
              }
              return response.text();
          })
          .then((publicKey) => {
              const encrypt = new JSEncrypt();
              encrypt.setPublicKey(publicKey);

              const encryptedEmail = encrypt.encrypt(email);
              const encryptedPassword = encrypt.encrypt(password);

              if (!encryptedEmail || !encryptedPassword) {
                  alert("Erro ao criptografar os dados.");
                  return;
              }

              const formData = new FormData();
              formData.append("email", encryptedEmail);
              formData.append("password", encryptedPassword);

              fetch("../php/autenticacao_login_usuario.php", {
                  method: "POST",
                  body: formData,
              })
                  .then((response) => {
                      if (!response.ok) {
                          throw new Error("Erro na resposta do servidor.");
                      }
                      return response.json();
                  })
                  .then((data) => {
                      alert(data.message);
                      if (data.success) {
                          window.location.href = "verificar_codigo_usuario.html";
                      }
                  })
                  .catch((error) => {
                      console.error("Erro na requisição: ", error);
                      alert("Erro ao processar o login: " + error.message);
                  });
          })
          .catch((error) => {
              console.error("Erro ao carregar a chave pública: ", error);
              alert("Erro ao carregar a chave pública.");
          });
  });
});
