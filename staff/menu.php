<?php
include '../config/db.php';
if (!isset($_SESSION['staff'])) header("Location: ../login.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_category'])) {
        $cat = trim($_POST['category_name']);
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
        $stmt->execute([$cat]);
    }
    
    if (isset($_POST['delete_category'])) {
        $cat = trim($_POST['category_name']);
        $stmt = $pdo->prepare("DELETE FROM categories WHERE name = ?");
        $stmt->execute([$cat]);
    }

    if (isset($_POST['delete_product'])) {
        $delId = $_POST['product_id'];
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$delId]);
        // Clean up linked recipes automatically
        $pdo->prepare("DELETE FROM recipes WHERE product_id = ?")->execute([$delId]);
    }

    if (isset($_POST['add_product'])) {
        $id = uniqid('prd-');
        $imagePath = null;
        
        // Process image upload
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('img_') . '.' . $ext;
            $uploadDir = '../uploads/';
            
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadDir . $fileName)) {
                $imagePath = 'uploads/' . $fileName;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO products (id, name, category, price, `desc`, active, image) VALUES (?, ?, ?, ?, ?, 1, ?)");
        $stmt->execute([$id, $_POST['name'], $_POST['category'], $_POST['price'], $_POST['desc'], $imagePath]);
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
<link rel="stylesheet" href="/css/styles.css" />
</head>
<body>
<div class="staff-layout">
  <?php include '../includes/staff-sidebar.php'; ?>
  <main class="staff-main">
    <div class="page-head"><h1>Menu Configuration</h1></div>

    <div class="grid g2">
      <div class="card">
        <h2>Add product</h2>
        <form method="POST" enctype="multipart/form-data">
          <div class="field"><label>Name</label><input name="name" required /></div>
          <div class="row">
            <div class="field grow"><label>Category</label>
              <select name="category">
                <?php
                $cats = $pdo->query("SELECT name FROM categories");
                while ($c = $cats->fetch()) echo "<option value='" . htmlspecialchars($c['name']) . "'>{$c['name']}</option>";
                ?>
              </select>
            </div>
            <div class="field grow"><label>Price (₱)</label><input name="price" type="number" min="0" value="129" required /></div>
          </div>
          <div class="field"><label>Description</label><input name="desc" required /></div>
          <div class="field"><label>Product Image (Optional)</label><input type="file" name="product_image" accept="image/*" /></div>
          <button type="submit" name="add_product" class="btn">Add product</button>
        </form>
      </div>
      
      <div class="card">
        <h2>Categories</h2>
        <div id="cats" class="chips" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
          <?php
          $cats = $pdo->query("SELECT name FROM categories");
          while ($c = $cats->fetch()):
          ?>
            <div class="chip" style="display: flex; align-items: center; gap: 0.4rem; padding-right: 0.4rem;">
              <span><?= htmlspecialchars($c['name']) ?></span>
              <form method="POST" style="margin: 0;" onsubmit="return confirm('Delete the category <?= htmlspecialchars($c['name']) ?>?');">
                <input type="hidden" name="category_name" value="<?= htmlspecialchars($c['name']) ?>">
                <button type="submit" name="delete_category" class="btn ghost sm" style="padding: 0.1rem 0.4rem; border: none; font-size: 0.75rem; min-width: auto;">✕</button>
              </form>
            </div>
          <?php endwhile; ?>
        </div>
        <form method="POST" class="row">
          <input name="category_name" placeholder="New category" required />
          <button type="submit" name="add_category" class="btn sm">Add</button>
        </form>
      </div>
    </div>

    <div class="card" style="margin-top: 1rem;">
      <h2>Manage Products</h2>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Product Name</th><th>Category</th><th>Price</th><th class="right">Action</th></tr></thead>
          <tbody>
            <?php
            $prods = $pdo->query("SELECT * FROM products ORDER BY category ASC, name ASC");
            while ($p = $prods->fetch()):
            ?>
            <tr>
              <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
              <td><?= htmlspecialchars($p['category']) ?></td>
              <td>₱<?= number_format($p['price'], 2) ?></td>
              <td class="right">
                <form method="POST" style="margin:0" onsubmit="return confirm('Are you sure you want to delete <?= htmlspecialchars($p['name']) ?>?');">
                  <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                  <button type="submit" name="delete_product" class="btn ghost sm">Delete</button>
                </form>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>
<script src="../js/ui.js"></script>
</body>
</html>