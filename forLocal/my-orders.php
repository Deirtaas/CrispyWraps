<?php
include 'config/db.php';

if (!isset($_SESSION['customer'])) {
    header("Location: login.php");
    exit;
}
$me = $_SESSION['customer'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>My Orders — CrispyWraps</title>
<link rel="stylesheet" href="css/styles.css" />
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="wrap">
  <h1>My Orders</h1>
  <p class="muted">Track the status of every order you placed on this device.</p>

  <?php if (isset($_GET['placed'])): ?>
  <div class="alert ok" style="margin-top:1rem">
    Order <strong><?= htmlspecialchars($_GET['placed']) ?></strong> placed. Ingredients were deducted automatically through recipe mapping.
  </div>
  <?php endif; ?>

  <div class="card" style="margin-top:1rem" id="list">
    <?php
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_email = ? ORDER BY date DESC");
    $stmt->execute([$me['email']]);
    $orders = $stmt->fetchAll();

    if (count($orders) > 0) {
        echo '<div class="table-wrap"><table>';
        echo '<thead><tr><th>Order</th><th>Items</th><th>Type</th><th>Payment</th><th>Total</th><th>Status</th></tr></thead><tbody>';

        $itemStmt = $pdo->prepare("
            SELECT products.name, order_items.qty
            FROM order_items
            JOIN products ON order_items.product_id = products.id
            WHERE order_items.order_id = ?
        ");

        $statusMap = [
            'New' => 'b-new', 'Cooking' => 'b-cook', 'Ready' => 'b-ready',
            'Completed' => 'b-done', 'Cancelled' => 'b-cancel', 'Refunded' => 'b-cancel'
        ];

        foreach ($orders as $o) {
            $itemStmt->execute([$o['id']]);
            $items = $itemStmt->fetchAll();
            $itemHtml = implode('<br>', array_map(function ($i) {
                return htmlspecialchars($i['name']) . ' × ' . (int)$i['qty'];
            }, $items));

            $badgeClass = $statusMap[$o['status']] ?? '';
            $statusBadge = "<span class='badge " . $badgeClass . "'>" . htmlspecialchars($o['status']) . "</span>";
            $formattedDate = date("Y-m-d H:i", strtotime($o['date']));

            echo "<tr>
                <td><strong>" . htmlspecialchars($o['id']) . "</strong><br><small>{$formattedDate}</small></td>
                <td>{$itemHtml}</td>
                <td>" . htmlspecialchars($o['type']) . "</td>
                <td>" . htmlspecialchars($o['payment']) . "</td>
                <td>₱" . number_format((float)$o['total'], 2) . "</td>
                <td>{$statusBadge}</td>
            </tr>";
        }
        echo '</tbody></table></div>';
    } else {
        echo '<div class="empty">No orders yet. <a href="index.php">Browse the menu</a></div>';
    }
    ?>
  </div>
</main>
<script src="js/ui.js"></script>
</body>
</html>