<?php
require_once 'config/config.php';
require_once 'config/database.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $qty       = max(1, (int)($_POST['quantity'] ?? 1));
    $size      = trim($_POST['size'] ?? 'M');
    $color     = trim($_POST['color'] ?? '');
    $colorHex  = trim($_POST['color_hex'] ?? '');

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND active = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) { echo json_encode(['success'=>false, 'error'=>'Product not found']); exit; }

    $price   = $product['promo_price'] ?: $product['price'];
    $cartKey = $productId . '_' . $size . ($color ? '_' . slugify($color) : '');

    function slugify(string $str): string {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '-', $str));
    }

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if (isset($_SESSION['cart'][$cartKey])) {
        $_SESSION['cart'][$cartKey]['quantity'] += $qty;
    } else {
        $_SESSION['cart'][$cartKey] = [
            'product_id'   => $productId,
            'name'         => $product['name'],
            'price'        => $price,
            'image'        => $product['image'],
            'size'         => $size,
            'color'        => $color,
            'color_hex'    => $colorHex,
            'quantity'     => $qty,
            'is_custom'    => false,
            'measurements' => null,
        ];
    }
    $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
    echo json_encode(['success' => true, 'cartCount' => $cartCount]);

} elseif ($action === 'remove') {
    $key = $_POST['key'] ?? '';
    if (isset($_SESSION['cart'][$key])) unset($_SESSION['cart'][$key]);
    echo json_encode(['success' => true]);

} elseif ($action === 'update') {
    $key = $_POST['key'] ?? '';
    $qty = max(1, (int)($_POST['quantity'] ?? 1));
    if (isset($_SESSION['cart'][$key])) $_SESSION['cart'][$key]['quantity'] = $qty;
    echo json_encode(['success' => true]);

} else {
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
