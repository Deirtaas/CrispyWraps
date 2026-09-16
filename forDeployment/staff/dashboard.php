<?php
include '../config/db.php';
if (!isset($_SESSION['staff'])) { header("Location: ../login.php"); exit; }

$today = date('Y-m-d');

$stmt = $pdo->prepare("SELECT SUM(total) AS total FROM orders WHERE DATE(date) = ? AND status != 'Cancelled'");
$stmt->execute([$today]);
$salesToday = $stmt->fetch()['total'] ?? 0;

$openOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('New','Cooking','Ready')")->fetchColumn();

$lowStockCount = (int)$pdo->query("SELECT COUNT(*) FROM ingredients WHERE stock <= reorder")->fetchColumn();

$menuItems = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE active = 1")->fetchColumn();

$liveOrders = $pdo->query("SELECT * FROM orders WHERE status IN ('New','Cooking','Ready') ORDER BY date ASC")->fetchAll();
$lowIngs    = $pdo->query("SELECT name, stock, unit, reorder FROM ingredients WHERE stock <= reorder")->fetchAll();
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
      <div class="stat"><div class="k">Sales today</div><div class="v">₱<?= number_format((float)$salesToday, 2) ?></div></div>
      <div class="stat"><div class="k">Open orders</div><div class="v"><?= $openOrders ?></div></div>
      <div class="stat"><div class="k">Low stock items</div><div class="v"><?= $lowStockCount ?></div></div>
      <div class="stat"><div class="k">Menu items</div><div class="v"><?= $menuItems ?></div></div>
    </div>

    <div class="grid g2" style="margin-top:1rem">
      <div class="card">
        <h2>Live orders</h2>
        <?php if (count($liveOrders) > 0): ?>
          <table><tbody>
          <?php foreach ($liveOrders as $o):
            $statusMap = ['New' => 'b-new', 'Cooking' => 'b-cook', 'Ready' => 'b-ready'];
            $badgeClass = $statusMap[$o['status']] ?? '';
          ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($o['id']) ?></strong><br>
                <small><?= htmlspecialchars($o['customer_name']) ?></small>
              </td>
              <td>₱<?= number_format((float)$o['total'], 2) ?></td>
              <td class="right">
                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($o['status']) ?></span>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody></table>
        <?php else: ?>
          <div class="empty">No open orders.</div>
        <?php endif; ?>
      </div>

      <div class="card">
        <h2>Alerts</h2>
        <?php if (count($lowIngs) > 0): ?>
          <?php foreach ($lowIngs as $i): ?>
            <div class="alert bad">
              Low stock: <strong><?= htmlspecialchars($i['name']) ?></strong> —
              <?= (int)$i['stock'] ?><?= htmlspecialchars($i['unit']) ?> left
              (reorder at <?= (int)$i['reorder'] ?><?= htmlspecialchars($i['unit']) ?>)
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="alert ok">All ingredients are above their reorder level.</div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
<script src="../js/ui.js"></script>
</body>
</html>