<?php
require_once '../config/config.php';
session_destroy();
header('Location: ' . ADMIN_URL . '/login.php');
exit;
