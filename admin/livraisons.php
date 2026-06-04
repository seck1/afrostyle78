<?php
require_once 'includes/auth.php';
$adminTitle = 'Suivi Livraisons';
$db = getDB();

$orders = $db->query("SELECT o.*, c.first_name, c.last_name, c.phone, c.city,
    (SELECT dt.status FROM delivery_tracking dt WHERE dt.order_id=o.id ORDER BY dt.created_at DESC LIMIT 1) as last_tracking
    FROM orders o JOIN customers c ON o.customer_id=c.id
    WHERE o.status IN ('confirmed','in_production','shipped')
    ORDER BY o.updated_at DESC")->fetchAll();

$statusLabels = ['pending'=>'En attente','confirmed'=>'Confirmée','in_production'=>'En confection','shipped'=>'Expédiée','delivered'=>'Livrée','cancelled'=>'Annulée'];
require_once 'includes/admin_header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <div class="admin-card-title">Commandes en cours (<?= count($orders) ?>)</div>
    </div>
    <?php if(empty($orders)): ?>
    <div style="text-align:center; padding:48px; color:var(--muted);">Aucune commande en cours de livraison.</div>
    <?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>N° Commande</th>
                <th>Client</th>
                <th>Téléphone</th>
                <th>Ville</th>
                <th>Statut</th>
                <th>Dernier suivi</th>
                <th>Mise à jour</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($orders as $o): ?>
            <tr>
                <td><strong><?= htmlspecialchars($o['order_number']) ?></strong></td>
                <td><?= htmlspecialchars($o['first_name'] . ' ' . $o['last_name']) ?></td>
                <td><?= htmlspecialchars($o['phone']) ?></td>
                <td><?= htmlspecialchars($o['city']) ?></td>
                <td><span class="status-badge status-<?= $o['status'] ?>"><?= $statusLabels[$o['status']] ?? $o['status'] ?></span></td>
                <td style="font-size:1.05rem; color:var(--muted);"><?= $o['last_tracking'] ? htmlspecialchars($o['last_tracking']) : '—' ?></td>
                <td style="font-size:1rem; color:var(--muted);"><?= date('d/m H:i', strtotime($o['updated_at'])) ?></td>
                <td><a href="commande-detail.php?id=<?= $o['id'] ?>" class="btn-admin btn-gold btn-sm">Gérer</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
