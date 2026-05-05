<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Smart Plate - Alimentation Durable</title>
    <link rel="stylesheet" href="css/Template-front.css?v=<?php echo time(); ?>">
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
    <div class="forum-hero">
        <div class="forum-hero-bg"></div>
        <div class="forum-hero-content">
            <div class="forum-hero-icon">💬</div>
            <h1 class="forum-hero-title">Forum</h1>
            <p class="forum-hero-subtitle">Échangez, débattez et partagez vos idées sur l'alimentation durable</p>
            <div class="forum-hero-stats">
                <span class="forum-stat-pill">🔥 Discussions actives</span>
                <span class="forum-stat-pill">🌍 Communauté engagée</span>
                <span class="forum-stat-pill live-pulse">⚡ En direct</span>
            </div>
        </div>
    </div>

    <!-- Liste des articles -->
    <div class="card">
        <div class="card-header">
            <h2>📋 Discussions Récentes</h2>
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
                const imageEl = a.image_url ? `<img src="${escapeHtml(a.image_url)}" alt="${escapeHtml(a.name)}" style="width:100%; height:250px; object-fit:contain; border-radius:8px; margin-bottom:15px; background: rgba(0,0,0,0.05);">` : '';

                card.innerHTML = `
                    <div class="meal-info" style="width: 100%;">
                        ${imageEl}
                        <div class="meal-tags">
                            <span class="badge green-light">Article</span>
                            ${typeTag}
                            <span class="time-info">Par ${escapeHtml(a.author||'Admin')} • ${date.toLocaleDateString()}</span>
                        </div>
                        <h3>${escapeHtml(a.name)}</h3>
                        
                        <!-- Star Rating Section -->
                        ${(() => {
                            const avg = a.rating_count > 0 ? (a.rating_sum / a.rating_count).toFixed(1) : '0.0';
                            return `
                            <div class="article-rating" style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                                <div class="stars-container" id="stars-container-${a.id}" style="font-size: 1.5rem; color: #ddd; cursor: pointer;">
                                    <span data-star="1" data-article="${a.id}">★</span>
                                    <span data-star="2" data-article="${a.id}">★</span>
                                    <span data-star="3" data-article="${a.id}">★</span>
                                    <span data-star="4" data-article="${a.id}">★</span>
                                    <span data-star="5" data-article="${a.id}">★</span>
                                </div>
                                <span id="rating-text-${a.id}" style="font-size: 0.9rem; color: #666; font-weight: bold;">
                                    ${avg}/5 (${a.rating_count} votes)
                                </span>
                            </div>`;
                        })()}

                        <p style="margin-bottom:20px;">${escapeHtml(a.content)}</p>
                        
                        <!-- Comment Section -->
                        <div class="glass-container" style="padding: 25px; border-radius: 16px; margin-top: 25px;">
                            <h4 style="margin-bottom: 20px; font-size: 1.2rem; display: flex; align-items: center; gap: 8px;">
                                Commentaires
                            </h4>
                            <div id="comments-${a.id}" style="margin-bottom: 25px;">
                                <p style="font-size: 0.95rem; color: #666;">Chargement des commentaires...</p>
                            </div>
                            
                            <!-- Comment Form -->
                            <form data-submit-comment="${a.id}" class="glass-container" style="display: flex; flex-direction: column; gap: 15px; padding: 20px; border-radius: 12px;">
                                <h5 style="margin: 0 0 5px 0; font-size: 1rem; color: var(--text-dark);">Laisser un commentaire</h5>
                                
                                <div>
                                    <textarea id="comment-${a.id}" class="modern-input" placeholder="Ajouter un commentaire..." rows="1" onfocus="expandCommentForm(${a.id})" data-clear-error="error-comment-${a.id}" style="resize: vertical; transition: all 0.3s ease;"></textarea>
                                    <div id="error-comment-${a.id}" style="color: #e74c3c; font-size: 0.85rem; margin-top: 4px;"></div>
                                </div>

                                <div id="comment-extra-${a.id}" style="display: none; flex-direction: column; gap: 15px; animation: fadeInForm 0.3s ease forwards;">
                                    <div>
                                        <input type="text" id="username-${a.id}" class="modern-input" placeholder="Votre nom" data-clear-error="error-username-${a.id}">
                                        <div id="error-username-${a.id}" style="color: #e74c3c; font-size: 0.85rem; margin-top: 4px;"></div>
                                    </div>
                                    <div>
                                        <label style="font-size: 0.9rem; font-weight: 600; margin-bottom: 8px; display: block; color: var(--text-dark);">Humeur :</label>
                                        <div class="emoji-picker-container" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                            <input type="radio" name="emoji-${a.id}" id="emoji-1-${a.id}" value="😀" class="emoji-radio" style="display: none;" checked>
                                            <label for="emoji-1-${a.id}" class="emoji-label">😀</label>
                                            
                                            <input type="radio" name="emoji-${a.id}" id="emoji-2-${a.id}" value="🔥" class="emoji-radio" style="display: none;">
                                            <label for="emoji-2-${a.id}" class="emoji-label">🔥</label>
                                            
                                            <input type="radio" name="emoji-${a.id}" id="emoji-3-${a.id}" value="💡" class="emoji-radio" style="display: none;">
                                            <label for="emoji-3-${a.id}" class="emoji-label">💡</label>
                                            
                                            <input type="radio" name="emoji-${a.id}" id="emoji-4-${a.id}" value="🤔" class="emoji-radio" style="display: none;">
                                            <label for="emoji-4-${a.id}" class="emoji-label">🤔</label>
                                            
                                            <input type="radio" name="emoji-${a.id}" id="emoji-5-${a.id}" value="👎" class="emoji-radio" style="display: none;">
                                            <label for="emoji-5-${a.id}" class="emoji-label">👎</label>
                                        </div>
                                    </div>
                                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 5px;">
                                        <button type="button" onclick="collapseCommentForm(${a.id})" class="btn-text">
                                            Annuler
                                        </button>
                                        <button type="submit" class="btn-main">
                                            ✨ Publier
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>`;
                container.appendChild(card);
                
                // Load comments for this article
                loadComments(a.id);
            });

            // Repasse les commentaires (purge toxiques côté serveur après 1 min + mise à jour affichage)
            const articleIds = articles.map(function(a) { return a.id; });
            if (window.__commentToxicPoll) {
                clearInterval(window.__commentToxicPoll);
            }
            window.__commentToxicPoll = setInterval(function() {
                articleIds.forEach(function(id) {
                    if (typeof window.loadComments === 'function') {
                        window.loadComments(id);
                    }
                });
            }, 20000);
        })
        .catch(err => {
            const container = document.getElementById('articles-list');
            if (container) container.innerHTML = '<p>Erreur chargement articles.</p>';
            console.error(err);
        });

    // Make functions globally available
    window.loadComments = function(articleId) {
        // Fetch published comments (status = 1) - adding all=1 bypasses moderation for local testing
        fetch(`index.php?controller=comment&action=list&article_id=${articleId}&all=1&public_mask=1`)
            .then(r => r.json())
            .then(comments => {
                const container = document.getElementById(`comments-${articleId}`);
                if (!Array.isArray(comments) || comments.length === 0) {
                    container.innerHTML = '<p style="font-size: 0.9rem; color: #888; font-style: italic;">Soyez le premier à commenter !</p>';
                    return;
                }
                container.innerHTML = '';
                
                // Group comments by parent_id
                const parents = [];
                const replies = {};
                comments.forEach(c => {
                    if (c.parent_id) {
                        if (!replies[c.parent_id]) replies[c.parent_id] = [];
                        replies[c.parent_id].push(c);
                    } else {
                        parents.push(c);
                    }
                });

                function renderComment(c, isReply = false) {
                    const cDate = new Date(c.created_at).toLocaleDateString();
                    const div = document.createElement('div');
                    div.id = `comment-item-${c.id}`;
                    div.className = 'glass-container';
                    const paddingStr = isReply ? '12px 18px' : '18px';
                    const marginStr = isReply ? '10px 0 0 0' : '15px 0';
                    div.style.cssText = `padding: ${paddingStr}; margin: ${marginStr}; transition: all 0.3s ease; border-radius: 12px;`;
                    
                    const modText = c.status == 0 ? '<span style="color: #e74c3c; font-size: 0.8rem; background: #fee2e2; padding: 2px 8px; border-radius: 10px; margin-left: 8px;">(En attente)</span>' : '';
                    const badgeText = c.badge ? `<span style="background: linear-gradient(135deg, #9b59b6, #8e44ad); color: white; font-size: 0.75rem; padding: 3px 8px; border-radius: 12px; margin-left: 8px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">🎖️ ${escapeHtml(c.badge)}</span>` : '';
                    const toxicNote = c.toxic_masked ? '<span style="color: #b45309; font-size: 0.78rem; margin-left: 8px;">(Contenu masqué)</span>' : '';
                    const editStyle = c.toxic_masked ? 'display:none;' : '';

                    const replyBtn = !isReply ? `
                        <button onclick="toggleReplyForm(${c.id})" class="vote-btn btn-reply-front" style="font-weight: bold; margin-left: auto;">
                            💬 Répondre
                        </button>` : '';

                    div.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                            <div style="flex: 1;" id="comment-display-${c.id}">
                                <div style="font-size: 0.95rem; margin-bottom: 8px; display: flex; align-items: center;">
                                    <div class="glass-container" style="border-radius: 50%; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-right: 12px; flex-shrink: 0; box-shadow: inset 0 2px 4px rgba(255,255,255,0.1);">
                                        ${c.emoji ? escapeHtml(c.emoji) : '👤'}
                                    </div>
                                    <div>
                                        <strong style="font-size: 1rem; color: var(--text-dark);">${escapeHtml(c.username)}</strong> ${badgeText}
                                        <span style="color: #888; font-size: 0.85rem; margin-left: 6px;">• ${cDate}</span> ${modText} ${toxicNote}
                                    </div>
                                </div>
                                <div style="font-size: 1rem; color: var(--text-dark, #444); line-height: 1.5; padding-left: 42px;">
                                    ${escapeHtml(c.comment)}
                                </div>
                                <div style="margin-top: 15px; margin-left: 42px; display: flex; gap: 8px; border-top: 1px solid var(--border-color); padding-top: 12px; flex-wrap: wrap;">
                                    <button data-vote-comment="${c.id}" data-vote-type="agree" class="vote-btn btn-like">
                                        👍 Like (<span id="agree-count-${c.id}">${c.agree_count || 0}</span>)
                                    </button>
                                    <button data-vote-comment="${c.id}" data-vote-type="disagree" class="vote-btn btn-dislike">
                                        👎 Dislike (<span id="disagree-count-${c.id}">${c.disagree_count || 0}</span>)
                                    </button>
                                    <button onclick="reportCommentFront(${c.id})" id="btn-report-${c.id}" class="vote-btn btn-report-front">
                                        🚨 Signaler
                                    </button>
                                    ${replyBtn}
                                </div>
                            </div>
                            <div style="display: flex; gap: 6px; flex-shrink: 0; align-items: center;" id="comment-actions-${c.id}">
                                <button data-edit-comment="${c.id}" data-article="${articleId}" title="Modifier" class="btn-action" style="padding: 0.65rem 0.9rem; font-size: 0.8rem; display: flex; align-items: center; gap: 0.4rem; ${editStyle}">
                                    ✏️ Modifier
                                </button>
                                <button data-delete-comment="${c.id}" data-article="${articleId}" title="Supprimer" class="btn-danger" style="padding: 0.65rem 0.9rem; font-size: 0.8rem; display: flex; align-items: center; gap: 0.4rem;">
                                    🗑️ Supprimer
                                </button>
                            </div>
                        </div>

                        <!-- Formulaire de réponse (caché) -->
                        <div id="reply-form-${c.id}" style="display: none; margin-top: 15px; margin-left: 42px; padding: 15px; background: rgba(255,255,255,0.4); border-radius: 12px; border-left: 3px solid #4facfe;">
                            <form data-submit-reply="${articleId}" data-parent-id="${c.id}" style="display: flex; flex-direction: column; gap: 10px;">
                                <input type="text" id="reply-username-${c.id}" class="modern-input" placeholder="Votre nom" style="padding: 8px; font-size: 0.9rem;" data-clear-error="error-reply-username-${c.id}">
                                <div id="error-reply-username-${c.id}" style="color: #e74c3c; font-size: 0.85rem; margin-top: 2px;"></div>
                                <textarea id="reply-text-${c.id}" class="modern-input" placeholder="Votre réponse..." rows="2" style="padding: 8px; font-size: 0.9rem; resize: vertical;" data-clear-error="error-reply-text-${c.id}"></textarea>
                                <div id="error-reply-text-${c.id}" style="color: #e74c3c; font-size: 0.85rem; margin-top: 2px;"></div>
                                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <button type="button" onclick="toggleReplyForm(${c.id})" class="btn-text">
                                        Annuler
                                    </button>
                                    <button type="submit" class="btn-main">
                                        Publier
                                    </button>
                                </div>
                            </form>
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
                    return div;
                }

                parents.forEach(p => {
                    const parentDiv = renderComment(p, false);
                    container.appendChild(parentDiv);
                    
                    const childReplies = replies[p.id];
                    if (childReplies && childReplies.length > 0) {
                        const repliesContainer = document.createElement('div');
                        // Ligne courbée youtube style: border-left, padding-left
                        repliesContainer.style.cssText = 'margin-left: 42px; border-left: 2px solid rgba(0,0,0,0.1); padding-left: 15px; margin-top: -5px; margin-bottom: 20px; border-bottom-left-radius: 15px;';
                        
                        const toggleBtn = document.createElement('button');
                        toggleBtn.className = 'vote-btn';
                        toggleBtn.style.cssText = 'color: #3b82f6; font-weight: bold; padding: 5px 10px; margin-bottom: 10px; display: inline-flex; align-items: center; gap: 5px;';
                        toggleBtn.innerHTML = `<span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="transition: transform 0.3s;"><polyline points="6 9 12 15 18 9"></polyline></svg></span> ${childReplies.length} réponse${childReplies.length > 1 ? 's' : ''}`;
                        
                        const repliesWrapper = document.createElement('div');
                        repliesWrapper.style.display = 'none'; // Hidden by default
                        
                        // Reverse so oldest reply is at the top (usually DESC from DB, so we reverse to ASC)
                        childReplies.slice().reverse().forEach(r => {
                            repliesWrapper.appendChild(renderComment(r, true));
                        });
                        
                        toggleBtn.onclick = () => {
                            const isHidden = repliesWrapper.style.display === 'none';
                            repliesWrapper.style.display = isHidden ? 'block' : 'none';
                            const svg = toggleBtn.querySelector('svg');
                            if (svg) svg.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
                        };
                        
                        repliesContainer.appendChild(toggleBtn);
                        repliesContainer.appendChild(repliesWrapper);
                        container.appendChild(repliesContainer);
                    }
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

    window.collapseCommentForm = function(id) {
        const extra = document.getElementById('comment-extra-' + id);
        const textarea = document.getElementById('comment-' + id);
        if (extra) {
            extra.style.display = 'none';
            textarea.rows = 1;
            textarea.value = '';
            document.getElementById('username-' + id).value = '';
        }
    };

    window.expandCommentForm = function(id) {
        const extra = document.getElementById('comment-extra-' + id);
        const textarea = document.getElementById('comment-' + id);
        if (extra && extra.style.display === 'none') {
            extra.style.display = 'flex';
            textarea.rows = 3;
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
        if (!/[a-zA-Z\u00C0-\u017F]/.test(newText)) {
            showInlineError('error-edit-' + commentId, 'Le commentaire doit contenir au moins une lettre.');
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
    window.commentIsSubmitting = false;

    // Unified Comment/Reply Submission Handler
    document.addEventListener('submit', function(e) {
        // Main Comment
        if (e.target.hasAttribute('data-submit-comment')) {
            e.preventDefault();
            if (window.commentIsSubmitting) return;

            const articleId = e.target.getAttribute('data-submit-comment');
            const usernameInput = document.getElementById('username-' + articleId);
            const commentInput = document.getElementById('comment-' + articleId);
            const emojiInput = document.querySelector(`input[name="emoji-${articleId}"]:checked`);
            
            const username = usernameInput ? usernameInput.value.trim() : '';
            const comment = commentInput ? commentInput.value.trim() : '';
            const emoji = emojiInput ? emojiInput.value : '';

            let hasError = false;
            if (!username) {
                showInlineError('error-username-' + articleId, 'Veuillez saisir votre nom.');
                hasError = true;
            } else if (!/[a-zA-Z\u00C0-\u017F]/.test(username)) {
                showInlineError('error-username-' + articleId, 'Le nom doit contenir au moins une lettre.');
                hasError = true;
            } else if (username.length > 20) {
                showInlineError('error-username-' + articleId, 'Le nom ne doit pas dépasser 20 caractères.');
                hasError = true;
            } else {
                clearInlineError('error-username-' + articleId);
            }

            if (!comment) {
                showInlineError('error-comment-' + articleId, 'Le commentaire ne peut pas être vide.');
                hasError = true;
            } else if (!/[a-zA-Z\u00C0-\u017F]/.test(comment)) {
                showInlineError('error-comment-' + articleId, 'Le commentaire doit contenir au moins une lettre.');
                hasError = true;
            } else {
                clearInlineError('error-comment-' + articleId);
            }

            if (hasError) return;

            window.commentIsSubmitting = true;
            const fd = new FormData();
            fd.append('action', 'create');
            fd.append('article_id', articleId);
            fd.append('username', username);
            fd.append('comment', comment);
            fd.append('status', 1);
            if (emoji) fd.append('emoji', emoji);

            fetch('index.php?controller=comment', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    if (res.error) {
                        showInlineError('error-comment-' + articleId, res.error);
                    } else {
                        collapseCommentForm(articleId);
                        window.loadComments(articleId);
                        customAlert('Votre commentaire a été publié avec succès.', 'Succès', '✅');
                    }
                })
                .catch(err => console.error(err))
                .finally(() => { window.commentIsSubmitting = false; });
        }

        // Reply to Comment
        else if (e.target.hasAttribute('data-submit-reply')) {
            e.preventDefault();
            if (window.commentIsSubmitting) return;

            const articleId = e.target.getAttribute('data-submit-reply');
            const parentId = e.target.getAttribute('data-parent-id');
            
            const usernameInput = document.getElementById('reply-username-' + parentId);
            const commentInput = document.getElementById('reply-text-' + parentId);
            
            const username = usernameInput ? usernameInput.value.trim() : '';
            const comment = commentInput ? commentInput.value.trim() : '';

            let hasError = false;
            if (!username) {
                showInlineError('error-reply-username-' + parentId, 'Veuillez saisir votre nom.');
                hasError = true;
            } else if (!/[a-zA-Z\u00C0-\u017F]/.test(username)) {
                showInlineError('error-reply-username-' + parentId, 'Le nom doit contenir au moins une lettre.');
                hasError = true;
            } else if (username.length > 20) {
                showInlineError('error-reply-username-' + parentId, 'Le nom ne doit pas dépasser 20 caractères.');
                hasError = true;
            } else {
                clearInlineError('error-reply-username-' + parentId);
            }

            if (!comment) {
                showInlineError('error-reply-text-' + parentId, 'La réponse ne peut pas être vide.');
                hasError = true;
            } else if (!/[a-zA-Z\u00C0-\u017F]/.test(comment)) {
                showInlineError('error-reply-text-' + parentId, 'La réponse doit contenir au moins une lettre.');
                hasError = true;
            } else {
                clearInlineError('error-reply-text-' + parentId);
            }

            if (hasError) return;

            window.commentIsSubmitting = true;
            const fd = new FormData();
            fd.append('action', 'create');
            fd.append('article_id', articleId);
            fd.append('parent_id', parentId);
            fd.append('username', username);
            fd.append('comment', comment);
            fd.append('status', 1);

            fetch('index.php?controller=comment', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    if (res.error) {
                        customAlert(res.error, 'Erreur', '❌');
                    } else {
                        window.loadComments(articleId);
                    }
                })
                .catch(err => console.error(err))
                .finally(() => { window.commentIsSubmitting = false; });
        }
    });

    // Toggle reply form
    window.toggleReplyForm = function(commentId) {
        const form = document.getElementById(`reply-form-${commentId}`);
        if (form) {
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
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
        customConfirm('Voulez-vous vraiment supprimer ce commentaire ?', 'Suppression', '🗑️').then(confirmed => {
            if (!confirmed) return;

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
        });
    };

    // Vote on a comment
    window.voteCommentFront = function(commentId, voteType) {
        const votedKey = `vote_comment_${commentId}`;
        const previousVote = localStorage.getItem(votedKey);
        
        if (previousVote === voteType) {
            customAlert('Vous avez déjà donné ce vote pour ce commentaire.', 'Déjà voté', '⚠️');
            return;
        }

        const fd = new FormData();
        fd.append('action', 'vote');
        fd.append('id', commentId);
        fd.append('type', voteType);
        if (previousVote) {
            fd.append('oldType', previousVote);
        }

        fetch('index.php?controller=comment', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    localStorage.setItem(votedKey, voteType);
                    
                    // Increment new vote counter
                    const newCountSpan = document.getElementById(`${voteType}-count-${commentId}`);
                    if (newCountSpan) {
                        newCountSpan.innerText = parseInt(newCountSpan.innerText) + 1;
                    }

                    // Decrement old vote counter if it existed
                    if (previousVote) {
                        const oldCountSpan = document.getElementById(`${previousVote}-count-${commentId}`);
                        if (oldCountSpan) {
                            oldCountSpan.innerText = Math.max(0, parseInt(oldCountSpan.innerText) - 1);
                        }
                    }
                }
            })
            .catch(err => console.error(err));
    };

    // Report a comment
    window.reportCommentFront = function(commentId) {
        const reportKey = `report_comment_${commentId}`;
        if (localStorage.getItem(reportKey)) {
            customAlert('Vous avez déjà signalé ce commentaire.', 'Déjà signalé', '⚠️');
            return;
        }

        customConfirm('Voulez-vous vraiment signaler ce commentaire pour contenu inapproprié ?', 'Signalement', '🚨').then(confirmed => {
            if (!confirmed) return;

            const fd = new FormData();
            fd.append('action', 'report');
            fd.append('id', commentId);

            fetch('index.php?controller=comment', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        localStorage.setItem(reportKey, '1');
                        customAlert('Le commentaire a été signalé à la modération. Merci.', 'Signalé', '✅');
                        const btn = document.getElementById(`btn-report-${commentId}`);
                        if (btn) {
                            btn.innerText = '✅ Signalé';
                            btn.style.opacity = '0.6';
                            btn.style.pointerEvents = 'none';
                        }
                    } else {
                        customAlert('Erreur lors du signalement.', 'Erreur', '❌');
                    }
                })
                .catch(err => console.error(err));
        });
    };

    // Rate Article
    window.rateArticleFront = function(articleId, stars) {
        const fd = new FormData();
        fd.append('action', 'rate');
        fd.append('id', articleId);
        fd.append('stars', stars);

        fetch('index.php?controller=article', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    const container = document.getElementById(`stars-container-${articleId}`);
                    const text = document.getElementById(`rating-text-${articleId}`);
                    if (container && text) {
                        const avg = (res.rating_sum / res.rating_count).toFixed(1);
                        text.innerText = `${avg}/5 (${res.rating_count} votes)`;
                        container.style.pointerEvents = 'none'; // Lock voting
                        container.style.opacity = '0.7';
                        // Color the stars up to the voted amount
                        Array.from(container.children).forEach((star, index) => {
                            star.style.color = index < stars ? '#f1c40f' : '#ddd';
                        });
                    }
                }
            })
            .catch(err => console.error(err));
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

        
        // Click delegation for votes
        articlesContainer.addEventListener('click', function(e) {
            var voteBtn = e.target.closest('button[data-vote-comment]');
            if (voteBtn) {
                e.preventDefault();
                var cId = voteBtn.getAttribute('data-vote-comment');
                var vType = voteBtn.getAttribute('data-vote-type');
                voteCommentFront(cId, vType);
                // Simple feedback effect
                voteBtn.style.transform = 'scale(1.1)';
                setTimeout(() => voteBtn.style.transform = 'scale(1)', 200);
            }
        });

        // Click delegation for star rating
        articlesContainer.addEventListener('click', function(e) {
            if (e.target.hasAttribute('data-star')) {
                const starVal = parseInt(e.target.getAttribute('data-star'));
                const artId = e.target.getAttribute('data-article');
                rateArticleFront(artId, starVal);
            }
        });

        // Hover effect for stars
        articlesContainer.addEventListener('mouseover', function(e) {
            if (e.target.hasAttribute('data-star')) {
                const container = e.target.parentElement;
                if (container.style.pointerEvents === 'none') return; // already voted
                const hoverVal = parseInt(e.target.getAttribute('data-star'));
                Array.from(container.children).forEach((star, index) => {
                    star.style.color = index < hoverVal ? '#f1c40f' : '#ddd';
                });
            }
        });
        articlesContainer.addEventListener('mouseout', function(e) {
            if (e.target.hasAttribute('data-star')) {
                const container = e.target.parentElement;
                if (container.style.pointerEvents === 'none') return; // already voted
                // Reset to gray
                Array.from(container.children).forEach(star => {
                    star.style.color = '#ddd';
                });
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

    // ===== FRONT VIEW SWITCHING =====
    window.switchFrontView = function(view) {
        // Hide all views
        document.getElementById('fo-view-articles').style.display = 'none';
        document.getElementById('fo-view-collaborations').style.display = 'none';
        document.getElementById('fo-view-team').style.display = 'none';

        // Remove active from all nav items
        ['nav-fo-articles','nav-fo-collaborations','nav-fo-team'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.remove('active');
        });

        if (view === 'articles') {
            document.getElementById('fo-view-articles').style.display = 'block';
            document.getElementById('nav-fo-articles').classList.add('active');
            document.getElementById('fo-page-title').textContent = 'Forum';
            document.getElementById('fo-page-subtitle').textContent = 'Lisez nos articles et partagez votre avis';
        } else if (view === 'collaborations') {
            document.getElementById('fo-view-collaborations').style.display = 'block';
            document.getElementById('nav-fo-collaborations').classList.add('active');
            document.getElementById('fo-page-title').textContent = 'Nos Collaborations';
            document.getElementById('fo-page-subtitle').textContent = 'Partenaires & projets collectifs SmartPlate';
            renderCollaborations();
        } else if (view === 'team') {
            document.getElementById('fo-view-team').style.display = 'block';
            document.getElementById('nav-fo-team').classList.add('active');
            document.getElementById('fo-page-title').textContent = 'Notre Équipe';
            document.getElementById('fo-page-subtitle').textContent = 'Rencontrez les membres qui font SmartPlate';
            renderTeam();
        }
    };

    // ===== COLLABORATIONS DATA & RENDER =====
    var collaborationsData = [
        { name: 'GreenHarvest', role: 'Agriculture Durable', logo: '🌿', description: 'Partenaire en approvisionnement bio et circuits courts pour nos recettes.', since: '2023', tags: ['Bio', 'Local', 'Durable'] },
        { name: 'NutriLab Research', role: 'Institut de Recherche', logo: '🔬', description: 'Collaboration scientifique pour valider les valeurs nutritionnelles de nos articles.', since: '2024', tags: ['Science', 'Nutrition', 'Recherche'] },
        { name: 'EcoPackaging Co.', role: 'Emballages Écoresponsables', logo: '♻️', description: 'Fournisseur d\'emballages 100% recyclables pour nos partenaires de livraison.', since: '2023', tags: ['Éco', 'Recyclable', 'Innovant'] },
        { name: 'ChefConnect', role: 'Réseau de Chefs', logo: '👨‍🍳', description: 'Communauté de chefs professionnels qui contribuent à nos contenus éditoriaux.', since: '2022', tags: ['Gastronomie', 'Expertise', 'Communauté'] },
        { name: 'FoodTech Hub', role: 'Accélérateur Startup', logo: '🚀', description: 'Soutien technologique et réseau d\'innovation pour le développement de la plateforme.', since: '2024', tags: ['Tech', 'Innovation', 'Startup'] },
        { name: 'Université Alimentaire', role: 'Partenariat Académique', logo: '🎓', description: 'Échanges académiques et publications communes sur l\'alimentation intelligente.', since: '2022', tags: ['Académique', 'Publication', 'Recherche'] }
    ];

    function renderCollaborations() {
        var container = document.getElementById('fo-collaborations-list');
        if (!container) return;
        var html = '<div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap:20px;">';
        collaborationsData.forEach(function(c) {
            var tagsHtml = c.tags.map(t => `<span style="background:rgba(0,242,254,0.12); color:#4facfe; padding:3px 10px; border-radius:20px; font-size:0.78rem; font-weight:600;">${escapeHtml(t)}</span>`).join(' ');
            html += `
            <div class="glass-container" style="padding:22px; border-radius:16px; display:flex; flex-direction:column; gap:12px; transition:transform 0.25s ease, box-shadow 0.25s ease;"
                 onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 30px rgba(0,0,0,0.12)';"
                 onmouseout="this.style.transform='none'; this.style.boxShadow='';">
                <div style="display:flex; align-items:center; gap:14px;">
                    <div style="font-size:2.4rem; width:54px; height:54px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,rgba(0,242,254,0.15),rgba(79,172,254,0.15)); border-radius:12px;">${escapeHtml(c.logo)}</div>
                    <div>
                        <div style="font-weight:700; font-size:1.05rem; color:var(--text-dark);">${escapeHtml(c.name)}</div>
                        <div style="font-size:0.82rem; color:#4facfe; font-weight:600;">${escapeHtml(c.role)}</div>
                    </div>
                </div>
                <p style="font-size:0.92rem; color:var(--text-dark); line-height:1.55; margin:0;">${escapeHtml(c.description)}</p>
                <div style="display:flex; gap:6px; flex-wrap:wrap;">${tagsHtml}</div>
                <div style="font-size:0.78rem; color:#aaa; border-top:1px solid rgba(0,0,0,0.06); padding-top:10px; margin-top:auto;">Partenaire depuis ${escapeHtml(c.since)}</div>
            </div>`;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    // ===== TEAM DATA & RENDER =====
    var teamData = [
        { name: 'Sophie Martin', role: 'Directrice & Fondatrice', emoji: '👩‍💼', bio: 'Passionnée d\'alimentation durable, Sophie a fondé SmartPlate pour connecter nutrition et technologie.', skills: ['Leadership', 'Vision', 'Nutrition'], social: '🔗' },
        { name: 'Alexandre Dupont', role: 'Développeur Full-Stack', emoji: '👨‍💻', bio: 'Architecte de la plateforme SmartPlate, expert en PHP MVC et interfaces modernes.', skills: ['PHP', 'JavaScript', 'SQL'], social: '🔗' },
        { name: 'Amina Benali', role: 'Nutritionniste & Rédactrice', emoji: '👩‍⚕️', bio: 'Valide le contenu scientifique de chaque article et rédige nos analyses nutritionnelles.', skills: ['Nutrition', 'Rédaction', 'Recherche'], social: '🔗' },
        { name: 'Lucas Rousseau', role: 'Designer UX/UI', emoji: '🎨', bio: 'Crée des expériences visuelles intuitives pour rendre SmartPlate accessible à tous.', skills: ['Figma', 'CSS', 'Accessibilité'], social: '🔗' },
        { name: 'Fatima Zahra', role: 'Community Manager', emoji: '📢', bio: 'Anime la communauté SmartPlate sur les réseaux sociaux et gère les collaborations influenceurs.', skills: ['Social Media', 'Contenu', 'Communication'], social: '🔗' },
        { name: 'Julien Petit', role: 'Chef Partenaire', emoji: '👨‍🍳', bio: 'Chef étoilé qui teste et valide les recettes avant leur publication sur la plateforme.', skills: ['Gastronomie', 'Bio', 'Innovation'], social: '🔗' }
    ];

    function renderTeam() {
        var container = document.getElementById('fo-team-list');
        if (!container) return;
        var html = '<div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap:24px;">';
        teamData.forEach(function(m) {
            var skillsHtml = m.skills.map(s => `<span style="background:rgba(32,201,151,0.12); color:#20c997; padding:3px 10px; border-radius:20px; font-size:0.78rem; font-weight:600;">${escapeHtml(s)}</span>`).join(' ');
            html += `
            <div class="glass-container" style="padding:24px; border-radius:18px; text-align:center; display:flex; flex-direction:column; align-items:center; gap:12px; transition:transform 0.25s ease, box-shadow 0.25s ease;"
                 onmouseover="this.style.transform='translateY(-5px) scale(1.01)'; this.style.boxShadow='0 16px 35px rgba(0,0,0,0.12)';"
                 onmouseout="this.style.transform='none'; this.style.boxShadow='';">
                <div style="font-size:3.2rem; width:72px; height:72px; background:linear-gradient(135deg,rgba(0,242,254,0.18),rgba(79,172,254,0.18)); border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 12px rgba(79,172,254,0.2);">${escapeHtml(m.emoji)}</div>
                <div>
                    <div style="font-weight:700; font-size:1.08rem; color:var(--text-dark);">${escapeHtml(m.name)}</div>
                    <div style="font-size:0.83rem; color:#20c997; font-weight:600; margin-top:2px;">${escapeHtml(m.role)}</div>
                </div>
                <p style="font-size:0.88rem; color:var(--text-dark); line-height:1.5; margin:0;">${escapeHtml(m.bio)}</p>
                <div style="display:flex; gap:6px; flex-wrap:wrap; justify-content:center;">${skillsHtml}</div>
            </div>`;
        });
        html += '</div>';
        container.innerHTML = html;
    }

});
</script>

<!-- Floating Theme Toggle -->
<button id="theme-toggle" onclick="toggleDarkMode(event)" style="position: fixed; bottom: 30px; right: 30px; z-index: 9999; background: #1e293b; color: white; border: none; border-radius: 50px; padding: 12px 20px; font-size: 1rem; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2); transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">🌙 Mode Sombre</button>

<!-- Custom Modal System -->
<div id="custom-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 10000; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
    <div id="custom-modal-box" style="background: var(--card-bg); color: var(--text-dark); padding: 30px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.3); max-width: 400px; width: 90%; transform: scale(0.9); transition: transform 0.3s ease; text-align: center;">
        <div id="custom-modal-icon" style="font-size: 3.5rem; margin-bottom: 15px;"></div>
        <h3 id="custom-modal-title" style="margin: 0 0 10px 0; font-size: 1.4rem; font-weight: bold;"></h3>
        <p id="custom-modal-message" style="margin: 0 0 25px 0; font-size: 1.05rem; color: var(--text-gray); line-height: 1.5;"></p>
        <div id="custom-modal-actions" style="display: flex; gap: 15px; justify-content: center;"></div>
    </div>
</div>

<script>
const customModalOverlay = document.getElementById('custom-modal-overlay');
const customModalBox = document.getElementById('custom-modal-box');
const customModalIcon = document.getElementById('custom-modal-icon');
const customModalTitle = document.getElementById('custom-modal-title');
const customModalMessage = document.getElementById('custom-modal-message');
const customModalActions = document.getElementById('custom-modal-actions');

function openCustomModal(options) {
    return new Promise((resolve) => {
        customModalIcon.innerHTML = options.icon || 'ℹ️';
        customModalTitle.innerText = options.title || 'Information';
        customModalMessage.innerText = options.message || '';
        customModalActions.innerHTML = '';
        
        const closeBtn = document.createElement('button');
        closeBtn.innerText = options.cancelText || 'Fermer';
        closeBtn.style.cssText = 'padding: 12px 24px; border: none; border-radius: 12px; cursor: pointer; font-weight: 600; font-size: 1rem; background: #e2e8f0; color: #475569; transition: all 0.2s;';
        closeBtn.onmouseover = () => closeBtn.style.background = '#cbd5e1';
        closeBtn.onmouseout = () => closeBtn.style.background = '#e2e8f0';
        closeBtn.onclick = () => { closeCustomModal(); resolve(false); };
        
        if (options.type === 'confirm') {
            const confirmBtn = document.createElement('button');
            confirmBtn.innerText = options.confirmText || 'Confirmer';
            confirmBtn.style.cssText = `padding: 12px 24px; border: none; border-radius: 12px; cursor: pointer; font-weight: 600; font-size: 1rem; color: white; transition: all 0.2s; background: ${options.confirmColor || '#3b82f6'}; box-shadow: 0 4px 10px rgba(0,0,0,0.15);`;
            confirmBtn.onmouseover = () => confirmBtn.style.transform = 'translateY(-2px)';
            confirmBtn.onmouseout = () => confirmBtn.style.transform = 'translateY(0)';
            confirmBtn.onclick = () => { closeCustomModal(); resolve(true); };
            customModalActions.appendChild(closeBtn);
            customModalActions.appendChild(confirmBtn);
        } else {
            closeBtn.innerText = 'OK';
            closeBtn.style.background = '#3b82f6';
            closeBtn.style.color = '#fff';
            closeBtn.onmouseover = () => closeBtn.style.background = '#2563eb';
            closeBtn.onmouseout = () => closeBtn.style.background = '#3b82f6';
            customModalActions.appendChild(closeBtn);
        }

        customModalOverlay.style.display = 'flex';
        void customModalOverlay.offsetWidth;
        customModalOverlay.style.opacity = '1';
        customModalBox.style.transform = 'scale(1)';
    });
}

function closeCustomModal() {
    customModalOverlay.style.opacity = '0';
    customModalBox.style.transform = 'scale(0.9)';
    setTimeout(() => {
        customModalOverlay.style.display = 'none';
    }, 300);
}

window.customAlert = function(message, title = 'Information', icon = 'ℹ️') {
    return openCustomModal({ type: 'alert', message, title, icon });
};

window.customConfirm = function(message, title = 'Confirmation', icon = '❓', confirmColor = '#e74c3c') {
    return openCustomModal({ type: 'confirm', message, title, icon, confirmColor, confirmText: 'Oui', cancelText: 'Non' });
};

function initTheme() {
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
        var btn = document.getElementById('theme-toggle');
        if (btn) btn.innerHTML = '☀️ Mode Clair';
        if (customModalBox) {
            customModalBox.style.background = '#1e293b';
            customModalTitle.style.color = '#f1f5f9';
            customModalMessage.style.color = '#cbd5e1';
        }
    }
}
function toggleDarkMode(e) {
    if(e) e.preventDefault();
    document.body.classList.toggle('dark-mode');
    var isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    var btn = document.getElementById('theme-toggle');
    if (btn) btn.innerHTML = isDark ? '☀️ Mode Clair' : '🌙 Mode Sombre';
}
    // Live input control for username length
    document.addEventListener('input', function(e) {
        if (e.target.id && (e.target.id.startsWith('username-') || e.target.id.startsWith('reply-username-'))) {
            const val = e.target.value.trim();
            const idPart = e.target.id.split('-').pop();
            const errorId = e.target.id.startsWith('username-') ? 'error-username-' + idPart : 'error-reply-username-' + idPart;
            
            if (val.length > 20) {
                showInlineError(errorId, 'Le nom ne doit pas dépasser 20 caractères.', 0);
            } else if (val.length > 0 && !/[a-zA-Z\u00C0-\u017F]/.test(val)) {
                showInlineError(errorId, 'Le nom doit contenir au moins une lettre.', 0);
            } else {
                clearInlineError(errorId);
            }
        }
    });

    initTheme();
</script>

<style>
@keyframes fadeInForm {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

</body>
</html>