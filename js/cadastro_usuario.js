document.addEventListener("DOMContentLoaded", function () {
    const usuarioForm = document.getElementById("form-cadastro-usuario");

    function validarSenha(senha) {
        const senhaRegex =
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        return senhaRegex.test(senha);
    }

    function validarEmail(email) {
        const emailRegex = /^\S+@\S+\.\S+$/;
        return emailRegex.test(email);
    }

    function validarCpfCnpj(cpfCnpj) {
        const cleaned = cpfCnpj.replace(/\D/g, '');
        return cleaned.length === 11 || cleaned.length === 14;
    }

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
        } else if (!login.trim()) {
            alert("Por favor, preencha o campo de login.");
        } else if (!validarCpfCnpj(cpf)) {
            alert("Por favor, insira um CPF ou CNPJ válido.");
        } else if (!validarEmail(email)) {
            alert("Por favor, insira um e-mail válido.");
        } else if (!data_nasc.trim()) {
            alert("Por favor, preencha o campo de data de nascimento.");
        } else if (!validarSenha(password)) {
            alert(
                "A senha não atende aos requisitos mínimos. Mínimo de 8 caracteres dentre eles uma letra minúscula, uma letra maiúscula, um caractere especial e um número."
            );
        } else if (password !== check_password) {
            alert("As senhas não coincidem.");
        } else {
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
                        .then((response) => response.json())
                        .then((data) => {
                            alert(data.message);
                            if (data.success) {
                                usuarioForm.reset();
                            }
                        })
                        .catch((error) => {
                            console.error("Erro na requisição: ", error);
                            alert("Erro ao cadastrar o usuário: " + error.message);
                        });
                })
                .catch((error) => {
                    console.error("Erro ao carregar a chave pública: ", error);
                    alert("Erro ao carregar a chave pública.");
                });
        }
    });
});
