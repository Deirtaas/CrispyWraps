<?php
include 'config/db.php';

$customerId = isset($_SESSION['customer']) ? $_SESSION['customer']['id'] : session_id();

// Handle cart updates via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $pid = $_POST['product_id'];
    
    if ($_POST['action'] === 'add') {
        $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
        $stmt = $pdo->prepare("SELECT id, qty FROM cart WHERE customer_id = ? AND product_id = ?");
        $stmt->execute([$customerId, $pid]);
        if ($row = $stmt->fetch()) {
            $pdo->prepare("UPDATE cart SET qty = qty + ? WHERE id = ?")->execute([$qty, $row['id']]);
        } else {
            $pdo->prepare("INSERT INTO cart (customer_id, product_id, qty) VALUES (?, ?, ?)")->execute([$customerId, $pid, $qty]);
        }
    } elseif ($_POST['action'] === 'update') {
        $qty = (int)$_POST['qty'];
        if ($qty <= 0) {
            $pdo->prepare("DELETE FROM cart WHERE customer_id = ? AND product_id = ?")->execute([$customerId, $pid]);
        } else {
            $pdo->prepare("UPDATE cart SET qty = ? WHERE customer_id = ? AND product_id = ?")->execute([$qty, $customerId, $pid]);
        }
    } elseif ($_POST['action'] === 'delete') {
        $pdo->prepare("DELETE FROM cart WHERE customer_id = ? AND product_id = ?")->execute([$customerId, $pid]);
    }
    
    header("Location: cart.php");
    exit;
}

// Fetch active cart items[cite: 2, 10]
$stmt = $pdo->prepare("
    SELECT c.qty, c.product_id, p.name, p.category, p.price 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.customer_id = ?
");
$stmt->execute([$customerId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Cart — CrispyWraps</title>
<link rel="stylesheet" href="/css/styles.css" />
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="wrap">
  <h1>Your Cart</h1>
  <p class="muted">Review your items before checkout.</p>
  
  <div class="card" style="margin-top:1rem" id="cart-card">
    <?php if (count($cartItems) === 0): ?>
      <div class="empty">Your cart is empty. <a class="btn sm" href="index.php">Browse the menu</a></div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Item</th><th>Price</th><th>Qty</th><th class="right">Subtotal</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($cartItems as $item): 
                $subtotal = $item['price'] * $item['qty'];
                $total += $subtotal;
            ?>
            <tr>
              <td><strong><?= htmlspecialchars($item['name']) ?></strong><br><small><?= htmlspecialchars($item['category']) ?></small></td>
              <td>₱<?= number_format($item['price'], 2) ?></td>
              <td>
                <div class="row">
                  <form method="POST" style="margin:0;display:inline-block;">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                    <input type="hidden" name="qty" value="<?= $item['qty'] - 1 ?>">
                    <button type="submit" class="btn ghost sm">−</button>
                  </form>
                  <span><?= $item['qty'] ?></span>
                  <form method="POST" style="margin:0;display:inline-block;">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                    <input type="hidden" name="qty" value="<?= $item['qty'] + 1 ?>">
                    <button type="submit" class="btn ghost sm">+</button>
                  </form>
                </div>
              </td>
              <td class="right">₱<?= number_format($subtotal, 2) ?></td>
              <td class="right">
                <form method="POST" style="margin:0;">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                  <button type="submit" class="btn ghost sm">Remove</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="spread" style="margin-top:1rem">
        <div><small>Total</small><div style="font-size:1.5rem;font-weight:800">₱<?= number_format($total, 2) ?></div></div>
        <a class="btn" href="checkout.php">Proceed to checkout</a>
      </div>
    <?php endif; ?>
  </div>
</main>
</body>
</html>