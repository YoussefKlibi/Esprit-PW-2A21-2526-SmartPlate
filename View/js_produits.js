//pagination
const productsPerPage = 10; // Nombre de produits par page
const productsGrid = document.getElementById("productsGrid");
const paginationTop = document.getElementById("paginationTop");
const paginationBottom = document.getElementById("paginationBottom");

let currentPage = 1;
const productCards = Array.from(productsGrid.getElementsByClassName("product-card"));
const totalPages = Math.ceil(productCards.length / productsPerPage);

function showPage(page) {
    currentPage = page;

    // Afficher seulement les produits de la page
    productCards.forEach((card, index) => {
        const start = (page - 1) * productsPerPage;
        const end = start + productsPerPage;
        card.style.display = index >= start && index < end ? "block" : "none";
    });

    // Générer la pagination
    renderPagination();
}

function renderPagination() {
    const containers = [paginationTop, paginationBottom];
    containers.forEach(container => {
        container.innerHTML = "";
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement("button");
            btn.textContent = i;
            btn.className = i === currentPage ? "active" : "";
            btn.addEventListener("click", () => showPage(i));
            container.appendChild(btn);
        }
    });
}

// Initialiser
showPage(1);
