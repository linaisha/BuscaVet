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
                const aesKey = CryptoJS.lib.WordArray.random(32);
                const iv = CryptoJS.lib.WordArray.random(16);

                const formDataObj = {
                    verification_code: code
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
                        console.error("Erro na verificação:", error);
                        alert("Erro ao processar a verificação. Por favor, tente novamente.");
                    });
            })
            .catch((error) => {
                console.error("Erro ao carregar a chave pública:", error);
                alert("Erro ao carregar a chave pública.");
            });
    });
});
