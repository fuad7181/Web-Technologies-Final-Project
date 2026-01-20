<?php
// ❌ DO NOT call session_start() here
require_once __DIR__ . '/../../models/helpers/auth.php';

// Protect agent page
if (
    !isset($_SESSION['logged_in']) ||
    ($_SESSION['role'] ?? '') !== 'agent'
) {
    header('Location: index.php?url=Auth/login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Agent Dashboard</title>

<style>
:root{
  --text:#eaf0ff;
  --muted:#9aa8c7;
  --accent:#4f7cff;
  --green:#22c55e;
  --orange:#f59e0b;
  --border:rgba(255,255,255,.15);
}
*{box-sizing:border-box}
html, body{width:100%; height:100%}

body{
  margin:0;
  font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;
  background:
    linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)),
    url("/webtech/f/icons/agent_bg.jpg");
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  background-attachment: fixed;
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
.brand{display:flex; gap:18px; align-items:center;}
.brand-badge{
  width:60px; height:60px; border-radius:18px;
  background: linear-gradient(135deg,var(--accent),#8aa6ff);
  display:flex; align-items:center; justify-content:center;
  font-size:26px; font-weight:900; color:#06102a;
}
.brand-text h1{margin:0; font-size:30px;}
.brand-text h2{margin:6px 0 0; font-size:18px; font-weight:500; color:var(--muted);}

.logout{
  padding:12px 22px;
  border-radius:14px;
  background:#ef4444;
  color:#fff;
  text-decoration:none;
  font-weight:800;
}

/* CENTER */
.center{flex:1; display:flex; align-items:center; justify-content:center; padding:20px;}

/* FEATURE BOX */
.feature-box{
  border:1px solid var(--border);
  border-radius:26px;
  background: rgba(15,25,45,.60);
  backdrop-filter: blur(12px);
  padding:56px 70px;
}

/* TITLE */
.mid-title{
  text-align:center;
  font-size:34px;
  font-weight:1000;
  margin:0 0 28px;
  letter-spacing:1px;
  background: linear-gradient(90deg, #38bdf8, #a78bfa);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  position:relative;
}
.mid-title::after{
  content:'';
  display:block;
  width:120px;
  height:3px;
  margin:12px auto 0;
  border-radius:10px;
  background: linear-gradient(90deg, #38bdf8, #a78bfa);
  box-shadow:0 0 12px rgba(56,189,248,.55);
}

/* FEATURES */
.features{display:grid; grid-template-columns: repeat(3, 1fr); gap:46px;}

.feature{
  text-decoration:none;
  color:var(--text);
  background: rgba(10,16,30,.65);
  border-radius:24px;
  padding:50px;
  text-align:center;
  min-width:260px;
  transition:.3s;
}
.feature:hover{
  transform:translateY(-8px);
  box-shadow:0 20px 50px rgba(0,0,0,.6);
}

.circle{
  width:92px; height:92px; margin:0 auto;
  border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  font-weight:900; font-size:26px; color:#06102a;
}
.green{background:var(--green)}
.blue{background:var(--accent)}
.orange{background:var(--orange)}

.feature span{display:block; margin-top:22px; font-size:22px; font-weight:800;}

/* FOOTER */
footer{
  text-align:center;
  padding:16px;
  border-top:1px solid var(--border);
  background: rgba(10,16,30,.45);
  backdrop-filter: blur(10px);
  color:var(--muted);
  font-size:13px;
}

/* Responsive */
@media (max-width: 768px){
  .features{grid-template-columns:1fr;}
  .feature-box{padding:30px;}
  .mid-title{font-size:24px;}
  .brand-text h1{font-size:24px;}
  .brand-text h2{font-size:14px;}
}
</style>
</head>

<body>

<div class="topbar">
  <div class="brand">
    <div class="brand-badge">DP</div>
    <div class="brand-text">
      <h1>Agent Dashboard</h1>
      <h2>Digital Payment and Security System</h2>
    </div>
  </div>

  <!-- ✅ Correct logout -->
  <a href="index.php?url=Auth/logout" class="logout">Logout</a>
</div>

<div class="center">
  <div class="feature-box">
    <div class="mid-title">Agent Dashboard</div>

    <div class="features">
      <a href="index.php?url=Agent/userVerification" class="feature">
        <div class="circle green">UV</div>
        <span>User Verification</span>
      </a>

      <a href="index.php?url=Agent/cashIn" class="feature">
        <div class="circle blue">CI</div>
        <span>Cash In Request</span>
      </a>

      <a href="index.php?url=Agent/cashOut" class="feature">
        <div class="circle orange">CO</div>
        <span>Cash Out Request</span>
      </a>
    </div>
  </div>
</div>

<footer>© Digital Payment and Security System 2026</footer>

</body>
</html>
