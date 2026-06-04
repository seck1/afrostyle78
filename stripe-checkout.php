<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'vendor/autoload.php';

header('Content-Type: application/json');

// Vérifier session client
if (empty($_SESSION['customer_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Vous devez être connecté pour payer.']);
    exit;
}

$db       = getDB();
$settings = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_group='stripe'")->fetchAll(PDO::FETCH_KEY_PAIR);

$secretKey = $settings['stripe_secret_key'] ?? '';
$currency  = $settings['stripe_currency'] ?? 'eur';
$rate      = (float)($settings['stripe_fcfa_to_eur'] ?? 0.00152);

if (!$secretKey) {
    echo json_encode(['error' => 'Stripe non configuré. Ajoutez vos clés dans les paramètres admin.']);
    exit;
}

$orderNumber = trim($_POST['order_number'] ?? '');
if (!$orderNumber) {
    echo json_encode(['error' => 'Numéro de commande manquant.']);
    exit;
}

// Récupérer la commande depuis la DB — ignorer tout montant POST
$stmt = $db->prepare("SELECT o.*, c.email, c.first_name, c.last_name FROM orders o JOIN customers c ON o.customer_id=c.id WHERE o.order_number=?");
$stmt->execute([$orderNumber]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['error' => 'Commande introuvable.']);
    exit;
}

// Vérifier que la commande appartient au client connecté
if ((int)$order['customer_id'] !== (int)$_SESSION['customer_id']) {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé.']);
    exit;
}

// Vérifier que la commande n'est pas déjà payée
if ($order['payment_status'] === 'paid') {
    echo json_encode(['error' => 'Cette commande est déjà payée.']);
    exit;
}

// Montant depuis la DB uniquement
$totalFcfa   = (float)$order['total_amount'];
$amountEur   = round($totalFcfa * $rate, 2);
$amountCents = (int)round($amountEur * 100);

if ($amountCents < 50) {
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
        'mode'           => 'payment',
        'customer_email' => $order['email'],
        'success_url'    => SITE_URL . '/stripe-success.php?order=' . urlencode($orderNumber) . '&session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'     => SITE_URL . '/confirmation.php?order=' . urlencode($orderNumber) . '&payment=cancelled',
        'metadata'       => [
            'order_number' => $orderNumber,
            'order_id'     => $order['id'],
            'fcfa_amount'  => $totalFcfa,
        ],
    ]);

    echo json_encode(['url' => $session->url]);

} catch (\Stripe\Exception\ApiErrorException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
