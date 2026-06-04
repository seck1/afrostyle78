<?php
require_once 'includes/auth.php';

$adminTitle = 'Tableau de bord';
$db = getDB();
$stats = [
    'total_orders' => $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'pending' => $db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn(),
    'revenue' => $db->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status != 'cancelled'")->fetchColumn(),
    'customers' => $db->query("SELECT COUNT(*) FROM customers")->fetchColumn(),
    'products' => $db->query("SELECT COUNT(*) FROM products WHERE active=1")->fetchColumn(),
    'shipped' => $db->query("SELECT COUNT(*) FROM orders WHERE status IN ('shipped','in_production')")->fetchColumn(),
    'delivered_month' => $db->query("SELECT COUNT(*) FROM orders WHERE status='delivered' AND MONTH(created_at)=MONTH(NOW())")->fetchColumn(),
];
$recentOrders = $db->query("SELECT o.*, c.first_name, c.last_name FROM orders o JOIN customers c ON o.customer_id=c.id ORDER BY o.created_at DESC LIMIT 8")->fetchAll();
$statusLabels = ['pending'=>'En attente','confirmed'=>'Confirmée','in_production'=>'En confection','shipped'=>'Expédiée','delivered'=>'Livrée','cancelled'=>'Annulée'];

require_once 'includes/admin_header.php';
?>

<div class="stat-grid">
    <div class="stat-card" style="border-color:#C8921A;">
        <div class="stat-icon">📦</div>
        <div class="stat-value"><?= $stats['total_orders'] ?></div>
        <div class="stat-label">Commandes totales</div>
    </div>
    <div class="stat-card" style="border-color:#E65100;">
        <div class="stat-icon">⏳</div>
        <div class="stat-value"><?= $stats['pending'] ?></div>
        <div class="stat-label">En attente</div>
    </div>
    <div class="stat-card" style="border-color:#1A7A4A;">
        <div class="stat-icon">💰</div>
        <div class="stat-value" style="font-size:1.3rem;"><?= number_format($stats['revenue'], 0, ',', ' ') ?></div>
        <div class="stat-label">CA total (€)</div>
    </div>
    <div class="stat-card" style="border-color:#1565C0;">
        <div class="stat-icon">👥</div>
        <div class="stat-value"><?= $stats['customers'] ?></div>
        <div class="stat-label">Clients</div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 300px; gap:24px;">
    <div class="admin-card">
        <div class="admin-card-header">
            <div class="admin-card-title">Commandes récentes</div>
            <a href="commandes.php" class="btn-admin btn-outline btn-sm">Voir toutes →</a>
        </div>
        <table class="admin-table">
            <thead><tr><th>N° Commande</th><th>Client</th><th>Montant</th><th>Statut</th><th>Date</th><th></th></tr></thead>
            <tbody>
                <?php foreach($recentOrders as $ord): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($ord['order_number']) ?></strong></td>
                    <td><?= htmlspecialchars($ord['first_name'] . ' ' . $ord['last_name']) ?></td>
                    <td><?= number_format($ord['total_amount'], 0, ',', ' ') ?> €</td>
                    <td><span class="status-badge status-<?= $ord['status'] ?>"><?= $statusLabels[$ord['status']] ?? $ord['status'] ?></span></td>
                    <td style="color:var(--muted); font-size:0.78rem;"><?= date('d/m/Y', strtotime($ord['created_at'])) ?></td>
                    <td><a href="commande-detail.php?id=<?= $ord['id'] ?>" class="btn-admin btn-gold btn-sm">Voir</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div>
        <div class="admin-card">
            <div class="admin-card-header"><div class="admin-card-title">Résumé</div></div>
            <div style="display:flex; flex-direction:column; gap:2px;">
                <?php
                $rows = [
                    ['Produits actifs', $stats['products'],''],
                    ['En production/expédition', $stats['shipped'], 'color:#E65100'],
                    ['Livrées ce mois', $stats['delivered_month'], 'color:#1A7A4A'],
                ];
                foreach($rows as $r): ?>
                <div style="display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid #F0EBE3; font-size:0.82rem;">
                    <span style="color:var(--muted);"><?= $r[0] ?></span>
                    <strong style="<?= $r[2] ?>"><?= $r[1] ?></strong>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="admin-card">
            <div class="admin-card-title" style="margin-bottom:16px;">Actions rapides</div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <a href="produits.php?action=add" class="btn-admin btn-gold" style="justify-content:center;">+ Ajouter un produit</a>
                <a href="commandes.php?filter=pending" class="btn-admin btn-dark" style="justify-content:center;">⏳ Commandes en attente</a>
                <a href="livraisons.php" class="btn-admin btn-outline" style="justify-content:center;">🚚 Suivi livraisons</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
