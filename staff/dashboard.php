<?php
include '../config/db.php';
if (!isset($_SESSION['staff'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch Stats
$today = date('Y-m-d');
$salesStmt = $pdo->query("SELECT SUM(total) as total FROM orders WHERE DATE(date) = '$today' AND status != 'Cancelled'");
$salesToday = $salesStmt->fetch()['total'] ?? 0;

$openOrdersStmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status IN ('New', 'Cooking', 'Ready')");
$openOrders = $openOrdersStmt->fetch()['count'];

$lowStockStmt = $pdo->query("SELECT COUNT(*) as count FROM ingredients WHERE stock <= reorder");
$lowStockCount = $lowStockStmt->fetch()['count'];

$menuItemsStmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE active = 1");
$menuItems = $menuItemsStmt->fetch()['count'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Dashboard — CrispyWraps Staff</title>
<link rel="stylesheet" href="../css/styles.css" />
</head>
<body>
<div class="staff-layout">
  <?php include '../includes/staff-sidebar.php'; ?>
  <main class="staff-main">
    <div class="page-head"><h1>Dashboard</h1><p class="muted">Today's operation at a glance.</p></div>
    
    <div class="grid g4" id="stats">
      <div class="stat"><div class="k">Sales today</div><div class="v">₱<?= number_format($salesToday, 2) ?></div></div>
      <div class="stat"><div class="k">Open orders</div><div class="v"><?= $openOrders ?></div></div>
      <div class="stat"><div class="k">Low stock items</div><div class="v"><?= $lowStockCount ?></div></div>
      <div class="stat"><div class="k">Menu items</div><div class="v"><?= $menuItems ?></div></div>
    </div>

    <div class="grid g2" style="margin-top:1rem">
      <div class="card">
        <h2>Live orders</h2>
        <?php
        $liveStmt = $pdo->query("SELECT * FROM orders WHERE status IN ('New', 'Cooking', 'Ready') ORDER BY date ASC");
        if ($liveStmt->rowCount() > 0) {
            echo "<table><tbody>";
            while ($o = $liveStmt->fetch()) {
                $statusMap = ['New' => 'b-new', 'Cooking' => 'b-cook', 'Ready' => 'b-ready'];
                $badgeClass = $statusMap[$o['status']] ?? '';
                echo "<tr>
                    <td><strong>{$o['id']}</strong><br><small>{$o['customer_name']}</small></td>
                    <td>₱" . number_format($o['total'], 2) . "</td>
                    <td class='right'><span class='badge {$badgeClass}'>{$o['status']}</span></td>
                </tr>";
            }
            echo "</tbody></table>";
        } else {
            echo '<div class="empty">No open orders.</div>';
        }
        ?>
      </div>
      <div class="card">
        <h2>Alerts</h2>
        <?php
        $alerts = false;
        $lowIngStmt = $pdo->query("SELECT name, stock, unit, reorder FROM ingredients WHERE stock <= reorder");
        while ($i = $lowIngStmt->fetch()) {
            echo "<div class='alert bad'>Low stock: <strong>{$i['name']}</strong> — {$i['stock']}{$i['unit']} left (reorder at {$i['reorder']}{$i['unit']})</div>";
            $alerts = true;
        }
        if (!$alerts) echo '<div class="alert ok">All ingredients are above their reorder level.</div>';
        ?>
      </div>
    </div>
  </main>
</div>
<script src="../js/ui.js"></script>
</body>
</html>