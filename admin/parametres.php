<?php
require_once 'includes/auth.php';
$db  = getDB();
$msg = '';

// Sauvegarder
$allowedKeys = [
    'stripe_publishable_key','stripe_secret_key','stripe_mode','stripe_currency','stripe_fcfa_to_eur',
    'site_name','site_phone','site_email','site_address',
    'wave_number','orange_money_number','bank_name','bank_iban','bank_owner',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($allowedKeys as $key) {
        if (isset($_POST[$key])) {
            $stmt = $db->prepare("UPDATE settings SET setting_value=? WHERE setting_key=?");
            $stmt->execute([trim($_POST[$key]), $key]);
        }
    }
    // Upload photo "Tout voir"
    if (isset($_FILES['cat_all_image']) && $_FILES['cat_all_image']['size'] > 0 && $_FILES['cat_all_image']['error'] === UPLOAD_ERR_OK) {
        $ext  = strtolower(pathinfo($_FILES['cat_all_image']['name'], PATHINFO_EXTENSION));
        $name = uniqid('cat_all_', true) . '.' . $ext;
        if (move_uploaded_file($_FILES['cat_all_image']['tmp_name'], UPLOADS_DIR . $name)) {
            $db->prepare("UPDATE settings SET setting_value=? WHERE setting_key='cat_all_image'")->execute([$name]);
        }
    }
    if (isset($_POST['delete_cat_all_image'])) {
        $db->prepare("UPDATE settings SET setting_value='' WHERE setting_key='cat_all_image'")->execute();
    }
    $msg = 'success';
}

// Charger tous les settings
$rows = $db->query("SELECT * FROM settings ORDER BY setting_group, id")->fetchAll();
$settings = [];
foreach ($rows as $r) $settings[$r['setting_key']] = $r;

function sv(array $settings, string $key): string {
    return htmlspecialchars($settings[$key]['setting_value'] ?? '');
}

$currentPage = 'parametres';
$adminTitle  = 'Paramètres';
require_once 'includes/admin_header.php';
?>

<div class="admin-content">

<?php if ($msg === 'success'): ?>
<div style="background:#f0fff4;border:1px solid #9ae6b4;color:#276749;padding:14px 20px;margin-bottom:24px;font-size:1rem;display:flex;align-items:center;gap:10px;">
    ✓ Paramètres enregistrés avec succès.
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<div style="display:grid; grid-template-columns:1fr 1fr; gap:28px; align-items:start;">

  <!-- COLONNE GAUCHE -->
  <div style="display:flex; flex-direction:column; gap:24px;">

    <!-- STRIPE -->
    <div class="admin-card">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid #f0ebe0;">
        <div style="background:#635bff;color:#fff;padding:8px 14px;font-size:1.1rem;font-weight:700;letter-spacing:0.05em;">stripe</div>
        <div>
          <div style="font-size:1.1rem;font-weight:700;color:var(--dark);">Paiement par carte</div>
          <div style="font-size:0.88rem;color:var(--muted);">Clés API Stripe</div>
        </div>
        <div style="margin-left:auto;">
          <span id="stripe-mode-badge" style="padding:4px 14px;font-size:0.82rem;font-weight:700;border-radius:20px;
            <?= sv($settings,'stripe_mode')==='live' ? 'background:rgba(56,161,105,0.1);color:#276749;' : 'background:rgba(255,152,0,0.1);color:#c05621;' ?>">
            <?= sv($settings,'stripe_mode') === 'live' ? '🟢 LIVE' : '🟡 TEST' ?>
          </span>
        </div>
      </div>

      <div class="admin-form">
        <div style="margin-bottom:18px;">
          <label>Mode</label>
          <select name="stripe_mode" onchange="updateModeBadge(this.value)" style="width:100%;padding:12px 14px;border:1.5px solid #e0d8ce;font-family:inherit;font-size:1rem;background:#fff;outline:none;">
            <option value="test" <?= sv($settings,'stripe_mode')==='test'?'selected':'' ?>>🟡 Test — pour les essais</option>
            <option value="live" <?= sv($settings,'stripe_mode')==='live'?'selected':'' ?>>🟢 Live — paiements réels</option>
          </select>
        </div>
        <div style="margin-bottom:18px;">
          <label>Publishable Key</label>
          <input type="text" name="stripe_publishable_key" value="<?= sv($settings,'stripe_publishable_key') ?>" placeholder="pk_test_...">
        </div>
        <div style="margin-bottom:18px;">
          <label>Secret Key</label>
          <div style="position:relative;">
            <input type="password" name="stripe_secret_key" id="stripe_sk" value="<?= sv($settings,'stripe_secret_key') ?>" placeholder="sk_test_...">
            <button type="button" onclick="toggleSecret()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:0.9rem;">
              👁
            </button>
          </div>
          <small style="color:var(--muted);font-size:0.85rem;">Ne partagez jamais cette clé — elle donne accès à votre compte Stripe.</small>
        </div>
        <div style="margin-bottom:0;">
          <label>Devise</label>
          <select name="stripe_currency" style="width:100%;padding:12px 14px;border:1.5px solid #e0d8ce;font-family:inherit;font-size:1rem;background:#fff;outline:none;">
            <option value="eur" <?= sv($settings,'stripe_currency')==='eur'?'selected':'' ?>>EUR — Euro (€)</option>
            <option value="usd" <?= sv($settings,'stripe_currency')==='usd'?'selected':'' ?>>USD — Dollar ($)</option>
            <option value="xof" <?= sv($settings,'stripe_currency')==='xof'?'selected':'' ?>>XOF — Franc CFA</option>
          </select>
        </div>

        <!-- CONVERSION -->
        <div style="background:#fffbf0;border:1px solid rgba(200,146,26,0.2);padding:18px;margin-top:4px;">
          <div style="font-size:0.85rem;font-weight:700;color:var(--gold);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:14px;">
            💱 Conversion FCFA → EUR
          </div>
          <div class="admin-form">
            <div style="margin-bottom:14px;">
              <label>Taux FCFA → EUR</label>
              <input type="number" name="stripe_fcfa_to_eur"
                     value="<?= sv($settings,'stripe_fcfa_to_eur') ?>"
                     step="0.00001" min="0" placeholder="0.00152"
                     oninput="updateConversionPreview()">
              <small style="color:var(--muted);font-size:0.85rem;">
                Taux actuel BCF : 1 EUR = 655.957 FCFA → taux = 0.00152
              </small>
            </div>
            <!-- Simulateur -->
            <div style="background:#fff;border:1px solid #e0d8ce;padding:14px 16px;">
              <div style="font-size:0.82rem;color:var(--muted);margin-bottom:10px;font-weight:600;">Simulateur de conversion</div>
              <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <input type="number" id="sim_fcfa" value="190000" oninput="updateConversionPreview()"
                  style="width:130px;padding:8px 12px;border:1px solid #e0d8ce;font-size:1rem;font-family:inherit;">
                <span style="color:var(--muted);">FCFA =</span>
                <strong id="sim_eur" style="font-size:1.2rem;color:#635bff;">288.80 €</strong>
                <span style="color:var(--muted);font-size:0.85rem;">(Stripe reçoit ce montant)</span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- PAIEMENTS MOBILES -->
    <div class="admin-card">
      <div style="font-size:1.1rem;font-weight:700;color:var(--dark);margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #f0ebe0;">
        📱 Paiements mobiles & virement
      </div>
      <div class="admin-form">
        <div style="margin-bottom:16px;">
          <label>Numéro Wave</label>
          <input type="text" name="wave_number" value="<?= sv($settings,'wave_number') ?>" placeholder="+33 6 44 72 87 30">
        </div>
        <div style="margin-bottom:16px;">
          <label>Numéro Orange Money</label>
          <input type="text" name="orange_money_number" value="<?= sv($settings,'orange_money_number') ?>" placeholder="+33 6 44 72 87 30">
        </div>
        <div style="margin-bottom:16px;">
          <label>Banque</label>
          <input type="text" name="bank_name" value="<?= sv($settings,'bank_name') ?>" placeholder="CBAO Dakar">
        </div>
        <div style="margin-bottom:16px;">
          <label>Titulaire du compte</label>
          <input type="text" name="bank_owner" value="<?= sv($settings,'bank_owner') ?>" placeholder="AfroStyle Atelier">
        </div>
        <div style="margin-bottom:0;">
          <label>IBAN / RIB</label>
          <input type="text" name="bank_iban" value="<?= sv($settings,'bank_iban') ?>" placeholder="FR76 0000 0000 0000 0000 0000 000">
        </div>
      </div>
    </div>

  </div>

  <!-- COLONNE DROITE -->
  <div style="display:flex; flex-direction:column; gap:24px;">

    <!-- INFOS SITE -->
    <div class="admin-card">
      <div style="font-size:1.1rem;font-weight:700;color:var(--dark);margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #f0ebe0;">
        🏪 Informations du site
      </div>
      <div class="admin-form">
        <div style="margin-bottom:16px;">
          <label>Nom du site</label>
          <input type="text" name="site_name" value="<?= sv($settings,'site_name') ?>" placeholder="AfroStyle">
        </div>
        <div style="margin-bottom:16px;">
          <label>Téléphone</label>
          <input type="text" name="site_phone" value="<?= sv($settings,'site_phone') ?>" placeholder="+33 6 44 72 87 30">
        </div>
        <div style="margin-bottom:16px;">
          <label>Email de contact</label>
          <input type="email" name="site_email" value="<?= sv($settings,'site_email') ?>" placeholder="contact@afrostyle.sn">
        </div>
        <div style="margin-bottom:0;">
          <label>Adresse</label>
          <input type="text" name="site_address" value="<?= sv($settings,'site_address') ?>" placeholder="Dakar, Sénégal">
        </div>
      </div>
    </div>

    <!-- PHOTO TOUT VOIR -->
    <div class="admin-card">
      <div style="font-size:1.1rem;font-weight:700;color:var(--dark);margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid #f0ebe0;">
        🖼️ Photo carte "Toutes collections"
      </div>
      <div class="admin-form">
        <?php
        $catAllImg = $settings['cat_all_image']['setting_value'] ?? '';
        ?>
        <?php if ($catAllImg): ?>
        <div style="margin-bottom:16px;position:relative;display:inline-block;">
          <img src="<?= UPLOADS_URL . htmlspecialchars($catAllImg) ?>"
               style="width:100%;max-height:200px;object-fit:cover;border:1px solid #e0d8ce;">
          <div style="position:absolute;top:8px;right:8px;background:rgba(200,146,26,0.9);color:var(--dark);padding:4px 10px;font-size:0.8rem;font-weight:700;">✦ Tout voir</div>
        </div>
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.95rem;color:#e53e3e;font-weight:600;text-transform:none;letter-spacing:0;margin-bottom:12px;">
          <input type="checkbox" name="delete_cat_all_image" value="1">
          Supprimer cette photo
        </label>
        <?php endif; ?>

        <div style="margin-bottom:8px;">
          <label><?= $catAllImg ? 'Remplacer la photo' : 'Ajouter une photo' ?></label>
          <input type="file" name="cat_all_image" accept="image/*">
        </div>
        <small style="color:var(--muted);font-size:0.85rem;">
          Recommandé : format portrait ou carré (ex: 600×800px)<br>
          Cette photo s'affiche derrière le titre "Tout voir" dans la section catégories.
        </small>
      </div>
    </div>

    <!-- AIDE STRIPE -->
    <div style="background:#f0f0ff;border:1px solid rgba(99,91,255,0.2);padding:24px;">
      <p style="margin:0 0 12px;font-size:0.9rem;font-weight:700;color:#635bff;letter-spacing:0.05em;text-transform:uppercase;">Comment trouver vos clés Stripe ?</p>
      <ol style="margin:0;padding-left:20px;font-size:0.95rem;color:#444;line-height:2;">
        <li>Connectez-vous sur <strong>dashboard.stripe.com</strong></li>
        <li>Allez dans <strong>Développeurs → Clés API</strong></li>
        <li>Copiez la <strong>Publishable key</strong> (pk_test_...)</li>
        <li>Cliquez sur <strong>"Afficher"</strong> pour la Secret key</li>
        <li>Collez les deux clés ci-contre</li>
      </ol>
      <div style="margin-top:16px;padding:12px;background:#fff;border:1px solid rgba(99,91,255,0.15);">
        <p style="margin:0;font-size:0.88rem;color:#635bff;">
          🟡 <strong>Mode Test</strong> — utilisez la carte <code>4242 4242 4242 4242</code> (exp: 12/26, CVV: 123) pour tester sans frais réels.
        </p>
      </div>
    </div>

  </div>
</div>

<!-- BOUTON SAVE -->
<div style="margin-top:28px;display:flex;justify-content:flex-end;gap:12px;">
  <button type="submit" class="btn-admin btn-gold" style="padding:14px 40px;font-size:1rem;">
    ✓ Enregistrer tous les paramètres
  </button>
</div>

</form>
</div>

<script>
function toggleSecret() {
    const input = document.getElementById('stripe_sk');
    input.type = input.type === 'password' ? 'text' : 'password';
}
function updateConversionPreview() {
    const rate  = parseFloat(document.querySelector('input[name=stripe_fcfa_to_eur]').value) || 0.00152;
    const fcfa  = parseFloat(document.getElementById('sim_fcfa').value) || 0;
    const eur   = (fcfa * rate).toFixed(2);
    document.getElementById('sim_eur').textContent = parseFloat(eur).toLocaleString('fr-FR', {minimumFractionDigits:2}) + ' €';
}
// Init simulateur
document.addEventListener('DOMContentLoaded', updateConversionPreview);
function updateModeBadge(val) {
    const badge = document.getElementById('stripe-mode-badge');
    if (val === 'live') {
        badge.textContent = '🟢 LIVE';
        badge.style.background = 'rgba(56,161,105,0.1)';
        badge.style.color = '#276749';
    } else {
        badge.textContent = '🟡 TEST';
        badge.style.background = 'rgba(255,152,0,0.1)';
        badge.style.color = '#c05621';
    }
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>
