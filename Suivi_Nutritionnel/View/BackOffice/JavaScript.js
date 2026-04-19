// On attend que la page soit totalement chargée
document.addEventListener('DOMContentLoaded', function() {

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase().replace('#', '');
            document.querySelectorAll('.admin-table tbody tr').forEach(function(row) {
                if (!row.cells || row.cells.length === 0) return;
                const idCell = row.cells[0].textContent.toLowerCase();
                row.style.display = idCell.includes(filter) ? '' : 'none';
            });
        });
    }

    const clearErrors = function(scopeEl) {
        scopeEl.querySelectorAll('.error-text').forEach(function(el) { el.remove(); });
        scopeEl.querySelectorAll('.input-error').forEach(function(el) {
            el.classList.remove('input-error', 'shake');
        });
    };

    // Helpers: restreindre la saisie en temps réel et déclencher l'effet 'shake'
    const enforceNumericInput = function(el) {
        if (!el) return;
        el.addEventListener('input', function() {
            const old = this.value;
            this.value = this.value.replace(/[^0-9.]/g, '');
            if (old !== this.value) {
                this.classList.add('input-error');
                this.classList.remove('shake');
                void this.offsetWidth;
                this.classList.add('shake');
                setTimeout(() => { this.classList.remove('input-error'); }, 400);
            }
            if ((this.value.match(/\./g) || []).length > 1) {
                // Empêcher plusieurs points
                this.value = this.value.replace(/\.(?=.*\.)/g, '');
                this.classList.remove('shake');
                void this.offsetWidth;
                this.classList.add('shake');
            }
        });
    };

    const enforceAlphaInput = function(el) {
        if (!el) return;
        el.addEventListener('input', function() {
            const old = this.value;
            this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s'\-]/g, '');
            if (old !== this.value) {
                this.classList.add('input-error');
                this.classList.remove('shake');
                void this.offsetWidth;
                this.classList.add('shake');
                setTimeout(() => { this.classList.remove('input-error'); }, 400);
            }
        });
    };

    const attachValidation = function(formEl, rulesFn) {
        if (!formEl) return;
        formEl.addEventListener('submit', function(e) {
            let isValid = true;
            clearErrors(formEl);

            const showError = function(inputId, message) {
                const input = document.getElementById(inputId);
                if (!input) return;
                input.classList.add('input-error');
                input.classList.remove('shake');
                void input.offsetWidth;
                input.classList.add('shake');
                const errorSpan = document.createElement('span');
                errorSpan.className = 'error-text';
                errorSpan.textContent = '⚠️ ' + message;
                input.parentNode.appendChild(errorSpan);
                isValid = false;
            };

            rulesFn(showError);
            if (!isValid) e.preventDefault();
        });
    };

    attachValidation(document.getElementById('objectifForm'), function(showError) {
        const idUserEl = document.getElementById('id_utilisateur');
        const typeEl = document.getElementById('type');
        const poidsCibleEl = document.getElementById('poids_cible');
        const debutEl = document.getElementById('Date_Debut');
        const finEl = document.getElementById('Date_Fin');
        const statutEl = document.getElementById('statut');

        if (idUserEl && !idUserEl.value) showError('id_utilisateur', 'Veuillez renseigner l\'ID utilisateur.');
        if (typeEl && typeEl.value === '') showError('type', 'Veuillez sélectionner le type d\'objectif.');

        if (poidsCibleEl) {
            const p = Number(poidsCibleEl.value);
            if (poidsCibleEl.value === '') showError('poids_cible', 'Le poids cible est obligatoire.');
            else if (Number.isNaN(p) || p < 30 || p > 250) showError('poids_cible', 'Entrez un poids cible réaliste (entre 30kg et 250kg).');
        }

        const debut = debutEl ? debutEl.value : '';
        if (!debut) showError('Date_Debut', 'La date de démarrage est indispensable.');

        const fin = finEl ? finEl.value : '';
        if (debut && fin) {
            const d1 = new Date(debut + 'T00:00:00');
            const d2 = new Date(fin + 'T00:00:00');
            if (d2 <= d1) showError('Date_Fin', 'La date de fin doit être ultérieure à la date de début.');
        }

        if (statutEl && statutEl.value === '') showError('statut', 'Veuillez sélectionner le statut.');
    });

    attachValidation(document.getElementById('adminJournalForm'), function(showError) {
        const idUserEl = document.getElementById('id_utilisateur');
        const dateEl = document.getElementById('date_journal');
        const poidsEl = document.getElementById('poids_actuel');
        const sommeilEl = document.getElementById('Heure_Sommeil') || document.getElementsByName('heures_sommeil')[0];
        const humeurEl = document.getElementById('humeur') || document.getElementsByName('humeur')[0];

        if (!idUserEl || !idUserEl.value) showError('id_utilisateur', 'L\'ID utilisateur est obligatoire.');

        const dateVal = dateEl ? dateEl.value : '';
        if (!dateVal) showError('date_journal', 'La date du journal est obligatoire.');
        else {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const chosen = new Date(dateVal + 'T00:00:00');
            if (chosen.getTime() > today.getTime()) showError('date_journal', 'La date du journal ne peut pas être dans le futur.');
        }

        if (!poidsEl || poidsEl.value === '') showError('poids_actuel', 'Le poids actuel est obligatoire.');
        else {
            const p = Number(poidsEl.value);
            if (Number.isNaN(p) || p < 30 || p > 250) showError('poids_actuel', 'Entrez un poids actuel réaliste (entre 30kg et 250kg).');
        }

        if (!sommeilEl || sommeilEl.value === '') showError('Heure_Sommeil', 'Les heures de sommeil sont obligatoires.');
        else {
            const h = Number(sommeilEl.value);
            if (Number.isNaN(h) || h < 3 || h > 14) showError('Heure_Sommeil', 'Entrez des heures de sommeil plausibles (entre 3h et 14h).');
        }

        const humeur = humeurEl ? humeurEl.value : '';
        if (!humeurEl || humeur === '') showError('humeur', 'Veuillez sélectionner une humeur.');
    });

    const searchJournal = document.getElementById('searchJournalId');
    if (searchJournal) {
        searchJournal.addEventListener('input', function() {
            const filter = this.value.toLowerCase().replace('#', '');
            document.querySelectorAll('#journauxTable tbody tr').forEach(function(row) {
                if (!row.cells || row.cells.length === 0) return;
                const idCell = row.cells[0].textContent.toLowerCase();
                row.style.display = idCell.includes(filter) ? '' : 'none';
            });
        });
    }

    const searchRepas = document.getElementById('searchRepasId');
    if (searchRepas) {
        searchRepas.addEventListener('input', function() {
            const filter = this.value.toLowerCase().replace('#', '');
            document.querySelectorAll('#repasTable tbody tr').forEach(function(row) {
                if (!row.cells || row.cells.length === 0) return;
                const idCell = row.cells[0].textContent.toLowerCase();
                row.style.display = idCell.includes(filter) ? '' : 'none';
            });
        });
    }

    const voirLinks = document.querySelectorAll('.voir-repas');
    const repasTable = document.getElementById('repasTable');
    if (voirLinks.length && repasTable) {
        voirLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const journalId = this.getAttribute('data-id');
                const userId = this.getAttribute('data-user');
                repasTable.querySelectorAll('tbody tr').forEach(function(r) {
                    const rJournal = r.dataset.id_journal || '';
                    const rUser = r.dataset.user || '';
                    if (rJournal === journalId && (!userId || rUser === userId)) r.style.display = '';
                    else r.style.display = 'none';
                });
                const btn = document.getElementById('btnShowAllRepas');
                if (btn) btn.style.display = 'inline-block';
            });
        });
    }

    window.showAllRepas = function() {
        if (!repasTable) return;
        repasTable.querySelectorAll('tbody tr').forEach(function(r) { r.style.display = ''; });
        const btn = document.getElementById('btnShowAllRepas');
        if (btn) btn.style.display = 'none';
    };

    // --- Attacher les protections de saisie en temps réel aux champs importants ---
    (function attachRealtimeRestrictions() {
        const numericIds = ['id_utilisateur','poids_cible','poids_actuel','Heure_Sommeil','heures_sommeil','quantite','nbre_calories','proteine','glucide','lipide'];
        numericIds.forEach(function(id) {
            let el = document.getElementById(id) || document.getElementsByName(id)[0];
            if (el) enforceNumericInput(el);
        });

        // champs texte (nom de repas etc.)
        const alphaIds = ['nom'];
        alphaIds.forEach(function(name) {
            // peut être name ou id selon le formulaire
            let el = document.getElementById(name) || document.getElementsByName(name)[0];
            if (el) enforceAlphaInput(el);
        });
    })();
});

window.openEditForm = function(id, id_user, type, poids, debut, fin, statut) {
    const section = document.getElementById('sectionAjout');
    const form = document.getElementById('objectifForm');
    if (!section || !form) return;
    section.style.display = 'block';
    document.getElementById('formTitle').innerText = '✏️ Modifier l\'objectif #' + id;
    form.action = '../../Controller/ObjectifController.php?action=update&id=' + id;
    document.getElementById('id_utilisateur').value = id_user;
    document.getElementById('type').value = type;
    document.getElementById('poids_cible').value = poids;
    document.getElementById('Date_Debut').value = debut;
    document.getElementById('Date_Fin').value = fin;
    document.getElementById('statut').value = statut;
    section.scrollIntoView({ behavior: 'smooth' });
};

window.closeObjectifForm = function() {
    const section = document.getElementById('sectionAjout');
    const form = document.getElementById('objectifForm');
    if (!section || !form) return;
    section.style.display = 'none';
    form.reset();
    const title = document.getElementById('formTitle');
    if (title) title.innerText = '➕ Ajouter un Nouvel Objectif';
    form.action = '../../Controller/ObjectifController.php?action=add';
    document.querySelectorAll('.error-text').forEach(function(el) { el.remove(); });
    document.querySelectorAll('.input-error').forEach(function(el) { el.classList.remove('input-error', 'shake'); });
};

function openEditForm(id, id_user, type, poids, debut, fin, statut) {
    window.openEditForm(id, id_user, type, poids, debut, fin, statut);
}

function toggleForm() {
    const formSection = document.getElementById('sectionAjout');
    if (!formSection) return;
    formSection.style.display = formSection.style.display === 'none' ? 'block' : 'none';
}

function openAddForm() {
    const section = document.getElementById('sectionAjout');
    const form = document.getElementById('adminJournalForm');
    const title = document.getElementById('formTitle');
    const saveBtn = document.getElementById('adminSaveBtn');
    if (!section || !form) return;
    section.style.display = 'block';
    if (title) title.textContent = '➕ Ajouter un Journal';
    form.action = '../../Controller/JournalController.php?action=add';
    form.reset();
    if (saveBtn) saveBtn.textContent = '💾 Enregistrer';
    section.scrollIntoView({ behavior: 'smooth' });
}

function closeJournalForm() {
    const section = document.getElementById('sectionAjout');
    if (section) section.style.display = 'none';
}

function openJournalEdit(btn) {
    if (!btn) return;
    const section = document.getElementById('sectionAjout');
    const form = document.getElementById('adminJournalForm');
    const title = document.getElementById('formTitle');
    const saveBtn = document.getElementById('adminSaveBtn');
    if (!section || !form) return;

    const id = btn.getAttribute('data-id');
    const user = btn.getAttribute('data-user');
    const date = btn.getAttribute('data-date');
    const poids = btn.getAttribute('data-poids');
    const heures = btn.getAttribute('data-heures');
    const humeur = btn.getAttribute('data-humeur');

    section.style.display = 'block';
    if (title) title.textContent = '✏️ Modifier le Journal #' + id;
    form.action = '../../Controller/JournalController.php?action=update&id=' + encodeURIComponent(id);

    const elUser = document.getElementById('id_utilisateur');
    const elDate = document.getElementById('date_journal');
    const elPoids = document.getElementById('poids_actuel');
    const elHeures = document.getElementById('Heure_Sommeil') || document.getElementsByName('heures_sommeil')[0];
    const elHumeur = document.getElementById('humeur') || document.getElementsByName('humeur')[0];

    if (elUser) elUser.value = user || '';

    if (elDate) {
        let normalized = '';
        if (date) {
            const m = date.match(/^(\d{4}-\d{2}-\d{2})/);
            if (m) normalized = m[1];
            else {
                const parts = date.split('/');
                if (parts.length === 3) {
                    normalized = parts[2] + '-' + parts[1].padStart(2, '0') + '-' + parts[0].padStart(2, '0');
                } else normalized = date;
            }
        }
        elDate.value = normalized;
    }

    if (elPoids) elPoids.value = poids || '';
    if (elHeures) elHeures.value = heures || '';
    if (elHumeur) elHumeur.value = humeur || '';

    if (saveBtn) saveBtn.textContent = 'Enregistrer les modifications';
    section.scrollIntoView({ behavior: 'smooth' });
}

function openAddRepasForm() {
    const f = document.getElementById('sectionAjoutRepas');
    if (!f) return;
    resetRepasFormToAdd();
    f.style.display = 'block';
    setTimeout(function() { f.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 60);
}

function closeAddRepasForm() {
    const f = document.getElementById('sectionAjoutRepas');
    if (!f) return;
    f.style.display = 'none';
    resetRepasFormToAdd();
}

function resetRepasFormToAdd() {
    const form = document.getElementById('adminRepasForm');
    const title = document.getElementById('formRepasTitle');
    const btn = document.getElementById('btnSubmitRepas');
    if (title) title.textContent = '➕ Ajouter un nouveau Repas';
    if (form) {
        form.action = '../../Controller/RepasController.php?action=add';
        form.reset();
    }
    if (btn) btn.textContent = '💾 Enregistrer le repas';
}

function openRepasEdit(btn) {
    if (!btn) return;
    const section = document.getElementById('sectionAjoutRepas');
    const form = document.getElementById('adminRepasForm');
    const title = document.getElementById('formRepasTitle');
    const submitBtn = document.getElementById('btnSubmitRepas');
    if (!section || !form) return;

    const id = btn.getAttribute('data-id');
    const idJournal = btn.getAttribute('data-id_journal');
    const type = btn.getAttribute('data-type');
    const heure = btn.getAttribute('data-heure');
    const nom = btn.getAttribute('data-nom');
    const quantite = btn.getAttribute('data-quantite');
    const calories = btn.getAttribute('data-calories');
    const proteine = btn.getAttribute('data-proteine');
    const glucide = btn.getAttribute('data-glucide');
    const lipide = btn.getAttribute('data-lipide');

    section.style.display = 'block';
    if (title) title.textContent = '✏️ Modifier le Repas #' + id;
    form.action = '../../Controller/RepasController.php?action=update&id=' + encodeURIComponent(id);
    if (submitBtn) submitBtn.textContent = '💾 Enregistrer les modifications';

    const elJournal = document.getElementById('id_journal');
    const elType = document.getElementById('type_repas');
    const elHeure = document.getElementById('heure_repas');
    const elNom = document.getElementById('nom');
    const elQte = document.getElementById('quantite');
    const elCal = document.getElementById('nbre_calories');
    const elProt = document.getElementById('proteine');
    const elGlu = document.getElementById('glucide');
    const elLip = document.getElementById('lipide');
    const elImg = document.getElementById('repas_image');

    if (elJournal && idJournal) elJournal.value = idJournal;
    if (elType && type) elType.value = type;
    if (elHeure) elHeure.value = heure || '';
    if (elNom) elNom.value = nom || '';
    if (elQte) elQte.value = quantite || '';
    if (elCal) elCal.value = calories || '';
    if (elProt) elProt.value = proteine || '';
    if (elGlu) elGlu.value = glucide || '';
    if (elLip) elLip.value = lipide || '';
    if (elImg) elImg.value = '';

    setTimeout(function() { section.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 60);
}
