// JavaScript_Front.js

document.addEventListener('DOMContentLoaded', function() {
    
    // =========================================================================
    // FONCTION MODERNE : AFFICHER LES ERREURS DANS LE SITE (SANS ALERT)
    // =========================================================================
    function afficherErreurs(formulaire, erreurs) {
        // 1. On nettoie les anciennes erreurs s'il y en avait déjà
        let ancienneBoite = formulaire.parentNode.querySelector('.boite-erreurs');
        if (ancienneBoite) {
            ancienneBoite.remove();
        }

        // 2. S'il y a de nouvelles erreurs, on crée un bloc HTML dynamiquement
        if (erreurs.length > 0) {
            let boite = document.createElement('div');
            boite.className = 'boite-erreurs';
            
            // Style moderne intégré directement via JS
            boite.style.backgroundColor = '#fff5f5'; // Fond rouge très clair
            boite.style.color = '#e53e3e'; // Texte rouge vif
            boite.style.border = '1px solid #feb2b2';
            boite.style.padding = '1rem';
            boite.style.borderRadius = '12px';
            boite.style.marginBottom = '1rem';
            boite.style.fontSize = '0.9rem';
            boite.style.width = '100%';

            // Titre de la boîte
            let titre = document.createElement('div');
            titre.innerHTML = '<strong>⚠️ Veuillez corriger ces erreurs :</strong>';
            titre.style.marginBottom = '0.5rem';
            boite.appendChild(titre);

            // Liste à puces pour les erreurs
            let liste = document.createElement('ul');
            liste.style.margin = '0';
            liste.style.paddingLeft = '1.5rem';

            // On ajoute chaque erreur dans la liste
            erreurs.forEach(erreur => {
                let puce = document.createElement('li');
                puce.textContent = erreur;
                liste.appendChild(puce);
            });

            boite.appendChild(liste);

            // 3. On insère cette belle boîte juste avant le formulaire
            formulaire.parentNode.insertBefore(boite, formulaire);
        }
    }


    // =========================================================================
    // --- 1. CONTRÔLE DU JOURNAL (Date et Poids) ---
    // =========================================================================
    const formJournal = document.getElementById('journalAddForm');
    if (formJournal) {
        formJournal.addEventListener('submit', function(e) {
            let dateJournal = formJournal.querySelector('input[name="date_journal"]').value;
            let poids = formJournal.querySelector('input[name="poids_actuel"]').value;
            let erreurs = [];

            if (dateJournal.trim() === "") {
                erreurs.push("La date du journal est obligatoire.");
            }
            
            if (poids.trim() !== "") {
                if (parseFloat(poids) < 20 || parseFloat(poids) > 250) {
                    erreurs.push("Le poids doit être réaliste (entre 20kg et 250kg).");
                }
            }

            if (erreurs.length > 0) {
                e.preventDefault();
                afficherErreurs(formJournal, erreurs); // Appelle notre nouvelle fonction
            } else {
                afficherErreurs(formJournal, []); // Efface la boîte si tout est bon
            }
        });
    }

    // =========================================================================
    // --- 2. CONTRÔLE DE L'AJOUT D'UN REPAS ---
    // =========================================================================
    const formRepas = document.getElementById('repasForm');
    if (formRepas) {
        formRepas.addEventListener('submit', function(e) {
            let nomEl = document.getElementById('nom');
            let qteEl = document.getElementById('qte');
            let caloriesEl = document.getElementById('nbre_calories');
            let proteineEl = document.getElementById('proteine');
            let glucideEl = document.getElementById('glucide');
            let lipideEl = document.getElementById('lipide');

            let nom = nomEl ? nomEl.value : '';
            let qte = qteEl ? qteEl.value : '';
            let calories = caloriesEl ? caloriesEl.value : '';
            let proteine = proteineEl ? proteineEl.value : '';
            let glucide = glucideEl ? glucideEl.value : '';
            let lipide = lipideEl ? lipideEl.value : '';
            
            let erreurs = [];

            if (nom.trim() === "") erreurs.push("Le nom du repas est obligatoire.");
            else if (nom.length < 3) erreurs.push("Le nom du repas doit faire au moins 3 caractères.");
            
            if (qte === "" || parseFloat(qte) <= 0) erreurs.push("La quantité doit être supérieure à 0.");
            if (calories === "" || parseFloat(calories) < 0) erreurs.push("Les calories ne peuvent pas être négatives.");
            if (proteine === "" || parseFloat(proteine) < 0) erreurs.push("Les protéines ne peuvent pas être négatives.");
            if (glucide === "" || parseFloat(glucide) < 0) erreurs.push("Les glucides ne peuvent pas être négatifs.");
            if (lipide === "" || parseFloat(lipide) < 0) erreurs.push("Les lipides ne peuvent pas être négatifs.");

            if (erreurs.length > 0) {
                e.preventDefault();
                afficherErreurs(formRepas, erreurs);
            } else {
                afficherErreurs(formRepas, []);
            }
        });
    }

    // =========================================================================
    // --- 3. CONTRÔLE DE L'OBJECTIF ---
    // =========================================================================
    const formObjectif = document.querySelector('form[action="../../Controller/ObjectifController.php?action=add"]');
    if (formObjectif) {
        formObjectif.addEventListener('submit', function(e) {
            let poidsCible = document.getElementById('poids_cible').value;
            let dateDebut = document.getElementById('Date_Debut').value;
            let dateFin = document.getElementById('Date_Fin').value;
            
            let erreurs = [];

            if (poidsCible.trim() === "") {
                erreurs.push("Le poids cible est obligatoire.");
            } else if (parseFloat(poidsCible) < 20 || parseFloat(poidsCible) > 250) {
                erreurs.push("Le poids cible doit être réaliste (entre 20 et 250 kg).");
            }

            if (dateDebut.trim() === "") erreurs.push("La date de début est obligatoire.");
            if (dateFin.trim() === "") erreurs.push("La date de fin est obligatoire.");

            if (dateDebut !== "" && dateFin !== "") {
                let debut = new Date(dateDebut);
                let fin = new Date(dateFin);

                if (fin <= debut) {
                    erreurs.push("La date de fin doit être strictement après la date de début.");
                }
            }

            if (erreurs.length > 0) {
                e.preventDefault();
                afficherErreurs(formObjectif, erreurs);
            } else {
                afficherErreurs(formObjectif, []);
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
            // 1. On cible le formulaire de la page
            const form = document.querySelector('form[action*="ObjectifController.php"]');

            if(form) {
                // Désactive les bulles grises moches du HTML5 (required)
                form.setAttribute('novalidate', true);

                // 2. PROTECTION EN TEMPS RÉEL : Poids Cible (Anti-Alphabet)
                const poidsInput = form.querySelector('input[name="poids_cible"]');
                if(poidsInput) {
                    poidsInput.addEventListener('input', function(e) {
                        // Expression régulière : On remplace tout ce qui n'est PAS un chiffre (0-9) ou un point (.) par du vide
                        this.value = this.value.replace(/[^0-9.]/g, '');
                    });
                }

                // 3. VÉRIFICATION À LA SOUMISSION DU BOUTON
                form.addEventListener('submit', function(e) {
                    let isValid = true;

                    // Nettoyage visuel : on enlève les anciens messages rouges
                    document.querySelectorAll('.error-text').forEach(el => el.remove());
                    form.querySelectorAll('input').forEach(el => {
                        el.style.border = '1px solid #ddd'; // Remet la bordure normale
                        el.classList.remove('shake');
                    });

                    // Fonction magique pour animer l'erreur
                    const showError = (inputName, message) => {
                        const input = form.querySelector(`input[name="${inputName}"]`);
                        if(input) {
                            input.style.border = '2px solid #e74c3c'; // Bordure rouge
                            
                            // On déclenche l'effet de tremblement
                            input.classList.remove('shake');
                            void input.offsetWidth; 
                            input.classList.add('shake');

                            // Création du texte d'erreur
                            const errorSpan = document.createElement('span');
                            errorSpan.className = 'error-text';
                            errorSpan.style.color = '#e74c3c';
                            errorSpan.style.fontSize = '0.85rem';
                            errorSpan.style.fontWeight = '600';
                            errorSpan.style.display = 'block';
                            errorSpan.style.marginTop = '5px';
                            errorSpan.innerHTML = '⚠️ ' + message;
                            
                            input.parentNode.appendChild(errorSpan);
                            isValid = false;
                        }
                    };

                    // --- LES RÈGLES MÉTICULEUSES ---

                    // A. Vérification du Poids
                    const poidsVal = parseFloat(poidsInput.value);
                    if(!poidsVal || poidsVal < 30 || poidsVal > 250) {
                        showError('poids_cible', 'Veuillez saisir un poids réaliste (entre 30 et 250 kg).');
                    }

                    // B. Vérification de la Logique des Dates
                    const debutInput = form.querySelector('input[name="Date_Debut"]');
                    const finInput = form.querySelector('input[name="Date_Fin"]');
                    
                    if (debutInput && finInput && finInput.value !== "") {
                        const dateDebut = new Date(debutInput.value);
                        const dateFin = new Date(finInput.value);
                        
                        // Règle 1 : La date de fin ne peut pas être dans le passé de la date de début
                        if (dateFin <= dateDebut) {
                            showError('Date_Fin', 'Voyage dans le temps interdit : la date de fin doit être ultérieure.');
                        } else {
                            // Règle 2 : Plus de 30 jours
                            // Calcul savant : (Différence en millisecondes) divisé par (1000ms * 60s * 60m * 24h)
                            const diffTemps = Math.abs(dateFin - dateDebut);
                            const diffJours = Math.ceil(diffTemps / (1000 * 60 * 60 * 24)); 
                            
                            if (diffJours < 30) {
                                showError('Date_Fin', `Un objectif sérieux nécessite au moins 30 jours (Seulement ${diffJours} jours saisis).`);
                            }
                        }
                    }

                    // Si une seule erreur est détectée, on bloque l'envoi au serveur PHP !
                    if (!isValid) {
                        e.preventDefault(); 
                    }
                });
            }
        });


        //Partie controle de saisie Journal
        
        
        document.addEventListener('DOMContentLoaded', function() {
    const journalForm = document.getElementById('journalAddForm');

    if (journalForm) {
        journalForm.addEventListener('submit', function(e) {
            let isValid = true;

            // 1. Nettoyage des anciennes erreurs
            document.querySelectorAll('.error-text').forEach(el => el.remove());
            journalForm.querySelectorAll('input').forEach(input => {
                input.classList.remove('shake', 'input-error');
                input.style.border = '1px solid #ddd';
            });

            // 2. Fonction pour afficher l'erreur avec animation
            const showError = (id, message) => {
                const field = document.getElementById(id);
                if (field) {
                    field.style.border = '2px solid #e74c3c';
                    field.classList.add('shake');
                    
                    const error = document.createElement('span');
                    error.className = 'error-text';
                    error.style.color = '#e74c3c';
                    error.style.fontSize = '0.8rem';
                    error.innerHTML = '⚠️ ' + message;
                    field.parentNode.appendChild(error);
                    isValid = false;
                }
            };

            // 3. RÈGLES DE VALIDATION
            // 🔍 Vérification des champs

    const poids = document.getElementById("poids_actuel");
            const sommeil = document.getElementById("heures_sommeil");

            if (!poids || poids.value.trim() === "") {
                showError("poids_actuel", "Le poids est obligatoire");
            }

            if (!sommeil || sommeil.value.trim() === "") {
                showError("heures_sommeil", "Les heures de sommeil sont obligatoires");

            }
            // Aucun contrôle des champs de repas ici — ce formulaire gère uniquement le journal (date, poids, sommeil, humeur)

            // Bloquer l'envoi si une erreur existe
            if (!isValid) {
                e.preventDefault();
            }
        });

        // 4. PROTECTION EN TEMPS RÉEL (Empêcher de taper autre chose que des chiffres)
        const inputsNumber = journalForm.querySelectorAll('input[type="number"]');
        inputsNumber.forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9.]/g, '');
            });
        });
    }
});

// ============================================================
    // 1. RESTRICTION DYNAMIQUE (FILTRAGE PENDANT LA SAISIE)
    // ============================================================
    
    // Bind meal-specific validations only to the meal form (`repasForm`)
    const repasFormBind = document.getElementById('repasForm');

    if (repasFormBind) {
        // --- A. Restriction pour les LETTRES UNIQUEMENT (Nom de l'aliment) ---
        const nomAliment = repasFormBind.querySelector('[name="nom"]');
        if (nomAliment) {
            nomAliment.addEventListener('input', function() {
                this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
            });
        }

        // --- B. Restriction pour les CHIFFRES UNIQUEMENT (Calories, Macros) ---
        const champsNumeriquesRepas = repasFormBind.querySelectorAll('input[type="number"], input[type="text"], input[id*="nbre_"], input[id*="qte"], input[id*="proteine"], input[id*="glucide"], input[id*="lipide"]');
        champsNumeriquesRepas.forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9.]/g, '');
            });
        });

        // Contrôle à la soumission du formulaire repas
        repasFormBind.addEventListener('submit', function(e) {
            let isValid = true;
            // Nettoyage des erreurs précédentes
            document.querySelectorAll('.error-text').forEach(el => el.remove());

            const showError = (id, message) => {
                const field = document.getElementById(id);
                if (field) {
                    field.style.border = '2px solid #e74c3c';
                    field.classList.add('shake');
                    const error = document.createElement('span');
                    error.className = 'error-text';
                    error.style.color = '#e74c3c';
                    error.style.fontSize = '0.8rem';
                    error.style.display = 'block';
                    error.style.marginTop = '5px';
                    error.innerHTML = '⚠️ ' + message;
                    field.parentNode.appendChild(error);
                    isValid = false;
                }
            };

            const nom = repasFormBind.querySelector('[name="nom"]');
            if (!nom || nom.value.trim().length < 3) {
                showError(nom ? nom.id : 'nom', 'Le nom du repas est obligatoire et doit faire au moins 3 caractères.');
            }

            const calories = repasFormBind.querySelector('#nbre_calories');
            if (calories && (calories.value === '' || parseFloat(calories.value) < 0)) {
                showError('nbre_calories', 'Veuillez saisir un nombre de calories valide.');
            }

            if (!isValid) e.preventDefault();
        });
    }
document.addEventListener("DOMContentLoaded", function () {

    // 🔥 Fonction pour déclencher le shake
    function shakeField(field) {
        field.classList.add("shake", "input-error");

        setTimeout(() => {
            field.classList.remove("shake", "input-error");
        }, 300);
    }

    // 🔢 Champs numériques
    const numericFields = [
        "poids_actuel",
        "qte",
        "nbre_calories",
        "proteine",
        "glucide",
        "lipide",
        "heures_sommeil" // ✅ ajouté
    ];

    numericFields.forEach(function(name) {
        let inputs = document.querySelectorAll(`[name="${name}"]`);

        inputs.forEach(function(input) {
            input.addEventListener("input", function () {
                let oldValue = this.value;

                // Autoriser seulement chiffres + point
                this.value = this.value.replace(/[^0-9.]/g, "");

                // Si modification → erreur → shake
                if (oldValue !== this.value) {
                    shakeField(this);
                }

                // Empêcher plusieurs points
                if ((this.value.match(/\./g) || []).length > 1) {
                    this.value = this.value.slice(0, -1);
                    shakeField(this);
                }
            });
        });
    });

    // 🔤 Champs texte
    const textFields = ["nom"];

    textFields.forEach(function(name) {
        let inputs = document.querySelectorAll(`[name="${name}"]`);

        inputs.forEach(function(input) {
            input.addEventListener("input", function () {
                let oldValue = this.value;

                // Autoriser seulement lettres + espaces + accents
                this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, "");

                // Si modification → erreur → shake
                if (oldValue !== this.value) {
                    shakeField(this);
                }
            });
        });
    });

});

// Handler pour remplir le formulaire d'ajout quand on clique sur "Modifier" dans le petit bloc
document.addEventListener('DOMContentLoaded', function() {
    const modifyBtns = document.querySelectorAll('.btn-modify');
    const journalForm = document.getElementById('journalAddForm');
    if (!journalForm) return;

    const addBtn = document.getElementById('journalAddBtn');
    const updateBtn = document.getElementById('journalUpdateBtn');

    modifyBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id || '';
            const date = this.dataset.date || '';
            const poids = this.dataset.poids || '';
            const heures = this.dataset.heures || '';
            const humeur = this.dataset.humeur || '';

            const dateEl = document.getElementById('date_journal');
            const poidsEl = document.getElementById('poids_actuel');
            const heuresEl = document.getElementById('heures_sommeil');
            const humeurEl = document.getElementById('humeur');

            if (dateEl) dateEl.value = date;
            if (poidsEl) poidsEl.value = poids;
            if (heuresEl) heuresEl.value = heures;
            if (humeurEl) {
                Array.from(humeurEl.options).forEach(opt => opt.selected = (opt.value === humeur));
            }

            // Préparer le bouton update pour soumettre vers l'action update avec l'id
            if (updateBtn) {
                updateBtn.style.display = 'inline-block';
                if (window.JOURNAL_CONTROLLER_URL) {
                    updateBtn.formAction = window.JOURNAL_CONTROLLER_URL + '?action=update&id=' + encodeURIComponent(id);
                } else {
                    updateBtn.formAction = '../../Controller/JournalController.php?action=update&id=' + encodeURIComponent(id);
                }
            }

            // Optionnel : scroller vers le formulaire
            journalForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });
});