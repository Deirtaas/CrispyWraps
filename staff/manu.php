<?php
include '../config/db.php';
if (!isset($_SESSION['staff'])) header("Location: ../login.php");

// Handle POST actions for adding categories and products
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_category'])) {
        $cat = trim($_POST['category_name']);
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
        $stmt->execute([$cat]);
    }
    if (isset($_POST['add_product'])) {
        $id = uniqid('prd-');
        $stmt = $pdo->prepare("INSERT INTO products (id, name, category, price, desc, active) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$id, $_POST['name'], $_POST['category'], $_POST['price'], $_POST['desc']]);
    }
    header("Location: menu.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Menu Configuration — CrispyWraps Staff</title>
<link rel="stylesheet" href="../css/styles.css" />
</head>
<body>
<div class="staff-layout">
  <div id="staff-shell"></div>
  <main class="staff-main">
    <div class="page-head"><h1>Menu Configuration</h1></div>

    <div class="grid g2">
      <div class="card">
        <h2>Add product</h2>
        <form method="POST">
          <div class="field"><label>Name</label><input name="name" required /></div>
          <div class="row">
            <div class="field grow"><label>Category</label>
              <select name="category">
                <?php
                $cats = $pdo->query("SELECT name FROM categories");
                while ($c = $cats->fetch()) echo "<option>{$c['name']}</option>";
                ?>
              </select>
            </div>
            <div class="field grow"><label>Price (₱)</label><input name="price" type="number" min="0" value="129" required /></div>
          </div>
          <div class="field"><label>Description</label><input name="desc" /></div>
          <button type="submit" name="add_product" class="btn">Add product</button>
        </form>
      </div>
      
      <div class="card">
        <h2>Categories</h2>
        <div id="cats" class="chips">
          <?php
          $cats = $pdo->query("SELECT name FROM categories");
          while ($c = $cats->fetch()) echo "<span class='chip'>{$c['name']}</span>";
          ?>
        </div>
        <form method="POST" class="row">
          <input name="category_name" placeholder="New category" required />
          <button type="submit" name="add_category" class="btn sm">Add</button>
        </form>
      </div>
    </div>
  </main>
</div>
<script src="../js/ui.js"></script>
<script>mountStaffShell("menu.php");</script>
</body>
</html>