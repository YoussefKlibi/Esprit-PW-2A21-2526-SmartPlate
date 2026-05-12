const productsGrid = document.getElementById("productsGrid");
const cards = Array.from(document.querySelectorAll(".product-card"));
const searchInput = document.getElementById("searchInput");
const category = document.getElementById("filterCategory");
const paginationBottom = document.getElementById("paginationBottom");

let filtered = [...cards];
let currentPage = 1;
const perPage = 12;

function applyFilters() {
    const search = searchInput ? searchInput.value.toLowerCase() : "";
    const cat = category.value;

    filtered = cards.filter(c =>
        c.dataset.name.toLowerCase().includes(search) &&
        (cat === "all" || c.dataset.category === cat)
    );

    showPage(1);
}

function showPage(page) {
    currentPage = page;

    if (!productsGrid || cards.length === 0) {
        renderPagination();
        return;
    }

    const start = (page - 1) * perPage;
    const pageItems = filtered.slice(start, start + perPage);

    cards.forEach(c => c.style.display = "none");

    productsGrid.innerHTML = "";

    pageItems.forEach(c => {
        c.style.display = "block";
        productsGrid.appendChild(c);
    });

    renderPagination();

    // ✅ Remonter en haut de la page
    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
}

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

if (searchInput) {
    searchInput.addEventListener("input", applyFilters);
}
category.addEventListener("change", applyFilters);

applyFilters();

function showDetails(code, t, d, imagePath, price, stockData = [], hasPanierOption = true) {
    document.getElementById("modalTitle").innerText = t;
    document.getElementById("modalDescription").innerText = "Description : " + d;
    document.getElementById("modalImage").src = imagePath;
    document.getElementById("modalImage").alt = t;
    document.getElementById("modalPrice").innerText = "Prix : " + price + " DT";

    const stockList = document.getElementById("modalStockList");
    stockList.innerHTML = "";

    if (stockData && stockData.length > 0) {
        stockData.forEach(item => {
            let li = document.createElement("li");
            li.style.marginBottom = "5px";

            let color = item.quantite > 0 ? "var(--admin-green, #20c997)" : "#e74c3c";
            let status = item.quantite > 0 ? `En stock` : "Rupture de stock";

            li.innerHTML = `<strong>${item.boutique}</strong> : <span style="color: ${color}; font-weight: 600;">${status}</span>`;
            stockList.appendChild(li);
        });
    } else {
        let li = document.createElement("li");
        li.innerText = "Information de stock indisponible.";
        li.style.color = "#888";
        stockList.appendChild(li);
    }

    const addBtn = document.getElementById("modalAddBtn");
    if (addBtn) {
        addBtn.style.display = hasPanierOption ? "block" : "none";
        addBtn.onclick = () => addToCart(code);
    }

    document.getElementById("productModal").style.display = "flex";
}

function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icon = type === 'success' ? '✓' : '✕';
    
    toast.innerHTML = `
        <div class="toast-icon">${icon}</div>
        <div class="toast-content">${message}</div>
    `;

    container.appendChild(toast);

    // Force reflow
    toast.offsetHeight;

    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 400);
    }, 3000);
}

function addToCart(code) {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('code_produit', code);
    formData.append('quantite', 1);

    fetch('../../controller/Produit/PanierController.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToast(data.message, 'success');
            // Optionnel: mettre à jour un badge de panier si existant
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Erreur lors de l\'ajout au panier', 'error');
    });
}

// Event delegation for grid buttons
if (productsGrid) {
    productsGrid.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-panier')) {
            const code = e.target.dataset.code;
            addToCart(code);
        }
    });
}

function closeModal() {
    document.getElementById("productModal").style.display = "none";
}