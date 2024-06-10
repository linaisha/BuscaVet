document.addEventListener("DOMContentLoaded", function () {
  fetch("../php/get_perfil_clinica.php")
    .then((response) => {
      if (!response.ok) {
        throw new Error("Erro na resposta da rede");
      }
      return response.json();
    })
    .then((data) => {
      if (!data.success) {
        throw new Error(data.message);
      }
      document.getElementById("name").value = data.data.name;
      document.getElementById("especializacao").value =
        data.data.especializacao;
      document.getElementById("email").value = data.data.email;
      document.getElementById("phone").value = data.data.phone;
      document.getElementById("endereco").value = data.data.endereco;
    })
    .catch((error) => console.error("Erro ao carregar perfil:", error));

  const formPerfilClinica = document.getElementById("form-perfil-clinica");
  formPerfilClinica.addEventListener("submit", function (event) {
    event.preventDefault();

    const name = document.getElementById("name").value;
    const especializacao = document.getElementById("especializacao").value;
    const email = document.getElementById("email").value;
    const phone = document.getElementById("phone").value;
    const endereco = document.getElementById("endereco").value;

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

        const encryptedData = {
          name: encrypt.encrypt(name),
          especializacao: encrypt.encrypt(especializacao),
          email: encrypt.encrypt(email),
          phone: encrypt.encrypt(phone),
          endereco: encrypt.encrypt(endereco),
        };

        if (
          !encryptedData.name ||
          !encryptedData.especializacao ||
          !encryptedData.email ||
          !encryptedData.phone ||
          !encryptedData.endereco
        ) {
          alert("Erro ao criptografar os dados.");
          return;
        }

        const formData = new FormData();
        formData.append("name", encryptedData.name);
        formData.append("especializacao", encryptedData.especializacao);
        formData.append("email", encryptedData.email);
        formData.append("phone", encryptedData.phone);
        formData.append("endereco", encryptedData.endereco);

        fetch("../php/atualizar_perfil_clinica.php", {
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
          })
          .catch((error) => {
            console.error("Erro na requisição: ", error);
            alert("Erro ao atualizar perfil: " + error.message);
          });
      })
      .catch((error) => {
        console.error("Erro ao carregar a chave pública: ", error);
        alert("Erro ao carregar a chave pública.");
      });
  });
});
