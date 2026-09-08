<?php
include '../config/db.php';
if (!isset($_SESSION['staff'])) { header("Location: ../login.php"); exit; }

// Handle Inventory Form Actions[cite: 17]
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'movement') {
            $ingId = (int)$_POST['ingredient_id'];
            $qty = (int)$_POST['qty'];
            $type = $_POST['type'];
            $note = trim($_POST['note']) ?: ($type === 'IN' ? 'Stock in' : 'Stock out');
            
            if ($type === 'IN') {
                $pdo->prepare("UPDATE ingredients SET stock = stock + ? WHERE id = ?")->execute([$qty, $ingId]);
            } else {
                $pdo->prepare("UPDATE ingredients SET stock = GREATEST(0, stock - ?) WHERE id = ?")->execute([$qty, $ingId]);
            }
            $pdo->prepare("INSERT INTO stock_log (type, ingredient_id, qty, note) VALUES (?, ?, ?, ?)")->execute([$type, $ingId, $qty, $note]);
        }
        elseif ($action === 'waste') {
            $ingId = (int)$_POST['ingredient_id'];
            $qty = (int)$_POST['qty'];
            $reason = $_POST['reason'];
            $cost = (float)$_POST['cost'];
            
            $pdo->prepare("UPDATE ingredients SET stock = GREATEST(0, stock - ?) WHERE id = ?")->execute([$qty, $ingId]);
            $pdo->prepare("INSERT INTO waste (ingredient_id, qty, reason, cost) VALUES (?, ?, ?, ?)")->execute([$ingId, $qty, $reason, $cost]);
            $pdo->prepare("INSERT INTO stock_log (type, ingredient_id, qty, note) VALUES ('OUT', ?, ?, ?)")->execute([$ingId, $qty, "Waste — " . $reason]);
        }
        elseif ($action === 'add_ing') {
            $pdo->prepare("INSERT INTO ingredients (name, unit, stock, reorder, expiry) VALUES (?, ?, 0, 100, DATE_ADD(CURDATE(), INTERVAL 14 DAY))")
                ->execute([trim($_POST['name']), trim($_POST['unit'])]);
        }
        elseif ($action === 'edit_ing') {
            $pdo->prepare("UPDATE ingredients SET reorder = ?, expiry = ? WHERE id = ?")
                ->execute([$_POST['reorder'], $_POST['expiry'], $_POST['id']]);
        }
        elseif ($action === 'delete_ing') {
            $pdo->prepare("DELETE FROM ingredients WHERE id = ?")->execute([$_POST['id']]);
        }
        header("Location: inventory.php");
        exit;
    }
}
$ingredients = $pdo->query("SELECT * FROM ingredients ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Inventory — CrispyWraps Staff</title>
<link rel="stylesheet" href="../css/styles.css" />
</head>
<body>
<div class="staff-layout">
  <div id="staff-shell"></div>
  <main class="staff-main">
    <div class="page-head"><h1>Inventory</h1><p class="muted">Stock in, stock out, low stock alerts, expiration and waste.</p></div>

    <div id="alerts">
      <?php
      $alerts = false;
      foreach ($ingredients as $i) {
          if ($i['stock'] <= $i['reorder']) {
              echo "<div class='alert bad'>Low stock alert: <strong>{$i['name']}</strong> at {$i['stock']}{$i['unit']} (reorder level {$i['reorder']}{$i['unit']})</div>";
              $alerts = true;
          }
          if (strtotime($i['expiry']) <= strtotime('+5 days')) {
              echo "<div class='alert warn'>Expiration alert: <strong>{$i['name']}</strong> expires {$i['expiry']}</div>";
              $alerts = true;
          }
      }
      if (!$alerts) echo '<div class="alert ok">No low stock or expiration alerts right now.</div>';
      ?>
    </div>

    <div class="grid g2">
      <div class="card">
        <h2>Record stock movement</h2>
        <form method="POST">
          <input type="hidden" name="action" value="movement">
          <div class="field"><label>Ingredient</label>
            <select name="ingredient_id">
              <?php foreach ($ingredients as $i) echo "<option value='{$i['id']}'>{$i['name']} ({$i['stock']}{$i['unit']})</option>"; ?>
            </select>
          </div>
          <div class="row">
            <div class="field grow"><label>Type</label><select name="type"><option value="IN">Stock In</option><option value="OUT">Stock Out</option></select></div>
            <div class="field grow"><label>Quantity</label><input name="qty" type="number" min="1" value="100" required /></div>
          </div>
          <div class="field"><label>Note</label><input name="note" placeholder="Delivery, prep usage…" /></div>
          <button type="submit" class="btn">Record movement</button>
        </form>
      </div>

      <div class="card">
        <h2>Record expiration / waste</h2>
        <form method="POST">
          <input type="hidden" name="action" value="waste">
          <div class="field"><label>Ingredient</label>
            <select name="ingredient_id">
              <?php foreach ($ingredients as $i) echo "<option value='{$i['id']}'>{$i['name']} ({$i['stock']}{$i['unit']})</option>"; ?>
            </select>
          </div>
          <div class="row">
            <div class="field grow"><label>Quantity</label><input name="qty" type="number" min="1" value="50" required /></div>
            <div class="field grow"><label>Reason</label><select name="reason"><option>Expired</option><option>Spoiled</option><option>Spillage</option><option>Overcooked</option></select></div>
          </div>
          <div class="field"><label>Estimated cost (₱)</label><input name="cost" type="number" min="0" value="50" step="0.01" /></div>
          <button type="submit" class="btn danger">Record waste</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="spread"><h2>Ingredient stock levels</h2>
        <form method="POST" style="margin:0; display:flex; gap:.5rem;">
          <input type="hidden" name="action" value="add_ing">
          <input name="name" placeholder="New ingredient" required style="width:150px">
          <input name="unit" placeholder="Unit (g, pcs)" value="g" required style="width:100px">
          <button type="submit" class="btn ghost sm">Add</button>
        </form>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Ingredient</th><th>Stock</th><th>Level</th><th>Reorder at</th><th>Expiry</th><th class="right">Edit</th></tr></thead>
          <tbody>
            <?php foreach ($ingredients as $i): 
              $pct = min(100, round(($i['stock'] / max(1, $i['reorder'] * 2)) * 100));
              $low = $i['stock'] <= $i['reorder'];
              $bg = $low ? '#c8452f' : '#2f8f5b';
            ?>
            <tr>
              <td><strong><?= htmlspecialchars($i['name']) ?></strong></td>
              <td><?= $i['stock'] ?> <?= $i['unit'] ?> <?= $low ? '<span class="badge b-cancel">LOW</span>' : '' ?></td>
              <td style="min-width:120px"><div class="bar"><i style="width:<?= $pct ?>%;background:<?= $bg ?>"></i></div></td>
              <form method="POST" style="margin:0">
                <input type="hidden" name="action" value="edit_ing">
                <input type="hidden" name="id" value="<?= $i['id'] ?>">
                <td><input name="reorder" type="number" value="<?= $i['reorder'] ?>" onchange="this.form.submit()" style="width:90px" /></td>
                <td><input name="expiry" type="date" value="<?= $i['expiry'] ?>" onchange="this.form.submit()" style="width:150px" /></td>
              </form>
              <td class="right">
                <form method="POST" style="margin:0" onsubmit="return confirm('Delete this ingredient?');">
                  <input type="hidden" name="action" value="delete_ing">
                  <input type="hidden" name="id" value="<?= $i['id'] ?>">
                  <button type="submit" class="btn ghost sm">Delete</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
<script src="../js/ui.js"></script>
<script>mountStaffShell("inventory.php");</script>
</body>
</html>