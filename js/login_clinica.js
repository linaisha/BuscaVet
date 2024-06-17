document.getElementById('form-login').addEventListener('submit', function (event) {
    event.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    fetch('../chaves/public_key.pem')
        .then((response) => response.text())
        .then((publicKey) => {
            const encrypt = new JSEncrypt();
            encrypt.setPublicKey(publicKey);

            const encryptedEmail = encrypt.encrypt(email);
            const encryptedPassword = encrypt.encrypt(password);

            const formData = new FormData();
            formData.append('encrypted_email', encryptedEmail);
            formData.append('encrypted_password', encryptedPassword);

            fetch('../php/autenticacao_login_clinica.php', {
                method: 'POST',
                body: formData,
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        window.location.href = 'verificar_codigo_clinica.html';
                    } else {
                        alert(data.message);
                    }
                })
                .catch((error) => {
                    console.error('Erro:', error);
                });
        });
});
