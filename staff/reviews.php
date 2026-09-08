<?php
include '../config/db.php';
if (!isset($_SESSION['staff'])) { header("Location: ../login.php"); exit; }

// Handle staff replies[cite: 14]
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_id'])) {
    $stmt = $pdo->prepare("UPDATE feedback SET reply = ? WHERE id = ?");
    $stmt->execute([trim($_POST['reply_text']), $_POST['reply_id']]);
    header("Location: reviews.php");
    exit;
}

$feedbacks = $pdo->query("SELECT f.*, p.name as prod_name FROM feedback f LEFT JOIN products p ON f.product_id = p.id ORDER BY f.date DESC, f.id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Calculate Stats[cite: 14]
$totalFeedback = count($feedbacks);
$avgRating = $totalFeedback > 0 ? number_format(array_sum(array_column($feedbacks, 'rating')) / $totalFeedback, 1) : "—";
$awaitingReply = count(array_filter($feedbacks, function($f) { return empty($f['reply']); }));
$fiveStars = count(array_filter($feedbacks, function($f) { return $f['rating'] == 5; }));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Customer Reviews — CrispyWraps Staff</title>
<link rel="stylesheet" href="../css/styles.css" />
</head>
<body>
<div class="staff-layout">
  <div id="staff-shell"></div>
  <main class="staff-main">
    <div class="page-head"><h1>Customer Reviews</h1><p class="muted">View feedback and respond to customers.</p></div>
    
    <div class="grid g4" id="stats">
      <div class="stat"><div class="k">Total feedback</div><div class="v"><?= $totalFeedback ?></div></div>
      <div class="stat"><div class="k">Average rating</div><div class="v"><?= $avgRating ?></div></div>
      <div class="stat"><div class="k">Awaiting reply</div><div class="v"><?= $awaitingReply ?></div></div>
      <div class="stat"><div class="k">5-star reviews</div><div class="v"><?= $fiveStars ?></div></div>
    </div>
    
    <div class="card" style="margin-top:1rem">
      <div id="list">
        <?php if ($totalFeedback > 0): ?>
          <?php foreach ($feedbacks as $f): 
            $stars = str_repeat('★', $f['rating']) . str_repeat('☆', 5 - $f['rating']);
          ?>
            <div style="border-bottom:1px solid #f3ede4;padding:.9rem 0">
              <div class="spread">
                <div><strong><?= htmlspecialchars($f['customer']) ?></strong> · <small class="muted"><?= htmlspecialchars($f['prod_name'] ?? 'Product') ?> · <?= $f['date'] ?></small></div>
                <span style="color:var(--amber);letter-spacing:.08em;"><?= $stars ?></span>
              </div>
              <p style="margin:.4rem 0"><?= htmlspecialchars($f['comment']) ?></p>
              <?php if (!empty($f['reply'])): ?>
                <div class="alert ok">Your reply: <?= htmlspecialchars($f['reply']) ?></div>
              <?php endif; ?>
              
              <form method="POST" class="row">
                <input type="hidden" name="reply_id" value="<?= $f['id'] ?>">
                <input name="reply_text" placeholder="Write a response…" value="<?= htmlspecialchars($f['reply'] ?? '') ?>" class="grow" required />
                <button type="submit" class="btn sm"><?= !empty($f['reply']) ? "Update reply" : "Respond" ?></button>
              </form>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty">No customer feedback yet.</div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
<script src="../js/ui.js"></script>
<script>mountStaffShell("reviews.php");</script>
</body>
</html>