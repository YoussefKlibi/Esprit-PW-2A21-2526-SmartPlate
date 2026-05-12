const search = document.querySelector(".search-box-boutiques"); // ✔ corrigé
const input = document.getElementById("searchInputBoutique");
const button = document.querySelector(".search-box-boutiques button"); // ✅ AJOUT

let map; // carte globale
let markersLayer; // couche markers

window.addEventListener("scroll", () => {
    if (window.scrollY > 200) {
        search.classList.add("sticky-search");
    } else {
        search.classList.remove("sticky-search");
    }
});

window.addEventListener("scroll", () => {
    const scrollY = window.scrollY;

    if (scrollY > 50) {
        document.body.classList.add("scrolled");
    } else {
        document.body.classList.remove("scrolled");
    }
});

document.addEventListener("DOMContentLoaded", function () {

    // 📍 MAP INIT
    map = L.map('map').setView([36.8065, 10.1815], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    markersLayer = L.layerGroup().addTo(map);

    // 🔥 affichage initial
    drawMap(boutiques);

    // 🔍 AJAX SEARCH LIVE (NE CHANGE PAS)
    input.addEventListener("input", function () {

        const value = this.value.trim();

        fetch(`../Controller/BoutiqueController.php?action=searchAjax&NomB=${encodeURIComponent(value)}`)
            .then(res => res.json())
            .then(data => {

                updateList(data);
                drawMap(data);
            });
    });

    // 🔍 CLICK BUTTON + RESET INPUT (AJOUT)
    button.addEventListener("click", function () {

        const value = input.value.trim();

        fetch(`../Controller/BoutiqueController.php?action=searchAjax&NomB=${encodeURIComponent(value)}`)
            .then(res => res.json())
            .then(data => {

                updateList(data);
                drawMap(data);

                // 🔥 vider input après recherche bouton
                input.value = "";
            });
    });

});


// ===============================
// 🧾 UPDATE LISTE HTML
// ===============================
function updateList(data) {

    const container = document.querySelector(".shops-list");
    container.innerHTML = "";

    if (!data || data.length === 0) {
        container.innerHTML = `
            <div class="shop-card">
                <h3>Aucune boutique trouvée</h3>
            </div>
        `;
        return;
    }

    data.forEach(b => {
        container.innerHTML += `
            <div class="shop-card">
                <div class="shop-header">
                    <img src="Images/logo.jpg" class="shop-logo">
                    <h3>${b.NomB}</h3>
                </div>
                <p>📍 ${b.AdresseB}</p>
                <p>📞 ${b.TelB}</p>
                <p>✉️ ${b.EmailB}</p>
            </div>
        `;
    });
}


// ===============================
// 🗺️ UPDATE MAP
// ===============================
function drawMap(data) {

    if (!map) return;

    markersLayer.clearLayers();

    let bounds = [];

    data.forEach(b => {

        if (b.latitude && b.longitude) {

            const lat = parseFloat(b.latitude);
            const lng = parseFloat(b.longitude);

            const marker = L.marker([lat, lng]).addTo(markersLayer);

            marker.bindPopup(`
                <b>${b.NomB}</b><br>
                📍 ${b.AdresseB}<br>
                📞 ${b.TelB}
            `);

            bounds.push([lat, lng]);
        }
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds);
    }
}


input.addEventListener("keypress", function (e) {

    if (e.key === "Enter") {
        e.preventDefault();

        const value = input.value.trim();

        fetch(`../Controller/BoutiqueController.php?action=searchAjax&NomB=${encodeURIComponent(value)}`)
            .then(res => res.json())
            .then(data => {

                updateList(data);
                drawMap(data);

                // 🔥 vider input après recherche ENTER
                input.value = "";
            });
    }
});