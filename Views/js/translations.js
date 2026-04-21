const translations = {
    fr: {
        // Login Page
        welcome: "Bienvenue",
        login_sub: "Connectez-vous pour continuer vers votre espace personnel.",
        email_label: "Adresse mail",
        email_placeholder: "exemple@mail.com",
        password_label: "Mot de passe",
        password_placeholder: "..................",
        forgot_password: "Mot de passe oublié ?",
        login_btn: "Se connecter",
        or: "ou",
        google_login: "Continuer avec Google",
        new_here: "Nouveau sur SmartPlate ?",
        register_now: "Créer un compte maintenant",
        slogan_title: "Mangez bien, <br>Vivez mieux",
        slogan_desc: "La première plateforme intelligente qui vous accompagne dans l'atteinte de vos objectifs nutritionnels jour après jour.",

        // Register Page
        join_us: "Rejoignez-nous ! 🚀",
        register_sub: "Créez votre compte en quelques secondes.",
        firstname_label: "Prénom",
        firstname_placeholder: "Jean",
        lastname_label: "Nom",
        lastname_placeholder: "Dupont",
        confirm_password_label: "Confirmer le mot de passe",
        register_btn: "Créer mon compte",
        google_register: "S'inscrire avec Google",
        already_account: "Vous avez déjà un compte ?",
        login_link: "Se connecter",
        register_slogan_title: "Votre parcours<br>commence ici",
        register_slogan_desc: "Définissez vos objectifs, suivez vos repas en détail et progressez à votre rythme grâce à notre accompagnement intelligent."
    },
    en: {
        // Login Page
        welcome: "Welcome",
        login_sub: "Log in to continue to your personal space.",
        email_label: "Email Address",
        email_placeholder: "example@mail.com",
        password_label: "Password",
        password_placeholder: "..................",
        forgot_password: "Forgot password?",
        login_btn: "Log inward", // using basic en
        or: "or",
        google_login: "Continue with Google",
        new_here: "New to SmartPlate?",
        register_now: "Create an account now",
        slogan_title: "Eat well, <br>Live better",
        slogan_desc: "The first intelligent platform that supports you in achieving your nutritional goals day after day.",

        // Register Page
        join_us: "Join us! 🚀",
        register_sub: "Create your account in seconds.",
        firstname_label: "First Name",
        firstname_placeholder: "John",
        lastname_label: "Last Name",
        lastname_placeholder: "Doe",
        confirm_password_label: "Confirm Password",
        register_btn: "Create my account",
        google_register: "Sign up with Google",
        already_account: "Already have an account?",
        login_link: "Log in",
        register_slogan_title: "Your journey<br>starts here",
        register_slogan_desc: "Set your goals, track your meals in detail and progress at your own pace with our intelligent support."
    }
};

function changeLanguage(lang) {
    if (!translations[lang]) return;
    localStorage.setItem('smartplate_lang', lang);

    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (translations[lang][key]) {
            if (el.tagName === 'INPUT') {
                el.placeholder = translations[lang][key];
            } else {
                el.innerHTML = translations[lang][key];
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // Check local storage for language, default to French
    const savedLang = localStorage.getItem('smartplate_lang') || 'fr';
    changeLanguage(savedLang);

    const langSelect = document.getElementById('lang-select');
    if (langSelect) {
        langSelect.value = savedLang;
        langSelect.addEventListener('change', (e) => {
            changeLanguage(e.target.value);
        });
    }
});
