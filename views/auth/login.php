<?php require __DIR__ . '/../_guard.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Digital Payment System</title>
<style>
:root{--text:#eaf0ff;--muted:#9aa8c7;--accent:#4f7cff;--border:rgba(255,255,255,.15);--error:#ef4444;}
*{box-sizing:border-box}
html,body{height:100%}
body{
  margin:0;
  font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;
  background:linear-gradient(135deg,#060b18,#0b1635,#071022);
  color:var(--text);
  display:flex;
  align-items:center;
  justify-content:center;
  padding:24px;
}
.card{
  width:min(520px,94vw);
  border:1px solid var(--border);
  border-radius:22px;
  background:rgba(15,25,45,.65);
  backdrop-filter:blur(12px);
  padding:34px 30px;
  box-shadow:0 20px 50px rgba(0,0,0,.55);
}
.title{margin:0 0 6px;text-align:center;font-size:28px;font-weight:900;letter-spacing:.5px;}
.subtitle{margin:0 0 18px;text-align:center;color:var(--muted);font-size:13px;font-weight:700;}
.alert{margin-bottom:14px;padding:12px 14px;border-radius:14px;border:1px solid rgba(239,68,68,.35);background:rgba(239,68,68,.12);color:#ffd0d0;font-weight:800;text-align:center;}
.ok{margin-bottom:14px;padding:12px 14px;border-radius:14px;border:1px solid rgba(34,197,94,.35);background:rgba(34,197,94,.12);color:#d1fae5;font-weight:800;text-align:center;}
.role-box{display:flex;gap:10px;justify-content:center;margin:8px 0 18px;flex-wrap:wrap;}
.role-box label{cursor:pointer;user-select:none;}
.role-box input{display:none;}
.role-box span{display:inline-block;padding:10px 12px;border-radius:14px;border:1px solid rgba(255,255,255,.16);background:rgba(10,16,30,.45);font-weight:900;font-size:13px;color:var(--text);transition:.2s;}
.role-box input:checked+span{background:linear-gradient(135deg,var(--accent),#8aa6ff);color:#06102a;border-color:transparent;}
.field{margin-bottom:14px;}
.field label{display:block;margin-bottom:6px;font-size:13px;font-weight:800;color:var(--muted);}
.field input{width:100%;padding:12px 14px;border-radius:14px;border:1px solid rgba(255,255,255,.18);background:rgba(10,16,30,.55);color:var(--text);outline:none;font-size:14px;}
.field input:focus{border-color:rgba(79,124,255,.7);}
.field-error{margin-top:6px;font-size:12px;color:#fecaca;font-weight:700;}
.btn{width:100%;padding:12px 16px;border:none;border-radius:14px;cursor:pointer;font-weight:900;font-size:15px;background:linear-gradient(135deg,var(--accent),#8aa6ff);color:#06102a;}
.btn:hover{opacity:.95}
.links{margin-top:18px;text-align:center;font-size:13px;color:var(--muted);font-weight:700;}
.links a{color:#93c5fd;text-decoration:none;font-weight:900;}
.links a:hover{text-decoration:underline;}
</style>
</head>
<body>
<div class="card">
  <h1 class="title">DPS Login</h1>
  <p class="subtitle">Use the same login page for Customer, Agent, or Admin</p>

  <?php if (!empty($success)): ?>
    <div class="ok"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="alert"><?= htmlspecialchars(is_array($errors) ? implode(' ', $errors) : (string)$errors) ?></div>
  <?php endif; ?>

  <form method="post" action="index.php?url=Auth/loginSubmit" novalidate>
    <div class="role-box">
      <?php $roleVal = htmlspecialchars($_POST['role'] ?? 'customer'); ?>
      <?php foreach (['customer'=>'Customer','agent'=>'Agent','admin'=>'Admin'] as $r=>$label): ?>
        <label>
          <input type="radio" name="role" value="<?= $r ?>" <?= ($roleVal===$r?'checked':'') ?>>
          <span><?= $label ?></span>
        </label>
      <?php endforeach; ?>
    </div>
    <div class="field-error"><?= htmlspecialchars($fieldErrors['role'] ?? '') ?></div>

    <div class="field">
      <label for="identifier">Email or User ID</label>
      <input id="identifier" name="identifier" value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>" placeholder="e.g. customer1@example.com or admin" autocomplete="username">
      <div class="field-error"><?= htmlspecialchars($fieldErrors['identifier'] ?? '') ?></div>
    </div>

    <div class="field">
      <label for="password">Password</label>
      <input id="password" type="password" name="password" placeholder="Your password" autocomplete="current-password">
      <div class="field-error"><?= htmlspecialchars($fieldErrors['password'] ?? '') ?></div>
    </div>

    <button class="btn" type="submit">Login</button>
  </form>

  <div class="links">
    <a href="index.php?url=Security/signup">Create account</a>
    &nbsp;·&nbsp;
    <a href="index.php?url=Security/forgot">Forgot password</a>
  </div>
</div>
</body>
</html>
