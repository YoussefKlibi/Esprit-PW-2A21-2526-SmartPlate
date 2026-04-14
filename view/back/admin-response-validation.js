document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('.js-admin-response-form');
    if (!form) {
        return;
    }

    var textarea = form.querySelector('textarea[name="reponse"]');
    if (!textarea) {
        return;
    }

    var MIN_LENGTH = 5;
    var MAX_LENGTH = 2000;

    function validateResponseField() {
        var value = textarea.value.trim();
        var message = '';

        if (value.length === 0) {
            message = 'La réponse est obligatoire.';
        } else if (value.length < MIN_LENGTH) {
            message = 'La réponse doit contenir au moins 5 caractères.';
        } else if (value.length > MAX_LENGTH) {
            message = 'La réponse ne doit pas dépasser 2000 caractères.';
        }

        textarea.setCustomValidity(message);
        return message === '';
    }

    textarea.addEventListener('input', validateResponseField);
    textarea.addEventListener('blur', function () {
        validateResponseField();
        textarea.reportValidity();
    });

    form.addEventListener('submit', function (event) {
        if (!validateResponseField()) {
            event.preventDefault();
            textarea.focus();
            textarea.reportValidity();
        }
    });
});
