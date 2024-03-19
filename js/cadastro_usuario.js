document.addEventListener("DOMContentLoaded", function () {
    const usuarioForm = document.getElementById("form-cadastro");

    function validarCPF(cpf) {
        // Adicione aqui a lógica de validação de CPF
        // Este é um exemplo simples e pode não ser adequado para a validação completa de CPF
        cpf = cpf.replace(/[^\d]+/g,'');
        if(cpf === '') return false;
        // Elimina CPFs invalidos conhecidos
        if (cpf.length !== 11 || 
            cpf === "00000000000" || 
            cpf === "11111111111" || 
            cpf === "22222222222" || 
            cpf === "33333333333" || 
            cpf === "44444444444" || 
            cpf === "55555555555" || 
            cpf === "66666666666" || 
            cpf === "77777777777" || 
            cpf === "88888888888" || 
            cpf === "99999999999")
                return false;       
        // Valida 1o digito
        let add = 0;
        for (let i=0; i < 9; i ++)       
            add += parseInt(cpf.charAt(i)) * (10 - i);  
        let rev = 11 - (add % 11);
        if (rev === 10 || rev === 11)     
            rev = 0;    
        if (rev !== parseInt(cpf.charAt(9)))     
            return false;       
        // Valida 2o digito
        add = 0;
        for (let i = 0; i < 10; i ++)        
            add += parseInt(cpf.charAt(i)) * (11 - i);  
        rev = 11 - (add % 11);  
        if (rev === 10 || rev === 11) 
            rev = 0;    
        if (rev !== parseInt(cpf.charAt(10)))
            return false;       
        return true;
    }

    function validarSenha(senha) {
        const senhaRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        return senhaRegex.test(senha);
    }

    function validarEmail(email) {
        const emailRegex = /^\S+@\S+\.\S+$/;
        return emailRegex.test(email);
    }

    usuarioForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const name = document.getElementById("name").value;
        const login = document.getElementById("login").value;
        const email = document.getElementById("email").value;
        const cpf = document.getElementById("cpf").value;
        const password = document.getElementById("password").value;
        const check_password = document.getElementById("check_password").value;

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

        if (!validarSenha(password)) {
            alert("A senha não atende aos requisitos mínimos.");
            return;
        }

        if (password !== check_password) {
            alert("As senhas não coincidem.");
            return;
        }

        const formData = new FormData(usuarioForm);

        fetch("../php/cadastro_usuario.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.mensagem);
        })
        .catch(error => {
            alert("Erro ao cadastrar o usuário: " + error);
        });
    });
});
