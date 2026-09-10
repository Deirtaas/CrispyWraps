<?php
$cartCount = 0;
$isCustomer = isset($_SESSION['customer']);
$isStaff = isset($_SESSION['staff']);

if ($isCustomer && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT SUM(qty) FROM cart WHERE customer_id = ?");
    $stmt->execute([$_SESSION['customer']['id']]);
    $cartCount = $stmt->fetchColumn() ?: 0;
}
$activePage = basename($_SERVER['PHP_SELF']);
?>
<header class="site">
  <div class="nav-inner">
    <a class="brand" href="index.php"><span class="brand-mark">CW</span>
      <span><strong>CrispyWraps</strong><small>Inventory &amp; Ordering</small></span>
    </a>
    <nav class="nav-links">
      <a class="<?= $activePage == 'index.php' ? 'active' : '' ?>" href="index.php">Home</a>
      <a class="<?= $activePage == 'menu.php' ? 'active' : '' ?>" href="menu.php">Menu</a>
      
      <?php if($isCustomer): ?>
        <a class="<?= $activePage == 'cart.php' ? 'active' : '' ?>" href="cart.php">Cart <span class="pill" id="cart-pill"><?= $cartCount ?></span></a>
        <a class="<?= $activePage == 'my-orders.php' ? 'active' : '' ?>" href="my-orders.php">My Orders</a>
      <?php endif; ?>
      
      <a class="<?= $activePage == 'feedback.php' ? 'active' : '' ?>" href="feedback.php">Feedback</a>
    </nav>
    <div class="nav-actions">
      <?php if($isCustomer): ?>
        <span class="who">Hi, <?= htmlspecialchars(explode(' ', $_SESSION['customer']['name'])[0]) ?></span>
        <a class="btn sm" href="logout.php">Log out</a>
      <?php elseif($isStaff): ?>
        <span class="who">Staff: <?= htmlspecialchars(explode(' ', $_SESSION['staff']['name'])[0]) ?></span>
        <a class="btn dark sm" href="staff/dashboard.php">Dashboard</a>
      <?php else: ?>
        <a class="btn sm" href="login.php">Log in</a>
      <?php endif; ?>
    </div>
  </div>
</header>