<?php
session_start();
require_once 'config/config.php';
if(!isLoggedIn()) {
    exit("Nicht eingeloggt");
}

$order_id = (int)($_GET['order_id'] ?? 0);
$givenAmount = isset($_GET['given']) ? (float)$_GET['given'] : 0.0;
$changeAmount = isset($_GET['change']) ? (float)$_GET['change'] : 0.0;
$paymentMethod = $_GET['method'] ?? 'cash';

$stmt = $pdo->prepare("SELECT o.id, o.order_date, o.total, o.tax, o.subtotal, u.username
FROM orders o
JOIN users u ON o.user_id=u.id
WHERE o.id=?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if(!$order) {
    die("Bestellung nicht gefunden");
}

$stmtItems = $pdo->prepare("SELECT oi.quantity, oi.price, p.name, p.tax_rate FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
$stmtItems->execute([$order_id]);
$items = $stmtItems->fetchAll();

$config = $_SESSION['receipt_config'];
$companyName = $config['company_name'];
$companyAddress = $config['company_address'];
$systemSerial = $config['system_serial'];
$signatureCounter = $config['signature_counter'];
$checkValue = $config['check_value'];
$previousOrderStart = $config['previous_order_start'];

?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bon - <?php echo t('order_number'); ?> <?php echo $order_id; ?></title>
<style>
body {font-family:monospace; background:#f5f5f5; padding:20px;}
h2 {margin:0; padding:0;}
.receipt {
    width:300px;
    border:1px solid #000;
    padding:10px;
    margin:auto;
    background:#fff;
    border-radius:5px;
}
.receipt h2 {text-align:center; margin-bottom:10px;}
.receipt table {width:100%; border-collapse:collapse;}
.receipt td {padding:5px 0;}
.receipt .total {font-weight:bold;}
@media print {
    body {background:#fff; }
    button {display:none;}
}
</style>
</head>
<body onload="window.print()">
<div class="receipt">
    <h2><?php echo t('app_name'); ?> Bon</h2>
    <p><strong><?php echo htmlspecialchars($companyName); ?></strong></p>
    <p><?php echo htmlspecialchars($companyAddress); ?></p>
    <hr>
    <p><?php echo t('order_number'); ?>: <?php echo $order['id']; ?></p>
    <p><?php echo t('date'); ?>: <?php echo $order['order_date']; ?></p>
    <p><?php echo t('employee'); ?>: <?php echo htmlspecialchars($order['username']); ?></p>
    <?php if($previousOrderStart): ?>
    <p>Startzeitpunkt vorherige Bestellung: <?php echo $previousOrderStart; ?></p>
    <?php endif; ?>
    <hr>
    <table>
    <?php foreach($items as $item): ?>
        <tr>
            <td><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></td>
            <td style="text-align:right;"><?php echo number_format($item['price']*$item['quantity'],2,',','.'); ?> €</td>
        </tr>
        <tr>
            <td colspan="2" style="font-size:0.9em; color:#555;"><?php echo $item['tax_rate']; ?>% MwSt</td>
        </tr>
    <?php endforeach; ?>
    </table>
    <hr>
    <p><?php echo t('subtotal'); ?>: <?php echo number_format($order['subtotal'],2,',','.'); ?> €</p>
    <p><?php echo t('tax'); ?>: <?php echo number_format($order['tax'],2,',','.'); ?> €</p>
    <p class="total"><?php echo t('total'); ?>: <?php echo number_format($order['total'],2,',','.'); ?> €</p>
    <hr>
    <p>Zahlungsart: <?php echo ($paymentMethod=='cash'?'Bar':'Karte'); ?></p>
    <?php if($paymentMethod=='cash'): ?>
    <p><?php echo t('given'); ?>: <?php echo number_format($givenAmount,2,',','.'); ?> €</p>
    <p><?php echo t('change'); ?>: <?php echo number_format($changeAmount,2,',','.'); ?> €</p>
    <?php endif; ?>
    <hr>
    <p>Seriennr.: <?php echo htmlspecialchars($systemSerial); ?></p>
    <p>Signaturzähler: <?php echo htmlspecialchars($signatureCounter); ?></p>
    <p>Prüfwert: <?php echo htmlspecialchars($checkValue); ?></p>
    <hr>
    <p><?php echo t('thank_you'); ?></p>
</div>
</body>
</html>
