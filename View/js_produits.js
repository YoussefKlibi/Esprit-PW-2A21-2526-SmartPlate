const cards = Array.from(document.querySelectorAll(".product-card"));
const searchInput = document.getElementById("searchInput");
const category = document.getElementById("filterCategory");
const sortPrice = document.getElementById("sortPrice");

const paginationBottom = document.getElementById("paginationBottom");

let filtered = [...cards];
let currentPage = 1;
const perPage = 8;

// =======================
// FILTRE + TRI
// =======================
function applyFilters() {
    const search = searchInput.value.toLowerCase();
    const cat = category.value;

    filtered = cards.filter(c =>
        c.dataset.name.toLowerCase().includes(search) &&
        (cat === "all" || c.dataset.category === cat)
    );

    applySort(); // 🔥 tri après filtre
}

// =======================
// TRI PRIX
// =======================
function applySort() {
    const type = sortPrice.value;

    if (type === "asc") {
        filtered.sort((a, b) =>
            parseFloat(a.querySelector(".price").innerText) -
            parseFloat(b.querySelector(".price").innerText)
        );
    }

    if (type === "desc") {
        filtered.sort((a, b) =>
            parseFloat(b.querySelector(".price").innerText) -
            parseFloat(a.querySelector(".price").innerText)
        );
    }

    showPage(1);
}

// =======================
// PAGINATION
// =======================
function showPage(page) {
    currentPage = page;

    cards.forEach(c => c.style.display = "none");

    const start = (page - 1) * perPage;
    const pageItems = filtered.slice(start, start + perPage);

    pageItems.forEach(c => c.style.display = "block");

    renderPagination();
}

// =======================
// PAGINATION UI
// =======================
function renderPagination() {
    const total = Math.ceil(filtered.length / perPage);

    paginationBottom.innerHTML = "";

    for (let i = 1; i <= total; i++) {
        let btn = document.createElement("button");
        btn.textContent = i;

        if (i === currentPage) btn.classList.add("active");

        btn.onclick = () => showPage(i);

        paginationBottom.appendChild(btn);
    }
}

// =======================
// EVENTS
// =======================
searchInput.addEventListener("input", applyFilters);
category.addEventListener("change", applyFilters);
sortPrice.addEventListener("change", applySort);

// =======================
// INIT
// =======================
applyFilters();

// =======================
// MODAL
// =======================
function showDetails(t, d) {
    document.getElementById("modalTitle").innerText = t;
    document.getElementById("modalDescription").innerText = d;
    document.getElementById("productModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("productModal").style.display = "none";
}
