<?php
// ❌ DO NOT call session_start() here
require_once __DIR__ . '/../../models/helpers/auth.php';

// Protect admin page
if (
    !isset($_SESSION['logged_in']) ||
    ($_SESSION['role'] ?? '') !== 'admin'
) {
    header('Location: index.php?url=Auth/login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>

<style>
:root{
  --text:#eaf0ff;
  --muted:#9aa8c7;
  --accent:#4f7cff;
  --green:#22c55e;
  --orange:#f59e0b;
  --purple:#a78bfa;
  --border:rgba(255,255,255,.15);
}
*{box-sizing:border-box}
html,body{height:100%}

body{
  margin:0;
  font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;
  background:
    radial-gradient(1000px 600px at 20% 20%, rgba(79,124,255,.45), transparent 60%),
    radial-gradient(800px 500px at 80% 30%, rgba(167,139,250,.35), transparent 55%),
    linear-gradient(135deg, #050b18, #0b1635, #060f22);
  color:var(--text);
  display:flex;
  flex-direction:column;
}

/* HEADER */
.topbar{
  padding:26px 40px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  border-bottom:1px solid var(--border);
  background: rgba(10,16,30,.45);
  backdrop-filter: blur(10px);
}
.brand h1{
  margin:0;
  font-size:34px;
  font-weight:1000;
  background: linear-gradient(90deg,#60a5fa,#a78bfa,#34d399);
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
}
.brand p{
  margin:6px 0 0;
  color:var(--muted);
  font-size:15px;
  font-weight:600;
}

.logout{
  padding:12px 24px;
  border-radius:16px;
  background:#ef4444;
  color:#fff;
  text-decoration:none;
  font-weight:900;
}

/* CENTER */
.center{
  flex:1;
  display:flex;
  justify-content:center;
  align-items:center;
  padding:30px;
}

/* FEATURE BOX */
.feature-box{
  background: rgba(15,25,45,.65);
  border:1px solid var(--border);
  border-radius:28px;
  padding:60px 70px;
  backdrop-filter: blur(12px);
}

.features{
  display:grid;
  grid-template-columns: repeat(3, 1fr);
  gap:50px;
}

/* FEATURE CARD */
.feature{
  text-decoration:none;
  color:var(--text);
  background: rgba(10,16,30,.65);
  border-radius:26px;
  padding:55px;
  text-align:center;
  min-width:260px;
  transition:.3s;
}
.feature:hover{
  transform:translateY(-10px);
  box-shadow:0 22px 55px rgba(0,0,0,.6);
}

.circle{
  width:96px;
  height:96px;
  margin:0 auto;
  border-radius:50%;
  display:flex;
  align-items:center;
  justify-content:center;
  font-weight:1000;
  font-size:28px;
  color:#06102a;
}
.green{background:var(--green)}
.orange{background:var(--orange)}
.purple{background:var(--purple)}

.feature span{
  display:block;
  margin-top:24px;
  font-size:22px;
  font-weight:900;
}

/* FOOTER */
footer{
  text-align:center;
  padding:16px;
  border-top:1px solid var(--border);
  background: rgba(10,16,30,.45);
  color:var(--muted);
  font-size:13px;
}

/* Responsive */
@media (max-width: 768px){
  .features{grid-template-columns:1fr;}
  .feature-box{padding:35px;}
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="topbar">
  <div class="brand">
    <h1>Admin Dashboard</h1>
    <p>Digital Payment and Security System</p>
  </div>
  <!-- ✅ Correct logout -->
  <a href="index.php?url=Auth/logout" class="logout">Logout</a>
</div>

<!-- CENTER -->
<div class="center">
  <div class="feature-box">
    <div class="features">

      <a href="index.php?url=Admin/manageRoles" class="feature">
        <div class="circle green">MR</div>
        <span>Manage Roles</span>
      </a>

      <a href="index.php?url=Security/termsConditions" class="feature">
        <div class="circle purple">TC</div>
        <span>Terms & Conditions</span>
      </a>

      <a href="index.php?url=Security/loanStatus" class="feature">
        <div class="circle orange">LS</div>
        <span>View Loan Status</span>
      </a>

    </div>
  </div>
</div>

<footer>© Digital Payment and Security System 2026</footer>

</body>
</html>
