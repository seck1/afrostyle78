<?php
require_once 'config/config.php';
unset($_SESSION['customer_id'], $_SESSION['customer_name'], $_SESSION['customer_email']);
header('Location: ' . SITE_URL);
exit;
