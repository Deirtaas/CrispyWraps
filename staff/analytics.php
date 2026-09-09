<?php
include '../config/db.php';
if (!isset($_SESSION['staff'])) { header("Location: ../login.php"); exit; }

// SQL Aggregations[cite: 15]
$revenue = $pdo->query("SELECT SUM(total) FROM orders WHERE status NOT IN ('Cancelled', 'Refunded')")->fetchColumn() ?: 0;
$salesToday = $pdo->query("SELECT SUM(total) FROM orders WHERE DATE(date) = CURDATE() AND status NOT IN ('Cancelled', 'Refunded')")->fetchColumn() ?: 0;
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status NOT IN ('Cancelled', 'Refunded')")->fetchColumn() ?: 0;
$wasteCost = $pdo->query("SELECT SUM(cost) FROM waste")->fetchColumn() ?: 0;

// Sales by Product[cite: 15]
$salesStmt = $pdo->query("
    SELECT p.name, SUM(oi.qty) as qty, SUM(oi.price * oi.qty) as revenue 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    JOIN orders o ON oi.order_id = o.id 
    WHERE o.status NOT IN ('Cancelled', 'Refunded')
    GROUP BY p.id 
    ORDER BY revenue DESC
");
$salesData = $salesStmt->fetchAll(PDO::FETCH_ASSOC);
$top6 = array_slice($salesData, 0, 6);

// Stock & Waste[cite: 15]
$ingredients = $pdo->query("SELECT name, stock, unit, reorder FROM ingredients ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$wasteLog = $pdo->query("SELECT w.*, i.name as ing_name FROM waste w LEFT JOIN ingredients i ON w.ingredient_id = i.id ORDER BY w.date DESC")->fetchAll(PDO::FETCH_ASSOC);
$lowStockCount = count(array_filter($ingredients, function($i) { return $i['stock'] <= $i['reorder']; }));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Analytics — CrispyWraps Staff</title>
<link rel="stylesheet" href="/css/styles.css" />
</head>
<body>
<div class="staff-layout">
  <?php include '../includes/staff-sidebar.php'; ?>
  <main class="staff-main">
    <div class="page-head"><h1>Analytics</h1><p class="muted">Sales, stock analysis and waste reports.</p></div>
    
    <div class="grid g4" id="stats">
      <div class="stat"><div class="k">Total revenue</div><div class="v">₱<?= number_format($revenue, 2) ?></div></div>
      <div class="stat"><div class="k">Sales today</div><div class="v">₱<?= number_format($salesToday, 2) ?></div></div>
      <div class="stat"><div class="k">Orders</div><div class="v"><?= $totalOrders ?></div></div>
      <div class="stat"><div class="k">Waste cost</div><div class="v">₱<?= number_format($wasteCost, 2) ?></div></div>
    </div>

    <div class="card" style="margin-top:1rem">
      <h2>Top products by revenue</h2>
      <canvas id="chart-sales" style="width:100%;height:260px"></canvas>
    </div>
    
    <div class="grid g2" style="margin-top:1rem">
      <div class="card">
        <h2>Sales report</h2>
        <?php if (count($salesData) > 0): ?>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Product</th><th>Qty sold</th><th class="right">Revenue</th></tr></thead>
              <tbody>
                <?php foreach ($salesData as $row): ?>
                  <tr><td><?= htmlspecialchars($row['name']) ?></td><td><?= $row['qty'] ?></td><td class="right">₱<?= number_format($row['revenue'], 2) ?></td></tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="empty">No sales yet.</div>
        <?php endif; ?>
      </div>
      
      <div class="card">
        <h2>Stock analysis</h2>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Ingredient</th><th>On hand</th><th class="right">Status</th></tr></thead>
            <tbody>
              <?php foreach ($ingredients as $i): ?>
                <tr>
                  <td><?= htmlspecialchars($i['name']) ?></td>
                  <td><?= $i['stock'] ?> <?= $i['unit'] ?></td>
                  <td class="right"><?= $i['stock'] <= $i['reorder'] ? '<span class="badge b-cancel">Reorder</span>' : '<span class="badge b-ready">Healthy</span>' ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <p class="muted" style="margin-top:.6rem"><?= $lowStockCount ?> item(s) at or below reorder level.</p>
      </div>
    </div>
    
    <div class="card" style="margin-top:1rem">
      <h2>Waste report</h2>
      <?php if (count($wasteLog) > 0): ?>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Date</th><th>Ingredient</th><th>Qty</th><th>Reason</th><th class="right">Cost</th></tr></thead>
            <tbody>
              <?php foreach ($wasteLog as $w): ?>
                <tr>
                  <td><?= $w['date'] ?></td>
                  <td><?= htmlspecialchars($w['ing_name'] ?? '—') ?></td>
                  <td><?= $w['qty'] ?></td>
                  <td><?= htmlspecialchars($w['reason']) ?></td>
                  <td class="right">₱<?= number_format($w['cost'], 2) ?></td>
                </tr>
              <?php endforeach; ?>
              <tr><td colspan="4"><strong>Total waste cost</strong></td><td class="right"><strong>₱<?= number_format($wasteCost, 2) ?></strong></td></tr>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty">No waste recorded.</div>
      <?php endif; ?>
    </div>
  </main>
</div>

<script src="../js/ui.js"></script>
</body>
</html>