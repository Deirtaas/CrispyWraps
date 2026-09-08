<?php
include 'config/db.php';
// Fetch top 6 active products[cite: 6, 10]
$picks = $pdo->query("SELECT * FROM products WHERE active = 1 LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>An Inventory and Ordering System of CrispyWraps</title>
<link rel="stylesheet" href="css/styles.css" />
</head>
<body>
<?php include 'includes/header.php'; ?>
<main class="wrap">

  <section class="hero land-hero">
    <span class="brand-mark big">CW</span>
    <h1>An Inventory and Ordering System of CrispyWraps</h1>
    <p>Order your wraps in a few taps. Behind the counter, every order updates stock automatically through recipe mapping.</p>
    <div class="row" style="margin-top:1.1rem">
      <a class="btn" href="index.php">Order Now</a>
      <a class="btn ghost light" href="login.php">Staff Portal</a>
    </div>
    <div class="tags"><span>Order List Display</span><span>Recipe Mapping</span><span>Analytical Reports</span></div>
  </section>

  <h2 class="sec-title">What the system does</h2>
  <div class="grid g3" style="margin-bottom:1.8rem">
    <div class="card feat"><span class="feat-ico">OL</span><strong>Order List Display</strong><small>Every order is listed with its status from New to Completed, for customers and staff alike.</small></div>
    <div class="card feat"><span class="feat-ico">LS</span><strong>Low Stock Alert</strong><small>Items that fall below their reorder point are flagged right away, with expiry warnings too.</small></div>
    <div class="card feat"><span class="feat-ico">AR</span><strong>Analytical Reports</strong><small>Sales, best sellers, stock levels and waste, shown as simple charts and summaries.</small></div>
    <div class="card feat"><span class="feat-ico">CF</span><strong>Customer Feedback</strong><small>Customers rate items and leave comments; staff can reply from the console.</small></div>
    <div class="card feat"><span class="feat-ico">RM</span><strong>Recipe Mapping</strong><small>Each menu item is linked to its ingredients, so ordering deducts stock automatically.</small></div>
  </div>

  <div class="spread" style="margin-bottom:.8rem">
    <h2 class="sec-title" style="margin:0">Popular picks</h2>
    <a class="btn ghost sm" href="index.php">See full menu</a>
  </div>
  
  <div class="grid g3" style="margin-bottom:1.8rem">
    <?php foreach ($picks as $p): 
      $words = explode(' ', $p['name']);
      $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
    ?>
      <article class="prod">
        <div class="thumb"><?= $initials ?></div>
        <div class="body">
          <div class="spread"><strong><?= htmlspecialchars($p['name']) ?></strong><span class="price">₱<?= number_format($p['price'], 2) ?></span></div>
          <small><?= htmlspecialchars($p['desc']) ?></small>
          <div style="margin-top:auto;padding-top:.6rem" class="spread">
            <span class="badge"><?= htmlspecialchars($p['category']) ?></span>
            <a class="btn sm" href="index.php">Order</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <section class="card staff-cta">
    <div class="grow">
      <h2>For the staff</h2>
      <small>Dashboard with today's sales and open orders, order operations, inventory with stock in/out and waste logging, recipe mapping, menu configuration, analytics and customer reviews.</small>
      <div class="hint" style="margin-top:.7rem">Demo sign-in — admin@crispywraps.com / admin123 &nbsp;·&nbsp; staff@crispywraps.com / staff123</div>
    </div>
    <a class="btn dark" href="login.php">Open Staff Console</a>
  </section>

</main>
<footer class="site-foot">CrispyWraps — front-end demo. Adapted for PHP Backend.</footer>
<script src="js/ui.js"></script>
<script>mountCustomerNav("landing.php");</script>
</body>
</html>