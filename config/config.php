<?php
define('SITE_NAME', 'AfroStyle');
define('SITE_URL', 'http://localhost/afrostyle');
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOADS_DIR', __DIR__ . '/../uploads/products/');
define('UPLOADS_URL', SITE_URL . '/uploads/products/');
define('CURRENCY', 'FCFA');
define('SESSION_NAME', 'afrostyle_session');

session_name(SESSION_NAME);
if (session_status() === PHP_SESSION_NONE) session_start();
