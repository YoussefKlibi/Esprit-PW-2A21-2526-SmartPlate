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
    const formObjectif = document.querySelector('form[action="index.php?action=updateObjectif"]');
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