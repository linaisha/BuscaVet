document.addEventListener("DOMContentLoaded", function () {
    const clinicaForm = document.getElementById("form-cadastro-clinica");

    function validarCNPJ(cnpj) {
        cnpj = cnpj.replace(/[^\d]+/g,'');
    
        if (cnpj === '' || cnpj.length !== 14 || /^(\d)\1+$/.test(cnpj)) {
            return false;
        }
    
        let tamanho = cnpj.length - 2;
        let numeros = cnpj.substring(0, tamanho);
        let digitos = cnpj.substring(tamanho);
        let soma = 0;
        let pos = tamanho - 7;
    
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

    function validarCRMV(crmv) {
        let match = crmv.match(/^([A-Z]{2})\/(\d+)$/);
        if (match && parseInt(match[2], 10) >= 1000) {
            return true;
        }
        return false;
    }
    

    document.getElementById('cnpj').addEventListener('input', function (e) {
        e.target.value = e.target.value
            .replace(/\D/g, '') 
            .replace(/^(\d{2})(\d)/, '$1.$2')
            .replace(/^(\d{2}\.\d{3})(\d)/, '$1.$2') 
            .replace(/^(\d{2}\.\d{3}\.\d{3})(\d)/, '$1/$2')
            .replace(/^(\d{2}\.\d{3}\.\d{3}\/\d{4})(\d)/, '$1-$2');
    });
    
    document.getElementById('crmv').addEventListener('input', function (e) {
        let input = e.target.value.toUpperCase();
    
        input = input.replace(/[^A-Z0-9\/]/gi, '');
    
        input = input.replace(/([A-Z]{2})([A-Z0-9]{0,})/gi, (match, p1, p2) => {
            return p2 ? `${p1}/${p2}` : p1;
        });
    
        input = input.replace(/(\/\d{4}).*/gi, '$1');
    
        e.target.value = input;
    });
    
    

    clinicaForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const name = document.getElementById("name").value;
        const login = document.getElementById("login").value;
        const crmv = document.getElementById("crmv").value;
        const email = document.getElementById("email").value;
        const cnpj = document.getElementById("cnpj").value;
        const endereco = document.getElementById("endereco").value;
        const password = document.getElementById("password").value;
        const check_password = document.getElementById("check_password").value;

        if (!name.trim()) alert("Por favor, preencha o campo de nome.");
        else if (!login.trim()) alert("Por favor, preencha o campo de login.");
        else if (!validarCRMV(crmv)) alert("Por favor, insira um CRMV válido.");
        else if (!validarEmail(email)) alert("Por favor, insira um e-mail válido.");
        else if (!validarCNPJ(cnpj)) alert("Por favor, insira um CNPJ válido.");
        else if (!endereco.trim()) alert("Por favor, preencha o campo endereco.");
        else if (!validarSenha(password)) alert("A senha não atende aos requisitos mínimos.");
        else if (password !== check_password) alert("As senhas não coincidem.");
        else {
            const formData = new FormData(clinicaForm);

            fetch("../php/cadastro_clinica.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.mensagem);
                clinicaForm.reset();
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Erro ao cadastrar a clínica: " + error.message);
            });
        }
    });
});
