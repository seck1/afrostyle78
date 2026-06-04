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

// Charger les settings
$allSettings = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$rate        = (float)($allSettings['stripe_fcfa_to_eur'] ?? 0.00152);
$stripeOk    = !empty($allSettings['stripe_secret_key']);
$waveNumber  = $allSettings['wave_number'] ?? '';
$omNumber    = $allSettings['orange_money_number'] ?? '';
$waveApiKey  = $allSettings['wave_api_key'] ?? '';
$isPaiementCarte = $order && $order['payment_method'] === 'carte';
$isPaiementWave  = $order && $order['payment_method'] === 'wave';
$isPaiementOM    = $order && $order['payment_method'] === 'orange_money';
$isPaid          = $order && $order['payment_status'] === 'paid';
?>

<div class="container" style="padding: 60px 40px;">
    <div class="confirmation-box">

        <?php if ($isPaid): ?>
        <!-- PAIEMENT CONFIRMÉ -->
        <div class="confirmation-icon">✅</div>
        <div class="confirmation-number">Commande <?= htmlspecialchars($orderNumber) ?></div>
        <h1 class="confirmation-title">Paiement confirmé !</h1>
        <p style="color:var(--text-muted); font-size:1rem; line-height:1.8; margin:16px 0 28px;">
            Merci <strong><?= htmlspecialchars($order['first_name'] ?? '') ?></strong> ! Votre paiement a été reçu.<br>
            Nos artisans vont commencer la confection de votre commande.
        </p>

        <?php elseif ($order && !$isPaid): ?>
        <!-- PAGE DE PAIEMENT -->
        <div class="confirmation-icon">🛍️</div>
        <div class="confirmation-number">Commande #<?= htmlspecialchars($orderNumber) ?></div>
        <h1 class="confirmation-title">Effectuez votre paiement</h1>
        <p style="color:var(--text-muted); font-size:1rem; margin:8px 0 28px;">
            Montant total : <strong style="font-size:1.4rem;color:var(--dark);"><?= number_format($order['total_amount'], 2, ',', ' ') ?> €</strong>
        </p>

        <!-- SÉLECTION MÉTHODE DE PAIEMENT -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:28px;">

            <?php if($isPaiementWave || $order['payment_method'] === 'wave'): ?>
            <!-- WAVE -->
            <div style="border:2px solid #00b464;padding:20px;text-align:center;border-radius:8px;background:#f0fff8;">
                <div style="font-size:2rem;margin-bottom:8px;">📱</div>
                <div style="font-weight:700;color:#00b464;font-size:1rem;margin-bottom:4px;">Wave</div>
                <?php if($waveApiKey): ?>
                <button onclick="payWithWave()" id="wave-btn" style="background:#00b464;color:#fff;border:none;padding:10px 20px;font-size:0.9rem;font-weight:700;cursor:pointer;border-radius:4px;width:100%;margin-top:8px;">
                    Payer avec Wave
                </button>
                <?php else: ?>
                <div style="font-size:0.85rem;color:#555;margin-top:8px;">Envoyez <strong><?= number_format($order['total_amount'],2,',','') ?> €</strong> au :</div>
                <div style="font-size:1.2rem;font-weight:700;color:#00b464;margin:6px 0;"><?= htmlspecialchars($waveNumber) ?></div>
                <div style="font-size:0.78rem;color:#888;">Réf: #<?= htmlspecialchars($orderNumber) ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if($order['payment_method'] === 'orange_money'): ?>
            <!-- ORANGE MONEY -->
            <div style="border:2px solid #ff8c00;padding:20px;text-align:center;border-radius:8px;background:#fff9f0;">
                <div style="font-size:2rem;margin-bottom:8px;">📱</div>
                <div style="font-weight:700;color:#ff8c00;font-size:1rem;margin-bottom:4px;">Orange Money</div>
                <div style="font-size:0.85rem;color:#555;margin-top:8px;">Envoyez <strong><?= number_format($order['total_amount'],2,',','') ?> €</strong> au :</div>
                <div style="font-size:1.2rem;font-weight:700;color:#ff8c00;margin:6px 0;"><?= htmlspecialchars($omNumber) ?></div>
                <div style="font-size:0.78rem;color:#888;">Réf: #<?= htmlspecialchars($orderNumber) ?></div>
            </div>
            <?php endif; ?>

            <?php if($order['payment_method'] === 'virement'): ?>
            <!-- VIREMENT -->
            <div style="border:2px solid #4a5568;padding:20px;text-align:center;border-radius:8px;background:#f8f9fa;">
                <div style="font-size:2rem;margin-bottom:8px;">🏦</div>
                <div style="font-weight:700;color:#4a5568;font-size:1rem;margin-bottom:8px;">Virement bancaire</div>
                <div style="text-align:left;font-size:0.85rem;color:#555;">
                    <div><strong>Banque :</strong> <?= htmlspecialchars($allSettings['bank_name'] ?? '') ?></div>
                    <div><strong>Titulaire :</strong> <?= htmlspecialchars($allSettings['bank_owner'] ?? '') ?></div>
                    <div><strong>IBAN :</strong> <?= htmlspecialchars($allSettings['bank_iban'] ?? '') ?></div>
                    <div><strong>Référence :</strong> #<?= htmlspecialchars($orderNumber) ?></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if($order['payment_method'] === 'cash'): ?>
            <!-- ESPÈCES -->
            <div style="border:2px solid #38a169;padding:20px;text-align:center;border-radius:8px;background:#f0fff4;">
                <div style="font-size:2rem;margin-bottom:8px;">💵</div>
                <div style="font-weight:700;color:#38a169;font-size:1rem;margin-bottom:8px;">Espèces à la livraison</div>
                <div style="font-size:0.85rem;color:#555;">Vous payez <strong><?= number_format($order['total_amount'],2,',','') ?> €</strong> en espèces à la réception de votre commande.</div>
            </div>
            <?php endif; ?>

        </div>

        <?php if(in_array($order['payment_method'], ['wave','orange_money'])): ?>
        <div style="background:#fffbf0;border:1px solid rgba(200,146,26,0.2);padding:14px;font-size:0.85rem;color:#7a6248;border-radius:4px;margin-bottom:20px;">
            📸 Après avoir effectué le paiement, envoyez la <strong>capture d'écran</strong> par WhatsApp au <strong><?= htmlspecialchars($waveNumber) ?></strong>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="confirmation-icon">🎉</div>
        <h1 class="confirmation-title">Commande enregistrée</h1>
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


<?php if ($order && $isPaiementWave && !$isPaid && $waveApiKey): ?>
<script>
function payWithWave() {
    const btn = document.getElementById('wave-btn');
    btn.textContent = '⏳ Redirection...';
    btn.disabled = true;

    fetch('<?= SITE_URL ?>/wave-checkout.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'order_number=<?= urlencode($orderNumber) ?>'
    })
    .then(r => r.json())
    .then(data => {
        if (data.url) {
            window.location.href = data.url;
        } else {
            alert('Erreur : ' + (data.error || 'Réessayez.'));
            btn.textContent = '📱 Payer avec Wave';
            btn.disabled = false;
        }
    });
}
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
