<?php
include '../config/db.php';
if (!isset($_SESSION['staff'])) { header("Location: ../login.php"); exit; }

// Handle status updates[cite: 12]
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['order_id']]);
    header("Location: orders.php?filter=" . urlencode($_GET['filter'] ?? 'All'));
    exit;
}

$filter = $_GET['filter'] ?? 'All';
$nextStatus = ['New' => 'Cooking', 'Cooking' => 'Ready', 'Ready' => 'Completed'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Order Operations — CrispyWraps Staff</title>
<link rel="stylesheet" href="/css/styles.css" />
</head>
<body>
<div class="staff-layout">
  <div id="staff-shell"></div>
  <main class="staff-main">
    <div class="page-head spread">
      <div><h1>Order Operations</h1><p class="muted">Order list display — view all orders, update status, cancel or refund.</p></div>
      <div class="row">
        <form method="GET" style="margin:0">
          <select name="filter" onchange="this.form.submit()" style="width:auto">
            <?php 
            $opts = ['All', 'New', 'Cooking', 'Ready', 'Completed', 'Cancelled', 'Refunded'];
            foreach ($opts as $opt) {
                $sel = $filter === $opt ? 'selected' : '';
                echo "<option $sel>$opt</option>";
            }
            ?>
          </select>
        </form>
      </div>
    </div>
    <div class="card">
      <div id="list">
        <?php
        $query = "SELECT * FROM orders";
        if ($filter !== 'All') $query .= " WHERE status = " . $pdo->quote($filter);
        $query .= " ORDER BY date DESC";
        $orders = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

        if (count($orders) > 0) {
            echo '<div class="table-wrap"><table>';
            echo '<thead><tr><th>Order</th><th>Customer</th><th>Items</th><th>Type</th><th>Payment</th><th>Total</th><th>Status</th><th class="right">Action</th></tr></thead><tbody>';
            
            foreach ($orders as $o) {
                // Fetch items
                $itemStmt = $pdo->prepare("SELECT p.name, oi.qty FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                $itemStmt->execute([$o['id']]);
                $itemsHtml = implode("<br>", array_map(function($i) { return htmlspecialchars($i['name']) . ' × ' . $i['qty']; }, $itemStmt->fetchAll(PDO::FETCH_ASSOC)));
                
                $statusMap = ['New' => 'b-new', 'Cooking' => 'b-cook', 'Ready' => 'b-ready', 'Completed' => 'b-done', 'Cancelled' => 'b-cancel', 'Refunded' => 'b-cancel'];
                $badge = "<span class='badge " . ($statusMap[$o['status']] ?? '') . "'>{$o['status']}</span>";

                echo "<tr>
                  <td><strong>{$o['id']}</strong><br><small>{$o['date']}</small></td>
                  <td>" . htmlspecialchars($o['customer_name']) . "</td>
                  <td><small>{$itemsHtml}</small></td>
                  <td>{$o['type']}</td><td>{$o['payment']}</td><td>₱" . number_format($o['total'], 2) . "</td>
                  <td>{$badge}</td>
                  <td class='right'><div class='row' style='justify-content:flex-end'>";
                
                // Next Status Button[cite: 12]
                if (isset($nextStatus[$o['status']])) {
                    echo "<form method='POST' style='margin:0'>
                            <input type='hidden' name='order_id' value='{$o['id']}'>
                            <input type='hidden' name='status' value='{$nextStatus[$o['status']]}'>
                            <button class='btn sm'>Mark {$nextStatus[$o['status']]}</button>
                          </form>";
                }
                // Cancel Button[cite: 12]
                if (in_array($o['status'], ['New', 'Cooking'])) {
                    echo "<form method='POST' style='margin:0'>
                            <input type='hidden' name='order_id' value='{$o['id']}'>
                            <input type='hidden' name='status' value='Cancelled'>
                            <button class='btn ghost sm'>Cancel</button>
                          </form>";
                }
                // Refund Button[cite: 12]
                if ($o['status'] === 'Completed') {
                    echo "<form method='POST' style='margin:0'>
                            <input type='hidden' name='order_id' value='{$o['id']}'>
                            <input type='hidden' name='status' value='Refunded'>
                            <button class='btn ghost sm'>Refund</button>
                          </form>";
                }
                echo "</div></td></tr>";
            }
            echo '</tbody></table></div>';
        } else {
            echo '<div class="empty">No orders in this filter.</div>';
        }
        ?>
      </div>
    </div>
  </main>
</div>
<script src="../js/ui.js"></script>
<script>mountStaffShell("orders.php");</script>
</body>
</html>