document.addEventListener("DOMContentLoaded", () => {

  /* ================= MODALS ================= */
  const productModal = document.getElementById("modalProduit");
  const boutiqueModal = document.getElementById("modalBoutique");
  const imageModal = document.getElementById("imageModal");
  const modalImg = document.getElementById("modalImg");

  /* ================= PRODUIT ================= */
  const btnAdd = document.getElementById("addProductBtn");
  const closeModal = document.getElementById("closeModal");

  const form = document.getElementById("formProduit");
  const submitBtn = document.getElementById("submitBtn");
  const titre_form = document.getElementById("titre_form");

  const code = document.getElementById("code");
  const nom = document.getElementById("nom");
  const categorie = document.getElementById("categorie");
  const prix = document.getElementById("prix");
  const descriptionInput = document.getElementById("description");

  /* ================= OPEN PRODUCT ================= */
  btnAdd.onclick = () => {
    productModal.style.display = "flex";
    form.reset();
    submitBtn.innerText = "Ajouter";
    submitBtn.name = "action";
    submitBtn.value = "add";
    titre_form.innerText = "Ajouter un produit";
  };

  closeModal.onclick = () => {
    productModal.style.display = "none";
  };

  /* ================= EDIT PRODUIT ================= */
  document.addEventListener("click", (e) => {
    if (e.target.classList.contains("btn-edit")) {

      const row = e.target.closest("tr");

      code.value = row.cells[0].innerText;
      nom.value = row.cells[1].innerText;
      categorie.value = row.cells[2].innerText;
      prix.value = row.cells[3].innerText;
      descriptionInput.value = row.cells[4].innerText;

      productModal.style.display = "flex";

      submitBtn.innerText = "Modifier";
      submitBtn.name = "action";
      submitBtn.value = "update";
      titre_form.innerText = "Modifier un produit";
    }
  });

  /* ================= DELETE PRODUIT ================= */
  document.addEventListener("click", (e) => {
    if (e.target.classList.contains("btn-delete")) {
      const code = e.target.dataset.code;

      if (confirm("Voulez-vous supprimer ce produit ?")) {
        window.location.href =
          "../Controller/ProduitController.php?action=delete&code=" + code;
      }
    }
  });

  /* ================= IMAGE ================= */
  const imageError = document.createElement("p");
  imageError.style.color = "white";
  imageError.style.textAlign = "center";
  imageError.style.marginTop = "20%";
  imageError.style.display = "none";
  imageError.innerText = "Aucune image";

  imageModal.appendChild(imageError);

  document.addEventListener("click", (e) => {
    if (e.target.classList.contains("btn-image")) {

      const img = e.target.dataset.img;

      imageModal.style.display = "block";

      if (img && img.trim() !== "") {
        modalImg.src = "../" + img;
        modalImg.style.display = "block";
        imageError.style.display = "none";
      } else {
        modalImg.style.display = "none";
        imageError.style.display = "block";
      }
    }
  });

  document.getElementById("closeImage").onclick = () => {
    imageModal.style.display = "none";
    modalImg.src = "";
  };

  /* ================= BOUTIQUE ================= */
  const btnAddBoutique = document.getElementById("addboutiqueBtn");
  const closeModalBoutique = document.getElementById("closeModalBoutique");
  const formBoutique = document.getElementById("formBoutique");

  const CodeBoutique = document.getElementById("CodeBoutique");
  const NomB = document.getElementById("NomB");
  const EmailB = document.getElementById("EmailB");
  const TelB = document.getElementById("TelB");
  const AdresseB = document.getElementById("AdresseB");
  const VilleB = document.getElementById("VilleB");
  const CodePostalB = document.getElementById("CodePostalB");
  const PaysB = document.getElementById("PaysB");
  const Latitude = document.getElementById("Latitude");
  const Longitude = document.getElementById("Longitude");

  btnAddBoutique.onclick = () => {
    boutiqueModal.style.display = "flex";
    formBoutique.reset();
  };

  closeModalBoutique.onclick = () => {
    boutiqueModal.style.display = "none";
  };

  /* ================= EDIT BOUTIQUE ================= */
  document.addEventListener("click", (e) => {
    if (e.target.classList.contains("btn-edit-boutique")) {

      const row = e.target.closest("tr");

      CodeBoutique.value = row.cells[0].innerText;
      NomB.value = row.cells[1].innerText;
      EmailB.value = row.cells[2].innerText;
      TelB.value = row.cells[3].innerText;
      AdresseB.value = row.cells[4].innerText;
      VilleB.value = row.cells[5].innerText;
      CodePostalB.value = row.cells[6].innerText;
      PaysB.value = row.cells[7].innerText;
      Latitude.value = row.cells[8].innerText;
      Longitude.value = row.cells[9].innerText;

      boutiqueModal.style.display = "flex";

      const btn = document.getElementById("submitBtnBoutique");
      btn.name = "action";
      btn.value = "update";
      btn.innerText = "Modifier";
    }
  });

  /* ================= DELETE BOUTIQUE ================= */
  document.addEventListener("click", (e) => {
    if (e.target.classList.contains("btn-delete-boutique")) {

      const code = e.target.dataset.code;

      if (confirm("Supprimer cette boutique ?")) {
        window.location.href =
          "../Controller/BoutiqueController.php?action=delete&codeb=" + code;
      }
    }
  });

  /* ================= CLOSE OUTSIDE ================= */
  window.onclick = (e) => {
    if (e.target === imageModal) {
      imageModal.style.display = "none";
      modalImg.src = "";
    }

    if (e.target === productModal) {
      productModal.style.display = "none";
    }

    if (e.target === boutiqueModal) {
      boutiqueModal.style.display = "none";
    }
  };

  /* ================= STOCK ================= */
  const stockBtn = document.getElementById("StockBtn");
  const stockContent = document.getElementById("stockContent");

  stockBtn.addEventListener("click", () => {
    stockContent.classList.toggle("open");
  });

  const stockForm = document.getElementById("stockForm");

  stockForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const formData = new FormData(stockForm);

    fetch("../Controller/StockController.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.text())
    .then(() => {

      const code = stockForm.querySelector("select[name='Code']").value;
      const codeText = stockForm.querySelector("select[name='Code'] option:checked").text;

      const codeB = stockForm.querySelector("select[name='CodeB']").value;
      const codeBText = stockForm.querySelector("select[name='CodeB'] option:checked").text;

      const stockValue = stockForm.querySelector("input[name='Stock']").value;

      const tbody = document.querySelector("#stockContent table tbody");

      let found = false;

      tbody.querySelectorAll("tr").forEach(tr => {
        const produit = tr.cells[1].innerText;
        const boutique = tr.cells[2].innerText;

        if (produit === codeText && boutique === codeBText) {
          tr.cells[3].innerText =
            parseInt(tr.cells[3].innerText) + parseInt(stockValue);
          found = true;
        }
      });

      if (!found) {
        const image = stockForm.querySelector("select[name='Code'] option:checked").dataset.image;

        const imageCell = image
          ? `<img src="../${image}" style="width:30px;height:30px;object-fit:cover;border-radius:6px;">`
          : `<span style="color:gray;">Aucune image</span>`;

        const newRow = `
          <tr data-code="${code}" data-codeb="${codeB}">
            <td>${imageCell}</td>
            <td>${codeText}</td>
            <td>${codeBText}</td>
            <td>${stockValue}</td>
            <td class="delete-stock">🗑️</td>
          </tr>
        `;
        tbody.insertAdjacentHTML("beforeend", newRow);
      }

      stockForm.querySelector("input[name='Stock']").value = "";
      stockContent.classList.add("open");
    });
  });

  document.addEventListener("click", (e) => {
    if (e.target.classList.contains("delete-stock")) {

      const row = e.target.closest("tr");

      const code = row.dataset.code;
      const codeB = row.dataset.codeb;

      fetch(`../Controller/StockController.php?action=delete&Code=${code}&CodeB=${codeB}`)
        .then(() => {
          row.remove();
          stockContent.classList.add("open");
        });
    }
  });

  /* ================= VALIDATION ================= */

  function setError(input, message) {
    const error = input.nextElementSibling;
    if (error) {
      error.innerText = message;
      error.style.color = "red";
    }
  }

  function clearError(input) {
    const error = input.nextElementSibling;
    if (error) error.innerText = "";
  }

  code.addEventListener("input", () => {
    /^[a-zA-Z0-9]+$/.test(code.value) ? clearError(code) : setError(code, "Code invalide");
  });

  nom.addEventListener("input", () => {
    nom.value.length >= 3 ? clearError(nom) : setError(nom, "Nom trop court");
  });

  prix.addEventListener("input", () => {
    prix.value > 0 ? clearError(prix) : setError(prix, "Prix invalide");
  });

  EmailB.addEventListener("input", () => {
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(EmailB.value)
      ? clearError(EmailB)
      : setError(EmailB, "Email invalide");
  });

TelB.addEventListener("input", () => {

  // uniquement chiffres
  if (!/^\d*$/.test(TelB.value)) {
    setError(TelB, "Téléphone invalide");
    return;
  }

  // limite à 8 chiffres max
  if (TelB.value.length > 8) {
    TelB.value = TelB.value.slice(0, 8);
  }

  // validation finale longueur
  if (TelB.value.length === 8) {
    clearError(TelB);
  } else {
    setError(TelB, "Le numéro doit contenir 8 chiffres");
  }
});


  /* ================= BLOCAGE CLAVIER AVANCE ================= */

  function allowOnly(input, regex) {
    input.addEventListener("keypress", (e) => {
      if (!regex.test(e.key)) e.preventDefault();
    });
  }

  function cleanInput(input, regex) {
    input.addEventListener("input", () => {
      input.value = input.value.replace(regex, "");
    });
  }

  // chiffres uniquement
  allowOnly(TelB, /[0-9]/);
  allowOnly(CodePostalB, /[0-9]/);
  allowOnly(prix, /[0-9.]/);

  cleanInput(TelB, /[^0-9]/g);
  cleanInput(CodePostalB, /[^0-9]/g);
  cleanInput(prix, /[^0-9.]/g);

  // lettres uniquement
  allowOnly(nom, /[a-zA-Z\s]/);
  allowOnly(categorie, /[a-zA-Z\s]/);
  allowOnly(NomB, /[a-zA-Z\s]/);
  allowOnly(VilleB, /[a-zA-Z\s]/);
  allowOnly(PaysB, /[a-zA-Z\s]/);

  cleanInput(nom, /[^a-zA-Z\s]/g);
  cleanInput(categorie, /[^a-zA-Z\s]/g);
  cleanInput(NomB, /[^a-zA-Z\s]/g);
  cleanInput(VilleB, /[^a-zA-Z\s]/g);
  cleanInput(PaysB, /[^a-zA-Z\s]/g);

  /* ================= BLOQUER SUBMIT ================= */

  form.addEventListener("submit", (e) => {
    if (
      !/^[a-zA-Z0-9]+$/.test(code.value) ||
      nom.value.length < 3 ||
      prix.value <= 0
    ) {
      e.preventDefault();
      alert("Corrige les erreurs produit !");
    }
  });

  formBoutique.addEventListener("submit", (e) => {
    if (
      !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(EmailB.value) ||
      !/^\d+$/.test(TelB.value)
    ) {
      e.preventDefault();
      alert("Corrige les erreurs boutique !");
    }
  });

  

});
