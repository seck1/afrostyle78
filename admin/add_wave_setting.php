<?php
require_once 'includes/auth.php';
$db = getDB();
$db->exec("INSERT IGNORE INTO settings (setting_key, setting_value, setting_group, setting_label) VALUES ('wave_api_key', '', 'paiement', 'Wave Business API Key')");
echo "OK — setting wave_api_key ajouté. <a href='parametres.php'>Retour paramètres</a>";
