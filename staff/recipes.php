<?php
include '../config/db.php';
if (!isset($_SESSION['staff'])) { header("Location: ../login.php"); exit; }

// Handle Recipe Mapping actions[cite: 13]
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $pid = $_POST['product_id'];
        
        if ($_POST['action'] === 'add') {
            $iid = $_POST['ingredient_id'];
            $qty = (int)$_POST['qty'];
            // Insert or update if exists
            $stmt = $pdo->prepare("INSERT INTO recipes (product_id, ingredient_id, qty) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE qty = ?");
            $stmt->execute([$pid, $iid, $qty, $qty]);
        } 
        elseif ($_POST['action'] === 'remove') {
            $iid = $_POST['ingredient_id'];
            $pdo->prepare("DELETE FROM recipes WHERE product_id = ? AND ingredient_id = ?")->execute([$pid, $iid]);
        }
        elseif ($_POST['action'] === 'update_qty') {
            $iid = $_POST['ingredient_id'];
            $qty = (int)$_POST['qty'];
            $pdo->prepare("UPDATE recipes SET qty = ? WHERE product_id = ? AND ingredient_id = ?")->execute([$qty, $pid, $iid]);
        }
        header("Location: recipes.php");
        exit;
    }
}

$products = $pdo->query("SELECT * FROM products ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$ingredients = $pdo->query("SELECT id, name, unit, stock FROM ingredients ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Recipe Mapping — CrispyWraps Staff</title>
<link rel="stylesheet" href="/css/styles.css" />
</head>
<body>
<div class="staff-layout">
  <?php include '../includes/staff-sidebar.php'; ?>
  <main class="staff-main">
    <div class="page-head">
      <h1>Recipe Mapping</h1>
      <p class="muted">Link each menu item to its ingredients. Every placed order deducts these amounts from stock automatically.</p>
    </div>
    
    <div class="grid g2" id="cards">
      <?php foreach ($products as $p): 
          // Get mapped recipes for this product[cite: 13]
          $stmt = $pdo->prepare("SELECT r.ingredient_id, r.qty, i.name, i.unit, i.stock FROM recipes r JOIN ingredients i ON r.ingredient_id = i.id WHERE r.product_id = ?");
          $stmt->execute([$p['id']]);
          $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
          
          // Calculate max servings
          $maxServings = 9999;
          if (count($recipes) > 0) {
              foreach ($recipes as $r) {
                  $servings = floor($r['stock'] / max(1, $r['qty']));
                  if ($servings < $maxServings) $maxServings = $servings;
              }
          } else {
              $maxServings = 99; // Default if unmapped
          }
      ?>
      <div class="card">
        <div class="spread">
          <div><h2><?= htmlspecialchars($p['name']) ?></h2><small class="muted"><?= htmlspecialchars($p['category']) ?></small></div>
          <span class="badge <?= $maxServings > 0 ? "b-ready" : "b-cancel" ?>"><?= $maxServings ?> servings possible</span>
        </div>
        
        <table style="margin-top:.6rem">
          <tbody>
            <?php if (count($recipes) > 0): ?>
              <?php foreach ($recipes as $r): ?>
                <tr>
                  <td><?= htmlspecialchars($r['name']) ?></td>
                  <td style="width:110px">
                    <form method="POST" style="margin:0">
                      <input type="hidden" name="action" value="update_qty">
                      <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                      <input type="hidden" name="ingredient_id" value="<?= $r['ingredient_id'] ?>">
                      <input type="number" name="qty" min="1" value="<?= $r['qty'] ?>" onchange="this.form.submit()" style="width:100%" />
                    </form>
                  </td>
                  <td style="width:40px"><small><?= $r['unit'] ?></small></td>
                  <td class="right">
                    <form method="POST" style="margin:0">
                      <input type="hidden" name="action" value="remove">
                      <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                      <input type="hidden" name="ingredient_id" value="<?= $r['ingredient_id'] ?>">
                      <button type="submit" class="btn ghost sm">✕</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="4"><small class="muted">No ingredients mapped yet.</small></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
        
        <form method="POST" class="row" style="margin-top:.6rem">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
          <select name="ingredient_id" style="flex:1">
            <?php foreach ($ingredients as $i) echo "<option value='{$i['id']}'>{$i['name']}</option>"; ?>
          </select>
          <input name="qty" type="number" min="1" value="50" style="width:90px" required />
          <button type="submit" class="btn sm">Add</button>
        </form>
      </div>
      <?php endforeach; ?>
    </div>
  </main>
</div>
<script src="../js/ui.js"></script>
<script>mountStaffShell("recipes.php");</script>
</body>
</html>