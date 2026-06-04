<?php
// Webhook Wave — appelé automatiquement par Wave après paiement
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/mailer.php';

$payload = file_get_contents('php://input');
$data    = json_decode($payload, true);

if (!$data || empty($data['type'])) {
    http_response_code(400);
    exit;
}

$db = getDB();

if ($data['type'] === 'checkout.session.completed') {
    $sessionId   = $data['data']['id'] ?? '';
    $orderNumber = $data['data']['client_reference'] ?? '';
    $status      = $data['data']['payment_status'] ?? '';

    if ($status === 'complete' && $orderNumber) {
        // Mettre à jour la commande
        $db->prepare("UPDATE orders SET payment_status='paid', payment_method='wave' WHERE order_number=?")
           ->execute([$orderNumber]);

        // Envoyer email de confirmation
        $stmt = $db->prepare("SELECT o.*, c.email, c.first_name, c.last_name FROM orders o JOIN customers c ON o.customer_id=c.id WHERE o.order_number=?");
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch();

        if ($order) {
            emailOrderConfirmed($order['email'], $order['first_name'], $order['last_name'], $orderNumber, $order['total_amount']);
        }
    }
}

http_response_code(200);
echo 'OK';
