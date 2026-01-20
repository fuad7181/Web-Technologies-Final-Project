<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php?url=Auth/login");
    exit;
}

/* ✅ Save helper */
function saveTermsToFile($arr){
    $file = __DIR__ . "/terms.json";
    file_put_contents($file, json_encode(array_values($arr), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/* ✅ Load from terms.json first (so admin sees latest) */
$file = __DIR__ . "/terms.json";
if (!isset($_SESSION['payment_terms'])) {
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data) && count($data)>0) {
            $_SESSION['payment_terms'] = $data;
        }
    }
}

/* ✅ Default Terms (if still empty) */
if (!isset($_SESSION['payment_terms']) || count($_SESSION['payment_terms']) === 0) {
    $_SESSION['payment_terms'] = [
        "Users must complete account verification (KYC) before accessing full payment services.",
        "Cash In and Cash Out transactions are subject to system verification and agent approval.",
        "Any suspicious transaction may be temporarily blocked for security review.",
        "A transaction fee may apply depending on the service type and amount.",
        "Users must ensure that the receiver’s User ID / account details are correct before sending money.",
        "Wrong transfer details may cause permanent loss of funds, and the system will not be responsible.",
        "All transactions are logged for audit and compliance purposes.",
        "Loan requests are approved/rejected based on eligibility rules defined by the admin.",
        "Users must keep their password confidential; sharing credentials is strictly prohibited.",
        "Accounts involved in fraud, scam, or illegal activities may be permanently suspended.",
        "Failed transactions may take time to reverse depending on the processing network.",
        "Admin reserves the right to update Terms & Conditions at any time without prior notice."
    ];
    saveTermsToFile($_SESSION['payment_terms']);
}

$success = "";
$error = "";

/* ✅ ADD new condition */
if (isset($_POST['add_term'])) {
    $newTerm = trim($_POST['new_term'] ?? '');
    if ($newTerm === "") {
        $error = "Please write a new condition before adding.";
    } else {
        $_SESSION['payment_terms'][] = $newTerm;
        saveTermsToFile($_SESSION['payment_terms']);
        $success = "New condition added successfully!";
    }
}

/* ✅ SAVE edits */
if (isset($_POST['save_all'])) {
    $terms = $_POST['terms'] ?? [];
    $updated = [];

    foreach ($terms as $t) {
        $t = trim($t);
        if ($t !== "") {
            $updated[] = $t;
        }
    }

    if (count($updated) === 0) {
        $error = "Terms list cannot be empty!";
    } else {
        $_SESSION['payment_terms'] = $updated;
        saveTermsToFile($_SESSION['payment_terms']);
        $success = "Terms & Conditions updated successfully!";
    }
}

/* ✅ DELETE */
if (isset($_POST['delete_term'])) {
    $index = (int)($_POST['delete_index'] ?? -1);

    if ($index >= 0 && $index < count($_SESSION['payment_terms'])) {
        array_splice($_SESSION['payment_terms'], $index, 1);
        saveTermsToFile($_SESSION['payment_terms']);
        $success = "Condition deleted successfully!";
    } else {
        $error = "Invalid delete request!";
    }
}

$termsList = $_SESSION['payment_terms'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Terms & Conditions</title>

<style>
:root{
  --text:#eaf0ff;
  --muted:#9aa8c7;
  --accent:#4f7cff;
  --border:rgba(255,255,255,.15);
  --green:#22c55e;
  --red:#ef4444;
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

/* TOPBAR */
.topbar{
  padding:22px 36px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  border-bottom:1px solid var(--border);
  background: rgba(10,16,30,.45);
  backdrop-filter: blur(10px);
}
.brand{
  display:flex; gap:14px; align-items:center;
}
.badge{
  width:52px; height:52px; border-radius:16px;
  background: linear-gradient(135deg,var(--accent),#8aa6ff);
  display:flex; align-items:center; justify-content:center;
  font-weight:1000; font-size:22px; color:#06102a;
}
.brand-text h1{ margin:0; font-size:24px; font-weight:1000; }
.brand-text p{ margin:4px 0 0; color:var(--muted); font-weight:600; font-size:14px; }

.btn{
  padding:10px 16px;
  border-radius:14px;
  border:1px solid rgba(255,255,255,.16);
  background: rgba(10,16,30,.40);
  color:var(--text);
  text-decoration:none;
  font-weight:900;
  cursor:pointer;
}
.btn:hover{opacity:.95}
.btn-primary{
  background: linear-gradient(135deg,var(--accent),#8aa6ff);
  border-color: transparent;
  color:#06102a;
}
.btn-danger{
  background: rgba(239,68,68,.92);
  border-color: transparent;
  color:#2a0606;
}

/* MAIN */
.container{
  flex:1;
  display:flex;
  justify-content:center;
  align-items:flex-start;
  padding:24px;
}
.card{
  width:min(980px, 95vw);
  border:1px solid var(--border);
  border-radius:22px;
  background: rgba(15,25,45,.60);
  backdrop-filter: blur(12px);
  padding:26px;
}

.title{ font-size:22px; font-weight:1000; margin:0 0 6px; }
.divider{
  height:1px;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.25), transparent);
  margin:14px 0 18px;
}

/* Alerts */
.alert{
  padding:12px 14px;
  border-radius:14px;
  font-weight:900;
  margin-bottom:14px;
}
.success{ border:1px solid rgba(34,197,94,.35); background: rgba(34,197,94,.12); color:#c9ffe0; }
.error{ border:1px solid rgba(239,68,68,.35); background: rgba(239,68,68,.12); color:#ffd0d0; }

/* SERIAL SHOW BOX */
.show-box{
  border:1px solid rgba(255,255,255,.14);
  background: rgba(10,16,30,.45);
  border-radius:16px;
  padding:18px;
  margin-bottom:18px;
}
.show-box h3{
  margin:0 0 12px;
  font-size:16px;
  font-weight:1000;
  color:#cfe0ff;
}
.serial-list{ margin:0; padding-left:22px; }
.serial-list li{ margin:8px 0; line-height:1.55; font-weight:700; }

/* ADD NEW */
.add-box{
  border:1px solid rgba(255,255,255,.14);
  background: rgba(10,16,30,.45);
  border-radius:16px;
  padding:14px;
  margin-bottom:16px;
}
.add-box h3{ margin:0 0 10px; font-size:16px; font-weight:1000; color:#cfe0ff; }
.add-box textarea{
  width:100%;
  min-height:80px;
  resize:vertical;
  padding:12px 14px;
  border-radius:14px;
  border:1px solid rgba(255,255,255,.18);
  background: rgba(10,16,30,.55);
  color:var(--text);
  outline:none;
  font-size:14px;
  line-height:1.5;
}
.add-actions{
  margin-top:10px;
  display:flex;
  justify-content:flex-end;
  gap:10px;
  flex-wrap:wrap;
}

/* EDIT BOX */
.edit-box{
  border:1px solid rgba(255,255,255,.14);
  background: rgba(10,16,30,.45);
  border-radius:16px;
  padding:14px;
}
.edit-box h3{ margin:0 0 10px; font-size:16px; font-weight:1000; color:#cfe0ff; }
.term-item{
  border:1px solid rgba(255,255,255,.14);
  background: rgba(10,16,30,.35);
  border-radius:14px;
  padding:12px;
  margin-bottom:12px;
}
.term-top{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:10px;
  margin-bottom:8px;
}
.term-top .num{ font-weight:1000; color:#cfe0ff; }
.term-textarea{
  width:100%;
  min-height:70px;
  resize:vertical;
  padding:12px 14px;
  border-radius:14px;
  border:1px solid rgba(255,255,255,.18);
  background: rgba(10,16,30,.55);
  color:var(--text);
  outline:none;
  font-size:14px;
  line-height:1.5;
}
.term-textarea:focus{border-color: rgba(79,124,255,.7);}

.save-area{
  margin-top:14px;
  display:flex;
  justify-content:flex-end;
  gap:10px;
  flex-wrap:wrap;
}

/* Footer */
.footer{
  text-align:center;
  padding:14px;
  border-top:1px solid var(--border);
  background: rgba(10,16,30,.35);
  color:var(--muted);
  font-size:13px;
}
</style>
</head>

<body>

<div class="topbar">
  <div class="brand">
    <div class="badge">DP</div>
    <div class="brand-text">
      <h1>Admin - Terms & Conditions</h1>
      <p>Digital Payment and Security System</p>
    </div>
  </div>
  <a class="btn" href="index.php?url=Admin/dashboard">⬅ Back</a>
</div>

<div class="container">
  <div class="card">

    <div class="title">Payment Terms & Conditions</div>
    <div class="divider"></div>

    <?php if ($success): ?><div class="alert success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <!-- ✅ 1) SHOW SERIAL FIRST -->
    <div class="show-box">
      <h3>Current Terms & Conditions (Serial)</h3>
      <ol class="serial-list">
        <?php foreach ($termsList as $t): ?>
          <li><?php echo htmlspecialchars($t); ?></li>
        <?php endforeach; ?>
      </ol>
    </div>

    <!-- ✅ 2) ADD -->
    <div class="add-box">
      <h3>➕ Add New Condition</h3>
      <form method="post">
        <textarea name="new_term" placeholder="Write new payment condition here..."></textarea>
        <div class="add-actions">
          <button class="btn btn-primary" type="submit" name="add_term">Add Condition</button>
        </div>
      </form>
    </div>

    <!-- ✅ 3) EDIT + DELETE -->
    <div class="edit-box">
      <h3>✏️ Edit / Delete Terms</h3>

      <form method="post">
        <?php foreach ($termsList as $i => $term): ?>
          <div class="term-item">
            <div class="term-top">
              <div class="num"><?php echo ($i+1) . "."; ?></div>
              <button class="btn btn-danger" type="submit" name="delete_term"
                onclick="return confirm('Delete this condition?');">Delete</button>
              <input type="hidden" name="delete_index" value="<?php echo $i; ?>">
            </div>

            <textarea class="term-textarea" name="terms[]"><?php echo htmlspecialchars($term); ?></textarea>
          </div>
        <?php endforeach; ?>

        <div class="save-area">
          <a class="btn" href="index.php?url=Admin/dashboard">Cancel</a>
          <button class="btn btn-primary" type="submit" name="save_all">Save All Changes</button>
        </div>
      </form>
    </div>

  </div>
</div>

<div class="footer">@Digital Payment and Security System_2026</div>

</body>
</html>
