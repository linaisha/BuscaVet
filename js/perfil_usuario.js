document.addEventListener("DOMContentLoaded", function () {
    fetch("../php/get_perfil_usuario.php")
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta da rede');
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message);
            }
            document.getElementById("name").value = data.data.name;
            document.getElementById("email").value = data.data.email;
            document.getElementById("phone").value = data.data.phone;
        })
        .catch(error => console.error("Erro ao carregar perfil:", error));

    const formPerfil = document.getElementById("form-perfil");
    formPerfil.addEventListener("submit", function (event) {
        event.preventDefault();

        const formData = new FormData(formPerfil);

        fetch("../php/atualizar_perfil_usuario.php", {
            method: "POST",
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta da rede');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert("Perfil atualizado com sucesso!");
            } else {
                alert("Erro ao atualizar perfil: " + data.message);
            }
        })
        .catch(error => console.error("Erro ao atualizar perfil:", error));
    });
});
