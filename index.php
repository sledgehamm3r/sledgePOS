<?php
session_start();
require_once 'config/config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY id");
$categories = $catStmt->fetchAll();

$currentCategory = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$barcode = isset($_POST['barcode']) ? trim($_POST['barcode']) : '';

if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!empty($barcode)) {
    $stmt = $pdo->prepare("SELECT id, name, price, tax_rate FROM products WHERE name LIKE ? LIMIT 1");
    $stmt->execute(["%$barcode%"]);
    $product = $stmt->fetch();
    if ($product) {
        $pid = $product['id'];
        if(isset($_SESSION['cart'][$pid])) {
            $_SESSION['cart'][$pid]['qty']++;
        } else {
            $_SESSION['cart'][$pid] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'qty' => 1,
                'tax_rate' => $product['tax_rate']
            ];
        }
    }
}

if (isset($_GET['add'])) {
    $productId = (int)$_GET['add'];
    $stmt = $pdo->prepare("SELECT id, name, price, tax_rate FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $prod = $stmt->fetch();
    if ($prod) {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['qty'] += 1;
        } else {
            $_SESSION['cart'][$productId] = [
                'id' => $prod['id'],
                'name' => $prod['name'],
                'price' => $prod['price'],
                'qty' => 1,
                'tax_rate' => $prod['tax_rate']
            ];
        }
    }
}

if (isset($_GET['changeqty']) && isset($_GET['pid'])) {
    $pid = $_GET['pid'];
    if (isset($_SESSION['cart'][$pid])) {
        if ($_GET['changeqty'] == 'inc') {
            $_SESSION['cart'][$pid]['qty']++;
        } elseif ($_GET['changeqty'] == 'dec') {
            $_SESSION['cart'][$pid]['qty'] = max(1, $_SESSION['cart'][$pid]['qty'] - 1);
        }
    }
}

if (isset($_GET['remove'])) {
    $pid = $_GET['remove'];
    if (isset($_SESSION['cart'][$pid])) {
        unset($_SESSION['cart'][$pid]);
    }
}

$sql = "SELECT p.id, p.name, p.price, p.image_url, p.tax_rate FROM products p WHERE 1 ";
if ($currentCategory > 0) {
    $sql .= " AND p.category_id = :cat ";
}
if (!empty($searchQuery)) {
    $sql .= " AND p.name LIKE :search ";
}
$sql .= " ORDER BY p.name";

$stmt = $pdo->prepare($sql);
if ($currentCategory > 0) $stmt->bindValue(':cat', $currentCategory, PDO::PARAM_INT);
if (!empty($searchQuery)) $stmt->bindValue(':search', "%$searchQuery%", PDO::PARAM_STR);
$stmt->execute();
$products = $stmt->fetchAll();

$errorMessage = '';
$checkoutSuccess = false;
$modalGiven = 0;
$modalChange = 0;
$orderIdForModal = 0;
$paymentMethod = ''; 

$subtotal = 0;
$totalTax = 0; 
foreach ($_SESSION['cart'] as $item) {
    $lineTotal = $item['price'] * $item['qty'];
    $subtotal += $lineTotal;
    $itemTax = $lineTotal * ($item['tax_rate']/100);
    $totalTax += $itemTax;
}
$total = $subtotal + $totalTax;

if (isset($_POST['pay_cash'])) {
    $paymentMethod = 'cash';
    $givenAmount = (float)($_POST['given'] ?? 0);

    if($givenAmount <= 0) {
        $givenAmount = $total;
    }

    if ($givenAmount < $total) {
        $errorMessage = t('insufficient_given');
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO orders (order_date, total, tax, subtotal, user_id) VALUES (NOW(), ?, ?, ?, ?)");
            $stmt->execute([$total, $totalTax, $subtotal, $_SESSION['user_id']]);
            $orderId = $pdo->lastInsertId();

            $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($_SESSION['cart'] as $item) {
                $stmtItem->execute([$orderId, $item['id'], $item['qty'], $item['price']]);
            }

            $pdo->commit();

            $change = $givenAmount - $total;
            $checkoutSuccess = true;
            $modalGiven = $givenAmount;
            $modalChange = $change;
            $orderIdForModal = $orderId;

            $_SESSION['cart'] = [];
        } catch (Exception $e) {
            $pdo->rollBack();
            $errorMessage = "Fehler beim Bestellabschluss: ".$e->getMessage();
        }
    }
}

// Karte
if (isset($_POST['pay_card'])) {
    $paymentMethod = 'card';
    $errorMessage = t('card_not_implemented');
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php echo t('app_name'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link href="assets/css/index.css" rel="stylesheet">

</head>
<body>
<header>
    <div class="brand">
        <?php echo t('app_name'); ?>
    </div>
    <div class="user-info">
        <?php echo t('logged_in_as'); ?> <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo $_SESSION['role']; ?>)
        <a class="logout-link" href="logout.php"><?php echo t('logout'); ?></a>
        <?php if(isAdmin()): ?>
            <nav><a href="settings.php">⚙ <?php echo t('settings'); ?></a></nav>
        <?php endif; ?>
    </div>
</header>

<div class="container">
    <div class="sidebar">
        <div class="category-list">
            <h2><?php echo t('categories'); ?></h2>
            <ul>
                <li class="<?php echo ($currentCategory==0?'active':''); ?>">
                    <a href="index.php"><?php echo t('all'); ?></a>
                </li>
                <?php foreach($categories as $cat): ?>
                <li class="<?php echo ($currentCategory==$cat['id']?'active':''); ?>">
                    <a href="index.php?cat=<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="main-content">
        <div class="product-header">
            <div class="form-group">
                <form method="get" action="index.php">
                    <?php if($currentCategory>0): ?>
                    <input type="hidden" name="cat" value="<?php echo $currentCategory; ?>">
                    <?php endif; ?>
                    <input type="text" name="search" placeholder="<?php echo t('search_product'); ?>" value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <button type="submit"><?php echo t('search'); ?></button>
                </form>
                <form method="post" action="index.php<?php echo ($currentCategory>0?'?cat='.$currentCategory:''); echo (!empty($searchQuery)?'&search='.urlencode($searchQuery):''); ?>">
                    <input type="text" name="barcode" placeholder="<?php echo t('barcode_name'); ?>">
                    <button type="submit"><?php echo t('scan_add'); ?></button>
                </form>
            </div>
        </div>
        <div class="product-list-container">
            <?php if(!empty($products)): ?>
                <?php foreach($products as $prod): ?>
                <a class="product" href="index.php?add=<?php echo $prod['id']; ?><?php echo ($currentCategory>0?'&cat='.$currentCategory:''); ?><?php echo (!empty($searchQuery)?'&search='.urlencode($searchQuery):''); ?>">
                    <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                    <h3><?php echo htmlspecialchars($prod['name']); ?></h3>
                    <p><?php echo number_format($prod['price'],2,',','.'); ?> € (<?php echo $prod['tax_rate']; ?>% MwSt)</p>
                    <div class="add-to-cart-btn"><?php echo t('add_to_cart'); ?></div>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column:1/-1; text-align:center;"><?php echo t('no_products_found'); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="cart-area">
        <h2><?php echo t('cart'); ?></h2>
        <div class="cart-items">
            <?php if(!empty($_SESSION['cart'])): ?>
                <?php foreach($_SESSION['cart'] as $pid=>$item): ?>
                <div class="cart-item">
                    <div class="cart-item-info">
                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                        <span><?php echo number_format($item['price'],2,',','.'); ?> € (<?php echo $item['tax_rate']; ?>%)</span>
                    </div>
                    <div class="cart-item-actions">
                        <a href="index.php?changeqty=dec&pid=<?php echo urlencode($pid); if($currentCategory>0) echo '&cat='.$currentCategory; if($searchQuery!='') echo '&search='.urlencode($searchQuery); ?>">
                            <button>-</button>
                        </a>
                        <span style="font-size:1.1em; font-weight:600;"><?php echo $item['qty']; ?></span>
                        <a href="index.php?changeqty=inc&pid=<?php echo urlencode($pid); if($currentCategory>0) echo '&cat='.$currentCategory; if($searchQuery!='') echo '&search='.urlencode($searchQuery); ?>">
                            <button>+</button>
                        </a>
                        <a href="index.php?remove=<?php echo urlencode($pid); if($currentCategory>0) echo '&cat='.$currentCategory; if($searchQuery!='') echo '&search='.urlencode($searchQuery); ?>">
                            <button class="remove-btn">x</button>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p><?php echo t('empty_cart'); ?></p>
            <?php endif; ?>
        </div>

        <div class="totals">
            <p><?php echo t('subtotal'); ?>: <strong><?php echo number_format($subtotal,2,',','.'); ?> €</strong></p>
            <p><?php echo t('tax'); ?>: <strong><?php echo number_format($totalTax,2,',','.'); ?> €</strong></p>
            <p><?php echo t('total'); ?>: <strong><?php echo number_format($total,2,',','.'); ?> €</strong></p>
        </div>
        <div class="checkout">
            <?php if($errorMessage): ?><div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div><?php endif; ?>
            <form method="post">
                <input type="number" step="0.01" name="given" placeholder="<?php echo t('given'); ?> €" value="<?php echo isset($_POST['given'])?htmlspecialchars($_POST['given']):''; ?>">

                <div class="checkout-buttons">
                    <button type="submit" name="pay_cash" <?php echo (empty($_SESSION['cart'])?'disabled':''); ?> class="cash-btn icon-cash" aria-label="Barzahlung"></button>
                    <button type="submit" name="pay_card" <?php echo (empty($_SESSION['cart'])?'disabled':''); ?> class="card-btn icon-card" aria-label="Kartenzahlung"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if($checkoutSuccess): ?>
<div class="modal-overlay">
    <div class="modal">
        <button class="close-btn" onclick="closeModal()">&times;</button>
        <h3><?php echo t('change'); ?></h3>
        <p><?php echo number_format($modalChange,2,',','.'); ?> €</p>
        <button onclick="printAndClose()"><?php echo t('print_receipt'); ?></button>
    </div>
</div>
<?php endif; ?>

<script>
function closeModal() {
    document.querySelector('.modal-overlay').style.display = 'none';
}

function printAndClose() {
    window.open('print_receipt.php?order_id=<?php echo $orderIdForModal; ?>&given=<?php echo $modalGiven; ?>&change=<?php echo $modalChange; ?>&method=<?php echo $paymentMethod; ?>','_blank');
    closeModal();
}
</script>

</body>
</html>
