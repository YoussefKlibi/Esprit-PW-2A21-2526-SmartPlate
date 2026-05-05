<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Gestion du Blog Smart Plate</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/template-backoffice.css?v=11">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
</head>
<body>

<!-- SIDEBAR -->
<div class="admin-sidebar">
    <div class="sidebar-header">
        <img src="assets/logo.png" alt="Smart Plate" class="sidebar-logo">
    </div>

    <div class="sidebar-menu">
        <div class="menu-category">Gestion</div>

        <a href="#" class="menu-item active" id="nav-dashboard" onclick="switchView('dashboard'); return false;">📊 Dashboard</a>
        <a href="#" class="menu-item" id="nav-articles" onclick="switchView('articles'); return false;">📝 Articles</a>
        <a href="#" class="menu-item" id="nav-drafts" onclick="switchView('drafts'); return false;">🕒 Brouillons</a>
    </div>
</div>

<!-- MAIN -->
<div class="main-content">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="forum-topbar-banner">
            <div class="forum-topbar-border"></div>
            <div class="forum-topbar-inner">
                <span class="forum-topbar-icon">💬</span>
                <h2 class="forum-topbar-title">Forum</h2>
                <span class="forum-topbar-pill live-pulse">⚡ En direct</span>
            </div>
        </div>

        <div class="admin-profile">
            <div id="notif-bell" class="notif-bell-wrap" role="button" tabindex="0" title="Nouveaux commentaires">
                <span class="notif-bell-icon" aria-hidden="true">🔔</span>
                <span id="notif-badge" class="notif-badge" hidden>0</span>
            </div>
            <span>Admin</span>
            <img src="https://via.placeholder.com/40" alt="" class="admin-avatar">
        </div>
    </div>

    <!-- DASHBOARD VIEW -->
    <div id="view-dashboard" class="dashboard-container">

        <!-- HEADER -->
        <div class="page-header">
            <div>
                <h1>Tableau de Bord</h1>
                <p>Gestion des articles et commentaires</p>
            </div>
            <button class="btn-action" style="background: #20c997;" onclick="switchView('articles')">+ Nouvel article</button>
        </div>

        <!-- KPI -->
        <div class="kpi-grid">

            <div id="kpi-card-articles" class="card kpi-card" role="button" tabindex="0" onclick="switchView('articles');" style="cursor:pointer;">
                <div class="kpi-icon green">📝</div>
                <div class="kpi-info">
                    <h3 id="kpi-articles">...</h3>
                    <span>Articles</span>
                </div>
            </div>

            <div class="card kpi-card">
                <div class="kpi-icon yellow">💬</div>
                <div class="kpi-info">
                    <h3 id="kpi-comments">...</h3>
                    <span>Commentaires</span>
                </div>
            </div>

            <div id="kpi-card-drafts" class="card kpi-card" role="button" tabindex="0" onclick="switchView('drafts');" style="cursor:pointer;">
                <div class="kpi-icon red">🕒</div>
                <div class="kpi-info">
                    <h3 id="kpi-drafts">...</h3>
                    <span>Brouillons</span>
                </div>
            </div>

        </div>

        <!-- CONTENT -->
        <div class="content-grid">
            <!-- COMMENTAIRES À MODÉRER -->
            <div class="card" style="grid-column: span 2;">
                <div class="card-header">
                    <h2>Commentaires à modérer</h2>
                </div>
                <div id="admin-comments-list">
                    <p style="padding: 15px;">Chargement des commentaires...</p>
                </div>
            </div>

            <!-- STATISTIQUES: Commentaires par article -->
            <div class="card" style="grid-column: span 2; margin-top: 16px;">
                <div class="card-header">
                    <h2>📊 Statistiques — Commentaires par article</h2>
                </div>
                <div style="padding: 20px; position: relative; height: 350px;">
                    <canvas id="chart-comments-by-article"></canvas>
                </div>
            </div>
        </div>

    </div>

    <!-- ARTICLES VIEW -->
    <div id="view-articles" class="dashboard-container" style="display: none;">

        <!-- HEADER -->
        <div class="page-header">
            <div>
                <h1>Gestion des Articles</h1>
                <p>Créer, modifier et supprimer vos articles</p>
            </div>
            <button class="btn-action" style="background: #20c997;" onclick="toggleArticleForm()">+ Ajouter un article</button>
        </div>

        <div class="card" id="article-form-card" style="margin-top:16px; display: none;">
            <div class="card-header"><h2 id="article-form-title">Créer un article</h2></div>
            <div style="padding:16px;">
                <form id="manage-article-form" action="index.php?controller=article" method="post" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="id" id="form-article-id" value="">
                    <input type="hidden" name="action" id="form-article-action" value="create">
                    
                    <div class="form-group">
                        <input type="text" name="name" id="form-article-name" placeholder="Nom de l'article" style="width:100%; padding:8px;">
                        <div id="error-article-name"></div>
                    </div>
                    <div class="form-group" style="margin-top:8px;">
                        <input type="text" name="type" id="form-article-type" placeholder="Type (ex: Veggie, Viande, etc.)" style="width:100%; padding:8px;">
                        <div id="error-article-type"></div>
                    </div>
                    <div class="form-group" style="margin-top:8px;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 500; color: #475569; font-size: 0.9rem;">Image de l'article</label>
                        <div id="image-preview-container" style="display: none; margin-bottom: 8px; position: relative; width: fit-content;">
                            <img id="image-preview" src="" style="max-width: 200px; max-height: 150px; border-radius: 8px; border: 2px solid #e2e8f0; object-fit: cover;">
                            <button type="button" id="btn-remove-image" style="position: absolute; top: -8px; right: -8px; background: #e74c3c; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 0.8rem; display: flex; align-items: center; justify-content: center;">✕</button>
                        </div>
                        <input type="file" name="image" id="form-article-image" accept="image/jpeg,image/png,image/gif,image/webp" style="width:100%; padding:8px; border: 2px dashed #e2e8f0; border-radius: 8px; cursor: pointer;">
                        <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 4px;">Formats acceptés : JPG, PNG, GIF, WEBP (max 5 Mo)</div>
                    </div>
                    <div class="form-group" style="margin-top:8px;">
                        <input type="text" name="author" id="form-article-author" placeholder="Auteur" style="width:100%; padding:8px;" value="Admin">
                    </div>
                    <div class="form-group" style="margin-top:8px;">
                        <textarea name="content" id="form-article-content" rows="6" placeholder="Contenu" style="width:100%; padding:8px;"></textarea>
                        <div id="error-article-content"></div>
                    </div>
                    <div style="margin-top:8px;">
                        <label><input type="checkbox" name="status" id="form-article-status" value="1" checked> Publier maintenant</label>
                    </div>
                    <div id="article-form-error" style="margin-top: 4px;"></div>
                    <div style="margin-top:12px; display: flex; gap: 10px;">
                        <button type="submit" class="btn-action" style="background:#20c997">Enregistrer</button>
                        <button type="button" id="btn-cancel-article" class="btn-action" style="background:#95a5a6">Annuler</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card" style="margin-top: 16px;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h2>Articles publiés et brouillons</h2>
                <div style="position: relative; width: 300px;">
                    <input type="text" id="search-articles" placeholder="🔍 Rechercher par nom..." 
                        style="width: 100%; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-family: inherit; font-size: 0.9rem; transition: border-color 0.2s ease, box-shadow 0.2s ease; outline: none; background: #fff;">
                </div>
            </div>
            <div style="overflow-x: auto; padding: 15px;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid #eee;">
                            <th style="padding: 10px;">ID</th>
                            <th style="padding: 10px;">Image</th>
                            <th style="padding: 10px;">Nom</th>
                            <th style="padding: 10px;">Type</th>
                            <th style="padding: 10px;">Auteur</th>
                            <th style="padding: 10px;">Statut</th>
                            <th style="padding: 10px;">Date</th>
                            <th style="padding: 10px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="admin-articles-list">
                        <tr><td colspan="8" style="padding: 15px;">Chargement des articles...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- DRAFTS VIEW -->
    <div id="view-drafts" class="dashboard-container" style="display: none;">

        <div class="page-header">
            <div>
                <h1>Brouillons</h1>
                <p>Articles en attente de publication (statut = 0)</p>
            </div>
            <button class="btn-action" style="background: #20c997;" onclick="switchView('articles')">Retour aux articles</button>
        </div>

        <div class="card" style="margin-top: 16px;">
            <div class="card-header">
                <h2>Brouillons</h2>
            </div>
            <div style="overflow-x: auto; padding: 15px;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid #eee;">
                            <th style="padding: 10px;">ID</th>
                            <th style="padding: 10px;">Image</th>
                            <th style="padding: 10px;">Nom</th>
                            <th style="padding: 10px;">Type</th>
                            <th style="padding: 10px;">Auteur</th>
                            <th style="padding: 10px;">Date</th>
                            <th style="padding: 10px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="admin-drafts-list">
                        <tr><td colspan="7" style="padding: 15px;">Chargement des brouillons...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    </div>

</div>

<!-- Floating Theme Toggle -->
<button id="theme-toggle-bo" onclick="toggleDarkModeBO(event)" style="position: fixed; bottom: 30px; right: 30px; z-index: 9999; background: #1e293b; color: white; border: none; border-radius: 50px; padding: 12px 20px; font-size: 1rem; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2); transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">🌙 Mode Sombre</button>

<script>
function initThemeBO() {
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
        var btn = document.getElementById('theme-toggle-bo');
        if (btn) btn.innerHTML = '☀️ Mode Clair';
        if (typeof Chart !== 'undefined') {
            Chart.defaults.color = '#f1f5f9';
        }
    }
}
function toggleDarkModeBO(e) {
    if(e) e.preventDefault();
    document.body.classList.toggle('dark-mode');
    var isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    var btn = document.getElementById('theme-toggle-bo');
    if (btn) btn.innerHTML = isDark ? '☀️ Mode Clair' : '🌙 Mode Sombre';
    
    if (typeof commentChart !== 'undefined' && commentChart !== null) {
        Chart.defaults.color = isDark ? '#f1f5f9' : '#666';
        if (commentChart.options.scales.x) {
            commentChart.options.scales.x.ticks.color = isDark ? '#f1f5f9' : '#666';
            commentChart.options.scales.x.grid.color = isDark ? '#334155' : '#eee';
        }
        if (commentChart.options.scales.y) {
            commentChart.options.scales.y.ticks.color = isDark ? '#f1f5f9' : '#666';
            commentChart.options.scales.y.grid.color = isDark ? '#334155' : '#eee';
        }
        commentChart.update();
    }
}
initThemeBO();

document.addEventListener('DOMContentLoaded', function() {
    function escapeHtml(s) { 
        return String(s || '').replace(/[&<>"']/g, function(m) { 
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m]; 
        }); 
    }

    // Inline error message helper — replaces all alert() popups
    function showInlineError(containerId, message, duration) {
        duration = duration || 5000;
        var container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = '<div style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; border-left: 4px solid #e74c3c; padding: 10px 14px; border-radius: 8px; font-size: 0.9rem; margin-top: 8px; animation: fadeInUp 0.3s ease-out; display: flex; align-items: center; gap: 8px;"><span style="font-size: 1.1rem;">⚠️</span><span>' + escapeHtml(message) + '</span></div>';
        if (duration > 0) {
            setTimeout(function() {
                if (container) container.innerHTML = '';
            }, duration);
        }
    }

    function clearInlineError(containerId) {
        var container = document.getElementById(containerId);
        if (container) container.innerHTML = '';
    }

    // track current view
    window.currentAdminView = 'dashboard';

    // View Switching
    window.switchView = function(view) {
        window.currentAdminView = view;
        // hide all
        document.getElementById('view-dashboard').style.display = 'none';
        document.getElementById('view-articles').style.display = 'none';
        const draftsEl = document.getElementById('view-drafts');
        if (draftsEl) draftsEl.style.display = 'none';

        // remove active on nav
        document.getElementById('nav-dashboard').classList.remove('active');
        document.getElementById('nav-articles').classList.remove('active');
        const navDrafts = document.getElementById('nav-drafts');
        if (navDrafts) navDrafts.classList.remove('active');

        if (view === 'dashboard') {
            document.getElementById('view-dashboard').style.display = 'block';
            document.getElementById('nav-dashboard').classList.add('active');
        } else if (view === 'articles') {
            document.getElementById('view-articles').style.display = 'block';
            document.getElementById('nav-articles').classList.add('active');
        } else if (view === 'drafts') {
            if (draftsEl) draftsEl.style.display = 'block';
            if (navDrafts) navDrafts.classList.add('active');
            // load drafts content
            loadDraftsView();
        }
    };

    // Make Articles KPI accessible via keyboard (Enter/Space)
    const kpiArticlesCard = document.getElementById('kpi-card-articles');
    if (kpiArticlesCard) {
        kpiArticlesCard.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                switchView('articles');
            }
        });
    }

    // Make Drafts KPI accessible
    const kpiDraftsCard = document.getElementById('kpi-card-drafts');
    if (kpiDraftsCard) {
        kpiDraftsCard.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                switchView('drafts');
            }
        });
    }

    function loadAdminDashboard(keyword) {
        // Fetch all articles
        var articleUrl = 'index.php?controller=article&action=list&all=1';
        if (keyword && keyword.trim() !== '') {
            articleUrl = 'index.php?controller=article&action=search&keyword=' + encodeURIComponent(keyword.trim()) + '&all=1';
        }
        fetch(articleUrl)
            .then(res => res.json())
            .then(articles => {
                if (articles.error) throw new Error(articles.error);

                // Only update KPIs when not searching (full load)
                if (!keyword || keyword.trim() === '') {
                    document.getElementById('kpi-articles').textContent = articles.length || 0;
                    const draftsCount = (articles.filter ? articles.filter(a => a.status == 0).length : 0);
                    const kpiDraftsEl = document.getElementById('kpi-drafts');
                    if (kpiDraftsEl) kpiDraftsEl.textContent = draftsCount || 0;
                }

                const articlesContainer = document.getElementById('admin-articles-list');
                articlesContainer.innerHTML = '';

                if (!articles.length) {
                    if (keyword && keyword.trim() !== '') {
                        articlesContainer.innerHTML = '<tr><td colspan="8" style="padding: 30px; text-align: center; color: #94a3b8;">🔍 Aucun article trouvé pour "<strong>' + escapeHtml(keyword) + '</strong>"</td></tr>';
                    } else {
                        articlesContainer.innerHTML = '<tr><td colspan="8" style="padding: 15px;">Aucun article trouvé.</td></tr>';
                    }
                } else {
                    let listHtml = '';
                    articles.forEach(a => {
                        const date = new Date(a.created_at).toLocaleDateString();
                        const encodedContent = escapeHtml(a.content).replace(/'/g, "\\'");
                        const encodedName = escapeHtml(a.name).replace(/'/g, "\\'");
                        const encodedType = escapeHtml(a.type).replace(/'/g, "\\'");
                        const encodedImage = escapeHtml(a.image_url).replace(/'/g, "\\'");
                        const encodedAuthor = escapeHtml(a.author).replace(/'/g, "\\'");

                        listHtml += `
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px;">#${a.id}</td>
                            <td style="padding: 15px;">
                                ${a.image_url ? `<img src="${escapeHtml(a.image_url)}" style="width:50px; height:50px; object-fit:cover; border-radius:4px;">` : '<span style="color:#aaa">Aucune</span>'}
                            </td>
                            <td style="padding: 15px;"><strong>${escapeHtml(a.name)}</strong></td>
                            <td style="padding: 15px;">${escapeHtml(a.type || '-')}</td>
                            <td style="padding: 15px;">${escapeHtml(a.author || 'Admin')}</td>
                            <td style="padding: 15px;">
                                <span style="background: ${a.status == 1 ? '#2ecc71' : '#f1c40f'}; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">
                                    ${a.status == 1 ? 'Publié' : 'Brouillon'}
                                </span>
                            </td>
                            <td style="padding: 15px;">${date}</td>
                            <td style="padding: 15px; text-align: right;">
                                <button class="btn-action" style="background: #9b59b6; margin-right: 5px; padding: 5px 10px;" onclick="viewComments(${a.id})">Commentaires</button>
                                <button class="btn-action" style="background: #3498db; margin-right: 5px; padding: 5px 10px;" onclick="editArticle(${a.id}, '${encodedName}', '${encodedType}', '${encodedImage}', '${encodedAuthor}', '${encodedContent}', ${a.status})">Modifier</button>
                                <button class="btn-action" style="background: #e74c3c; padding: 5px 10px;" onclick="deleteArticle(${a.id})">Supprimer</button>
                            </td>
                        </tr>
                        <tr id="comments-row-${a.id}" style="display: none; background: #f9f9f9; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); border-bottom: 2px solid #eee;">
                            <td colspan="8" style="padding: 15px;">
                                <div id="comments-container-${a.id}"></div>
                            </td>
                        </tr>
                        `;
                    });
                    articlesContainer.innerHTML = listHtml;
                }
            })
            .catch(err => console.error("Erreur chargement articles:", err));

        // Fetch all comments and display them (pending first) with actions
        fetch('index.php?controller=comment&action=list&all=1')
            .then(res => res.json())
            .then(comments => {
                if (comments.error) throw new Error(comments.error);

                const totalComments = comments.length || 0;
                const pendingComments = comments.filter(c => c.status == 0);
                document.getElementById('kpi-comments').textContent = totalComments;

                const commentsContainer = document.getElementById('admin-comments-list');
                commentsContainer.innerHTML = '';

                if (!totalComments) {
                    commentsContainer.innerHTML = '<p style="padding: 15px; color: #666;">Aucun commentaire trouvé.</p>';
                } else {
                    // Show all comments, sorting so pending (status=0) appear first
                    comments.sort((a, b) => a.status - b.status || new Date(b.created_at) - new Date(a.created_at));

                    comments.forEach(c => {
                        const date = new Date(c.created_at).toLocaleString();
                        const div = document.createElement('div');
                        div.className = 'timeline-item';
                        const statusBadge = c.status == 1
                            ? '<span style="background:#2ecc71;color:#fff;padding:4px 8px;border-radius:4px;font-size:0.75rem;margin-left:8px;">Validé</span>'
                            : '<span style="background:#f1c40f;color:#fff;padding:4px 8px;border-radius:4px;font-size:0.75rem;margin-left:8px;">En attente</span>';
                        
                        const reportBadge = c.report_count > 0
                            ? `<span style="background:#e74c3c;color:#fff;padding:4px 8px;border-radius:4px;font-size:0.75rem;margin-left:8px;" title="${c.report_count} signalements">🚨 Signalé (${c.report_count})</span>`
                            : '';

                        const assignedBadge = c.badge 
                            ? `<span style="background:#9b59b6;color:#fff;padding:4px 8px;border-radius:4px;font-size:0.75rem;margin-left:8px;" title="Badge">🎖️ ${c.badge}</span>`
                            : '';
                            
                        const badgeSelect = `
                            <select onchange="assignBadge(${c.id}, this.value)" class="form-control badge-select" style="margin-left:8px; min-width: 190px;">
                                <option value="">-- Badge --</option>
                                <option value="Top Commentaire" ${c.badge === 'Top Commentaire' ? 'selected' : ''}>Top Commentaire</option>
                                <option value="Expert" ${c.badge === 'Expert' ? 'selected' : ''}>Expert</option>
                                <option value="Pertinent" ${c.badge === 'Pertinent' ? 'selected' : ''}>Pertinent</option>
                            </select>
                        `;

                        const actions = c.status == 0
                            ? `<button class="btn-action" style="background:#28a745; margin-left:8px;" onclick="updateCommentStatus(${c.id}, 1)">Valider</button><button class="btn-action" style="background:#e74c3c; margin-left:8px;" onclick="deleteComment(${c.id})">Supprimer</button>`
                            : `<button class="btn-action" style="background:#e74c3c; margin-left:8px;" onclick="deleteComment(${c.id})">Supprimer</button>`;

                        div.innerHTML = `
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; padding:8px 0; border-bottom: 1px solid #f1f1f1;">
                                <div style="flex:1;">
                                    <div style="margin-bottom:6px;"><strong>${escapeHtml(c.username)}</strong> <span style="color:#888; font-size:0.9rem;">• ${date}</span> ${statusBadge} ${reportBadge} ${assignedBadge}</div>
                                    <div style="color:#333;">${escapeHtml(c.comment)}</div>
                                </div>
                                <div style="display:flex; align-items:center;">${badgeSelect} ${actions}</div>
                            </div>
                        `;
                        commentsContainer.appendChild(div);
                    });
                }
            })
            .catch(err => console.error("Erreur chargement commentaires:", err));
    }

    // Load drafts-only view
    function loadDraftsView() {
        fetch('index.php?controller=article&action=list&all=1')
            .then(r => r.json())
            .then(articles => {
                if (articles.error) throw new Error(articles.error);
                const drafts = (articles.filter ? articles.filter(a => a.status == 0) : []);
                const container = document.getElementById('admin-drafts-list');
                container.innerHTML = '';
                if (!drafts.length) {
                    container.innerHTML = '<tr><td colspan="7" style="padding: 15px;">Aucun brouillon trouvé.</td></tr>';
                    return;
                }
                let html = '';
                drafts.forEach(a => {
                    const date = new Date(a.created_at).toLocaleDateString();
                    const encodedContent = escapeHtml(a.content).replace(/'/g, "\\'");
                    const encodedName = escapeHtml(a.name).replace(/'/g, "\\'");
                    const encodedType = escapeHtml(a.type).replace(/'/g, "\\'");
                    const encodedImage = escapeHtml(a.image_url).replace(/'/g, "\\'");
                    const encodedAuthor = escapeHtml(a.author).replace(/'/g, "\\'");

                    html += `
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px;">#${a.id}</td>
                        <td style="padding: 15px;">${a.image_url ? `<img src="${escapeHtml(a.image_url)}" style="width:50px; height:50px; object-fit:cover; border-radius:4px;">` : '<span style="color:#aaa">Aucune</span>'}</td>
                        <td style="padding: 15px;"><strong>${escapeHtml(a.name)}</strong></td>
                        <td style="padding: 15px;">${escapeHtml(a.type || '-')}</td>
                        <td style="padding: 15px;">${escapeHtml(a.author || 'Admin')}</td>
                        <td style="padding: 15px;">${date}</td>
                        <td style="padding: 15px; text-align: right;">
                            <button class="btn-action" style="background: #3498db; margin-right: 5px; padding: 5px 10px;" onclick="editArticle(${a.id}, '${encodedName}', '${encodedType}', '${encodedImage}', '${encodedAuthor}', '${encodedContent}', ${a.status})">Modifier</button>
                            <button class="btn-action" style="background: #2ecc71; margin-right: 5px; padding: 5px 10px;" onclick="(function(){ customConfirm('Publier cet article ?', 'Publication', '✅', '#2ecc71').then(res => { if(res) { const fd=new FormData(); fd.append('action','update'); fd.append('id', ${a.id}); fd.append('status', 1); fetch('index.php?controller=article',{method:'POST', body: fd}).then(()=> loadDraftsView()); } }); })()">Publier</button>
                            <button class="btn-action" style="background: #e74c3c; padding: 5px 10px;" onclick="deleteArticle(${a.id})">Supprimer</button>
                        </td>
                    </tr>
                    `;
                });
                container.innerHTML = html;
            })
            .catch(err => console.error('Erreur chargement brouillons:', err));
    }

    // Global Action Functions

    window.toggleArticleForm = function(forceShow = false) {
        const formCard = document.getElementById('article-form-card');
        if (forceShow || formCard.style.display === 'none') {
            formCard.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            formCard.style.display = 'none';
            document.getElementById('manage-article-form').reset();
            document.getElementById('form-article-action').value = 'create';
            document.getElementById('form-article-id').value = '';
            document.getElementById('article-form-title').innerText = 'Créer un article';
            // Reset image preview
            document.getElementById('image-preview-container').style.display = 'none';
            document.getElementById('image-preview').src = '';
        }
    };

    window.editArticle = function(id, name, type, image, author, content, status) {
        document.getElementById('form-article-id').value = id;
        document.getElementById('form-article-action').value = 'update';
        document.getElementById('form-article-name').value = name;
        document.getElementById('form-article-type').value = type;
        document.getElementById('form-article-author').value = author;
        document.getElementById('form-article-content').value = content;
        document.getElementById('form-article-status').checked = (status == 1);
        document.getElementById('article-form-title').innerText = 'Modifier l\'article #' + id;

        // Show current image preview if exists
        var previewContainer = document.getElementById('image-preview-container');
        var previewImg = document.getElementById('image-preview');
        if (image && image !== '') {
            previewImg.src = image;
            previewContainer.style.display = 'block';
        } else {
            previewContainer.style.display = 'none';
            previewImg.src = '';
        }
        // Reset file input
        document.getElementById('form-article-image').value = '';

        toggleArticleForm(true);
    };

    window.deleteArticle = function(id) {
        customConfirm('Supprimer cet article ?', 'Suppression', '🗑️').then(confirmed => {
            if(!confirmed) return;
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            fetch('index.php?controller=article', { method: 'POST', body: fd })
                .then(r => r.json()).then(() => {
                    if (window.currentAdminView === 'drafts') loadDraftsView(); else loadAdminDashboard();
                });
        });
    };

    window.viewComments = function(articleId) {
        const row = document.getElementById('comments-row-' + articleId);
        const container = document.getElementById('comments-container-' + articleId);
        
        if (row.style.display === 'none') {
            row.style.display = 'table-row';
            container.innerHTML = '<p style="color: #666; font-style: italic;">Chargement des commentaires...</p>';
            
            fetch('index.php?controller=comment&action=list&all=1&article_id=' + articleId)
                .then(r => r.json())
                .then(comments => {
                    if (comments.error) throw new Error(comments.error);
                    
                    if (!comments.length) {
                        container.innerHTML = '<p style="color: #888;">Aucun commentaire pour cet article.</p>';
                        return;
                    }
                    
                    let commentsHtml = `
                        <h4 style="margin-bottom: 10px; color: #555;">Commentaires de l'article #${articleId}</h4>
                        <table style="width: 100%; border-collapse: collapse; text-align: left; background: #fff; border: 1px solid #ddd; border-radius: 8px;">
                            <thead>
                                <tr style="border-bottom: 1px solid #ddd; background: #f1f1f1;">
                                    <th style="padding: 8px;">Auteur</th>
                                    <th style="padding: 8px;">Commentaire</th>
                                    <th style="padding: 8px;">Statut</th>
                                    <th style="padding: 8px;">Date</th>
                                    <th style="padding: 8px; text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    comments.forEach(c => {
                        const date = new Date(c.created_at).toLocaleString();
                        const statusBadge = c.status == 1 
                            ? '<span style="background:#2ecce1;color:#fff;padding:2px 6px;border-radius:4px;font-size:0.75rem;">Validé</span>' 
                            : '<span style="background:#f1c40f;color:#fff;padding:2px 6px;border-radius:4px;font-size:0.75rem;">En attente</span>';
                            
                        const reportBadge = c.report_count > 0
                            ? `<span style="background:#e74c3c;color:#fff;padding:2px 6px;border-radius:4px;font-size:0.75rem;margin-left:4px;" title="${c.report_count} signalements">🚨 (${c.report_count})</span>`
                            : '';
                            
                        const assignedBadge = c.badge 
                            ? `<span style="background:#9b59b6;color:#fff;padding:2px 6px;border-radius:4px;font-size:0.75rem;margin-left:4px;">🎖️ ${c.badge}</span>`
                            : '';
                            
                        const badgeSelect = `
                            <select onchange="assignBadge(${c.id}, this.value)" class="form-control badge-select" style="margin-right: 4px; min-width: 160px;">
                                <option value="">Badge</option>
                                <option value="Top Commentaire" ${c.badge === 'Top Commentaire' ? 'selected' : ''}>Top</option>
                                <option value="Expert" ${c.badge === 'Expert' ? 'selected' : ''}>Expert</option>
                                <option value="Pertinent" ${c.badge === 'Pertinent' ? 'selected' : ''}>Pertinent</option>
                            </select>
                        `;
                            
                        // Use a specific global function for deleting from article view so it re-renders exactly this list
                        commentsHtml += `
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 8px;"><strong>${escapeHtml(c.username)}</strong></td>
                                <td style="padding: 8px; width: 40%;">${escapeHtml(c.comment)}</td>
                                <td style="padding: 8px;">${statusBadge} ${reportBadge} ${assignedBadge}</td>
                                <td style="padding: 8px; font-size: 0.85rem;">${date}</td>
                                <td style="padding: 8px; text-align: right;">
                                    ${badgeSelect}
                                    <button class="btn-action" style="background:#e74c3c; padding:3px 8px; font-size:0.8rem;" onclick="deleteCommentFromArticle(${c.id}, ${articleId})">Supprimer</button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    commentsHtml += `</tbody></table>`;
                    container.innerHTML = commentsHtml;
                })
                .catch(err => {
                    console.error("Erreur chargement:", err);
                    container.innerHTML = '<p style="color: red;">Erreur lors du chargement des commentaires.</p>';
                });
        } else {
            row.style.display = 'none';
        }
    };

    window.deleteCommentFromArticle = function(commentId, articleId) {
        customConfirm('Supprimer définitivement ce commentaire ?', 'Suppression', '🗑️').then(confirmed => {
            if(!confirmed) return;
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', commentId);
            fetch('index.php?controller=comment', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(() => {
                    // Refresh both the dashboard stats and the specific opened view
                    loadAdminDashboard(); // This will auto update KPIs and top list
                    document.getElementById('comments-row-' + articleId).style.display = 'none';
                    viewComments(articleId); // Re-open properly updated
                });
        });
    };

    window.updateCommentStatus = function(id, status) {
        const fd = new FormData();
        fd.append('action', 'update');
        fd.append('id', id);
        fd.append('status', status);
        fetch('index.php?controller=comment', { method: 'POST', body: fd })
            .then(r => r.json()).then(() => loadAdminDashboard());
    };

    window.assignBadge = function(id, badge) {
        const fd = new FormData();
        fd.append('action', 'badge');
        fd.append('id', id);
        fd.append('badge', badge);
        fetch('index.php?controller=comment', { method: 'POST', body: fd })
            .then(r => r.json()).then(() => {
                if (window.currentAdminView === 'drafts') loadDraftsView(); else loadAdminDashboard();
            });
    };

    window.deleteComment = function(id) {
        customConfirm('Supprimer ce commentaire ?', 'Suppression', '🗑️').then(confirmed => {
            if(!confirmed) return;
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            fetch('index.php?controller=comment', { method: 'POST', body: fd })
                .then(r => r.json()).then(() => loadAdminDashboard());
        });
    };

    // Form interception — validation for both CREATE and UPDATE
    const articleForm = document.getElementById('manage-article-form');
    if(articleForm) {
        // Image file preview
        var imageInput = document.getElementById('form-article-image');
        var previewContainer = document.getElementById('image-preview-container');
        var previewImg = document.getElementById('image-preview');
        var btnRemoveImage = document.getElementById('btn-remove-image');

        imageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        btnRemoveImage.addEventListener('click', function() {
            imageInput.value = '';
            previewImg.src = '';
            previewContainer.style.display = 'none';
        });
        // Clear error on typing (JS) — each field clears its own error
        var fieldErrorMap = {
            'form-article-name': 'error-article-name',
            'form-article-type': 'error-article-type',
            'form-article-content': 'error-article-content'
        };
        Object.keys(fieldErrorMap).forEach(function(fieldId) {
            var el = document.getElementById(fieldId);
            if (el) {
                el.addEventListener('input', function() {
                    clearInlineError(fieldErrorMap[fieldId]);
                });
            }
        });

        // Cancel button (JS, not HTML onclick)
        var btnCancel = document.getElementById('btn-cancel-article');
        if (btnCancel) {
            btnCancel.addEventListener('click', function() {
                toggleArticleForm();
            });
        }

        // Submit: validation works for BOTH create AND update
        articleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Clear all field errors
            clearInlineError('error-article-name');
            clearInlineError('error-article-type');
            clearInlineError('error-article-content');
            clearInlineError('article-form-error');

            var action = document.getElementById('form-article-action').value;
            var name = document.getElementById('form-article-name').value.trim();
            var type = document.getElementById('form-article-type').value.trim();
            var content = document.getElementById('form-article-content').value.trim();

            // Validation: chaque erreur sous son champ
            var hasError = false;
            
            // Name validation (max 30 letters/chars)
            if (!name) {
                showInlineError('error-article-name', 'Le nom de l\'article est obligatoire.', 0);
                if (!hasError) document.getElementById('form-article-name').focus();
                hasError = true;
            } else if (name.length > 30) {
                showInlineError('error-article-name', 'Le nom de l\'article ne doit pas dépasser 30 caractères.', 0);
                if (!hasError) document.getElementById('form-article-name').focus();
                hasError = true;
            }
            
            // Type validation (no special characters)
            // Accepts letters, numbers, spaces, and accented characters.
            if (!type) {
                showInlineError('error-article-type', 'Le type de l\'article est obligatoire.', 0);
                if (!hasError) document.getElementById('form-article-type').focus();
                hasError = true;
            } else if (!/^[a-zA-Z0-9\s\u00C0-\u017F]+$/.test(type)) {
                showInlineError('error-article-type', 'Le type ne doit pas contenir de caractères spéciaux.', 0);
                if (!hasError) document.getElementById('form-article-type').focus();
                hasError = true;
            }
            
            // Content validation (min 30 letters/chars)
            if (!content) {
                showInlineError('error-article-content', 'Le contenu de l\'article est obligatoire.', 0);
                if (!hasError) document.getElementById('form-article-content').focus();
                hasError = true;
            } else if (content.length < 30) {
                showInlineError('error-article-content', 'Le contenu de l\'article ne doit pas être inférieur à 30 caractères.', 0);
                if (!hasError) document.getElementById('form-article-content').focus();
                hasError = true;
            }
            
            if (hasError) return;

            const fd = new FormData(articleForm);
            
            if (!document.getElementById('form-article-status').checked) {
                fd.append('status', '0');
            }
            
            fetch('index.php?controller=article', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    if(res.success) {
                        articleForm.reset();
                        document.getElementById('form-article-action').value = 'create';
                        document.getElementById('form-article-id').value = '';
                        document.getElementById('article-form-title').innerText = 'Créer un article';
                        document.getElementById('article-form-card').style.display = 'none';
                        clearInlineError('error-article-name');
                        clearInlineError('error-article-type');
                        clearInlineError('error-article-content');
                        clearInlineError('article-form-error');
                        loadAdminDashboard();
                    } else {
                        showInlineError('article-form-error', res.error || 'Erreur lors de l\'enregistrement.', 0);
                    }
                })
                .catch(function(err) {
                    console.error(err);
                    showInlineError('article-form-error', 'Erreur réseau. Veuillez réessayer.');
                });
        });
    }

    // Initial load
    loadAdminDashboard();



    // Search bar — debounce
    var searchTimer = null;
    var searchInput = document.getElementById('search-articles');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            var val = this.value;
            searchTimer = setTimeout(function() {
                loadAdminDashboard(val);
            }, 300);
        });
        searchInput.addEventListener('focus', function() {
            this.style.borderColor = '#20c997';
            this.style.boxShadow = '0 0 0 3px rgba(32, 201, 151, 0.15)';
        });
        searchInput.addEventListener('blur', function() {
            this.style.borderColor = '#e2e8f0';
            this.style.boxShadow = 'none';
        });
    }

    // ===== CHART: Commentaires par article =====
    var commentChart = null;
    function loadCommentStats() {
        fetch('index.php?controller=comment&action=stats')
            .then(r => r.json())
            .then(data => {
                if (!Array.isArray(data) || data.length === 0) return;

                var labels = data.map(d => d.name.length > 20 ? d.name.substring(0, 20) + '...' : d.name);
                var counts = data.map(d => parseInt(d.comment_count));

                // Gradient colors
                var colors = [
                    'rgba(32, 201, 151, 0.8)',
                    'rgba(52, 152, 219, 0.8)',
                    'rgba(155, 89, 182, 0.8)',
                    'rgba(241, 196, 15, 0.8)',
                    'rgba(231, 76, 60, 0.8)',
                    'rgba(46, 204, 113, 0.8)',
                    'rgba(26, 188, 156, 0.8)',
                    'rgba(52, 73, 94, 0.8)'
                ];
                var bgColors = counts.map(function(_, i) { return colors[i % colors.length]; });
                var borderColors = bgColors.map(function(c) { return c.replace('0.8', '1'); });

                var ctx = document.getElementById('chart-comments-by-article');
                if (!ctx) return;

                if (commentChart) commentChart.destroy();

                commentChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Nombre de commentaires',
                            data: counts,
                            backgroundColor: bgColors,
                            borderColor: borderColors,
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.8)',
                                titleFont: { size: 13 },
                                bodyFont: { size: 12 },
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    title: function(items) {
                                        return data[items[0].dataIndex].name;
                                    },
                                    label: function(item) {
                                        return item.raw + ' commentaire' + (item.raw > 1 ? 's' : '');
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    font: { size: 12 },
                                    color: '#64748b'
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.05)'
                                }
                            },
                            x: {
                                ticks: {
                                    font: { size: 11 },
                                    color: '#64748b',
                                    maxRotation: 45,
                                    minRotation: 0
                                },
                                grid: { display: false }
                            }
                        }
                    }
                });
            })
            .catch(err => console.error('Erreur stats:', err));
    }

// Load chart on dashboard load
    loadCommentStats();
});
</script>

<!-- Custom Modal System -->
<div id="custom-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 10000; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
    <div id="custom-modal-box" style="background: var(--white); color: var(--text-dark); padding: 30px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.3); max-width: 400px; width: 90%; transform: scale(0.9); transition: transform 0.3s ease; text-align: center;">
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
</script>

<script>
(function() {
    var notifLastId = 0;
    var notifInitialized = false;
    var notifBellCount = 0;

    function initNotifBell() {
        var bell = document.getElementById('notif-bell');
        if (!bell || bell.getAttribute('data-notif-init') === '1') return;
        bell.setAttribute('data-notif-init', '1');
        function goDashboard() {
            notifBellCount = 0;
            var badge = document.getElementById('notif-badge');
            if (badge) { badge.hidden = true; badge.textContent = '0'; }
            if (typeof switchView === 'function') switchView('dashboard');
        }
        bell.addEventListener('click', goDashboard);
        bell.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); goDashboard(); }
        });
    }

    function updateNotifBadge(count) {
        notifBellCount += count;
        var badge = document.getElementById('notif-badge');
        if (!badge) return;
        if (notifBellCount > 0) {
            badge.textContent = notifBellCount > 99 ? '99+' : String(notifBellCount);
            badge.hidden = false;
            var bell = document.getElementById('notif-bell');
            if (bell) {
                bell.style.transform = 'rotate(12deg)';
                setTimeout(function() { bell.style.transform = 'rotate(-12deg)'; }, 120);
                setTimeout(function() { bell.style.transform = 'none'; }, 280);
            }
        } else {
            badge.hidden = true;
        }
    }

    function showDesktopNotification(comment) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        var articleName = comment.article_name || ('Article #' + comment.article_id);
        var isFlagged = comment.flagged == 1;
        var title = isFlagged
            ? 'Commentaire signalé'
            : 'Nouveau commentaire — ' + (comment.username || 'Anonyme');
        var body = isFlagged
            ? ('Commentaire à modérer sur « ' + articleName + ' »')
            : (String(comment.comment || '').substring(0, 100) + (String(comment.comment || '').length > 100 ? '…' : '') + '\n' + articleName);
        try {
            var n = new Notification(title, { body: body, icon: 'assets/logo.png', tag: 'c-' + comment.id });
            n.onclick = function() { window.focus(); if (typeof switchView === 'function') switchView('dashboard'); n.close(); };
            setTimeout(function() { try { n.close(); } catch (e) {} }, 8000);
        } catch (e) {}
    }

    function pollNewComments() {
        fetch('index.php?controller=comment&action=check_new&last_id=' + notifLastId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.new_comments || !data.new_comments.length) return;
                if (!notifInitialized) {
                    notifInitialized = true;
                    var maxId = 0;
                    data.new_comments.forEach(function(c) { if (c.id > maxId) maxId = c.id; });
                    notifLastId = maxId;
                    return;
                }
                var newCount = 0;
                data.new_comments.forEach(function(c) {
                    if (c.id > notifLastId) {
                        showDesktopNotification(c);
                        newCount++;
                    }
                });
                if (newCount > 0) {
                    updateNotifBadge(newCount);
                    if (window.currentAdminView === 'dashboard' && typeof loadAdminDashboard === 'function') {
                        loadAdminDashboard();
                    }
                }
                var maxId = notifLastId;
                data.new_comments.forEach(function(c) { if (c.id > maxId) maxId = c.id; });
                notifLastId = maxId;
            })
            .catch(function() {});
    }

    function boot() {
        initNotifBell();
        pollNewComments();
        setInterval(pollNewComments, 10000);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>

</body>
</html>