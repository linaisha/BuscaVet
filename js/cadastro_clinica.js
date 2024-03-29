document.addEventListener("DOMContentLoaded", function () {
    const clinicaForm = document.getElementById("form-cadastro-clinica");


    // link para conferir a validação de cnpj link: https://blog.dbins.com.br/como-funciona-a-logica-da-validacao-do-cnpj
    function validarCNPJ(cnpj) {
        cnpj = cnpj.replace(/[^\d]+/g,''); // Remove any non-digits
    
        // Reject invalid entries
        if (cnpj === '' || cnpj.length !== 14 || /^(\d)\1+$/.test(cnpj)) {
            return false;
        }
    
        let tamanho = cnpj.length - 2; // Without check digits
        let numeros = cnpj.substring(0, tamanho);
        let digitos = cnpj.substring(tamanho);
        let soma = 0;
        let pos = tamanho - 7;
    
        // Calculate check digits using modulo 11
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) {
                pos = 9;
            }
        }
    
        let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
        if (resultado != digitos.charAt(0)) {
            return false;
        }
    
        tamanho = tamanho + 1;
        numeros = cnpj.substring(0, tamanho);
        soma = 0;
        pos = tamanho - 7;
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) {
                pos = 9;
            }
        }
    
        resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
        if (resultado != digitos.charAt(1)) {
            return false;
        }
    
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

    clinicaForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const name = document.getElementById("name").value;
        const login = document.getElementById("login").value;
        const email = document.getElementById("email").value;
        const cnpj = document.getElementById("cnpj").value; // Changed from cpf to cnpj
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

        if (!validarCNPJ(cnpj)) { // Changed from validarCPF to validarCNPJ
            alert("Por favor, insira um CNPJ válido.");
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

        const formData = new FormData(clinicaForm); // Changed from usuarioForm to clinicaForm

        fetch("../php/cadastro_clinica.php", { // Point to the correct PHP file for clinic registration
            method: "POST",
            body: formData
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            } else {
                return response.text().then(text => { throw new Error(text) });
            }
        })
        .then(data => {
            alert(data.mensagem);
        })
        .catch(error => {
            alert("Erro ao cadastrar a clínica: " + error.message); // Changed user to clinic
        });
    });
});
