<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Smart Plate - Alimentation Durable</title>
    <link rel="stylesheet" href="css/Template-front.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-logo">
        <img src="assets/logo.png" alt="Smart Plate" style="width: 45px; height: 45px; border-radius: 10px;">
        <h2>Smart Plate</h2>
    </div>

    <div class="sidebar-nav">
        <span class="nav-section-title">Navigation</span>
        <a href="#" class="nav-item active">📖 Articles</a>
    </div>
</div>

<!-- Dashboard -->
<div class="dashboard">

    <!-- Header -->
    <div class="dashboard-header">
        <h1>Blog Alimentation Durable & Intelligente</h1>
        <p style="color: var(--text-gray); font-size: 0.9rem;">Lisez nos articles et partagez votre avis</p>
    </div>

    <!-- Liste des articles -->
    <div class="card">
        <div class="card-header">
            <h2>Derniers articles</h2>
        </div>

        <div id="articles-list">
            <p>Chargement des articles...</p>
        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    function escapeHtml(s){ return String(s).replace(/[&<>"]|'/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m]; }); }

    // Inline error message helper — replaces all alert() popups
    function showInlineError(containerId, message, duration) {
        duration = duration || 5000;
        let container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = '<div style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; border-left: 4px solid #e74c3c; padding: 10px 14px; border-radius: 8px; font-size: 0.9rem; margin-top: 8px; animation: slideIn 0.3s ease-out; display: flex; align-items: center; gap: 8px;"><span style="font-size: 1.1rem;">⚠️</span><span>' + escapeHtml(message) + '</span></div>';
        if (duration > 0) {
            setTimeout(function() {
                if (container) container.innerHTML = '';
            }, duration);
        }
    }

    function clearInlineError(containerId) {
        let container = document.getElementById(containerId);
        if (container) container.innerHTML = '';
    }

    // Fetch Articles
    fetch('index.php?controller=article&action=list')
        .then(r => r.json())
        .then(articles => {
            const container = document.getElementById('articles-list');
            if (articles.error) {
                container.innerHTML = '<p>Erreur: ' + escapeHtml(articles.error) + '</p>';
                return;
            }
            if (!Array.isArray(articles) || articles.length === 0) {
                container.innerHTML = '<p>Aucun article disponible.</p>';
                return;
            }
            container.innerHTML = '';
            
            articles.forEach(a => {
                const card = document.createElement('div');
                card.className = 'meal-card';
                card.style.marginBottom = '25px';
                const date = new Date(a.created_at);
                
                const typeTag = a.type ? `<span class="badge" style="background:#e8f8f5; color:#20c997;">${escapeHtml(a.type)}</span>` : '';
                const imageEl = a.image_url ? `<img src="${escapeHtml(a.image_url)}" alt="${escapeHtml(a.name)}" style="width:100%; height:200px; object-fit:cover; border-radius:8px; margin-bottom:15px;">` : '';

                card.innerHTML = `
                    <div class="meal-info" style="width: 100%;">
                        ${imageEl}
                        <div class="meal-tags">
                            <span class="badge green-light">Article</span>
                            ${typeTag}
                            <span class="time-info">Par ${escapeHtml(a.author||'Admin')} • ${date.toLocaleDateString()}</span>
                        </div>
                        <h3>${escapeHtml(a.name)}</h3>
                        <p style="margin-bottom:20px;">${escapeHtml(a.content)}</p>
                        
                        <!-- Comment Section -->
                        <div style="background: var(--bg-color); padding: 15px; border-radius: 8px; margin-top: 15px;">
                            <h4 style="margin-bottom: 10px; font-size: 1rem;">Commentaires</h4>
                            <div id="comments-${a.id}" style="margin-bottom: 15px;">
                                <p style="font-size: 0.9rem; color: #666;">Chargement des commentaires...</p>
                            </div>
                            
                            <!-- Comment Form -->
                            <form data-submit-comment="${a.id}" style="display: flex; flex-direction: column; gap: 10px;">
                                <div>
                                    <input type="text" id="username-${a.id}" placeholder="Votre nom" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: inherit; width: 100%;" data-clear-error="error-username-${a.id}">
                                    <div id="error-username-${a.id}"></div>
                                </div>
                                <div>
                                    <textarea id="comment-${a.id}" placeholder="Votre commentaire..." style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: inherit; resize: vertical; width: 100%;" rows="2" data-clear-error="error-comment-${a.id}"></textarea>
                                    <div id="error-comment-${a.id}"></div>
                                </div>
                                <button type="submit" class="btn-action" style="align-self: flex-start;">Publier</button>
                            </form>
                        </div>
                    </div>`;
                container.appendChild(card);
                
                // Load comments for this article
                loadComments(a.id);
            });
        })
        .catch(err => {
            const container = document.getElementById('articles-list');
            if (container) container.innerHTML = '<p>Erreur chargement articles.</p>';
            console.error(err);
        });

    // Make functions globally available
    window.loadComments = function(articleId) {
        // Fetch published comments (status = 1) - adding all=1 bypasses moderation for local testing
        fetch(`index.php?controller=comment&action=list&article_id=${articleId}&all=1`)
            .then(r => r.json())
            .then(comments => {
                const container = document.getElementById(`comments-${articleId}`);
                if (!Array.isArray(comments) || comments.length === 0) {
                    container.innerHTML = '<p style="font-size: 0.9rem; color: #888; font-style: italic;">Soyez le premier à commenter !</p>';
                    return;
                }
                container.innerHTML = '';
                comments.forEach(c => {
                    const cDate = new Date(c.created_at).toLocaleDateString();
                    const div = document.createElement('div');
                    div.id = `comment-item-${c.id}`;
                    div.style.cssText = 'border-bottom: 1px solid #eee; padding: 10px 0; margin-bottom: 8px; transition: background 0.2s ease; border-radius: 8px;';
                    // Mention if it is waiting for moderation
                    const modText = c.status == 0 ? '<span style="color: #e74c3c; font-size: 0.8rem; background: #fee2e2; padding: 2px 8px; border-radius: 10px;">(En attente de modération)</span>' : '';
                    div.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                            <div style="flex: 1;" id="comment-display-${c.id}">
                                <div style="font-size: 0.95rem; margin-bottom: 4px;">
                                    <strong>${escapeHtml(c.username)}</strong> 
                                    <span style="color: #888; font-size: 0.85rem;">• ${cDate}</span> ${modText}
                                </div>
                                <div style="font-size: 0.95rem; color: var(--text-dark, #333);">${escapeHtml(c.comment)}</div>
                            </div>
                            <div style="display: flex; gap: 6px; flex-shrink: 0; align-items: center;" id="comment-actions-${c.id}">
                                <button data-edit-comment="${c.id}" data-article="${articleId}" title="Modifier" 
                                    style="background: linear-gradient(135deg, #3498db, #2980b9); color: white; border: none; border-radius: 8px; padding: 6px 10px; cursor: pointer; font-size: 0.8rem; transition: all 0.2s ease; display: flex; align-items: center; gap: 4px;">
                                    ✏️ Modifier
                                </button>
                                <button data-delete-comment="${c.id}" data-article="${articleId}" title="Supprimer" 
                                    style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; border: none; border-radius: 8px; padding: 6px 10px; cursor: pointer; font-size: 0.8rem; transition: all 0.2s ease; display: flex; align-items: center; gap: 4px;">
                                    🗑️ Supprimer
                                </button>
                            </div>
                        </div>
                        <!-- Edit form (hidden by default) -->
                        <div id="comment-edit-form-${c.id}" style="display: none; margin-top: 10px; padding: 12px; background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-radius: 12px; border: 1px solid #e2e8f0;">
                            <div>
                                <textarea id="comment-edit-text-${c.id}" rows="3" data-clear-error="error-edit-${c.id}" style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-family: inherit; font-size: 0.95rem; resize: vertical; transition: border-color 0.2s ease;">${escapeHtml(c.comment)}</textarea>
                                <div id="error-edit-${c.id}"></div>
                            </div>
                            <div style="display: flex; gap: 8px; margin-top: 10px;">
                                <button data-save-comment="${c.id}" data-article="${articleId}" 
                                    style="background: linear-gradient(135deg, #2ecc71, #27ae60); color: white; border: none; border-radius: 8px; padding: 8px 16px; cursor: pointer; font-weight: 600; font-size: 0.85rem; transition: all 0.2s ease;">
                                    ✅ Enregistrer
                                </button>
                                <button data-cancel-comment="${c.id}" 
                                    style="background: linear-gradient(135deg, #95a5a6, #7f8c8d); color: white; border: none; border-radius: 8px; padding: 8px 16px; cursor: pointer; font-weight: 600; font-size: 0.85rem; transition: all 0.2s ease;">
                                    ❌ Annuler
                                </button>
                            </div>
                        </div>
                    `;
                    container.appendChild(div);
                });
            });
    };

    // Edit comment — show inline edit form
    window.editCommentFront = function(commentId, articleId) {
        // Hide display, show edit form
        const display = document.getElementById(`comment-display-${commentId}`);
        const actions = document.getElementById(`comment-actions-${commentId}`);
        const editForm = document.getElementById(`comment-edit-form-${commentId}`);
        if (display) display.style.display = 'none';
        if (actions) actions.style.display = 'none';
        if (editForm) {
            editForm.style.display = 'block';
            // Focus the textarea
            const textarea = document.getElementById(`comment-edit-text-${commentId}`);
            if (textarea) textarea.focus();
        }
    };

    // Save edited comment
    window.saveCommentEdit = function(commentId, articleId) {
        const textarea = document.getElementById(`comment-edit-text-${commentId}`);
        const newText = textarea ? textarea.value.trim() : '';

        if (!newText) {
            showInlineError('error-edit-' + commentId, 'Le commentaire ne peut pas être vide.');
            return;
        }
        clearInlineError('error-edit-' + commentId);

        const fd = new FormData();
        fd.append('action', 'update');
        fd.append('id', commentId);
        fd.append('comment', newText);

        fetch('index.php?controller=comment', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    window.loadComments(articleId);
                } else {
                    showInlineError('error-edit-' + commentId, res.error || 'Impossible de modifier le commentaire.');
                }
            })
            .catch(err => {
                console.error(err);
                showInlineError('error-edit-' + commentId, 'Erreur réseau. Veuillez réessayer.');
            });
    };

    // Cancel editing — restore display
    window.cancelCommentEdit = function(commentId) {
        const display = document.getElementById(`comment-display-${commentId}`);
        const actions = document.getElementById(`comment-actions-${commentId}`);
        const editForm = document.getElementById(`comment-edit-form-${commentId}`);
        if (display) display.style.display = 'block';
        if (actions) actions.style.display = 'flex';
        if (editForm) editForm.style.display = 'none';
    };

    // Delete comment
    window.deleteCommentFront = function(commentId, articleId) {
        if (!confirm('Voulez-vous vraiment supprimer ce commentaire ?')) return;

        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id', commentId);

        fetch('index.php?controller=comment', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    // Animate removal
                    const item = document.getElementById(`comment-item-${commentId}`);
                    if (item) {
                        item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        item.style.opacity = '0';
                        item.style.transform = 'translateX(-20px)';
                        setTimeout(() => window.loadComments(articleId), 300);
                    } else {
                        window.loadComments(articleId);
                    }
                } else {
                    showInlineError('error-comment-' + articleId, res.error || 'Impossible de supprimer le commentaire.');
                }
            })
            .catch(err => {
                console.error(err);
                showInlineError('error-comment-' + articleId, 'Erreur réseau. Veuillez réessayer.');
            });
    };

    window.submitComment = function(event, articleId) {
        event.preventDefault();
        const usernameInput = document.getElementById(`username-${articleId}`);
        const commentInput = document.getElementById(`comment-${articleId}`);

        var hasError = false;
        clearInlineError('error-username-' + articleId);
        clearInlineError('error-comment-' + articleId);

        if (!usernameInput.value.trim()) {
            showInlineError('error-username-' + articleId, 'Le nom est obligatoire.', 0);
            hasError = true;
        }
        if (!commentInput.value.trim()) {
            showInlineError('error-comment-' + articleId, 'Le commentaire est obligatoire.', 0);
            hasError = true;
        }
        if (hasError) return;
        
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('article_id', articleId);
        formData.append('username', usernameInput.value);
        formData.append('comment', commentInput.value);
        // By default status is 0 (waiting for moderation). We can set to 1 here to auto-publish if you want.
        formData.append('status', 1); 
        
        fetch('index.php?controller=comment', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                usernameInput.value = '';
                commentInput.value = '';
                // Reload comments to show the new one
                window.loadComments(articleId);
            } else {
                showInlineError('error-comment-' + articleId, res.error || 'Erreur lors de l\'ajout du commentaire.');
            }
        })
        .catch(err => {
            console.error(err);
            showInlineError('error-comment-' + articleId, 'Erreur réseau. Veuillez réessayer.');
        });
    };

    // ===== EVENT DELEGATION (pas de handlers inline dans le HTML) =====
    var articlesContainer = document.getElementById('articles-list');
    if (articlesContainer) {
        // input/textarea: clear error on typing
        articlesContainer.addEventListener('input', function(e) {
            var errId = e.target.getAttribute('data-clear-error');
            if (errId) clearInlineError(errId);
        });
        // focus: style textarea border
        articlesContainer.addEventListener('focusin', function(e) {
            if (e.target.tagName === 'TEXTAREA' && e.target.hasAttribute('data-clear-error')) {
                e.target.style.borderColor = '#3498db';
                e.target.style.boxShadow = '0 0 0 3px rgba(52,152,219,0.1)';
            }
        });
        articlesContainer.addEventListener('focusout', function(e) {
            if (e.target.tagName === 'TEXTAREA' && e.target.hasAttribute('data-clear-error')) {
                e.target.style.borderColor = '#e2e8f0';
                e.target.style.boxShadow = 'none';
            }
        });
        // click delegation for buttons
        articlesContainer.addEventListener('click', function(e) {
            var btn = e.target.closest('button');
            if (!btn) return;
            var editId = btn.getAttribute('data-edit-comment');
            var deleteId = btn.getAttribute('data-delete-comment');
            var saveId = btn.getAttribute('data-save-comment');
            var cancelId = btn.getAttribute('data-cancel-comment');
            var artId = btn.getAttribute('data-article');
            if (editId) editCommentFront(parseInt(editId), parseInt(artId));
            else if (deleteId) deleteCommentFront(parseInt(deleteId), parseInt(artId));
            else if (saveId) saveCommentEdit(parseInt(saveId), parseInt(artId));
            else if (cancelId) cancelCommentEdit(parseInt(cancelId));
        });
        // form submit delegation
        articlesContainer.addEventListener('submit', function(e) {
            var form = e.target.closest('form[data-submit-comment]');
            if (form) {
                e.preventDefault();
                var artId = parseInt(form.getAttribute('data-submit-comment'));
                submitComment(e, artId);
            }
        });
        // hover effects for action buttons
        articlesContainer.addEventListener('mouseover', function(e) {
            var btn = e.target.closest('button[data-edit-comment], button[data-delete-comment], button[data-save-comment]');
            if (btn) { btn.style.transform = 'translateY(-2px)'; btn.style.boxShadow = '0 4px 12px rgba(0,0,0,0.2)'; }
        });
        articlesContainer.addEventListener('mouseout', function(e) {
            var btn = e.target.closest('button[data-edit-comment], button[data-delete-comment], button[data-save-comment]');
            if (btn) { btn.style.transform = 'none'; btn.style.boxShadow = 'none'; }
        });
    }
});
</script>

</body>
</html>