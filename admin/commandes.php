<?php
require_once 'includes/auth.php';
$adminTitle = 'Commandes';
$db = getDB();

$filter = $_GET['filter'] ?? '';
$where = '1=1';
$params = [];
if ($filter && $filter !== 'all') { $where .= ' AND o.status = ?'; $params[] = $filter; }

$search = trim($_GET['s'] ?? '');
if ($search) {
    $where .= ' AND (o.order_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.phone LIKE ?)';
    $params = array_merge($params, ["%$search%","%$search%","%$search%","%$search%"]);
}

$stmt = $db->prepare("SELECT o.*, c.first_name, c.last_name, c.email, c.phone FROM orders o JOIN customers c ON o.customer_id=c.id WHERE $where ORDER BY o.created_at DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();

$statusLabels = ['pending'=>'En attente','confirmed'=>'Confirmée','in_production'=>'En confection','shipped'=>'Expédiée','delivered'=>'Livrée','cancelled'=>'Annulée'];
require_once 'includes/admin_header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <div class="admin-card-title">Toutes les commandes (<?= count($orders) ?>)</div>
        <form method="GET" style="display:flex; gap:8px;">
            <input type="text" name="s" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>" style="padding:7px 14px; border:1px solid #E8E0D8; font-family:'Syne',sans-serif; font-size:1.05rem; outline:none;">
            <select name="filter" onchange="this.form.submit()" style="padding:7px 14px; border:1px solid #E8E0D8; font-family:'Syne',sans-serif; font-size:1.05rem; outline:none;">
                <option value="">Tous statuts</option>
                <?php foreach($statusLabels as $v=>$l): ?>
                <option value="<?= $v ?>" <?= $filter===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>N° Commande</th>
                <th>Client</th>
                <th>Contact</th>
                <th>Montant</th>
                <th>Livraison</th>
                <th>Statut</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($orders as $ord): ?>
            <tr>
                <td><strong style="font-size:1.1rem;"><?= htmlspecialchars($ord['order_number']) ?></strong></td>
                <td><?= htmlspecialchars($ord['first_name'] . ' ' . $ord['last_name']) ?></td>
                <td style="font-size:1.05rem; color:var(--muted);"><?= htmlspecialchars($ord['phone']) ?></td>
                <td><strong><?= number_format($ord['total_amount'], 0, ',', ' ') ?> FCFA</strong></td>
                <td><span style="font-size:1rem;"><?= $ord['delivery_method'] === 'domicile' ? '🚚 Domicile' : '📦 Retrait' ?></span></td>
                <td><span class="status-badge status-<?= $ord['status'] ?>"><?= $statusLabels[$ord['status']] ?? $ord['status'] ?></span></td>
                <td style="color:var(--muted); font-size:1.05rem;"><?= date('d/m/Y', strtotime($ord['created_at'])) ?></td>
                <td><a href="commande-detail.php?id=<?= $ord['id'] ?>" class="btn-admin btn-gold btn-sm">Détail</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
