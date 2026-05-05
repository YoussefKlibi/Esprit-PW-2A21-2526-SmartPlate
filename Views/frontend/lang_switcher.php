<!-- ════════════════════════════════════════════════
     Language Switcher Widget  (include in every page)
     Usage: <?php include __DIR__ . '/lang_switcher.php'; ?>
════════════════════════════════════════════════ -->
<div class="lang-switcher-wrap" id="langSwitcherWrap">
    <button type="button" class="lang-trigger" id="langTrigger" aria-haspopup="listbox" aria-expanded="false" title="Change language">
        <!-- Globe icon -->
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="2" y1="12" x2="22" y2="12"/>
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
        </svg>
        <span id="langLabel">FR</span>
        <span class="lang-caret">▼</span>
    </button>

    <div class="lang-dropdown" id="langDropdown" role="listbox">
        <button class="lang-option lang-btn" data-lang="fr" onclick="changeLanguage('fr')" role="option">
            <span class="lang-flag">🇫🇷</span> Français
        </button>
        <button class="lang-option lang-btn" data-lang="en" onclick="changeLanguage('en')" role="option">
            <span class="lang-flag">🇬🇧</span> English
        </button>
        <button class="lang-option lang-btn" data-lang="ar" onclick="changeLanguage('ar')" role="option">
            <span class="lang-flag">🇩🇿</span> العربية
        </button>
    </div>
</div>

<script>
(function () {
    const trigger  = document.getElementById('langTrigger');
    const dropdown = document.getElementById('langDropdown');
    const label    = document.getElementById('langLabel');
    const flagMap  = { fr: '🇫🇷 FR', en: '🇬🇧 EN', ar: '🇩🇿 AR' };

    // Sync label with current lang
    function syncLabel() {
        const lang = localStorage.getItem('smartplate_lang') || 'fr';
        label.textContent = flagMap[lang] || 'FR';
    }
    syncLabel();

    // Toggle dropdown
    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = dropdown.classList.toggle('open');
        trigger.setAttribute('aria-expanded', isOpen);
        const caret = trigger.querySelector('.lang-caret');
        caret.style.transform = isOpen ? 'rotate(180deg)' : '';
    });

    // Close on outside click
    document.addEventListener('click', () => {
        dropdown.classList.remove('open');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.querySelector('.lang-caret').style.transform = '';
    });

    // After changeLanguage is called, sync label
    const _orig = window.changeLanguage;
    window.changeLanguage = function(lang) {
        if (_orig) _orig(lang);
        syncLabel();
        dropdown.classList.remove('open');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.querySelector('.lang-caret').style.transform = '';
    };
})();
</script>
