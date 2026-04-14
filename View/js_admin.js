
// AJOUTER UN PRODUIT
document.addEventListener("DOMContentLoaded", () => {

  const btnAdd = document.getElementById("addProductBtn");
  const modal = document.getElementById("modalProduit");
  const closeModal = document.getElementById("closeModal");

  const form = document.getElementById("formProduit");
  const submitBtn = document.getElementById("submitBtn");
  const titre_form = document.getElementById("titre_form");

  btnAdd.onclick = () => {
    modal.style.display = "flex";
    form.reset();
    submitBtn.innerText = "Ajouter";
    submitBtn.value = "add";
    titre_form.innerText = "Ajouter un produit";
    form.dataset.mode = "add";
  };

  closeModal.onclick = () => {
    modal.style.display = "none";
  };

});



/* -- Controle de saisie AJOUT PRODUIT -- */
const form = document.getElementById("formProduit");

const code = document.getElementById("code");
const nom = document.getElementById("nom");
const categorie = document.getElementById("categorie");
const stock = document.getElementById("stock");
const prix = document.getElementById("prix");
const description = document.getElementById("description");

nom.addEventListener("keydown", blockNumbersInput);
categorie.addEventListener("keydown", blockNumbersInput);


function blockNumbersInput(e) {
  // autorise : lettres, espace, backspace, delete, flèches
  const allowedKeys = [
    "Backspace",
    "Delete",
    "ArrowLeft",
    "ArrowRight",
    "Tab"
  ];

  // si c'est un chiffre → blocage
  if (e.key >= "0" && e.key <= "9") {
    e.preventDefault();
  }

  // autoriser touches spéciales
  if (allowedKeys.includes(e.key)) {
    return;
  }

  // bloquer autres symboles si besoin (option stricte)
  if (!/^[a-zA-ZÀ-ÿ\s]$/.test(e.key)) {
    e.preventDefault();
  }
}

// helpers
function setError(input, message) {
  input.classList.add("error-input");
  input.classList.remove("success");
  document.getElementById(input.id + "Error").innerText = message;
}

function setSuccess(input) {
  input.classList.add("success");
  input.classList.remove("error-input");
  document.getElementById(input.id + "Error").innerText = "";
}

// validation rules
function validateCode() {
  if (code.value.trim().length < 3) {
    setError(code, "Code doit contenir au moins 3 caractères");
    return false;
  }
  setSuccess(code);
  return true;
}

function validateNom() {
  const value = nom.value.trim();

  /*if (value.length < 2) {
    setError(nom, "Nom invalide");
    return false;
  }

  if (!textOnlyRegex.test(value)) {
    setError(nom, "Le nom ne doit pas contenir de chiffres");
    return false;
  }*/

  setSuccess(nom);
  return true;
}

function validateCategorie() {
  const value = categorie.value.trim();

  if (value !== "" && value.length < 3) {
    setError(categorie, "Veuillez choisir une catégorie existante");
    return false;
  }

  /*if (value !== "" && !textOnlyRegex.test(value)) {
    setError(categorie, "La catégorie ne doit pas contenir de chiffres");
    return false;
  }*/

  setSuccess(categorie);
  return true;
}

function validateStock() {
  if (stock.value !== "" && stock.value < 0) {
    setError(stock, "Stock invalide");
    return false;
  }
  setSuccess(stock);
  return true;
}

function validatePrix() {
  if (prix.value <= 0) {
    setError(prix, "Veuillez saisir le prix correct ");
    return false;
  }
  setSuccess(prix);
  return true;
}

// LIVE validation
code.oninput = validateCode;
nom.oninput = validateNom;
categorie.oninput = validateCategorie;
stock.oninput = validateStock;
prix.oninput = validatePrix;

// submit
form.addEventListener("submit", function (e) {
  let v1 = validateCode();
  let v2 = validateNom();
  let v3 = validateCategorie();
  let v4 = validateStock();
  let v5 = validatePrix();

  if (!(v1 && v2 && v3 && v4 && v5)) {
    e.preventDefault(); 
    return;
  }

  //alert("Produit ajouté avec succès ");
});



/*SELECTIONNER UNE LIGNE DU TABLEAU */
const table = document.getElementById("productsTable");
const selectedInfo = document.getElementById("selectedInfo");
let selectedProductCode = null;

table.addEventListener("click", function (e) {
    const row = e.target.closest("tr.product-row");

    if (!row) return;

    // enlever sélection précédente
    document.querySelectorAll(".product-row.selected").forEach(r => {
        r.classList.remove("selected");
    });

    // ajouter sélection
    row.classList.add("selected");

    // récupérer le code produit (première colonne par exemple)
    selectedProductCode = row.cells[0].innerText;

    
    //selectedInfo.innerText = "Produit sélectionné : " + selectedProductCode;
});



document.addEventListener("click", function (e) {
    if (e.target.classList.contains("btn-delete")) {

        const code = e.target.dataset.code;

        if (confirm("Voulez-vous supprimer ce produit ?")) {
            window.location.href =
                "../Controller/ProduitController.php?action=delete&code=" + code;
        }
    }
});


document.addEventListener("click", function (e) {
    if (e.target.classList.contains("btn-edit")) {

        const row = e.target.closest("tr");

        const code = row.cells[0].innerText;
        const nom = row.cells[1].innerText;
        const categorie = row.cells[2].innerText;
        const stock = row.cells[3].innerText;
        const prix = row.cells[4].innerText;
        const description = row.cells[5].innerText;

        // ouvrir popup
        const modal = document.getElementById("modalProduit");
        modal.style.display = "flex";

        // remplir form
        document.getElementById("code").value = code;
        document.getElementById("nom").value = nom;
        document.getElementById("categorie").value = categorie;
        document.getElementById("stock").value = stock;
        document.getElementById("prix").value = prix;
        document.getElementById("description").value = description;

        //  CHANGE MODE TO UPDATE
        const form = document.getElementById("formProduit");
        const submitBtn = document.getElementById("submitBtn");
        const titre_form = document.getElementById("titre_form");

        submitBtn.innerText = "Modifier";   // ✔️ texte bouton
        submitBtn.value = "update";         // ✔️ action PHP
        titre_form.innerText="Modifier un produit";

        form.dataset.mode = "update";
    }
});


// RECHERCHER UN PRODUIT
const formRecherche = document.getElementById("searchForm");
const searchInput = document.getElementById("searchInput");

formRecherche.addEventListener("submit", function (e) {

  const value = searchInput.value.trim();

  if (!value) {
    e.preventDefault();
    alert("Veuillez saisir le code du produit à rechercher");
  }

});
