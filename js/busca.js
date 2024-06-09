document.addEventListener("DOMContentLoaded", function () {
  const inputBusca = document.getElementById("input-busca");
  const listaClinicas = document.getElementById("listar-clinica");

  inputBusca.addEventListener("input", function () {
    buscarClinicas();
  });

  function buscarClinicas() {
    const termoBusca = inputBusca.value;

    fetch(`../php/listar_clinicas.php?termo=${termoBusca}`)
      .then((response) => response.json())
      .then((clinicas) => {
        atualizarListaClinicas(clinicas);
      })
      .catch((error) => console.error(error));
  }

  function atualizarListaClinicas(clinicas) {
    listaClinicas.innerHTML = "";

    clinicas.forEach((clinica) => {
      const card = criarClinicaCard(clinica);
      listaClinicas.appendChild(card);
    });
  }

  function criarClinicaCard(clinica) {
    const card = document.createElement("div");
    card.classList.add("clinica-card", "card-interior");

    const nome = document.createElement("h3");
    nome.textContent = clinica.name;
    nome.className = "card-nome-clinica";

    const telefone = document.createElement("p");
    telefone.textContent = "Telefone: " + clinica.phone;
    telefone.className = "card-telefone-clinica";

    const endereco = document.createElement("p");
    endereco.textContent = "Endereço: " + clinica.endereco;
    endereco.className = "card-endereco-clinica";

    const email = document.createElement("p");
    email.textContent = "E-mail: " + clinica.email;
    email.className = "card-email-clinica";

    const especializacao = document.createElement("p");
    especializacao.textContent = "Especialização: " + clinica.especializacao;
    especializacao.className = "card-especializacao-clinica";

    card.appendChild(nome);
    card.appendChild(telefone);
    card.appendChild(endereco);
    card.appendChild(email);
    card.appendChild(especializacao);

    return card;
  }

  buscarClinicas();
});
