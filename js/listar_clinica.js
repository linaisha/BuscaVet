document.addEventListener("DOMContentLoaded", function () {
    const listaProdutosAdm = document.getElementById("lista-produtos-adm");

    function exibirProdutos() {
        listaProdutosAdm.innerHTML = '';

        fetch("../php/listar_produtos.php")
            .then(response => response.json())
            .then(data => {
                data.forEach(produto => {
                    const produtoCard = document.createElement("div");
                    produtoCard.className = "produto-card card-interior";

                    const nome = document.createElement("h3");
                    nome.textContent = produto.nome;
                    nome.className = "card-nome-produto";

                    const imagem = document.createElement("img");
                    imagem.src = "../img/" + produto.imagem;
                    imagem.className = "card-imagem-produto2";
                    imagem.alt = "Imagem do Produto";
                    const imagemCard = document.createElement("div");
                    imagemCard.className = "card-imagem-produto";
                    imagemCard.appendChild(imagem);

                    const preco = document.createElement("p");
                    preco.textContent = "Preço: R$ " + produto.valor;
                    preco.className = "card-valor-produto";

                    const descricao = document.createElement("p");
                    descricao.textContent = produto.descricao;
                    descricao.className = "card-descricao-produto";

                    const deletarButton = document.createElement("button");
                    deletarButton.textContent = "Deletar";
                    deletarButton.className = "botao-deletar";
                    deletarButton.addEventListener("click", () => deletarProduto(produto.id_produto));

                    produtoCard.appendChild(deletarButton);
                    produtoCard.appendChild(nome);
                    produtoCard.appendChild(imagemCard);
                    produtoCard.appendChild(preco);
                    produtoCard.appendChild(descricao);
                    produtoCard.appendChild(deletarButton);

                    listaProdutosAdm.appendChild(produtoCard);
                });
            })
            .catch(error => {
                console.error("Erro ao buscar produtos: " + error);
            });
    }

    exibirProdutos();
});
