<?php
session_start();
require_once __DIR__ . '/../../models/helpers/auth.php';

$error = "";
$success = "";

$uid = trim($_GET['uid'] ?? '');
$token = trim($_GET['token'] ?? '');

if ($uid === '' || $token === '') {
    $error = "Invalid reset link.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === "") {
    $newPass = $_POST['password'] ?? '';
    $cpass = $_POST['confirm_password'] ?? '';

    if ($newPass === '' || $cpass === '') {
        $error = "Please fill in all fields.";
    } elseif ($newPass !== $cpass) {
        $error = "Password and Confirm Password do not match.";
    } elseif (strlen($newPass) < 4) {
        $error = "Password must be at least 4 characters.";
    } else {
        $user = fetch_user_by_user_id($pdo, $uid);
        if (!$user) {
            $error = "User not found.";
        } else {
            $tokenHash = hash('sha256', $token);
            $stmt = $pdo->prepare(
                'SELECT * FROM password_resets WHERE user_id = ? AND token_hash = ? AND used_at IS NULL AND expires_at > NOW() ORDER BY id DESC LIMIT 1'
            );
            $stmt->execute([(int)$user['id'], $tokenHash]);
            $row = $stmt->fetch();

            if (!$row) {
                $error = "Reset token is invalid or expired.";
            } else {
                // Plain password (no hashing)
                $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')
                    ->execute([$newPass, (int)$user['id']]);

                $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')
                    ->execute([(int)$row['id']]);

                $success = "Password reset successful! You can now login.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>
  <style>
    :root{--text:#eaf0ff;--muted:#9aa8c7;--accent:#4f7cff;--red:#ef4444;--green:#22c55e;--border:rgba(255,255,255,.15);}
    *{box-sizing:border-box} html,body{height:100%}
    body{margin:0;font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;background:
      radial-gradient(1200px 700px at 15% 20%, rgba(79,124,255,0.45), transparent 60%),
      radial-gradient(900px 600px at 85% 30%, rgba(56,189,248,0.35), transparent 55%),
      radial-gradient(700px 500px at 40% 90%, rgba(167,139,250,0.25), transparent 55%),
      linear-gradient(135deg, #060b18, #0b1635, #071022);
      color:var(--text);display:flex;flex-direction:column;}
    .container{flex:1;display:flex;justify-content:center;align-items:center;padding:24px;}
    .card{width:min(500px, 92vw);border:1px solid var(--border);border-radius:22px;background: rgba(15,25,45,.60);
      backdrop-filter: blur(12px);padding:34px 30px;box-shadow: 0 20px 50px rgba(0,0,0,.55);}
    .title{text-align:center;font-size:30px;font-weight:1000;margin:0 0 6px;letter-spacing:1px;
      background: linear-gradient(90deg, #60a5fa, #a78bfa, #34d399);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;}
    .subtitle{text-align:center;margin:0 0 18px;color:var(--muted);font-size:14px;font-weight:600;}
    .alert{margin-bottom:14px;padding:12px 14px;border-radius:14px;font-weight:800;text-align:center;}
    .alert-error{border:1px solid rgba(239,68,68,.35);background: rgba(239,68,68,.12);color:#ffd0d0;}
    .alert-success{border:1px solid rgba(34,197,94,.35);background: rgba(34,197,94,.12);color:#c9ffe0;}
    .field{margin-bottom:14px;}
    .field label{display:block;margin-bottom:6px;font-size:13px;font-weight:800;color:var(--muted);}
    .field input{width:100%;padding:12px 14px;border-radius:14px;border:1px solid rgba(255,255,255,.18);background: rgba(10,16,30,.55);color:var(--text);outline:none;font-size:14px;}
    .btn{width:100%;padding:12px 16px;border:none;border-radius:14px;cursor:pointer;font-weight:900;font-size:15px;background: linear-gradient(135deg, var(--accent), #8aa6ff);color:#06102a;}
    .links{margin-top:16px;text-align:center;font-size:13px;color:var(--muted);font-weight:700;}
    .links a{color:#93c5fd;text-decoration:none;font-weight:900;}
    .links a:hover{text-decoration:underline;}
    footer{text-align:center;padding:14px;border-top:1px solid var(--border);background: rgba(10,16,30,.35);color:var(--muted);font-size:13px;}
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <h1 class="title">Reset Password</h1>
      <p class="subtitle">User ID: <?php echo htmlspecialchars($uid ?: '-'); ?></p>

      <?php if ($error !== ""): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <?php if ($success !== ""): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>

      <?php if ($success === "" && $error === ""): ?>
        <form method="POST">
          <div class="field">
            <label>New Password:</label>
            <input type="password" name="password" placeholder="Enter new password" required>
          </div>
          <div class="field">
            <label>Confirm Password:</label>
            <input type="password" name="confirm_password" placeholder="Confirm new password" required>
          </div>
          <button class="btn" type="submit">Reset Password</button>
        </form>
      <?php endif; ?>

      <div class="links">
        <a href="index.php?url=Auth/login">Back to Login</a>
      </div>
    </div>
  </div>
  <footer>@Digital Payment and Security System_2026</footer>
</body>
</html>
