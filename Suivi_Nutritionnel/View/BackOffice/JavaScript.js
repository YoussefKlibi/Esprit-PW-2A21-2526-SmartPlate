// On attend que la page soit totalement chargée
document.addEventListener('DOMContentLoaded', function() {

    // ==========================================
    // 1. RECHERCHE EN TEMPS RÉEL (Filtrage)
    // ==========================================
    const searchInput = document.getElementById('searchInput');
    if(searchInput) {
        searchInput.addEventListener('keyup', function() {
            let filter = this.value.toLowerCase().replace('#', ''); 
            let rows = document.querySelectorAll('.admin-table tbody tr');

            rows.forEach(row => {
                let idCell = row.cells[0].textContent.toLowerCase(); 
                if (idCell.includes(filter)) {
                    row.style.display = ''; 
                } else {
                    row.style.display = 'none'; 
                }
            });
        });
    }

    // ==========================================
    // 2. CONTRÔLE DE SAISIE CRÉATIF ET CAPTIVANT
    // ==========================================
    const form = document.getElementById('objectifForm');

    if(form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;

            // NÉTTOYAGE : Enlever les anciennes erreurs avant de revérifier
            document.querySelectorAll('.error-text').forEach(el => el.remove());
            document.querySelectorAll('.input-error').forEach(el => {
                el.classList.remove('input-error', 'shake');
            });

            // FONCTION OUTIL : Pour générer le message d'erreur et l'animation
            const showError = (inputId, message) => {
                const input = document.getElementById(inputId);
                
                // On déclenche l'animation de tremblement et la bordure rouge
                input.classList.add('input-error');
                
                // Petit hack pour relancer l'animation CSS à chaque fois
                input.classList.remove('shake');
                void input.offsetWidth; 
                input.classList.add('shake');

                // On crée le texte d'erreur sous l'input
                const errorSpan = document.createElement('span');
                errorSpan.className = 'error-text';
                errorSpan.innerHTML = '⚠️ ' + message;
                input.parentNode.appendChild(errorSpan);
                
                isValid = false;
            };

            // --- LES RÈGLES DE VALIDATION ---

            // 1. Type d'objectif obligatoire
            const type = document.getElementById('type').value;
            if (type === "") {
                showError('type', 'Veuillez sélectionner le type d\'objectif.');
            }

            // 2. Poids : doit être un nombre positif, logique pour un humain (ex: entre 30 et 250kg)
            const poids = document.getElementById('poids_cible').value;
            if (!poids || isNaN(poids) || poids < 30 || poids > 250) {
                showError('poids_cible', 'Entrez un poids cible réaliste (entre 30kg et 250kg).');
            }

            // 3. Date de début obligatoire
            const debut = document.getElementById('Date_Debut').value;
            if (!debut) {
                showError('Date_Debut', 'La date de démarrage est indispensable.');
            }

            // 4. Date de fin : Si elle existe, elle doit être APRÈS la date de début
            const fin = document.getElementById('Date_Fin').value;
            if (debut && fin) {
                const dateDebutObj = new Date(debut);
                const dateFinObj = new Date(fin);
                
                if (dateFinObj <= dateDebutObj) {
                    showError('Date_Fin', 'La date de fin doit être ultérieure à la date de début 🕰️.');
                }
            }

            // Si au moins une règle a échoué, on bloque l'envoi au serveur (PHP)
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});

// ==========================================
// 3. FONCTIONS GLOBALES (Pour les boutons onclick dans le HTML)
// ==========================================

window.openEditForm = function(id, id_user, type, poids, debut, fin, statut) {
    document.getElementById("sectionAjout").style.display = "block";
    document.getElementById("formTitle").innerText = "✏️ Modifier l'objectif #" + id;
    document.getElementById("objectifForm").action = "../../Controller/ObjectifController.php?action=update&id=" + id;
    
    document.getElementById("id_utilisateur").value = id_user;
    document.getElementById("type").value = type;
    document.getElementById("poids_cible").value = poids;
    document.getElementById("Date_Debut").value = debut;
    document.getElementById("Date_Fin").value = fin;
    document.getElementById("statut").value = statut;
    
    document.getElementById("sectionAjout").scrollIntoView({ behavior: 'smooth' });
};

window.closeForm = function() {
    document.getElementById("sectionAjout").style.display = "none";
    document.getElementById("objectifForm").reset();
    document.getElementById("formTitle").innerText = "➕ Ajouter un Nouvel Objectif";
    document.getElementById("objectifForm").action = "../../Controller/ObjectifController.php?action=add";
    
    // On nettoie aussi les erreurs si on annule
    document.querySelectorAll('.error-text').forEach(el => el.remove());
    document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error', 'shake'));
};

function openEditForm(id, id_user, type, poids, debut, fin, statut) {
    // 1. On affiche la section si elle est cachée
    document.getElementById("sectionAjout").style.display = "block";
    
    // 2. On change le titre et l'action du formulaire
    document.getElementById("formTitle").innerText = "✏️ Modifier l'objectif #" + id;
    document.getElementById("objectifForm").action = "../../Controller/ObjectifController.php?action=update&id=" + id;
    
    // 3. On remplit les champs
    document.getElementById("id_utilisateur").value = id_user;
    document.getElementById("type").value = type;
    document.getElementById("poids_cible").value = poids;
    document.getElementById("Date_Debut").value = debut;
    document.getElementById("Date_Fin").value = fin;
    document.getElementById("statut").value = statut;
    
    // 4. On scrolle vers le formulaire pour que l'admin le voie
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

 // 1. Fonction pour afficher/cacher le formulaire d'ajout
        function toggleForm() {
            var formSection = document.getElementById("sectionAjout");
            if (formSection.style.display === "none") {
                formSection.style.display = "block";
            } else {
                formSection.style.display = "none";
            }
        }
        // 2. Fonction de recherche dynamique
        document.getElementById('searchId').addEventListener('input', function() {
            let filter = this.value.toLowerCase().replace('#', ''); 
            let rows = document.querySelectorAll('.admin-table tbody tr');

            rows.forEach(row => {
                let idCell = row.cells[0].textContent.toLowerCase(); 
                if (idCell.includes(filter)) {
                    row.style.display = ''; 
                } else {
                    row.style.display = 'none'; 
                }
            });
        });

        // Fonction pour ouvrir le formulaire
function openAddForm() {
    const section = document.getElementById("sectionAjout");
    section.style.display = "block"; // On affiche la boîte
    document.getElementById("adminJournalForm").reset(); // On vide les champs
    
    // Défilement doux vers le formulaire pour une meilleure UX
    section.scrollIntoView({ behavior: 'smooth' });
}

// Fonction pour fermer le formulaire
function closeForm() {
    document.getElementById("sectionAjout").style.display = "none"; // On cache la boîte
}

 document.getElementById('searchId').addEventListener('input', function() {
            let filter = this.value.toLowerCase().replace('#', ''); 
            let rows = document.querySelectorAll('.admin-table tbody tr');

            rows.forEach(row => {
                // On vérifie que la ligne n'est pas le message "Aucun journal trouvé"
                if (row.cells.length > 1) {
                    let idCell = row.cells[0].textContent.toLowerCase(); 
                    if (idCell.includes(filter)) {
                        row.style.display = ''; 
                    } else {
                        row.style.display = 'none'; 
                    }
                }
            });
        });