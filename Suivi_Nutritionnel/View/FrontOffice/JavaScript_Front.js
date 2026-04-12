// JavaScript.js

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
    const formJournal = document.querySelector('form[action="index.php?action=addJournal"]');
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
    const formRepas = document.querySelector('form[action="index.php?action=addRepas"]');
    if (formRepas) {
        formRepas.addEventListener('submit', function(e) {
            let nom = document.getElementById('nom').value;
            let qte = document.getElementById('qte').value;
            let calories = document.getElementById('nbre_calories').value;
            let proteine = document.getElementById('proteine').value;
            let glucide = document.getElementById('glucide').value;
            let lipide = document.getElementById('lipide').value;
            
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