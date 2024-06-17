document.getElementById('form-atualizar').addEventListener('submit', function (event) {
    event.preventDefault();
  
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
  
    fetch('../chaves/public_key.pem')
      .then((response) => response.text())
      .then((publicKey) => {
        const encrypt = new JSEncrypt();
        encrypt.setPublicKey(publicKey);
  
        const encryptedName = encrypt.encrypt(name);
        const encryptedEmail = encrypt.encrypt(email);
        const encryptedPhone = encrypt.encrypt(phone);
  
        const formData = new FormData();
        formData.append('encrypted_name', encryptedName);
        formData.append('encrypted_email', encryptedEmail);
        formData.append('encrypted_phone', encryptedPhone);
  
        fetch('../php/atualizar_perfil_usuario.php', {
          method: 'POST',
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              alert('Perfil atualizado com sucesso');
            } else {
              alert(data.message);
            }
          })
          .catch((error) => {
            console.error('Erro:', error);
          });
      });
  });
  