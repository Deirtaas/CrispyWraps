<?php
include '../config/db.php';
if (!isset($_SESSION['staff'])) { header("Location: ../login.php"); exit; }

$today = date('Y-m-d');

$totalRev = (float)($pdo->query("SELECT SUM(total) FROM orders WHERE status != 'Cancelled'")->fetchColumn() ?: 0);

$stmt = $pdo->prepare("SELECT SUM(total) FROM orders WHERE status != 'Cancelled' AND DATE(date) = ?");
$stmt->execute([$today]);
$todayRev = (float)($stmt->fetchColumn() ?: 0);

$orderCount = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status != 'Cancelled'")->fetchColumn();
$wasteCost  = (float)($pdo->query("SELECT SUM(cost) FROM waste")->fetchColumn() ?: 0);

$chartData = $pdo->query("
    SELECT p.name, SUM(oi.price * oi.qty) AS rev
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status != 'Cancelled'
    GROUP BY p.name
    ORDER BY rev DESC
    LIMIT 5
")->fetchAll();

$labels = [];
$data   = [];
foreach ($chartData as $row) {
    $labels[] = $row['name'];
    $data[]   = (float)$row['rev'];
}

$salesReport = $pdo->query("
    SELECT p.name, SUM(oi.qty) AS qty, SUM(oi.price * oi.qty) AS rev
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status != 'Cancelled'
    GROUP BY p.name
    ORDER BY rev DESC
")->fetchAll();

$stockAnalysis = $pdo->query("SELECT name, stock, unit, reorder FROM ingredients ORDER BY name ASC")->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Analytics — CrispyWraps Staff</title>
<link rel="stylesheet" href="../css/styles.css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="staff-layout">
  <?php include '../includes/staff-sidebar.php'; ?>

  <main class="staff-main">

    <div class="grid g4" style="margin-bottom: 1.5rem;">
      <div class="stat"><div class="k">Total Revenue</div><div class="v">₱<?= number_format($totalRev, 2) ?></div></div>
      <div class="stat"><div class="k">Sales Today</div><div class="v">₱<?= number_format($todayRev, 2) ?></div></div>
      <div class="stat"><div class="k">Orders</div><div class="v"><?= $orderCount ?></div></div>
      <div class="stat"><div class="k">Waste Cost</div><div class="v">₱<?= number_format($wasteCost, 2) ?></div></div>
    </div>

    <div class="card" style="margin-bottom: 1.5rem;">
      <h2>Top products by revenue</h2>
      <div style="height: 300px; width: 100%; margin-top: 1rem;">
        <canvas id="revenueChart"></canvas>
      </div>
    </div>

    <div class="grid g2">
      <div class="card">
        <h2>Sales report</h2>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Product</th><th>Qty Sold</th><th class="right">Revenue</th></tr></thead>
            <tbody>
              <?php if (count($salesReport) > 0): ?>
                <?php foreach ($salesReport as $s): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                  <td><?= (int)$s['qty'] ?></td>
                  <td class="right">₱<?= number_format((float)$s['rev'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="3" class="muted center">No sales data available yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card">
        <h2>Stock analysis</h2>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Ingredient</th><th>On Hand</th><th class="right">Status</th></tr></thead>
            <tbody>
              <?php foreach ($stockAnalysis as $st):
                $isLow = $st['stock'] <= $st['reorder'];
                $statusHtml = $isLow
                    ? '<span class="badge b-cancel">Low</span>'
                    : '<span class="badge b-ready">Healthy</span>';
              ?>
              <tr>
                <td><strong><?= htmlspecialchars($st['name']) ?></strong></td>
                <td><?= (int)$st['stock'] . ' ' . htmlspecialchars($st['unit']) ?></td>
                <td class="right"><?= $statusHtml ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>
</div>

<script src="../js/ui.js"></script>
<script>
  const ctx = document.getElementById('revenueChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [{
        label: 'Revenue (₱)',
        data: <?= json_encode($data) ?>,
        backgroundColor: '#e8762c',
        hoverBackgroundColor: '#c25c19',
        borderRadius: 6,
        barPercentage: 0.6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { callback: v => '₱' + v },
          grid: { color: '#e9e1d6' }
        },
        x: { grid: { display: false } }
      }
    }
  });
</script>
</body>
</html>