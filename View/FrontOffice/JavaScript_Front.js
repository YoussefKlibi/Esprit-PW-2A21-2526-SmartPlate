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
            let qteEl = document.getElementById('quantite');
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
                shakeField(this); // Appelle la fonction de tremblement pour indiquer une erreur
                this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s'-]/g, '');

            });
        }

        // --- B. Restriction pour les CHIFFRES UNIQUEMENT (Calories, Macros) ---
        const champsNumeriquesRepas = repasFormBind.querySelectorAll('#quantite, #nbre_calories, #proteine, #glucide, #lipide');
        champsNumeriquesRepas.forEach(input => {
            input.addEventListener('input', function() {
                    shakeField(this); // Appelle la fonction de tremblement pour indiquer une erreur
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
        "quantite",
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

document.addEventListener('DOMContentLoaded', function() {
    const repasForm = document.getElementById('repasForm');
    if (!repasForm) return;

    const repasModifyBtns = document.querySelectorAll('.btn-meal-modify');
    const repasAddBtn = document.getElementById('repasAddBtn');
    const repasUpdateBtn = document.getElementById('repasUpdateBtn');
    const repasResetBtn = document.getElementById('repasResetBtn');
    const idJournalInput = repasForm.querySelector('input[name="id_journal"]');
    const imagePreview = document.getElementById('repasImagePreview');

    function setMealEditMode(isEdit, repasId) {
        if (!repasAddBtn || !repasUpdateBtn) return;

        repasAddBtn.style.display = isEdit ? 'none' : 'inline-block';
        repasUpdateBtn.style.display = isEdit ? 'inline-block' : 'none';

        if (isEdit && repasId) {
            const baseUrl = window.REPAS_CONTROLLER_URL || '../../Controller/RepasController.php';
            repasUpdateBtn.formAction = baseUrl + '?action=update&id=' + encodeURIComponent(repasId);
        } else {
            repasUpdateBtn.removeAttribute('formaction');
        }
    }

    repasModifyBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const typeEl = document.getElementById('type_repas');
            const heureEl = document.getElementById('heure_repas');
            const nomEl = document.getElementById('nom');
            const quantiteEl = document.getElementById('quantite');
            const caloriesEl = document.getElementById('nbre_calories');
            const proteineEl = document.getElementById('proteine');
            const glucideEl = document.getElementById('glucide');
            const lipideEl = document.getElementById('lipide');

            if (idJournalInput && this.dataset.journalId) idJournalInput.value = this.dataset.journalId;
            if (typeEl) typeEl.value = this.dataset.type || '';
            if (heureEl) heureEl.value = this.dataset.heure || '';
            if (nomEl) nomEl.value = this.dataset.nom || '';
            if (quantiteEl) quantiteEl.value = this.dataset.quantite || '';
            if (caloriesEl) caloriesEl.value = this.dataset.calories || '';
            if (proteineEl) proteineEl.value = this.dataset.proteine || '';
            if (glucideEl) glucideEl.value = this.dataset.glucide || '';
            if (lipideEl) lipideEl.value = this.dataset.lipide || '';

            if (imagePreview) {
                imagePreview.innerHTML = 'Image actuelle conservee';
            }

            setMealEditMode(true, this.dataset.id || '');
            repasForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });

    if (repasResetBtn) {
        repasResetBtn.addEventListener('click', function() {
            setMealEditMode(false);
            if (imagePreview) {
                imagePreview.innerHTML = 'Apercu';
            }
        });
    }
});

// Apercu de l'image selectionnee pour un repas
        (function(){
            var input = document.getElementById('repas_image');
            var preview = document.getElementById('repasImagePreview');
            if(!input || !preview) return;
            input.addEventListener('change', function(e){
                var file = this.files && this.files[0];
                if(!file){ preview.innerHTML = 'Apercu'; return; }
                if(!file.type.startsWith('image/')){ preview.innerHTML = 'Fichier non image'; return; }
                var reader = new FileReader();
                reader.onload = function(ev){
                    preview.innerHTML = '';
                    var img = document.createElement('img');
                    img.src = ev.target.result;
                    img.style.maxWidth = '100%';
                    img.style.maxHeight = '100%';
                    img.style.display = 'block';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        })();

(function(){
                var resetBtn = document.getElementById('journalResetBtn');
                if(!resetBtn) return;
                resetBtn.addEventListener('click', function(){
                    var form = document.getElementById('journalAddForm');
                    if(!form) return;
                    var poids = form.querySelector('#poids_actuel');
                    if(poids) poids.value = '';
                    var heures = form.querySelector('#heures_sommeil');
                    if(heures) heures.value = '';
                    var humeur = form.querySelector('#humeur');
                    if(humeur) humeur.value = 'excellent';
                    var dateEl = form.querySelector('#date_journal');
                    if(dateEl){
                        var today = new Date();
                        var yyyy = today.getFullYear();
                        var mm = String(today.getMonth()+1).padStart(2,'0');
                        var dd = String(today.getDate()).padStart(2,'0');
                        dateEl.value = yyyy + '-' + mm + '-' + dd;
                    }
                    // Ensure we're in add mode
                    var updateBtn = document.getElementById('journalUpdateBtn');
                    var addBtn = document.getElementById('journalAddBtn');
                    if(updateBtn) updateBtn.style.display = 'none';
                    if(addBtn) addBtn.style.display = 'inline-block';
                });
            })();

             

    (function() {
        var messagesEl = document.getElementById('spChatMessages');
        var formEl = document.getElementById('spChatForm');
        var inputEl = document.getElementById('spChatInput');
        var sendBtn = document.getElementById('spChatSend');

        if (!messagesEl || !formEl || !inputEl || !sendBtn) return;

        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function scrollToBottom() {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }

        function addMessage(role, text, meta) {
            var row = document.createElement('div');
            row.className = 'sp-chat-row ' + (role === 'user' ? 'user' : 'bot');

            var bubble = document.createElement('div');
            bubble.className = 'sp-chat-bubble';
            bubble.innerHTML = escapeHtml(text || '');

            row.appendChild(bubble);

            if (meta) {
                var m = document.createElement('div');
                m.className = 'sp-chat-meta';
                m.textContent = meta;
                bubble.appendChild(m);
            }

            messagesEl.appendChild(row);
            scrollToBottom();
        }

        function setSending(isSending) {
            sendBtn.disabled = !!isSending;
            inputEl.disabled = !!isSending;
            if (isSending) sendBtn.textContent = 'Envoi...';
            else sendBtn.textContent = 'Envoyer';
        }

        function loadHistory() {
            try {
                var raw = localStorage.getItem('sp_chat_history_v1');
                if (!raw) return null;
                var parsed = JSON.parse(raw);
                if (!Array.isArray(parsed)) return null;
                return parsed;
            } catch (e) {
                return null;
            }
        }

        function saveHistory(history) {
            try {
                localStorage.setItem('sp_chat_history_v1', JSON.stringify(history.slice(-40)));
            } catch (e) {}
        }

        var history = loadHistory() || [];
        if (history.length === 0) {
            addMessage('bot', "Bonjour ! Pose-moi une question sur ta progression, ton objectif, ou des idées de repas.");
        } else {
            history.forEach(function(msg) {
                addMessage(msg.role, msg.text);
            });
        }

        async function ask(question) {
            var url = window.CHATBOT_CONTROLLER_URL || '../../Controller/ChatbotController.php';
            var body = new URLSearchParams();
            body.set('action', 'ask');
            body.set('question', question);
            body.set('context', 'progression');

            var resp = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: body.toString()
            });

            var rawText = await resp.text();
            var data = null;
            try {
                data = rawText ? JSON.parse(rawText) : null;
            } catch (e) {
                throw new Error('Réponse serveur invalide (pas du JSON).');
            }

            function formatServerError() {
                var msg = (data && data.error) ? data.error : ('HTTP ' + resp.status);
                if (data && data.details != null) {
                    var d = data.details;
                    if (typeof d === 'object' && d !== null) {
                        var inner = d.error && typeof d.error === 'object' ? d.error : d;
                        if (inner.message) {
                            msg += ' — ' + inner.message;
                        } else {
                            msg += ' — ' + JSON.stringify(d).slice(0, 500);
                        }
                    } else {
                        msg += ' — ' + String(d).slice(0, 400);
                    }
                }
                return msg;
            }

            if (!resp.ok) {
                throw new Error(formatServerError());
            }

            if (!data || !data.ok) {
                throw new Error(formatServerError());
            }
            return data;
        }

        formEl.addEventListener('submit', async function(e) {
            e.preventDefault();
            var q = (inputEl.value || '').trim();
            if (!q) return;

            addMessage('user', q);
            history.push({ role: 'user', text: q });
            saveHistory(history);

            inputEl.value = '';
            setSending(true);

            var thinkingId = 'thinking_' + Date.now();
            addMessage('bot', 'Je réfléchis…');

            try {
                var data = await ask(q);
                // Remplacer le dernier message bot "Je réfléchis…"
                var rows = messagesEl.querySelectorAll('.sp-chat-row.bot');
                var lastBot = rows[rows.length - 1];
                if (lastBot) {
                    var bubble = lastBot.querySelector('.sp-chat-bubble');
                    if (bubble) bubble.innerHTML = escapeHtml(data.answer || '');
                } else {
                    addMessage('bot', data.answer || '');
                }

                history.push({ role: 'bot', text: data.answer || '' });
                saveHistory(history);
            } catch (err) {
                // Remplacer le dernier message bot "Je réfléchis…"
                var rows2 = messagesEl.querySelectorAll('.sp-chat-row.bot');
                var lastBot2 = rows2[rows2.length - 1];
                var msg = "Désolé, je n'arrive pas à répondre pour le moment. (" + (err && err.message ? err.message : 'Erreur') + ")";
                if (lastBot2) {
                    var bubble2 = lastBot2.querySelector('.sp-chat-bubble');
                    if (bubble2) bubble2.innerHTML = escapeHtml(msg);
                } else {
                    addMessage('bot', msg);
                }
            } finally {
                setSending(false);
                inputEl.focus();
            }
        });

        inputEl.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                // Le form submit gère
            }
        });
    })();

    function toggleNotifTime() {
        const checkbox = document.getElementById('checkNotif');
        const timeGroup = document.getElementById('timeInputGroup');
        if (!checkbox || !timeGroup) return;

        if (checkbox.checked) {
            timeGroup.style.display = 'block'; // On affiche
            timeGroup.style.animation = 'fadeInDown 0.3s ease-out';
        } else {
            timeGroup.style.display = 'none'; // On cache
        }
}

// Note: prediction fetch is handled by the single listener below which
// applies results only if the IA toggle is still enabled when the
// response arrives. This prevents autofill after the user disables IA.

document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('repas_image');
    const nomInput = document.getElementById('nom');
    const quantiteInput = document.getElementById('quantite'); // Le champ quantité
    
    // Les champs de résultats
    const caloriesInput = document.getElementById('nbre_calories');
    const proteineInput = document.getElementById('proteine');
    const glucideInput = document.getElementById('glucide');
    const lipideInput = document.getElementById('lipide');

    // Variables pour stocker les valeurs pour 100g reçues par l'API
    let baseNutrition = {
        calories: 0,
        proteines_g: 0,
        glucides_g: 0,
        lipides_g: 0
    };

    if (!imageInput) return;

    // 1️⃣ L'UTILISATEUR SÉLECTIONNE UNE IMAGE
    // 1️⃣ L'UTILISATEUR SÉLECTIONNE UNE IMAGE
    imageInput.addEventListener('change', function() {
        // --- NOUVEAU : On vérifie si l'IA est activée ---
        const iaToggle = document.getElementById('ia_mode_toggle');
        if (iaToggle && !iaToggle.checked) {
            // Si le bouton est décoché, on arrête tout ici ! 
            // L'image s'affichera dans l'aperçu (grâce à l'autre script), mais l'API ne sera pas appelée.
            return; 
        }
        // -------------------------------------------------

        if (this.files && this.files[0]) {
            if (nomInput) nomInput.value = "Analyse IA en cours...";

            const formData = new FormData();
            formData.append('repas_image', this.files[0]);

            fetch('http://localhost:5000/predict', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Re-vérifier le toggle IA au moment d'appliquer les résultats
                const iaToggle = document.getElementById('ia_mode_toggle');
                if (iaToggle && !iaToggle.checked) {
                    // L'utilisateur a désactivé l'IA depuis l'envoi — on n'applique rien.
                    return;
                }

                if (data && data.nom_detecte && nomInput) {
                    nomInput.value = data.nom_detecte.replace(/_/g, ' ');
                }

                // Si la base de données nutritionnelle a renvoyé des valeurs
                if (data && data.nutrition_pour_100g && !data.nutrition_pour_100g.erreur) {
                    // Re-vérifier encore une fois (sécurité)
                    if (iaToggle && !iaToggle.checked) return;

                    baseNutrition = data.nutrition_pour_100g;

                    if (!quantiteInput.value) quantiteInput.value = 100;
                    calculerMacros();
                }
            })
            .catch(error => {
                console.error('Erreur API:', error);
                if (nomInput) nomInput.value = "";
            });
        }
    });

    // 2️⃣ L'UTILISATEUR MODIFIE LA QUANTITÉ EN TEMPS RÉEL
    if(quantiteInput) {
        quantiteInput.addEventListener('input', calculerMacros);
    }

    // 🧮 LA FONCTION DE CALCUL MAGIQUE
    function calculerMacros() {
        // Si on n'a pas encore de valeurs de base, on ne fait rien
        if(baseNutrition.calories === 0) return;

        // Récupérer la quantité tapée (si vide, on considère 0)
        let qte = parseFloat(quantiteInput.value) || 0;

        // Le calcul en croix : (Valeur pour 100g * Quantité) / 100
        let facteur = qte / 100;

        // Mettre à jour les cases (on utilise toFixed(1) pour garder 1 chiffre après la virgule max)
        if(caloriesInput) caloriesInput.value = Math.round(baseNutrition.calories * facteur);
        if(proteineInput) proteineInput.value = (baseNutrition.proteines_g * facteur).toFixed(1);
        if(glucideInput) glucideInput.value = (baseNutrition.glucides_g * facteur).toFixed(1);
        if(lipideInput) lipideInput.value = (baseNutrition.lipides_g * facteur).toFixed(1);
    }
});
    
    