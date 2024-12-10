<?php
session_start();
require_once 'config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['category_action'])) {
        $category_action = $_POST['category_action'];
        if ($category_action === 'add_category') {
            $cat_name = trim($_POST['cat_name'] ?? '');
            if ($cat_name !== '') {
                $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
                $stmt->execute([$cat_name]);
                $message = "Kategorie '$cat_name' wurde hinzugefügt.";
            } else {
                $message = "Bitte einen gültigen Kategorienamen angeben.";
            }
        } elseif ($category_action === 'delete_category') {
            $cat_id = (int)$_POST['cat_id'];
            try {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id=?");
                $stmt->execute([$cat_id]);
                $message = "Kategorie wurde gelöscht.";
            } catch (Exception $e) {
                $message = "Fehler beim Löschen der Kategorie. Sie wird möglicherweise von Produkten verwendet.";
            }
        }
    }

    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        if ($action === 'add') {
            $name = trim($_POST['name']);
            $price = (float)$_POST['price'];
            $category_id = (int)$_POST['category_id'];
            $barcode_val = trim($_POST['barcode']);
            $tax_rate = (int)$_POST['tax_rate'];
            $image_url = trim($_POST['image_url'] ?? '');

            if ($name === '' || $price <= 0 || $category_id <= 0 || $barcode_val === '') {
                $message = "Bitte korrekte Produktdaten angeben (Name, Preis, gültige Kategorie, Barcode).";
            } else {
                $stmt = $pdo->prepare("INSERT INTO products (name, price, category_id, image_url, barcode, tax_rate) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$name, $price, $category_id, $image_url, $barcode_val, $tax_rate]);
                $message = "Produkt hinzugefügt.";
            }
        } elseif ($action === 'edit') {
            $id = (int)$_POST['id'];
            $name = trim($_POST['name']);
            $price = (float)$_POST['price'];
            $category_id = (int)$_POST['category_id'];
            $barcode_val = trim($_POST['barcode']);
            $tax_rate = (int)$_POST['tax_rate'];
            $image_url = trim($_POST['image_url'] ?? '');

            if ($name === '' || $price <= 0 || $category_id <= 0 || $barcode_val === '') {
                $message = "Bitte korrekte Produktdaten angeben.";
            } else {
                $stmt = $pdo->prepare("UPDATE products SET name=?, price=?, category_id=?, image_url=?, barcode=?, tax_rate=? WHERE id=?");
                $stmt->execute([$name, $price, $category_id, $image_url, $barcode_val, $tax_rate, $id]);
                $message = "Produkt aktualisiert.";
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
            $stmt->execute([$id]);
            $message = "Produkt gelöscht.";
        }
    }

    if (isset($_POST['user_settings'])) {
        $message = "Nutzerverwaltung aktualisiert (Beispiel).";
    }

    if (isset($_POST['tax_settings'])) {
        $message = "Steuereinstellungen gespeichert (Beispiel).";
    }

    if (isset($_POST['receipt_settings'])) {
        $_SESSION['receipt_config']['company_name'] = $_POST['company_name'];
        $_SESSION['receipt_config']['company_address'] = $_POST['company_address'];
        $_SESSION['receipt_config']['system_serial'] = $_POST['system_serial'];
        $_SESSION['receipt_config']['signature_counter'] = $_POST['signature_counter'];
        $_SESSION['receipt_config']['check_value'] = $_POST['check_value'];
        $_SESSION['receipt_config']['previous_order_start'] = $_POST['previous_order_start'];
        $message = "Boneinstellungen gespeichert.";
    }

    if (isset($_POST['translation_settings'])) {
        $newLang = $_POST['language_select'] ?? 'de';
        $_SESSION['lang'] = $newLang;
        $message = "Sprache wurde geändert.";
    }
}

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

$products = $pdo->query("SELECT p.id, p.name, p.price, p.image_url, p.barcode, p.tax_rate, c.name as cat_name 
    FROM products p 
    JOIN categories c ON p.category_id=c.id 
    ORDER BY p.name")->fetchAll();

$tab = $_GET['tab'] ?? 'categories';
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo t('settings'); ?> - <?php echo t('app_name'); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<link href="assets/css/settings.css" rel="stylesheet">
</head>
<body>
<header>
    <h1><?php echo t('app_name'); ?> - <?php echo t('settings'); ?></h1>
    <nav>
        <a href="index.php"><?php echo t('app_name'); ?></a>
    </nav>
</header>
<div class="container">
    <?php if($message): ?>
    <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="tabs">
        <a href="?tab=categories" class="<?php echo ($tab=='categories'?'active':''); ?>"><?php echo t('categories'); ?></a>
        <a href="?tab=products" class="<?php echo ($tab=='products'?'active':''); ?>">Produkte</a>
        <a href="?tab=users" class="<?php echo ($tab=='users'?'active':''); ?>">Nutzer</a>
        <a href="?tab=tax" class="<?php echo ($tab=='tax'?'active':''); ?>">Steuern</a>
        <a href="?tab=receipt" class="<?php echo ($tab=='receipt'?'active':''); ?>">Bon</a>
        <a href="?tab=translations" class="<?php echo ($tab=='translations'?'active':''); ?>">Übersetzungen</a>
    </div>

    <?php if ($tab == 'categories'): ?>
    <h2>Kategorien verwalten</h2>
    <div class="form-section">
        <h3>Neue Kategorie hinzufügen</h3>
        <form method="post">
            <input type="hidden" name="category_action" value="add_category">
            <input type="text" name="cat_name" placeholder="Kategoriename" required>
            <button type="submit">Hinzufügen</button>
        </form>
    </div>
    <h3>Alle Kategorien</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($categories as $c): ?>
            <tr>
                <td><?php echo $c['id']; ?></td>
                <td><?php echo htmlspecialchars($c['name']); ?></td>
                <td class="actions">
                    <form method="post" onsubmit="return confirm('Kategorie wirklich löschen?');">
                        <input type="hidden" name="category_action" value="delete_category">
                        <input type="hidden" name="cat_id" value="<?php echo $c['id']; ?>">
                        <button type="submit" class="delete">Löschen</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php elseif($tab=='products'): ?>
    <h2>Produkte verwalten</h2>
    <div class="form-section">
        <h3>Neues Produkt hinzufügen</h3>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <input type="text" name="name" placeholder="Produktname" required>
            <input type="number" step="0.01" name="price" placeholder="Preis (€)" required>
            <select name="category_id" required>
                <option value="">-- Kategorie wählen --</option>
                <?php foreach($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="barcode" placeholder="Barcode" required>
            <select name="tax_rate">
                <option value="19">19%</option>
                <option value="7">7%</option>
            </select>
            <input type="text" name="image_url" placeholder="Bild-URL (optional)">
            <button type="submit">Hinzufügen</button>
        </form>
    </div>

    <h3>Alle Produkte</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Preis</th><th>Kategorie</th><th>Barcode</th><th>Steuer</th><th>Bild</th><th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($products as $p): ?>
            <tr>
                <td><?php echo $p['id']; ?></td>
                <td><?php echo htmlspecialchars($p['name']); ?></td>
                <td><?php echo number_format($p['price'],2,',','.'); ?> €</td>
                <td><?php echo htmlspecialchars($p['cat_name']); ?></td>
                <td><?php echo htmlspecialchars($p['barcode']); ?></td>
                <td><?php echo $p['tax_rate']; ?>%</td>
                <td>
                    <?php if($p['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($p['image_url']); ?>" alt="Bild">
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td class="actions">
                    <form method="post" onsubmit="return confirm('Produkt wirklich löschen?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                        <button type="submit" class="delete">Löschen</button>
                    </form>
                    <button type="button" onclick="toggleEditForm(<?php echo $p['id']; ?>)">Bearbeiten</button>
                    <div id="edit-form-<?php echo $p['id']; ?>" style="display:none; margin-top:10px;">
                        <form method="post">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                            <input type="text" name="name" value="<?php echo htmlspecialchars($p['name']); ?>" required>
                            <input type="number" step="0.01" name="price" value="<?php echo $p['price']; ?>" required>
                            <select name="category_id" required>
                                <option value="">-- Kategorie wählen --</option>
                                <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php if($p['cat_name']==$cat['name']) echo 'selected';?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="barcode" value="<?php echo htmlspecialchars($p['barcode']); ?>" required>
                            <select name="tax_rate">
                                <option value="19" <?php if($p['tax_rate']==19) echo 'selected'; ?>>19%</option>
                                <option value="7" <?php if($p['tax_rate']==7) echo 'selected'; ?>>7%</option>
                            </select>
                            <input type="text" name="image_url" value="<?php echo htmlspecialchars($p['image_url']); ?>">
                            <button type="submit">Speichern</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php elseif($tab=='users'): ?>
    <h2>Nutzerverwaltung</h2>
    <div class="form-section">
        <h3>Nutzer Einstellungen (Beispiel)</h3>
        <form method="post">
            <input type="hidden" name="user_settings" value="1">
            <input type="text" name="new_username" placeholder="Neuer Nutzername (Bsp.)">
            <input type="password" name="new_password" placeholder="Neues Passwort (Bsp.)">
            <select name="new_role">
                <option value="cashier">Kassierer</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit">Speichern</button>
        </form>
    </div>

    <?php elseif($tab=='tax'): ?>
    <h2>Steuereinstellungen</h2>
    <div class="form-section">
        <h3>Steuersatz ändern (Beispiel)</h3>
        <form method="post">
            <input type="hidden" name="tax_settings" value="1">
            <input type="number" step="0.01" name="tax_rate" placeholder="Steuersatz in %" value="19.00">
            <button type="submit">Speichern</button>
        </form>
    </div>

    <?php elseif($tab=='receipt'): ?>
    <h2>Boneinstellungen</h2>
    <div class="form-section">
        <form method="post">
            <input type="hidden" name="receipt_settings" value="1">
            <input type="text" name="company_name" placeholder="Unternehmensname" value="<?php echo htmlspecialchars($_SESSION['receipt_config']['company_name'] ?? ''); ?>" required>
            <input type="text" name="company_address" placeholder="Unternehmensadresse" value="<?php echo htmlspecialchars($_SESSION['receipt_config']['company_address'] ?? ''); ?>" required>
            <input type="text" name="system_serial" placeholder="Seriennummer Kassensystem" value="<?php echo htmlspecialchars($_SESSION['receipt_config']['system_serial'] ?? ''); ?>" required>
            <input type="text" name="signature_counter" placeholder="Signaturzähler" value="<?php echo htmlspecialchars($_SESSION['receipt_config']['signature_counter'] ?? ''); ?>" required>
            <input type="text" name="check_value" placeholder="Prüfwert" value="<?php echo htmlspecialchars($_SESSION['receipt_config']['check_value'] ?? ''); ?>" required>
            <input type="text" name="previous_order_start" placeholder="Startzeitpunkt vorherige Bestellung (YYYY-MM-DD HH:MM:SS)" value="<?php echo htmlspecialchars($_SESSION['receipt_config']['previous_order_start'] ?? ''); ?>">
            <button type="submit">Speichern</button>
        </form>
    </div>

    <?php elseif($tab=='translations'): ?>
    <h2>Übersetzungen / Spracheinstellungen</h2>
    <div class="form-section">
        <h3>Sprache auswählen</h3>
        <form method="post">
            <input type="hidden" name="translation_settings" value="1">
            <select name="language_select">
                <option value="de" <?php if($_SESSION['lang']=='de') echo 'selected'; ?>>Deutsch</option>
                <option value="en" <?php if($_SESSION['lang']=='en') echo 'selected'; ?>>English</option>
            </select>
            <button type="submit">Speichern</button>
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
    function toggleEditForm(id) {
        var form = document.getElementById('edit-form-' + id);
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }
</script>
</body>
</html>
