<?php
// Force the server and browser to bypass caching for the active session
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include 'config/db.php';
$picks = $pdo->query("SELECT * FROM products WHERE active = 1 LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>An Inventory and Ordering System of CrispyWraps</title>
<link rel="stylesheet" href="/css/styles.css" />
</head>
<body>
<?php include 'includes/header.php'; ?>
<main class="wrap">

  <section class="hero land-hero">
    <span class="brand-mark big">CW</span>
    <h1>An Inventory and Ordering System of CrispyWraps</h1>
    <p>Order your wraps in a few taps. Behind the counter, every order updates stock automatically through recipe mapping.</p>
    <div class="row" style="margin-top:1.1rem">
      <a class="btn" href="menu.php">Order Now</a>
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
    <a class="btn ghost sm" href="menu.php">See full menu</a>
  </div>
  
  <div class="grid g3" style="margin-bottom:1.8rem">
    <?php foreach ($picks as $p): 
      $words = explode(' ', $p['name']);
      $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
    ?>
      <article class="prod">
        <div class="thumb" style="padding: 0; overflow: hidden;">
          <?php if (!empty($p['image'])): ?>
            <img src="/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block;" />
          <?php else: ?>
            <div style="display: grid; place-items: center; height: 100%; width: 100%;">
              <?= $initials ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="body">
          <div class="spread"><strong><?= htmlspecialchars($p['name']) ?></strong><span class="price">₱<?= number_format($p['price'], 2) ?></span></div>
          <small><?= htmlspecialchars($p['desc']) ?></small>
          <div style="margin-top:auto;padding-top:.6rem" class="spread">
            <span class="badge"><?= htmlspecialchars($p['category']) ?></span>
            <a class="btn sm" href="menu.php">Order</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

</main>
<footer style="text-align: center; padding: 2rem; color: #888; font-size: 0.875rem; border-top: 1px solid var(--line);">
  CrispyWraps — Inventory & Ordering System
</footer>
<script src="js/ui.js"></script>
</body>
</html>