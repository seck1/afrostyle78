<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'vendor/autoload.php';

header('Content-Type: application/json');

$db       = getDB();
$settings = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_group='stripe'")->fetchAll(PDO::FETCH_KEY_PAIR);

$secretKey   = $settings['stripe_secret_key'] ?? '';
$currency    = $settings['stripe_currency'] ?? 'eur';
$rate        = (float)($settings['stripe_fcfa_to_eur'] ?? 0.00152);
$mode        = $settings['stripe_mode'] ?? 'test';

if (!$secretKey) {
    echo json_encode(['error' => 'Stripe non configuré. Ajoutez vos clés dans les paramètres admin.']);
    exit;
}

$orderNumber = $_POST['order_number'] ?? '';
$totalFcfa   = (float)($_POST['total_fcfa'] ?? 0);

if (!$orderNumber || $totalFcfa <= 0) {
    echo json_encode(['error' => 'Données de commande invalides.']);
    exit;
}

// Récupérer la commande
$stmt = $db->prepare("SELECT o.*, c.email, c.first_name, c.last_name FROM orders o JOIN customers c ON o.customer_id=c.id WHERE o.order_number=?");
$stmt->execute([$orderNumber]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['error' => 'Commande introuvable.']);
    exit;
}

// Convertir FCFA → EUR (Stripe attend des centimes)
$amountEur    = round($totalFcfa * $rate, 2);
$amountCents  = (int)round($amountEur * 100);

if ($amountCents < 50) { // minimum Stripe = 0.50€
    echo json_encode(['error' => 'Montant trop faible pour Stripe (minimum 0.50€).']);
    exit;
}

\Stripe\Stripe::setApiKey($secretKey);

try {
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency'     => $currency,
                'unit_amount'  => $amountCents,
                'product_data' => [
                    'name'        => 'Commande AfroStyle ' . $orderNumber,
                    'description' => 'Total: ' . number_format($totalFcfa, 0, ',', ' ') . ' FCFA (' . number_format($amountEur, 2, ',', ' ') . ' €)',
                    'images'      => [SITE_URL . '/logo.jpg'],
                ],
            ],
            'quantity' => 1,
        ]],
        'mode'          => 'payment',
        'customer_email'=> $order['email'],
        'success_url'   => SITE_URL . '/stripe-success.php?order=' . $orderNumber . '&session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'    => SITE_URL . '/confirmation.php?order=' . $orderNumber . '&payment=cancelled',
        'metadata'      => [
            'order_number' => $orderNumber,
            'order_id'     => $order['id'],
            'fcfa_amount'  => $totalFcfa,
        ],
    ]);

    echo json_encode(['url' => $session->url]);

} catch (\Stripe\Exception\ApiErrorException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
