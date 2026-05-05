<div id="customConfirmOverlay" class="custom-confirm-overlay"></div>
<div id="customConfirmModal" class="custom-confirm-modal">
    <div id="customConfirmIcon" class="custom-confirm-icon">🗑️</div>
    <h3 id="customConfirmTitle" class="custom-confirm-title">Titre</h3>
    <p id="customConfirmDesc" class="custom-confirm-desc">Description</p>
    <div class="custom-confirm-actions">
        <button id="customConfirmCancelBtn" class="custom-confirm-btn custom-confirm-btn-cancel">Annuler</button>
        <button id="customConfirmActionBtn" class="custom-confirm-btn custom-confirm-btn-danger">Confirmer</button>
    </div>
</div>

<script>
let confirmCallback = null;

function showCustomConfirm(title, desc, icon, actionText, actionColor, callback) {
    document.getElementById('customConfirmTitle').innerText = title;
    document.getElementById('customConfirmDesc').innerText = desc;
    document.getElementById('customConfirmIcon').innerText = icon || '🗑️';
    
    const actionBtn = document.getElementById('customConfirmActionBtn');
    actionBtn.innerText = actionText || 'Confirmer';
    
    if (actionColor === 'red') {
        actionBtn.style.background = '#e74c3c';
        document.getElementById('customConfirmIcon').style.color = '#e74c3c';
        document.getElementById('customConfirmIcon').style.background = 'rgba(231,76,60,0.1)';
    } else if (actionColor === 'orange') {
        actionBtn.style.background = '#f39c12';
        document.getElementById('customConfirmIcon').style.color = '#d68910';
        document.getElementById('customConfirmIcon').style.background = 'rgba(243,156,18,0.1)';
    } else if (actionColor === 'blue') {
        actionBtn.style.background = '#3498db';
        document.getElementById('customConfirmIcon').style.color = '#2980b9';
        document.getElementById('customConfirmIcon').style.background = 'rgba(52,152,219,0.1)';
    } else {
        actionBtn.style.background = actionColor;
    }

    confirmCallback = callback;
    
    const overlay = document.getElementById('customConfirmOverlay');
    const modal = document.getElementById('customConfirmModal');
    
    overlay.style.display = 'block';
    modal.style.display = 'block';
    
    // Trigger animation
    setTimeout(() => {
        overlay.style.opacity = '1';
        modal.classList.add('show');
    }, 10);
}

function closeCustomConfirm() {
    const overlay = document.getElementById('customConfirmOverlay');
    const modal = document.getElementById('customConfirmModal');
    
    overlay.style.opacity = '0';
    modal.classList.remove('show');
    
    setTimeout(() => {
        overlay.style.display = 'none';
        modal.style.display = 'none';
    }, 300);
}

document.getElementById('customConfirmCancelBtn').addEventListener('click', closeCustomConfirm);
document.getElementById('customConfirmOverlay').addEventListener('click', closeCustomConfirm);
document.getElementById('customConfirmActionBtn').addEventListener('click', () => {
    if (confirmCallback) {
        confirmCallback();
    }
    closeCustomConfirm();
});
</script>
