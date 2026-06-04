<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Installation AfroStyle</title>
    <style>
        body{font-family:sans-serif;background:#0E0A06;color:#FDF6EC;padding:40px;max-width:700px;margin:0 auto;}
        h1{color:#C8921A;margin-bottom:32px;}
        .step{background:#1A1208;padding:20px;margin-bottom:12px;border-left:3px solid #C8921A;}
        .ok{border-color:#1A7A4A;color:#4CAF50;}
        .err{border-color:#C0392B;color:#e74c3c;}
        pre{background:#0a0706;padding:12px;overflow-x:auto;font-size:0.82rem;border:1px solid rgba(200,146,26,0.2);}
        .btn{display:inline-block;background:#C8921A;color:#0E0A06;padding:12px 28px;text-decoration:none;font-weight:bold;margin-top:20px;}
    </style>
</head>
<body>
<h1>🚀 Installation AfroStyle</h1>

<?php
$pdo = null;
try {
    $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", 'root', '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    echo '<div class="step ok">✓ Connexion MySQL réussie</div>';
} catch(Exception $e) {
    echo '<div class="step err">✕ Connexion MySQL échouée: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit;
}

// Create DB
$pdo->exec("CREATE DATABASE IF NOT EXISTS afrostyle CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo '<div class="step ok">✓ Base de données "afrostyle" créée</div>';

// Run SQL
$pdo->exec("USE afrostyle");
$sql = file_get_contents(__DIR__ . '/afrostyle.sql');
// Remove CREATE DATABASE and USE lines (already done)
$sql = preg_replace('/^(CREATE DATABASE|USE).*$/m', '', $sql);

try {
    $pdo->exec($sql);
    echo '<div class="step ok">✓ Tables créées et données de démonstration insérées</div>';
} catch(Exception $e) {
    // Ignore "already exists" errors
    if(strpos($e->getMessage(), 'already exists') !== false) {
        echo '<div class="step ok">✓ Tables déjà existantes (mise à jour ignorée)</div>';
    } else {
        echo '<div class="step err">Avertissement SQL: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

echo '<div class="step ok">✓ Installation terminée avec succès !</div>';
?>

<div class="step" style="margin-top:32px;">
    <h3 style="color:#C8921A; margin-bottom:16px;">Accès administration</h3>
    <p><strong>URL:</strong> <a href="/afrostyle/admin/" style="color:#C8921A;">/afrostyle/admin/</a></p>
    <p><strong>Identifiant:</strong> admin</p>
    <p><strong>Mot de passe:</strong> password</p>
    <p style="color:rgba(253,246,236,0.5); font-size:0.82rem; margin-top:12px;">⚠️ Changez le mot de passe après la première connexion !</p>
</div>

<a href="/afrostyle/" class="btn">→ Aller à la boutique</a>
<a href="/afrostyle/admin/" class="btn" style="margin-left:12px; background:#1A1208; color:#C8921A; border:1px solid #C8921A;">→ Panneau admin</a>

<p style="margin-top:40px; color:rgba(253,246,236,0.3); font-size:0.75rem;">Supprimez ce fichier install.php après l'installation.</p>
</body>
</html>
