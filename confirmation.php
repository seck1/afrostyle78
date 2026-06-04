<?php
ob_start();
$pageTitle = 'Commande confirmée';
require_once 'includes/header.php';

$orderNumber  = $_GET['order'] ?? '';
$paymentStatus = $_GET['payment'] ?? '';
$orderInfo    = $_SESSION['last_order'] ?? null;

// Charger les infos commande + paiement depuis la DB
$db    = getDB();
$order = null;
if ($orderNumber) {
    $stmt = $db->prepare("SELECT o.*, c.first_name, c.last_name FROM orders o JOIN customers c ON o.customer_id=c.id WHERE o.order_number=?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();
}

// Charger le taux de conversion
$settings = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_group='stripe'")->fetchAll(PDO::FETCH_KEY_PAIR);
$rate     = (float)($settings['stripe_fcfa_to_eur'] ?? 0.00152);
$stripeOk = !empty($settings['stripe_secret_key']);
$isPaiementCarte = $order && $order['payment_method'] === 'carte';
$isPaid          = $order && $order['payment_status'] === 'paid';
?>

<div class="container" style="padding: 60px 40px;">
    <div class="confirmation-box">

        <?php if ($paymentStatus === 'success' && $isPaid): ?>
        <div class="confirmation-icon">✅</div>
        <div class="confirmation-number">Commande <?= htmlspecialchars($orderNumber) ?></div>
        <h1 class="confirmation-title">Paiement reçu !</h1>
        <p style="color:var(--text-muted); font-size:1rem; line-height:1.8; margin:16px 0 28px;">
            Votre paiement par carte a été <strong style="color:#38a169;">confirmé par Stripe</strong>.<br>
            Nos artisans vont commencer la confection de votre commande.
        </p>

        <?php elseif ($paymentStatus === 'cancelled'): ?>
        <div class="confirmation-icon">⚠️</div>
        <div class="confirmation-number">Commande <?= htmlspecialchars($orderNumber) ?></div>
        <h1 class="confirmation-title">Paiement annulé</h1>
        <p style="color:var(--text-muted); font-size:1rem; line-height:1.8; margin:16px 0 28px;">
            Votre paiement par carte a été annulé. Votre commande est toujours enregistrée.<br>
            Vous pouvez réessayer ou choisir un autre mode de paiement.
        </p>
        <?php if ($order): ?>
        <button onclick="payWithStripe()" class="btn btn-primary" style="margin-bottom:16px;">
            💳 Réessayer le paiement par carte
        </button>
        <?php endif; ?>

        <?php else: ?>
        <div class="confirmation-icon">🎉</div>
        <div class="confirmation-number">Commande <?= htmlspecialchars($orderNumber) ?></div>
        <h1 class="confirmation-title">Merci pour votre commande !</h1>
        <?php if($orderInfo || $order): ?>
        <p style="color:var(--text-muted); font-size:1rem; line-height:1.8; margin:16px 0 28px;">
            Bonjour <strong><?= htmlspecialchars($order['first_name'] ?? $orderInfo['name'] ?? '') ?></strong>,
            votre commande <strong>#<?= htmlspecialchars($orderNumber) ?></strong> d'un montant de
            <strong><?= number_format($order['total_amount'] ?? $orderInfo['total'] ?? 0, 0, ',', ' ') ?> FCFA</strong>
            a bien été reçue.
        </p>
        <?php endif; ?>
        <?php endif; ?>

        <!-- PAIEMENT PAR CARTE — si non payé -->
        <?php if ($order && $isPaiementCarte && !$isPaid && $paymentStatus !== 'cancelled'): ?>
        <div style="background:#f0f0ff;border:1px solid rgba(99,91,255,0.2);padding:24px;margin-bottom:28px;text-align:center;">
            <p style="margin:0 0 6px;font-size:0.85rem;font-weight:700;color:#635bff;letter-spacing:0.05em;text-transform:uppercase;">💳 Paiement par carte</p>
            <p style="margin:0 0 16px;color:#444;font-size:0.95rem;line-height:1.6;">
                Vous avez choisi de payer par carte bancaire.<br>
                Montant : <strong><?= number_format($order['total_amount'], 0, ',', ' ') ?> FCFA</strong>
                = <strong style="color:#635bff;"><?= number_format($order['total_amount'] * $rate, 2, ',', ' ') ?> €</strong>
            </p>
            <?php if ($stripeOk): ?>
            <button onclick="payWithStripe()" id="stripe-btn" class="btn btn-primary" style="background:#635bff;border-color:#635bff;font-size:1rem;padding:14px 40px;">
                💳 Payer maintenant par carte
            </button>
            <p style="margin:10px 0 0;font-size:0.82rem;color:#888;">Paiement sécurisé via Stripe — Visa, Mastercard</p>
            <?php else: ?>
            <div style="background:#fff8f0;border:1px solid #f6ad55;padding:12px 16px;color:#c05621;font-size:0.9rem;">
                ⚠ Le paiement en ligne n'est pas encore configuré. Notre équipe vous contactera pour organiser le paiement.
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ÉTAPES -->
        <?php if ($paymentStatus !== 'cancelled'): ?>
        <div style="background:var(--cream-2); padding:24px; margin-bottom:28px; text-align:left;">
            <div style="font-size:0.78rem; font-weight:700; letter-spacing:0.15em; text-transform:uppercase; color:var(--gold); margin-bottom:16px;">Étapes suivantes</div>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <span style="background:var(--gold); color:var(--dark); width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.78rem; font-weight:700; flex-shrink:0;">1</span>
                    <span style="font-size:0.95rem;"><?= ($isPaid) ? '✅ Paiement confirmé' : 'Notre équipe valide votre commande sous 24h' ?></span>
                </div>
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <span style="background:var(--gold); color:var(--dark); width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.78rem; font-weight:700; flex-shrink:0;">2</span>
                    <span style="font-size:0.95rem;">Nos artisans commencent la confection (7–14 jours)</span>
                </div>
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <span style="background:var(--gold); color:var(--dark); width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.78rem; font-weight:700; flex-shrink:0;">3</span>
                    <span style="font-size:0.95rem;">Livraison à votre adresse ou retrait en boutique</span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
            <a href="<?= SITE_URL ?>/suivi.php?ref=<?= htmlspecialchars($orderNumber) ?>" class="btn btn-primary">Suivre ma commande</a>
            <a href="<?= SITE_URL ?>/boutique.php" class="btn btn-dark">Continuer les achats</a>
        </div>

    </div>
</div>

<?php if ($order && $isPaiementCarte && !$isPaid && $stripeOk): ?>
<script>
function payWithStripe() {
    const btn = document.getElementById('stripe-btn');
    if (btn) { btn.textContent = '⏳ Redirection...'; btn.disabled = true; }

    fetch('<?= SITE_URL ?>/stripe-checkout.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'order_number=<?= urlencode($orderNumber) ?>&total_fcfa=<?= $order['total_amount'] ?>'
    })
    .then(r => r.json())
    .then(data => {
        if (data.url) {
            window.location.href = data.url;
        } else {
            alert('Erreur : ' + (data.error || 'Réessayez.'));
            if (btn) { btn.textContent = '💳 Payer maintenant par carte'; btn.disabled = false; }
        }
    });
}
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
