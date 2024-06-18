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
                const aesKey = CryptoJS.lib.WordArray.random(32);
                const iv = CryptoJS.lib.WordArray.random(16);

                const formDataObj = {
                    email: email,
                    password: password
                };

                const formDataJson = JSON.stringify(formDataObj);

                const encryptedFormData = CryptoJS.AES.encrypt(formDataJson, aesKey, {
                    iv: iv,
                    mode: CryptoJS.mode.CBC,
                    padding: CryptoJS.pad.Pkcs7
                }).toString();

                const aesKeyBase64 = CryptoJS.enc.Base64.stringify(aesKey);
                const ivBase64 = CryptoJS.enc.Base64.stringify(iv);

                const encrypt = new JSEncrypt();
                encrypt.setPublicKey(publicKey);

                const encryptedAesKey = encrypt.encrypt(aesKeyBase64);
                const encryptedIv = encrypt.encrypt(ivBase64);

                if (!encryptedFormData || !encryptedAesKey || !encryptedIv) {
                    alert("Erro ao criptografar os dados.");
                    return;
                }

                const formData = new FormData();
                formData.append("formData", encryptedFormData);
                formData.append("aesKey", encryptedAesKey);
                formData.append("iv", encryptedIv);

                fetch("../php/autenticacao_login_clinica.php", {
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
                            window.location.href = "verificar_codigo_clinica.html";
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
