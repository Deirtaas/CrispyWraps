<?php
include 'config/db.php';

if (isset($_SESSION['staff'])) {
    header("Location: staff/dashboard.php");
    exit;
}
if (isset($_SESSION['customer'])) {
    header("Location: menu.php");
    exit;
}

$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? '';

    if ($action === 'login') {
        $email = trim($_POST['email']);
        $pass  = $_POST['password'];

        // ---- Staff / Admin ----
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $staff = $stmt->fetch();

        if ($staff && verify_and_maybe_rehash($pdo, 'users', $staff, $pass)) {
            $_SESSION['staff'] = [
                'id'   => $staff['id'],
                'name' => $staff['name'],
                'role' => $staff['role'],
            ];
            header("Location: staff/dashboard.php");
            exit;
        }

        // ---- Customer ----
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        if ($customer && verify_and_maybe_rehash($pdo, 'customers', $customer, $pass)) {
            $_SESSION['customer'] = [
                'id'    => $customer['id'],
                'name'  => $customer['name'],
                'email' => $customer['email'],
            ];
            header("Location: menu.php");
            exit;
        }

        $errorMsg = "Wrong email or password";

    } elseif ($action === 'register') {
        $name  = trim($_POST['name']);
        $email = trim($_POST['email']);
        $pass  = $_POST['password'];

        $uCheck = $pdo->prepare("SELECT email FROM users WHERE email = ?");
        $uCheck->execute([$email]);
        $cCheck = $pdo->prepare("SELECT email FROM customers WHERE email = ?");
        $cCheck->execute([$email]);

        if ($uCheck->fetch() || $cCheck->fetch()) {
            $errorMsg = "That email is already registered";
        } else {
            $id   = 'cus-' . substr(md5(uniqid('', true)), 0, 6);
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO customers (id, name, email, password) VALUES (?, ?, ?, ?)")
                ->execute([$id, $name, $email, $hash]);

            $_SESSION['customer'] = ['id' => $id, 'name' => $name, 'email' => $email];
            header("Location: menu.php");
            exit;
        }
    }
}

/**
 * Verifies a password. If the stored value is legacy plain text
 * (not a valid hash), it rehashes on success and updates the row.
 */
function verify_and_maybe_rehash(PDO $pdo, string $table, array $row, string $plain): bool {
    $stored = $row['password'];

    // Modern bcrypt/argon hash?
    if (password_verify($plain, $stored)) {
        return true;
    }

    // Legacy plain text fallback
    if (strlen($stored) < 60 && hash_equals($stored, $plain)) {
        $hash = password_hash($plain, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE {$table} SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $row['id']]);
        return true;
    }

    return false;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Sign In — CrispyWraps</title>
<link rel="stylesheet" href="css/styles.css" />
</head>
<body>
<main class="auth-page">
  <div class="card auth-card">
    <a class="brand" href="index.php" style="margin-bottom:1rem">
      <span class="brand-mark">CW</span>
      <span><strong>CrispyWraps</strong><small>One sign in for everyone</small></span>
    </a>

    <div class="tabs">
      <button class="active" data-t="login">Log in</button>
      <button data-t="register">Register</button>
    </div>

    <?php if ($errorMsg): ?>
      <div class="alert bad" style="margin-bottom:1rem;"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <form id="login" method="POST" action="login.php">
      <?= csrf_field() ?>
      <input type="hidden" name="form_action" value="login" />
      <div class="field"><label>Email</label><input name="email" type="email" placeholder="you@example.com" required /></div>
      <div class="field"><label>Password</label><input name="password" type="password" placeholder="••••••" required /></div>
      <button type="submit" class="btn" style="width:100%;justify-content:center">Log in</button>
    </form>

    <form id="register" method="POST" action="login.php" hidden>
      <?= csrf_field() ?>
      <input type="hidden" name="form_action" value="register" />
      <div class="field"><label>Full name</label><input name="name" required /></div>
      <div class="field"><label>Email</label><input name="email" type="email" required /></div>
      <div class="field"><label>Password</label><input name="password" type="password" required /></div>
      <button type="submit" class="btn" style="width:100%;justify-content:center">Create account</button>
    </form>

    <p class="center" style="margin-top:1rem"><a class="muted" href="index.php">← Back to home</a></p>
  </div>
</main>

<script>
document.querySelectorAll(".tabs button").forEach(b => b.onclick = () => {
  document.querySelectorAll(".tabs button").forEach(x => x.classList.toggle("active", x === b));
  document.getElementById("login").hidden = b.dataset.t !== "login";
  document.getElementById("register").hidden = b.dataset.t !== "register";
});
</script>
</body>
</html>