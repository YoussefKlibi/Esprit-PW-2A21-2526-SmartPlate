document.addEventListener("DOMContentLoaded", function () {
    const chatbotToggle = document.getElementById('chatbot-toggle');
    const chatbotWindow = document.getElementById('chatbot-window');
    const chatbotClose = document.getElementById('chatbot-close');
    const chatbotMessages = document.getElementById('chatbot-messages');
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');

    // Base de connaissances du bot
    const faqData = [
        {
            keywords: ["creer", "créer", "compte", "inscription", "inscrire", "nouveau"],
            answer: "Pour <b>créer un compte</b>, rendez-vous sur la page d'inscription. Remplissez le formulaire avec votre prénom, nom, adresse email et un mot de passe sécurisé. Vous serez immédiatement connecté et redirigé vers votre tableau de bord."
        },
        {
            keywords: ["connecter", "connexion", "login", "se connecter"],
            answer: "Pour <b>vous connecter</b>, allez sur la page de connexion (login). Entrez votre email et votre mot de passe, ou utilisez le bouton <b>'Continuer avec Google'</b> pour une connexion sécurisée en un clic."
        },
        {
            keywords: ["modifier", "profil", "mon profil", "changer", "editer"],
            answer: "Pour <b>modifier votre profil</b>, cliquez sur 'Mon Profil' dans le menu de gauche de votre tableau de bord. Vous pourrez y mettre à jour vos informations personnelles (nom, email) et modifier votre mot de passe."
        },
        {
            keywords: ["mot de passe oublié", "oublié", "réinitialiser", "perdu", "password"],
            answer: "Si vous avez oublié votre mot de passe, allez sur la page de connexion et cliquez sur <b>'Mot de passe oublié ?'</b>. Entrez votre email pour recevoir un lien de réinitialisation sécurisé."
        },
        {
            keywords: ["activité", "historique", "connexions", "appareils", "lieux", "sécurité"],
            answer: "Pour consulter vos lieux de connexion et vos appareils actifs, allez dans <b>Mon Profil</b>, cliquez sur l'icône <b>Paramètres (⚙️)</b>, puis sur <b>🛡️ Sécurité et Connexion</b>. Vous pourrez y gérer vos sessions."
        },
        {
            keywords: ["ajouter", "utilisateur", "nouveau utilisateur"],
            answer: "Cette action est réservée aux <b>administrateurs</b>. Depuis le panneau d'administration, naviguez vers 'Utilisateurs & Logins', puis cliquez sur le bouton <b>'+ Ajouter un utilisateur'</b> en haut à droite."
        },
        {
            keywords: ["modifier", "utilisateur", "changer utilisateur", "editer utilisateur"],
            answer: "En tant qu'<b>administrateur</b>, rendez-vous dans la liste des utilisateurs. Repérez la ligne de l'utilisateur concerné et cliquez sur le bouton bleu <b>'Modifier'</b> dans la colonne des actions."
        },
        {
            keywords: ["supprimer", "utilisateur", "effacer", "retirer"],
            answer: "En tant qu'<b>administrateur</b>, cherchez l'utilisateur dans la liste et cliquez sur le bouton rouge <b>'Supprimer'</b>. Une fenêtre de confirmation apparaîtra avant la suppression définitive."
        },
        {
            keywords: ["bonjour", "salut", "hello", "coucou"],
            answer: "Bonjour ! 👋 Comment puis-je vous aider aujourd'hui sur SmartPlate ?"
        },
        {
            keywords: ["merci", "thanks"],
            answer: "De rien ! N'hésitez pas si vous avez d'autres questions. 😊"
        }
    ];

    // Toggle Chat
    chatbotToggle.addEventListener('click', () => {
        chatbotWindow.classList.add('open');
        chatbotToggle.style.transform = 'scale(0)';
        setTimeout(() => chatInput.focus(), 300);
    });

    chatbotClose.addEventListener('click', () => {
        chatbotWindow.classList.remove('open');
        chatbotToggle.style.transform = 'scale(1)';
    });

    function addMessage(text, sender) {
        const msgDiv = document.createElement('div');
        msgDiv.classList.add('chat-message', sender);
        msgDiv.innerHTML = text;
        chatbotMessages.appendChild(msgDiv);

        // Auto scroll to bottom
        setTimeout(() => {
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        }, 10);
    }

    function addTypingIndicator() {
        const id = 'typing-' + Date.now();
        const msgDiv = document.createElement('div');
        msgDiv.classList.add('chat-message', 'bot');
        msgDiv.id = id;
        msgDiv.innerHTML = '<span style="opacity:0.5;">Écriture en cours... ✍️</span>';
        chatbotMessages.appendChild(msgDiv);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        return id;
    }

    function removeTypingIndicator(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function addQuickReplies() {
        const repliesDiv = document.createElement('div');
        repliesDiv.classList.add('chat-quick-replies');

        const questions = [
            "Créer un compte",
            "Se connecter",
            "Mot de passe oublié",
            "Modifier mon profil",
            "Activité de connexion",
            "Ajouter un utilisateur",
            "Modifier un utilisateur",
            "Supprimer un utilisateur"
        ];

        questions.forEach(q => {
            const chip = document.createElement('button');
            chip.classList.add('chat-chip');
            chip.textContent = q;
            chip.onclick = () => {
                repliesDiv.style.opacity = '0';
                setTimeout(() => repliesDiv.remove(), 300);
                processUserMessage(q);
            };
            repliesDiv.appendChild(chip);
        });

        chatbotMessages.appendChild(repliesDiv);
        setTimeout(() => {
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        }, 10);
    }

    function getBotResponse(message) {
        const msg = message.toLowerCase();
        let bestMatch = null;
        let maxMatches = 0;

        faqData.forEach(item => {
            let matches = 0;
            item.keywords.forEach(keyword => {
                // Utiliser une expression régulière pour trouver le mot entier si possible
                if (msg.includes(keyword)) {
                    matches++;
                }
            });
            if (matches > maxMatches) {
                maxMatches = matches;
                bestMatch = item.answer;
            }
        });

        if (bestMatch) {
            return bestMatch;
        } else {
            return "Je ne suis pas sûr de comprendre. 🤖<br>Pouvez-vous reformuler ? Vous pouvez me demander comment :<br>- Créer un compte<br>- Se connecter<br>- Gérer les utilisateurs<br>- Modifier un profil";
        }
    }

    function processUserMessage(msg) {
        if (!msg.trim()) return;

        addMessage(msg, 'user');
        chatInput.value = '';

        // Simulate thinking and typing delay
        const typingId = addTypingIndicator();

        setTimeout(() => {
            removeTypingIndicator(typingId);
            const response = getBotResponse(msg);
            addMessage(response, 'bot');
        }, 800 + Math.random() * 500); // Délai réaliste entre 0.8s et 1.3s
    }

    chatSend.addEventListener('click', () => {
        processUserMessage(chatInput.value);
    });

    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            processUserMessage(chatInput.value);
        }
    });

    // Message de bienvenue initial
    setTimeout(() => {
        const typingId = addTypingIndicator();
        setTimeout(() => {
            removeTypingIndicator(typingId);
            addMessage("Bonjour ! Je suis l'assistant intelligent de SmartPlate. 🤖<br>Sélectionnez une question ci-dessous ou tapez votre demande :", 'bot');
            addQuickReplies();
        }, 600);
    }, 500);
});
