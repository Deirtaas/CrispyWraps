<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config/db.php';

$selectedCategory = $_GET['category'] ?? '';
$menuItems = [];

try {
    if ($selectedCategory && $selectedCategory !== 'All') {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE active = 1 AND category = ?");
        $stmt->execute([$selectedCategory]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE active = 1");
        $stmt->execute();
    }
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div style='padding: 2rem; font-family: sans-serif; background: #ffebee; color: #c62828;'>
            <h3>Database Error in menu.php:</h3>
            <p>" . $e->getMessage() . "</p>
         </div>");
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Browse Menu — CrispyWraps</title>
  <link rel="stylesheet" href="/css/styles.css" />
</head>
<body>

  <?php include 'includes/header.php'; ?>

  <main class="container py-4">
    <div class="hero-banner wrap" style="padding-top: 2rem;">
      <h1>An Inventory and Ordering System of CrispyWraps</h1>
      <p>Order in a few taps. Behind the counter, every order updates stock automatically through recipe mapping.</p>
    </div>

    <div class="section-head wrap" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
      <h2>Browse Menu</h2>
      <input type="text" id="menu-search" placeholder="Search the menu..." style="padding: 0.5rem; border-radius: 4px; border: 1px solid #ccc; max-width: 250px;">
    </div>

    <div class="category-filters wrap" style="margin-bottom: 2rem; display: flex; gap: 0.5rem;">
      <a href="menu.php" class="btn sm <?= empty($selectedCategory) || $selectedCategory == 'All' ? 'active' : 'ghost' ?>">All</a>
      <a href="menu.php?category=Wraps" class="btn sm <?= $selectedCategory == 'Wraps' ? 'active' : 'ghost' ?>">Wraps</a>
      <a href="menu.php?category=Rice Meals" class="btn sm <?= $selectedCategory == 'Rice Meals' ? 'active' : 'ghost' ?>">Rice Meals</a>
      <a href="menu.php?category=Sides" class="btn sm <?= $selectedCategory == 'Sides' ? 'active' : 'ghost' ?>">Sides</a>
      <a href="menu.php?category=Drinks" class="btn sm <?= $selectedCategory == 'Drinks' ? 'active' : 'ghost' ?>">Drinks</a>
    </div>

    <div class="menu-grid wrap grid g3" id="menu-grid">
      <?php if (count($menuItems) > 0): ?>
        <?php foreach ($menuItems as $item): ?>
          <div class="prod">
            <div class="thumb">
              <?= strtoupper(substr($item['name'], 0, 2)) ?>
            </div>
            <div class="body">
              <div class="spread">
                <strong><?= htmlspecialchars($item['name']) ?></strong>
                <span class="price">₱<?= number_format($item['price'], 2) ?></span>
              </div>
              <p class="muted" style="margin-top: 0.5rem; font-size: 0.875rem;"><?= htmlspecialchars($item['desc']) ?></p>
              <div class="spread" style="margin-top: auto; padding-top: 1rem;">
                <span class="badge b-ready">In stock</span>
                <form method="POST" action="cart.php" style="margin: 0;">
                  <input type="hidden" name="action" value="add">
                  <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                  <button type="submit" class="btn sm">Add to cart</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="muted wrap">No items match your search in this category.</p>
      <?php endif; ?>
    </div>
  </main>

  <footer style="text-align: center; padding: 2rem; color: #888; font-size: 0.875rem;">
    CrispyWraps — Inventory & Ordering System
  </footer>

  <script src="js/ui.js"></script>
  <script>
    const searchInput = document.getElementById('menu-search');
    const menuItems = document.querySelectorAll('.prod');

    if (searchInput) {
      searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();

        menuItems.forEach(item => {
          // Find the product name inside the <strong> tag
          const itemName = item.querySelector('strong').textContent.toLowerCase();
          
          // Toggle display based on whether the name includes the search term
          if (itemName.includes(searchTerm)) {
            item.style.display = 'flex';
          } else {
            item.style.display = 'none';
          }
        });
      });
    }
  </script>
</body>
</html>
