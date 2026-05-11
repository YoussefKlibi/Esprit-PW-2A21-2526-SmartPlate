document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('deleteResponseModal');
    if (!modal) {
        return;
    }

    var modalText = document.getElementById('deleteResponseModalText');
    var cancelButton = document.getElementById('cancelDeleteResponseBtn');
    var confirmButton = document.getElementById('confirmDeleteResponseBtn');
    var triggerButtons = document.querySelectorAll('.js-delete-response-btn');

    var selectedForm = null;

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        selectedForm = null;
    }

    triggerButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var formId = button.getAttribute('data-form-id');
            var label = button.getAttribute('data-label') || 'cette réponse';
            selectedForm = document.getElementById(formId);

            if (!selectedForm) {
                return;
            }

            modalText.textContent = 'Voulez-vous vraiment supprimer ' + label + ' ?';
            openModal();
        });
    });

    cancelButton.addEventListener('click', closeModal);

    confirmButton.addEventListener('click', function () {
        if (selectedForm) {
            selectedForm.submit();
        }
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
});
