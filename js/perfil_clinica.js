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

    const formData = new FormData(formPerfilClinica);

    fetch("../php/atualizar_perfil_clinica.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Erro na resposta da rede");
        }
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          alert("Perfil atualizado com sucesso!");
        } else {
          alert("Erro ao atualizar perfil: " + data.message);
        }
      })
      .catch((error) => console.error("Erro ao atualizar perfil:", error));
  });
});
