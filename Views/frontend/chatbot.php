<!-- Chargement des styles du Chatbot -->
<link rel="stylesheet" href="../css/chatbot.css">

<!-- Structure du Chatbot -->
<div class="chatbot-container">
    <div class="chatbot-window" id="chatbot-window">
        <div class="chatbot-header">
            <div class="chatbot-header-info">
                <div class="chatbot-avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"></path><rect x="4" y="8" width="16" height="12" rx="2"></rect><path d="M2 14h2"></path><path d="M20 14h2"></path><path d="M15 13v2"></path><path d="M9 13v2"></path></svg>
                </div>
                <div>
                    <h3 class="chatbot-title">Assistant SmartPlate</h3>
                    <div class="chatbot-status">En ligne pour vous aider</div>
                </div>
            </div>
            <button class="chatbot-close" id="chatbot-close" title="Fermer le chat">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        
        <div class="chatbot-messages" id="chatbot-messages">
            <!-- Les messages apparaîtront ici dynamiquement -->
        </div>
        
        <div class="chatbot-input">
            <input type="text" id="chat-input" placeholder="Posez votre question ici..." autocomplete="off">
            <button id="chat-send" title="Envoyer">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
            </button>
        </div>
    </div>

    <!-- Bouton flottant pour ouvrir le chat -->
    <div class="chatbot-toggle" id="chatbot-toggle" title="Besoin d'aide ?">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
    </div>
</div>

<!-- Chargement du script logique du Chatbot -->
<script src="../js/chatbot.js"></script>
