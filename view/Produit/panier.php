<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . "/../../controller/Produit/PanierController.php";
$panierController = new PanierController();
$items = $panierController->list();
$total = 0;
foreach ($items as $item) {
    $total += $item['Prix'] * $item['quantite'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier - SmartPlate</title>
    <link rel="stylesheet" href="templates/template.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .panier-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .panier-grid {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
        }
        @media (max-width: 900px) {
            .panier-grid {
                grid-template-columns: 1fr;
            }
        }
        .cart-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.5rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }
        .cart-item:hover {
            transform: translateY(-2px);
        }
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 12px;
        }
        .item-info {
            flex: 1;
        }
        .item-info h3 {
            margin: 0 0 0.5rem 0;
            color: #1a1a1a;
        }
        .item-info .price {
            color: #20c997;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .item-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #f8fafc;
            padding: 0.5rem;
            border-radius: 10px;
        }
        .quantity-control button {
            background: white;
            border: 1px solid #e2e8f0;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.2s;
        }
        .quantity-control button:hover {
            background: #d4f283;
            border-color: #d4f283;
        }
        .remove-btn {
            color: #e74c3c;
            cursor: pointer;
            font-size: 1.2rem;
            background: none;
            border: none;
            padding: 0.5rem;
        }
        .summary-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        .summary-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            color: #64748b;
        }
        .summary-total {
            border-top: 2px solid #f1f5f9;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            display: flex;
            justify-content: space-between;
            font-weight: 800;
            font-size: 1.4rem;
            color: #1a1a1a;
        }
        .checkout-btn {
            width: 100%;
            background: #d4f283;
            color: #1a1a1a;
            border: none;
            padding: 1.2rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            margin-top: 2rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        .checkout-btn:hover {
            background: #c5e66a;
            transform: scale(1.02);
        }
        .empty-cart {
            text-align: center;
            padding: 5rem 0;
        }
        .empty-cart span {
            font-size: 5rem;
            display: block;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../front_sidebar.php'; ?>

<div class="dashboard">
    <header class="dashboard-header">
        <h1>Mon Panier</h1>
        <span class="date-badge"><?= count($items) ?> article(s)</span>
    </header>

    <div class="panier-container">
        <?php if (empty($items)): ?>
            <div class="empty-cart card">
                <span>🛒</span>
                <h2>Votre panier est vide</h2>
                <p>Découvrez nos produits frais et sains.</p>
                <a href="Front_produits.php" class="btn-action" style="display:inline-block; margin-top:1.5rem; text-decoration:none;">Voir les produits</a>
            </div>
        <?php else: ?>
            <div class="panier-grid">
                <div class="cart-items">
                    <?php foreach ($items as $item): ?>
                        <div class="cart-item">
                            <img src="/integration/view/<?= htmlspecialchars($item['Image']) ?>" alt="<?= htmlspecialchars($item['Nom']) ?>">
                            <div class="item-info">
                                <h3><?= htmlspecialchars($item['Nom']) ?></h3>
                                <div class="price"><?= number_format($item['Prix'], 2) ?> DT</div>
                            </div>
                            <div class="item-actions">
                                <div class="quantity-control">
                                    <button onclick="updateQty(<?= $item['code_produit'] ?>, <?= $item['quantite'] - 1 ?>)">-</button>
                                    <span><?= $item['quantite'] ?></span>
                                    <button onclick="updateQty(<?= $item['code_produit'] ?>, <?= $item['quantite'] + 1 ?>)">+</button>
                                </div>
                                <button class="remove-btn" onclick="removeItem(<?= $item['code_produit'] ?>)">🗑️</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-card">
                    <h3>Récapitulatif</h3>
                    <div class="summary-line">
                        <span>Sous-total</span>
                        <span><?= number_format($total, 2) ?> DT</span>
                    </div>
                    <div class="summary-line">
                        <span>Livraison</span>
                        <span style="color: #20c997;">Gratuite</span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span><?= number_format($total, 2) ?> DT</span>
                    </div>
                    <button class="checkout-btn">Passer la commande</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modern Confirmation Modal -->
<div id="confirmModal" class="confirm-modal">
    <div class="confirm-content">
        <span class="confirm-icon">🗑️</span>
        <h3 class="confirm-title">Supprimer l'article ?</h3>
        <p class="confirm-text">Cette action retirera le produit de votre panier.</p>
        <div class="confirm-actions">
            <button class="confirm-btn confirm-btn-cancel" onclick="hideConfirm()">Annuler</button>
            <button class="confirm-btn confirm-btn-delete" id="confirmDeleteBtn">Supprimer</button>
        </div>
    </div>
</div>

<script>
function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icon = type === 'success' ? '✓' : '✕';
    toast.innerHTML = `
        <div class="toast-icon">${icon}</div>
        <div class="toast-content">${message}</div>
    `;
    container.appendChild(toast);
    toast.offsetHeight;
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}

function updateQty(code, qty) {
    if (qty < 1) {
        removeItem(code);
        return;
    }
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('code_produit', code);
    formData.append('quantite', qty);

    fetch('../../controller/Produit/PanierController.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            location.reload();
        } else {
            showToast(data.message, 'error');
        }
    });
}

let itemToDelete = null;

function removeItem(code) {
    itemToDelete = code;
    document.getElementById('confirmModal').style.display = 'flex';
    document.getElementById('confirmDeleteBtn').onclick = () => {
        executeDelete(code);
    };
}

function hideConfirm() {
    document.getElementById('confirmModal').style.display = 'none';
    itemToDelete = null;
}

function executeDelete(code) {
    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('code_produit', code);

    fetch('../../controller/Produit/PanierController.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        hideConfirm();
        if (data.status === 'success') {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(err => {
        hideConfirm();
        showToast('Erreur lors de la suppression', 'error');
    });
}
</script>


</body>
</html>
