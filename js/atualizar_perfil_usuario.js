document.addEventListener("DOMContentLoaded", function () {
    fetch("../php/get_perfil_usuario.php")
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
            document.getElementById("email").value = data.data.email;
            document.getElementById("phone").value = data.data.phone;
        })
        .catch((error) => console.error("Erro ao carregar perfil:", error));
  
    const formPerfil = document.getElementById("form-perfil");
    formPerfil.addEventListener("submit", function (event) {
        event.preventDefault();
  
        const name = document.getElementById("name").value;
        const email = document.getElementById("email").value;
        const phone = document.getElementById("phone").value;
  
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
                const encryptedEmail = encrypt.encrypt(email);
                const encryptedPhone = encrypt.encrypt(phone);
  
                if (!encryptedName || !encryptedEmail || !encryptedPhone) {
                    alert("Erro ao criptografar os dados.");
                    return;
                }
  
                const formData = new FormData();
                formData.append("name", encryptedName);
                formData.append("email", encryptedEmail);
                formData.append("phone", encryptedPhone);
  
                fetch("../php/atualizar_perfil_usuario.php", {
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
            })
            .catch((error) => {
                console.error("Erro ao carregar a chave pública: ", error);
                alert("Erro ao carregar a chave pública.");
            });
    });
  });
  