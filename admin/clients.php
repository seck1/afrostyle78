<?php
require_once 'includes/auth.php';
$adminTitle = 'Clients';
$db = getDB();
$clients = $db->query("SELECT c.*, COUNT(o.id) as order_count, SUM(o.total_amount) as total_spent FROM customers c LEFT JOIN orders o ON o.customer_id=c.id GROUP BY c.id ORDER BY c.created_at DESC")->fetchAll();
require_once 'includes/admin_header.php';
?>
<div class="admin-card">
    <div class="admin-card-header"><div class="admin-card-title">Clients (<?= count($clients) ?>)</div></div>
    <table class="admin-table">
        <thead><tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Ville</th><th>Commandes</th><th>Total dépensé</th><th>Inscrit le</th></tr></thead>
        <tbody>
            <?php foreach($clients as $c): ?>
            <tr>
                <td><strong><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></strong></td>
                <td style="color:var(--muted); font-size:1.1rem;"><?= htmlspecialchars($c['email']) ?></td>
                <td style="font-size:1.1rem;"><?= htmlspecialchars($c['phone']) ?></td>
                <td style="font-size:1.1rem;"><?= htmlspecialchars($c['city']) ?></td>
                <td><strong><?= $c['order_count'] ?></strong></td>
                <td><?= number_format($c['total_spent'] ?? 0, 0, ',', ' ') ?> €</td>
                <td style="font-size:1rem; color:var(--muted);"><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once 'includes/admin_footer.php'; ?>
