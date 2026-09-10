<?php
include '../config/db.php';

// Strict Admin-only access check
if (!isset($_SESSION['staff']) || $_SESSION['staff']['role'] !== 'Admin') {
    header("Location: dashboard.php");
    exit;
}

$errorMsg = '';
$successMsg = '';

// Handle Account Creation & Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $pass = $_POST['password'];
        $role = $_POST['role'];
        
        // Prevent duplicate emails
        $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errorMsg = "That email is already registered to a staff member.";
        } else {
            $id = 'usr-' . substr(md5(uniqid()), 0, 6);
            $pdo->prepare("INSERT INTO users (id, name, email, password, role) VALUES (?, ?, ?, ?, ?)")->execute([$id, $name, $email, $pass, $role]);
            $successMsg = "Account for $name successfully created.";
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $delId = $_POST['id'];
        // Prevent the Admin from accidentally deleting themselves
        if ($delId !== $_SESSION['staff']['id']) { 
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$delId]);
            $successMsg = "Staff account deleted.";
        } else {
            $errorMsg = "You cannot delete your own active session.";
        }
    }
}

// Fetch all staff and admins
$users = $pdo->query("SELECT * FROM users ORDER BY role ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Accounts — CrispyWraps Admin</title>
<link rel="stylesheet" href="/css/styles.css" />
</head>
<body>
<div class="staff-layout">
  <?php include '../includes/staff-sidebar.php'; ?>
  
  <main class="staff-main">
    <div class="page-head">
      <h1>Manage Accounts</h1>
      <p class="muted">Create and manage internal Staff and Admin access.</p>
    </div>

    <?php if ($errorMsg): ?>
      <div class="alert bad"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>
    <?php if ($successMsg): ?>
      <div class="alert ok"><?= htmlspecialchars($successMsg) ?></div>
    <?php endif; ?>

    <div class="grid g2">
      <div class="card">
        <h2>Register New Staff</h2>
        <form method="POST">
          <input type="hidden" name="action" value="create">
          <div class="field">
            <label>Full Name</label>
            <input name="name" required />
          </div>
          <div class="field">
            <label>Email Address</label>
            <input name="email" type="email" required />
          </div>
          <div class="row">
            <div class="field grow">
              <label>Password</label>
              <input name="password" type="text" required />
            </div>
            <div class="field grow">
              <label>Account Role</label>
              <select name="role">
                <option value="Staff">Staff</option>
                <option value="Admin">Admin</option>
              </select>
            </div>
          </div>
          <button type="submit" class="btn">Create Account</button>
        </form>
      </div>

      <div class="card">
        <h2>Internal Directory</h2>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>Name</th><th>Role</th><th class="right">Action</th></tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($u['name']) ?></strong><br>
                    <small><?= htmlspecialchars($u['email']) ?></small>
                  </td>
                  <td>
                    <span class="badge <?= $u['role'] === 'Admin' ? 'b-new' : 'b-done' ?>">
                      <?= htmlspecialchars($u['role']) ?>
                    </span>
                  </td>
                  <td class="right">
                    <?php if ($u['id'] !== $_SESSION['staff']['id']): ?>
                      <form method="POST" style="margin:0" onsubmit="return confirm('Are you sure you want to revoke this account?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn ghost sm">Revoke</button>
                      </form>
                    <?php else: ?>
                      <span class="muted" style="font-size: 0.8rem;">(You)</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>
<script src="../js/ui.js"></script>
</body>
</html>