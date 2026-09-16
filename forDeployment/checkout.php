<?php
include 'config/db.php';

$customerId = isset($_SESSION['customer']) ? $_SESSION['customer']['id'] : session_id();

$stmt = $pdo->prepare("
    SELECT c.qty, c.product_id, p.name, p.price
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.customer_id = ?
");
$stmt->execute([$customerId]);
$cartItems = $stmt->fetchAll();

$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['qty'];
}

$checkoutError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && count($cartItems) > 0) {
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $type    = $_POST['type'];
    $payment = $_POST['payment'];

    try {
        $pdo->beginTransaction();

        $orderId = 'ORD-' . strtoupper(bin2hex(random_bytes(4)));

        $stmt = $pdo->prepare("
            INSERT INTO orders (id, customer_name, customer_email, type, payment, total, status, date)
            VALUES (?, ?, ?, ?, ?, ?, 'New', NOW())
        ");
        $stmt->execute([$orderId, $name, $email, $type, $payment, $total]);

        $insertItem = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?, ?, ?, ?)
        ");
        $recipeStmt = $pdo->prepare("SELECT ingredient_id, qty FROM recipes WHERE product_id = ?");
        $deductStmt = $pdo->prepare("UPDATE ingredients SET stock = GREATEST(0, stock - ?) WHERE id = ?");
        $logStmt    = $pdo->prepare("
            INSERT INTO stock_log (type, ingredient_id, qty, note, date) VALUES ('OUT', ?, ?, ?, NOW())
        ");

        foreach ($cartItems as $item) {
            $insertItem->execute([$orderId, $item['product_id'], $item['qty'], $item['price']]);

            $recipeStmt->execute([$item['product_id']]);
            foreach ($recipeStmt->fetchAll() as $recipe) {
                $usedQty = $recipe['qty'] * $item['qty'];
                $deductStmt->execute([$usedQty, $recipe['ingredient_id']]);
                $logStmt->execute([$recipe['ingredient_id'], $usedQty, "Order " . $orderId]);
            }
        }

        $pdo->prepare("DELETE FROM cart WHERE customer_id = ?")->execute([$customerId]);

        $pdo->commit();

        if (!isset($_SESSION['customer'])) {
            $_SESSION['customer'] = ['id' => 'guest', 'name' => $name, 'email' => $email];
        }

        header("Location: my-orders.php?placed=" . urlencode($orderId));
        exit;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $checkoutError = "Could not place your order. Please try again.";
    }
}

$userName  = $_SESSION['customer']['name']  ?? '';
$userEmail = $_SESSION['customer']['email'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Checkout — CrispyWraps</title>
<link rel="stylesheet" href="css/styles.css" />
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="wrap">
  <h1>Checkout</h1>

  <?php if ($checkoutError): ?>
    <div class="alert bad" style="margin-top:1rem"><?= htmlspecialchars($checkoutError) ?></div>
  <?php endif; ?>

  <div class="grid g2" style="margin-top:1rem">
    <div class="card">
      <h2>Order details</h2>
      <form method="POST" action="checkout.php">
        <?= csrf_field() ?>
        <div class="field">
          <label>Name</label>
          <input name="name" value="<?= htmlspecialchars($userName) ?>" placeholder="Your name" required />
        </div>
        <div class="field">
          <label>Email</label>
          <input name="email" type="email" value="<?= htmlspecialchars($userEmail) ?>" placeholder="you@example.com" required />
        </div>
        <div class="field">
          <label>Order type</label>
          <select name="type">
            <option>Dine-in</option><option>Takeout</option><option>Delivery</option>
          </select>
        </div>
        <div class="field">
          <label>Payment method</label>
          <select name="payment">
            <option>Cash</option><option>GCash</option><option>Card</option>
          </select>
        </div>
        <p class="hint">Payment is simulated — nothing is charged.</p>
        <button type="submit" class="btn" style="margin-top:.8rem" <?= count($cartItems) === 0 ? 'disabled' : '' ?>>Place order</button>
      </form>
    </div>

    <div class="card">
      <h2>Summary</h2>
      <div id="summary">
        <?php if (count($cartItems) === 0): ?>
          <div class="empty">Cart is empty. <a href="index.php">Add something tasty.</a></div>
        <?php else: ?>
          <table>
            <tbody>
              <?php foreach ($cartItems as $item): ?>
                <tr>
                  <td><?= htmlspecialchars($item['name']) ?> × <?= (int)$item['qty'] ?></td>
                  <td class="right">₱<?= number_format($item['price'] * $item['qty'], 2) ?></td>
                </tr>
              <?php endforeach; ?>
              <tr>
                <td><strong>Total</strong></td>
                <td class="right"><strong>₱<?= number_format($total, 2) ?></strong></td>
              </tr>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
</body>
</html>