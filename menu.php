<?php 
// config/db.php must be included to establish the $pdo connection
include 'config/db.php'; 
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Browse Menu — An Inventory and Ordering System of CrispyWraps</title>
<meta name="description" content="Order crispy wraps, rice meals, sides and drinks online from CrispyWraps." />
<link rel="stylesheet" href="css/styles.css" />
</head>
<body>
<header class="site"><div id="site-nav"></div></header>
<main class="wrap">
  <section class="hero">
    <h1>An Inventory and Ordering System of CrispyWraps</h1>
    <p>Order in a few taps. Behind the counter, every order updates stock automatically through recipe mapping.</p>
    <div class="tags"><span>Order List Display</span><span>Low Stock Alert</span><span>Analytical Reports</span><span>Customer Feedback</span><span>Recipe Mapping</span></div>
  </section>

  <div class="spread" style="margin-bottom:.8rem">
    <h2>Browse Menu</h2>
    <!-- Replaced JS search with a PHP GET form -->
    <form method="GET" action="index.php" style="display:flex; max-width:260px; width:100%">
      <?php if (isset($_GET['category'])): ?>
        <input type="hidden" name="category" value="<?php echo htmlspecialchars($_GET['category']); ?>" />
      <?php endif; ?>
      <input name="search" placeholder="Search the menu…" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="width:100%" />
    </form>
  </div>
  
  <div class="chips" id="chips">
    <?php
    // Fetch categories and render as links for filtering
    $activeCat = isset($_GET['category']) ? $_GET['category'] : 'All';
    $searchParam = isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '';
    
    $activeClass = $activeCat === 'All' ? 'active' : '';
    echo "<a href='index.php?category=All{$searchParam}' class='chip {$activeClass}' style='text-decoration:none'>All</a>";

    $catStmt = $pdo->query("SELECT name FROM categories");
    while ($c = $catStmt->fetch(PDO::FETCH_ASSOC)) {
        $catName = $c['name'];
        $activeClass = $activeCat === $catName ? 'active' : '';
        echo "<a href='index.php?category=" . urlencode($catName) . $searchParam . "' class='chip {$activeClass}' style='text-decoration:none'>{$catName}</a>";
    }
    ?>
  </div>
  
  <div class="grid g3" id="menu">
    <?php
    // Dynamically build the query based on selected category and search terms
    $query = "SELECT p.*, 
              (SELECT MIN(FLOOR(i.stock / r.qty)) 
               FROM recipes r 
               JOIN ingredients i ON r.ingredient_id = i.id 
               WHERE r.product_id = p.id) as max_servings 
              FROM products p WHERE p.active = 1";
    
    $params = [];
    
    if (isset($_GET['category']) && $_GET['category'] !== 'All') {
        $query .= " AND p.category = ?";
        $params[] = $_GET['category'];
    }
    
    if (isset($_GET['search']) && trim($_GET['search']) !== '') {
        $search = '%' . trim($_GET['search']) . '%';
        $query .= " AND (p.name LIKE ? OR p.desc LIKE ?)";
        $params[] = $search;
        $params[] = $search;
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($products) > 0) {
        foreach ($products as $p) {
            $words = explode(' ', $p['name']);
            $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
            
            // Treat unmapped recipes as infinite (99) like the original JS logic
            $avail = $p['max_servings'] !== null ? (int)$p['max_servings'] : 99; 
            $stockBadge = $avail > 0 ? '<span class="badge b-ready">In stock</span>' : '<span class="badge b-cancel">Out of stock</span>';
            $btnState = $avail > 0 ? '' : 'disabled';
            
            echo "<article class='prod'>
              <div class='thumb'>{$initials}</div>
              <div class='body'>
                <div class='spread'><strong>" . htmlspecialchars($p['name']) . "</strong><span class='price'>₱" . number_format($p['price'], 2) . "</span></div>
                <small>" . htmlspecialchars($p['desc']) . "</small>
                <div style='margin-top:auto;padding-top:.6rem' class='spread'>
                  {$stockBadge}
                  <button class='btn sm' data-add='{$p['id']}' {$btnState}>Add to cart</button>
                </div>
              </div></article>";
        }
    } else {
        echo '<div class="empty">No items match your search.</div>';
    }
    ?>
  </div>
</main>
<footer class="site-foot">CrispyWraps — front-end demo. Data is saved in this browser only.</footer>

<script src="js/store.js"></script>
<script src="js/ui.js"></script>
<script>
// Keep existing navigation mounting
mountCustomerNav("index.php");

// Sync the PHP-loaded products into the local JS memory so the existing cart logic still works
const loadedProducts = <?php 
    $jsonProds = array_map(function($p) {
        return ['id' => $p['id'], 'name' => $p['name'], 'price' => (float)$p['price'], 'category' => $p['category']];
    }, count($products) > 0 ? $products : []);
    echo json_encode($jsonProds); 
?>;

const d = db();
d.products = loadedProducts; // Override local products with database products
save();

// Re-attach the add to cart functionality
document.querySelectorAll("[data-add]").forEach(b => b.onclick = () => {
    addToCart(b.dataset.add, 1);
    const pill = document.querySelector("#cart-pill");
    if(pill) pill.textContent = cartCount();
    toast(d.products.find(x => x.id === b.dataset.add).name + " added to cart");
});
</script>
</body>
</html>