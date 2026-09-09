<?php
include 'config/db.php';

$customerName = isset($_SESSION['customer']) ? $_SESSION['customer']['name'] : '';

// Handle feedback submission[cite: 4]
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $name = trim($_POST['cname']);
    $prodId = $_POST['prod'];
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    if ($name && $comment) {
        $id = 'fb-' . substr(md5(uniqid()), 0, 6);
        $stmt = $pdo->prepare("INSERT INTO feedback (id, customer, product_id, rating, comment, date) VALUES (?, ?, ?, ?, ?, CURDATE())");
        $stmt->execute([$id, $name, $prodId, $rating, $comment]);
        header("Location: feedback.php?success=1");
        exit;
    }
}

// Fetch products for the dropdown[cite: 4, 10]
$products = $pdo->query("SELECT id, name FROM products WHERE active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all feedback[cite: 4, 10]
$feedbacks = $pdo->query("SELECT f.*, p.name as prod_name FROM feedback f LEFT JOIN products p ON f.product_id = p.id ORDER BY f.date DESC, f.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Feedback — CrispyWraps</title>
<link rel="stylesheet" href="/css/styles.css" />
</head>
<body>
<?php include 'includes/header.php'; ?>
<main class="wrap">
  <h1>Rate a product &amp; leave feedback</h1>
  
  <?php if (isset($_GET['success'])): ?>
    <div class="alert ok">Thanks for your feedback!</div>
  <?php endif; ?>

  <div class="grid g2" style="margin-top:1rem">
    <div class="card">
      <form method="POST">
        <div class="field">
          <label>Your name</label>
          <input name="cname" value="<?= htmlspecialchars($customerName) ?>" placeholder="Your name" required />
        </div>
        <div class="field">
          <label>Product</label>
          <select name="prod" required>
            <?php foreach ($products as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Rating</label>
          <select name="rating">
            <option value="5">★★★★★ Excellent</option>
            <option value="4">★★★★☆ Good</option>
            <option value="3">★★★☆☆ Okay</option>
            <option value="2">★★☆☆☆ Poor</option>
            <option value="1">★☆☆☆☆ Bad</option>
          </select>
        </div>
        <div class="field">
          <label>Comment</label>
          <textarea name="comment" rows="4" placeholder="Tell us about your experience" required></textarea>
        </div>
        <button type="submit" name="submit_feedback" class="btn">Submit feedback</button>
      </form>
    </div>
    
    <div class="card">
      <h2>What others said</h2>
      <div id="feed">
        <?php if (count($feedbacks) > 0): ?>
          <?php foreach ($feedbacks as $f): 
            $stars = str_repeat('★', $f['rating']) . str_repeat('☆', 5 - $f['rating']);
          ?>
            <div style="border-bottom:1px solid #f3ede4;padding:.7rem 0">
              <div class="spread"><strong><?= htmlspecialchars($f['customer']) ?></strong><span class="stars" style="color:var(--amber);letter-spacing:.08em;"><?= $stars ?></span></div>
              <small class="muted"><?= htmlspecialchars($f['prod_name'] ?? 'Product') ?> · <?= $f['date'] ?></small>
              <p style="margin:.35rem 0 0"><?= htmlspecialchars($f['comment']) ?></p>
              <?php if (!empty($f['reply'])): ?>
                <div class="alert ok" style="margin-top:.5rem">Staff reply: <?= htmlspecialchars($f['reply']) ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty">No feedback yet.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
<script src="js/ui.js"></script>
</body>
</html>