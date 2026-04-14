document.addEventListener('DOMContentLoaded', function () {

    // Validation for Login Form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            let isValid = true;

            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const errorEmail = document.getElementById('errorEmail');
            const errorPassword = document.getElementById('errorPassword');

            // Reset errors
            errorEmail.textContent = '';
            errorPassword.textContent = '';

            // Check email
            if (emailInput.value.trim() === '') {
                errorEmail.textContent = 'L\'adresse e-mail est obligatoire.';
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
                errorEmail.textContent = 'Le format de l\'adresse e-mail est invalide.';
                isValid = false;
            }

            // Check password
            if (passwordInput.value.trim() === '') {
                errorPassword.textContent = 'Le mot de passe est obligatoire.';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault(); // Stop submission
            }
        });
    }

    // Validation for Register Form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            let isValid = true;

            const prenomInfo = { el: document.getElementById('prenom'), err: document.getElementById('errorPrenom'), msg: 'Le prénom est obligatoire.' };
            const nomInfo = { el: document.getElementById('nom'), err: document.getElementById('errorNom'), msg: 'Le nom est obligatoire.' };
            const emailInfo = { el: document.getElementById('email'), err: document.getElementById('errorEmailRegister'), msg: 'L\'adresse e-mail est obligatoire.' };
            const passInfo = { el: document.getElementById('password'), err: document.getElementById('errorPasswordRegister'), msg: 'Le mot de passe est obligatoire.' };
            const confPassInfo = { el: document.getElementById('confirm-password'), err: document.getElementById('errorConfirmPassword'), msg: 'La confirmation est obligatoire.' };

            [prenomInfo, nomInfo, emailInfo, passInfo, confPassInfo].forEach(function (info) {
                info.err.textContent = '';
                if (info.el.value.trim() === '') {
                    info.err.textContent = info.msg;
                    isValid = false;
                }
            });

            if (isValid) {
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInfo.el.value)) {
                    emailInfo.err.textContent = 'Le format de l\'adresse e-mail est invalide.';
                    isValid = false;
                }

                if (passInfo.el.value !== confPassInfo.el.value) {
                    confPassInfo.err.textContent = 'Les mots de passe ne correspondent pas.';
                    isValid = false;
                } else {
                    const val = passInfo.el.value;
                    if (val.length < 8) {
                        passInfo.err.textContent = 'Le mot de passe doit comporter au moins 8 caractères.';
                        isValid = false;
                    } else if (!/[A-Z]/.test(val)) {
                        passInfo.err.textContent = 'Le mot de passe doit contenir au moins une lettre majuscule.';
                        isValid = false;
                    } else if (!/\d/.test(val)) {
                        passInfo.err.textContent = 'Le mot de passe doit contenir au moins un chiffre.';
                        isValid = false;
                    } else if (!/[@$!%*?&]/.test(val)) {
                        passInfo.err.textContent = 'Le mot de passe doit contenir au moins un caractère spécial (@$!%*?&).';
                        isValid = false;
                    }
                }
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    // Validation for Forgot Password Form
    const forgotForm = document.getElementById('forgotForm');
    if (forgotForm) {
        forgotForm.addEventListener('submit', function (e) {
            const emailInput = document.getElementById('emailForgot');
            const errorEmail = document.getElementById('errorEmailForgot');
            let isValid = true;

            errorEmail.textContent = '';
            if (emailInput.value.trim() === '') {
                errorEmail.textContent = 'L\'adresse e-mail est obligatoire.';
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
                errorEmail.textContent = 'Le format de l\'adresse e-mail est invalide.';
                isValid = false;
            }

            if (!isValid) e.preventDefault();
        });
    }

    // Validation for Reset Password Form
    const resetForm = document.getElementById('resetForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function (e) {
            const passInput = document.getElementById('passwordReset');
            const confPassInput = document.getElementById('confirmPasswordReset');
            const errorPass = document.getElementById('errorPasswordReset');
            const errorConf = document.getElementById('errorConfirmReset');
            let isValid = true;

            errorPass.textContent = '';
            errorConf.textContent = '';

            if (passInput.value.trim() === '') {
                errorPass.textContent = 'Le mot de passe est obligatoire.';
                isValid = false;
            } else {
                const val = passInput.value;
                if (val.length < 8) {
                    errorPass.textContent = 'Le mot de passe doit comporter au moins 8 caractères.';
                    isValid = false;
                } else if (!/[A-Z]/.test(val)) {
                    errorPass.textContent = 'Le mot de passe doit contenir au moins une lettre majuscule.';
                    isValid = false;
                } else if (!/\d/.test(val)) {
                    errorPass.textContent = 'Le mot de passe doit contenir au moins un chiffre.';
                    isValid = false;
                } else if (!/[@$!%*?&]/.test(val)) {
                    errorPass.textContent = 'Le mot de passe doit contenir au moins un caractère spécial (@$!%*?&).';
                    isValid = false;
                }
            }

            if (confPassInput.value.trim() === '') {
                errorConf.textContent = 'La confirmation est obligatoire.';
                isValid = false;
            } else if (passInput.value !== confPassInput.value) {
                errorConf.textContent = 'Les mots de passe ne correspondent pas.';
                isValid = false;
            }

            if (!isValid) e.preventDefault();
        });
    }
    // Validation for User Create Form (Backend)
    const userCreateForm = document.getElementById('userCreateForm');
    if (userCreateForm) {
        userCreateForm.addEventListener('submit', function (e) {
            let isValid = true;
            const prenomInfo = { el: document.getElementById('prenom'), err: document.getElementById('errorPrenom'), msg: 'Le prénom est obligatoire.' };
            const nomInfo = { el: document.getElementById('nom'), err: document.getElementById('errorNom'), msg: 'Le nom est obligatoire.' };
            const emailInfo = { el: document.getElementById('email'), err: document.getElementById('errorEmail'), msg: 'L\'adresse e-mail est obligatoire.' };
            const passInfo = { el: document.getElementById('mot_de_passe'), err: document.getElementById('errorPassword'), msg: 'Le mot de passe est obligatoire.' };

            [prenomInfo, nomInfo, emailInfo, passInfo].forEach(function (info) {
                info.err.textContent = '';
                if (info.el.value.trim() === '') {
                    info.err.textContent = info.msg;
                    isValid = false;
                }
            });

            if (isValid) {
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInfo.el.value)) {
                    emailInfo.err.textContent = 'Le format de l\'adresse e-mail est invalide.';
                    isValid = false;
                }
                const val = passInfo.el.value;
                if (val.length < 8) {
                    passInfo.err.textContent = 'Le mot de passe doit comporter au moins 8 caractères.';
                    isValid = false;
                } else if (!/[A-Z]/.test(val)) {
                    passInfo.err.textContent = 'Le mot de passe doit contenir au moins une lettre majuscule.';
                    isValid = false;
                } else if (!/\d/.test(val)) {
                    passInfo.err.textContent = 'Le mot de passe doit contenir au moins un chiffre.';
                    isValid = false;
                } else if (!/[@$!%*?&]/.test(val)) {
                    passInfo.err.textContent = 'Le mot de passe doit contenir au moins un caractère spécial (@$!%*?&).';
                    isValid = false;
                }
            }
            if (!isValid) e.preventDefault();
        });
    }

    // Validation for User Update Form (Backend)
    const userUpdateForm = document.getElementById('userUpdateForm');
    if (userUpdateForm) {
        userUpdateForm.addEventListener('submit', function (e) {
            let isValid = true;
            const nomInfo = { el: document.getElementById('update_nom'), err: document.getElementById('errorUpdateNom'), msg: 'Le nom est obligatoire.' };
            const emailInfo = { el: document.getElementById('update_email'), err: document.getElementById('errorUpdateEmail'), msg: 'L\'adresse e-mail est obligatoire.' };

            [nomInfo, emailInfo].forEach(function (info) {
                info.err.textContent = '';
                if (info.el.value.trim() === '') {
                    info.err.textContent = info.msg;
                    isValid = false;
                }
            });

            if (isValid) {
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInfo.el.value)) {
                    emailInfo.err.textContent = 'Le format de l\'adresse e-mail est invalide.';
                    isValid = false;
                }
            }
            if (!isValid) e.preventDefault();
        });
    }
});
