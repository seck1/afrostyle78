<?php
// Script temporaire — à supprimer après exécution
if (!isset($_GET['run']) || $_GET['run'] !== 'ok') {
    die('Ajouter ?run=ok pour exécuter');
}

require_once __DIR__ . '/config/database.php';

$updates = [
    1 => 'cat_robes.jpeg',
    2 => 'cat_ensemble_homme.jpeg',
    3 => 'cat_ensemble_femme.jpeg',
    4 => 'cat_accessoires.jpeg',
    5 => 'cat_bazin.jpeg',
];

$stmt = $db->prepare("UPDATE categories SET image=? WHERE id=?");
foreach ($updates as $id => $img) {
    $stmt->execute([$img, $id]);
    echo "OK: id=$id → $img<br>";
}
echo "<strong>Done.</strong>";
