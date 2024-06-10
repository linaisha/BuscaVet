document.addEventListener("DOMContentLoaded", function () {
  const usuarioForm = document.getElementById("form-cadastro");

  function validarCPF(cpf) {
    cpf = cpf.replace(/[^\d]+/g, "");
    if (cpf === "") return false;
    if (
      cpf.length !== 11 ||
      cpf === "00000000000" ||
      cpf === "11111111111" ||
      cpf === "22222222222" ||
      cpf === "33333333333" ||
      cpf === "44444444444" ||
      cpf === "55555555555" ||
      cpf === "66666666666" ||
      cpf === "77777777777" ||
      cpf === "88888888888" ||
      cpf === "99999999999"
    )
      return false;
    let add = 0;
    for (let i = 0; i < 9; i++) add += parseInt(cpf.charAt(i)) * (10 - i);
    let rev = 11 - (add % 11);
    if (rev === 10 || rev === 11) rev = 0;
    if (rev !== parseInt(cpf.charAt(9))) return false;
    add = 0;
    for (let i = 0; i < 10; i++) add += parseInt(cpf.charAt(i)) * (11 - i);
    rev = 11 - (add % 11);
    if (rev === 10 || rev === 11) rev = 0;
    if (rev !== parseInt(cpf.charAt(10))) return false;
    return true;
  }

  function validarSenha(senha) {
    const senhaRegex =
      /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    return senhaRegex.test(senha);
  }

  function validarEmail(email) {
    const emailRegex = /^\S+@\S+\.\S+$/;
    return emailRegex.test(email);
  }

  document.getElementById("cpf").addEventListener("input", function (e) {
    var valor = e.target.value.replace(/\D/g, "");
    valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
    valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
    valor = valor.replace(/(\d{3})(\d{1,2})/, "$1-$2");
    valor = valor.substring(0, 14);
    e.target.value = valor;
  });

  usuarioForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const name = document.getElementById("name").value;
    const login = document.getElementById("login").value;
    const email = document.getElementById("email").value;
    const cpf = document.getElementById("cpf").value;
    const data_nasc = document.getElementById("data_nasc").value;
    const password = document.getElementById("password").value;
    const check_password = document.getElementById("check_password").value;
    const phone = document.getElementById("phone").value;

    if (!name.trim()) {
      alert("Por favor, preencha o campo de nome.");
      return;
    }

    if (!login.trim()) {
      alert("Por favor, preencha o campo de login.");
      return;
    }

    if (!validarEmail(email)) {
      alert("Por favor, insira um e-mail válido.");
      return;
    }

    if (!validarCPF(cpf)) {
      alert("Por favor, insira um CPF válido.");
      return;
    }

    if (!data_nasc.trim()) {
      alert("Por favor, preencha o campo de data de nascimento.");
      return;
    }

    if (!validarSenha(password)) {
      alert(
        "A senha não atende aos requisitos mínimos. Mínimo de 8 caracteres dentre eles uma letra minúscula, uma letra maiúscula, um caractere especial e um número."
      );
      return;
    }

    if (password !== check_password) {
      alert("As senhas não coincidem.");
      return;
    }

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

        const encryptedName = encrypt.encrypt(name);
        const encryptedLogin = encrypt.encrypt(login);
        const encryptedEmail = encrypt.encrypt(email);
        const encryptedCpf = encrypt.encrypt(cpf);
        const encryptedDataNasc = encrypt.encrypt(data_nasc);
        const encryptedPassword = encrypt.encrypt(password);
        const encryptedPhone = encrypt.encrypt(phone);

        if (
          !encryptedName ||
          !encryptedLogin ||
          !encryptedEmail ||
          !encryptedCpf ||
          !encryptedDataNasc ||
          !encryptedPassword ||
          !encryptedPhone
        ) {
          alert("Erro ao criptografar os dados.");
          return;
        }

        const formData = new FormData();
        formData.append("name", encryptedName);
        formData.append("login", encryptedLogin);
        formData.append("email", encryptedEmail);
        formData.append("cpf", encryptedCpf);
        formData.append("data_nasc", encryptedDataNasc);
        formData.append("password", encryptedPassword);
        formData.append("phone", encryptedPhone);

        fetch("../php/cadastro_usuario.php", {
          method: "POST",
          body: formData,
        })
          .then((response) => {
            if (response.ok) {
              return response.json();
            } else {
              return response.text().then((text) => {
                throw new Error(text);
              });
            }
          })
          .then((data) => {
            alert(data.mensagem);
          })
          .catch((error) => {
            alert("Erro ao cadastrar o usuário: " + error.message);
          });
      })
      .catch((error) => {
        console.error("Erro ao carregar a chave pública: ", error);
        alert("Erro ao carregar a chave pública.");
      });
  });
});
