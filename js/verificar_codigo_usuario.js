document.addEventListener("DOMContentLoaded", function () {
  const formVerification = document.getElementById("verification-form");

  formVerification.addEventListener("submit", function (event) {
    event.preventDefault();
    const code = document.getElementById("verification-code").value.trim();

    fetch("../chaves/public_key.pem")
      .then((response) => {
        if (!response.ok) {
          throw new Error("Erro ao carregar a chave pública.");
        }
        return response.text();
      })
      .then((publicKey) => {
        //console.log("Chave pública carregada: ", publicKey);
        const encrypt = new JSEncrypt();
        encrypt.setPublicKey(publicKey);

        const encryptedCode = encrypt.encrypt(code);
        //console.log("Código criptografado: ", encryptedCode);

        if (!encryptedCode) {
          alert("Erro ao criptografar o código.");
          return;
        }

        const formData = new FormData();
        formData.append("verification_code", encryptedCode);

        fetch("../php/verificar_codigo_usuario.php", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              alert(data.message);
              window.location.href = data.redirect;
            } else {
              alert(data.message);
            }
          })
          .catch((error) => {
            console.error("Erro na verificação: ", error);
            alert(
              "Erro ao processar a verificação. Por favor, tente novamente."
            );
          });
      })
      .catch((error) => {
        console.error("Erro ao carregar a chave pública: ", error);
        alert("Erro ao carregar a chave pública.");
      });
  });
});
