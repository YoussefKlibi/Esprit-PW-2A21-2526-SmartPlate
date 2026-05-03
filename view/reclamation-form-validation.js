document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('.js-reclamation-form');
    if (!form) {
        return;
    }

    var rules = {
        nom_client: {
            minLength: 3,
            requiredMessage: 'Le nom complet est obligatoire.',
            minLengthMessage: 'Le nom complet doit contenir au moins 3 caracteres.'
        },
        email: {
            requiredMessage: 'L\'adresse e-mail est obligatoire.',
            formatMessage: 'Veuillez saisir une adresse e-mail valide.'
        },
        sujet: {
            requiredMessage: 'Veuillez sélectionner un sujet.'
        },
        message: {
            minLength: 10,
            requiredMessage: 'Le message est obligatoire.',
            minLengthMessage: 'Le message doit contenir au moins 10 caracteres.'
        }
    };

    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function validateField(field) {
        var rule = rules[field.name];
        if (!rule) {
            return true;
        }

        var value = field.value.trim();
        var message = '';

        if (value.length === 0) {
            message = rule.requiredMessage;
        } else if (rule.minLength && value.length < rule.minLength) {
            message = rule.minLengthMessage;
        } else if (field.name === 'email' && !emailPattern.test(value)) {
            message = rule.formatMessage;
        }

        field.setCustomValidity(message);
        return message === '';
    }

    var inputs = form.querySelectorAll('input[name], textarea[name]');
    inputs.forEach(function (field) {
        field.addEventListener('input', function () {
            validateField(field);
        });

        field.addEventListener('blur', function () {
            validateField(field);
            field.reportValidity();
        });
    });

    form.addEventListener('submit', function (event) {
        var firstInvalidField = null;

        inputs.forEach(function (field) {
            var isValid = validateField(field);
            if (!isValid && firstInvalidField === null) {
                firstInvalidField = field;
            }
        });

        if (firstInvalidField !== null) {
            event.preventDefault();
            firstInvalidField.focus();
            firstInvalidField.reportValidity();
        }
    });
});
