<?php
$cartCount = 0;
$customerId = isset($_SESSION['customer']) ? $_SESSION['customer']['id'] : session_id();

if (isset($pdo)) {
    $stmt = $pdo->prepare("SELECT SUM(qty) FROM cart WHERE customer_id = ?");
    $stmt->execute([$customerId]);
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
      <a class="<?= $activePage == 'cart.php' ? 'active' : '' ?>" href="cart.php">Cart <span class="pill" id="cart-pill"><?= $cartCount ?></span></a>
      <a class="<?= $activePage == 'my-orders.php' ? 'active' : '' ?>" href="my-orders.php">My Orders</a>
      <a class="<?= $activePage == 'feedback.php' ? 'active' : '' ?>" href="feedback.php">Feedback</a>
    </nav>
    <div class="nav-actions">
      <?php if(isset($_SESSION['customer'])): ?>
        <span class="who">Hi, <?= htmlspecialchars(explode(' ', $_SESSION['customer']['name'])[0]) ?></span>
        <a class="btn sm" href="logout.php">Log out</a>
      <?php else: ?>
        <a class="btn sm" href="login.php">Log in</a>
      <?php endif; ?>
    </div>
  </div>
</header>