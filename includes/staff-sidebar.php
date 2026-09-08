<?php
$activePage = basename($_SERVER['PHP_SELF']);
$staffName = isset($_SESSION['staff']) ? $_SESSION['staff']['name'] : '';
$staffRole = isset($_SESSION['staff']) ? $_SESSION['staff']['role'] : '';

$navItems = [
    'dashboard.php' => ['Dashboard', 'DB'],
    'orders.php' => ['Order Operations', 'OR'],
    'inventory.php' => ['Inventory', 'IN'],
    'recipes.php' => ['Recipe Mapping', 'RM'],
    'menu.php' => ['Menu Configuration', 'MC'],
    'analytics.php' => ['Analytics', 'AN'],
    'reviews.php' => ['Customer Reviews', 'CR']
];
?>
<aside class="sidebar">
  <a class="brand" href="dashboard.php">
    <span class="brand-mark">CW</span>
    <span><strong>CrispyWraps</strong><small>Staff Console</small></span>
  </a>
  <nav>
    <?php foreach ($navItems as $url => $info): ?>
      <a class="<?= $activePage == $url ? 'active' : '' ?>" href="<?= $url ?>">
        <span><?= $info[1] ?></span><?= $info[0] ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-foot">
    <div class="who-box">
      <strong><?= htmlspecialchars($staffName) ?></strong>
      <small><?= htmlspecialchars($staffRole) ?></small>
    </div>
    <a class="btn sm" href="../logout.php" style="text-align:center; display:block;">Log out</a>
    <a class="btn ghost sm" href="../index.php" style="text-align:center; display:block;">View storefront</a>
  </div>
</aside>