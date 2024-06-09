document.addEventListener("DOMContentLoaded", function () {
  const clinicaForm = document.getElementById("form-cadastro-clinica");

  function validarSenha(senha) {
    const senhaRegex =
      /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    return senhaRegex.test(senha);
  }

  function validarEmail(email) {
    const emailRegex = /^\S+@\S+\.\S+$/;
    return emailRegex.test(email);
  }

  function validarCRMV(crmv) {
    let match = crmv.match(/^([A-Z]{2})\/(\d+)$/);
    if (match && parseInt(match[2], 10) >= 1000) {
      return true;
    }
    return false;
  }

  document.getElementById("crmv").addEventListener("input", function (e) {
    let input = e.target.value.toUpperCase();

    input = input.replace(/[^A-Z0-9\/]/gi, "");

    input = input.replace(/([A-Z]{2})([A-Z0-9]{0,})/gi, (match, p1, p2) => {
      return p2 ? `${p1}/${p2}` : p1;
    });

    input = input.replace(/(\/\d{4}).*/gi, "$1");

    e.target.value = input;
  });

  clinicaForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const name = document.getElementById("name").value;
    const login = document.getElementById("login").value;
    const crmv = document.getElementById("crmv").value;
    const email = document.getElementById("email").value;
    const endereco = document.getElementById("endereco").value;
    const especializacao = document.getElementById("especializacao").value;
    const password = document.getElementById("password").value;
    const check_password = document.getElementById("check_password").value;
    const phone = document.getElementById("phone").value;

    if (!name.trim()) {
      alert("Por favor, preencha o campo de nome.");
    } else if (!login.trim()) {
      alert("Por favor, preencha o campo de login.");
    } else if (!validarCRMV(crmv)) {
      alert("Por favor, insira um CRMV válido.");
    } else if (!validarEmail(email)) {
      alert("Por favor, insira um e-mail válido.");
    } else if (!endereco.trim()) {
      alert("Por favor, preencha o campo endereço.");
    } else if (!especializacao) {
      alert("Por favor, selecione uma especialização.");
    } else if (!validarSenha(password)) {
      alert("A senha não atende aos requisitos mínimos. Mínimo de 8 caracteres dentre eles uma letra minúscula, uma letra maiúscula, um caractere especial e um número.");
    } else if (password !== check_password) {
      alert("As senhas não coincidem.");
    } else {
      const formData = new FormData(clinicaForm);

      fetch("../php/cadastro_clinica.php", {
        method: "POST",
        body: formData,
      })
        .then(response => response.json())
        .then(data => {
          alert(data.message);
          if (data.success) {
            clinicaForm.reset();
          }
        })
        .catch(error => {
          console.error("Erro na requisição: ", error);
          alert("Erro ao cadastrar a clínica: " + error.message);
        });
    }
  });
});
