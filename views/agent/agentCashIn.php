<?php
// Use the unified auth + DB layer
require_once __DIR__ . '/../../models/bootstrap.php';

// Extra safety if someone hits the view directly
requireLoginRedirect();
if (currentUserRole() !== 'agent') {
    $_SESSION['errors'] = ["Access denied."];
    header('Location: index.php?url=Auth/login');
    exit;
}

$fee = 10; // fixed cash-in fee (BDT)
$recent = $_SESSION['recent_cash_in'] ?? null;
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetUserId = trim((string)($_POST['user_id'] ?? '')); // expects users.user_id (e.g. cust1)
    $amountRaw    = trim((string)($_POST['amount'] ?? ''));

    $amount = is_numeric($amountRaw) ? (float)$amountRaw : 0;
    if ($targetUserId === '' || $amountRaw === '') {
        $error = "Please enter User ID and Amount.";
    } elseif ($amount <= 0) {
        $error = "Amount must be a valid positive number.";
    } else {
        try {
            $pdo = db();
            $pdo->beginTransaction();

            // Lock agent
            $stmt = $pdo->prepare('SELECT id, balance FROM users WHERE id = :id AND role = \'agent\' FOR UPDATE');
            $stmt->execute([':id' => currentUserId()]);
            $agent = $stmt->fetch();
            if (!$agent) {
                $pdo->rollBack();
                $error = "Agent account not found.";
            } else {
                // Find + lock customer by public user_id (fallback: numeric internal id)
                $customer = null;
                $stmt = $pdo->prepare('SELECT id, balance FROM users WHERE user_id = :uid AND role = \'customer\' LIMIT 1 FOR UPDATE');
                $stmt->execute([':uid' => $targetUserId]);
                $customer = $stmt->fetch();

                if (!$customer && ctype_digit($targetUserId)) {
                    $stmt = $pdo->prepare('SELECT id, balance FROM users WHERE id = :id AND role = \'customer\' LIMIT 1 FOR UPDATE');
                    $stmt->execute([':id' => (int)$targetUserId]);
                    $customer = $stmt->fetch();
                }
                if (!$customer) {
                    $pdo->rollBack();
                    $error = "Customer not found (User ID).";
                } else {
                    $totalDebit = $amount + $fee;
                    if ((float)$agent['balance'] < $totalDebit) {
                        $pdo->rollBack();
                        $error = "Insufficient agent balance (needs amount + fee).";
                    } else {
                        // Update balances
                        $stmt = $pdo->prepare('UPDATE users SET balance = balance - :amt WHERE id = :id');
                        $stmt->execute([':amt' => $totalDebit, ':id' => (int)$agent['id']]);

                        $stmt = $pdo->prepare('UPDATE users SET balance = balance + :amt WHERE id = :id');
                        $stmt->execute([':amt' => $amount, ':id' => (int)$customer['id']]);

                        // Record transaction (cash_in)
                        $stmt = $pdo->prepare('INSERT INTO transactions (type, amount, fee, sender_id, receiver_id, reference, created_at)
                                               VALUES (:type, :amount, :fee, :sid, :rid, :ref, NOW())');
                        $stmt->execute([
                            ':type' => 'cash_in',
                            ':amount' => $amount,
                            ':fee' => $fee,
                            ':sid' => (int)$agent['id'],
                            ':rid' => (int)$customer['id'],
                            ':ref' => $targetUserId,
                        ]);

                        $pdo->commit();

                        $recent = [
                            'user_id' => $targetUserId,
                            'amount'  => number_format($amount, 2, '.', ''),
                            'fee'     => number_format($fee, 2, '.', ''),
                            'status'  => 'Completed'
                        ];
                        $_SESSION['recent_cash_in'] = $recent;
                    }
                }
            }
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Server error. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AGENT - Cash In</title>

<style>
:root{
  --text:#eaf0ff;
  --muted:#9aa8c7;
  --accent:#4f7cff;
  --green:#22c55e;
  --red:#ef4444;
  --border:rgba(255,255,255,.15);
}
*{box-sizing:border-box}
html,body{height:100%}

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

/* ===== TOP BAR ===== */
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
  display:flex;
  gap:16px;
  align-items:center;
}
.brand-badge{
  width:52px;
  height:52px;
  border-radius:16px;
  background: linear-gradient(135deg,var(--accent),#8aa6ff);
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:22px;
  font-weight:900;
  color:#06102a;
}
.brand-text{line-height:1.15}
.brand-text h1{
  margin:0;
  font-size:26px;
  font-weight:1000;
}
.brand-text h2{
  margin:4px 0 0;
  font-size:17px;
  font-weight:600;
  color:var(--muted);
}
.logout{
  padding:12px 22px;
  border-radius:14px;
  background:var(--red);
  color:#fff;
  text-decoration:none;
  font-weight:900;
}
.logout:hover{opacity:.9}

/* ===== MAIN ===== */
.container{
  flex:1;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:24px;
}
.card{
  width:min(900px, 95vw);
  border:1px solid var(--border);
  border-radius:22px;
  background: rgba(15,25,45,.60);
  backdrop-filter: blur(12px);
  padding:30px;
}

.page-title{
  text-align:center;
  font-size:36px;
  font-weight:1000;
  margin:8px 0 18px;
  letter-spacing:1px;
  background: linear-gradient(90deg, #60a5fa, #a78bfa, #34d399);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  text-shadow: 0 0 28px rgba(96,165,250,.35);
}
.page-sub{
  text-align:center;
  margin:0 0 22px;
  color:var(--muted);
  font-weight:600;
  font-size:14px;
}
.divider{
  height:1px;
  width:100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.25), transparent);
  margin:12px 0 22px;
}

.form-grid{
  display:grid;
  grid-template-columns: 1fr 1fr;
  gap:14px;
}
.field{
  border:1px solid rgba(255,255,255,.14);
  background: rgba(10,16,30,.45);
  border-radius:14px;
  padding:12px 14px;
}
.field label{
  display:block;
  font-size:12px;
  color:var(--muted);
  margin-bottom:6px;
  font-weight:800;
}
.field input{
  width:100%;
  padding:10px 12px;
  border-radius:12px;
  border:1px solid rgba(255,255,255,.18);
  background: rgba(10,16,30,.55);
  color:var(--text);
  outline:none;
}
.field input:focus{border-color: rgba(79,124,255,.7);}

.fee-row{
  margin-top:14px;
  border:1px solid rgba(255,255,255,.14);
  background: rgba(10,16,30,.45);
  border-radius:14px;
  padding:12px 14px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  font-weight:900;
}
.fee-row span{color:var(--muted); font-weight:800}

.actions{
  margin-top:14px;
  display:flex;
  gap:12px;
  flex-wrap:wrap;
}
.btn{
  padding:12px 18px;
  border:none;
  border-radius:12px;
  cursor:pointer;
  font-weight:900;
}
.btn-cashin{
  background: rgba(34,197,94,.95);
  color:#06210f;
}
.btn-cashin:hover{opacity:.95}

.alert{
  margin:0 0 12px;
  padding:12px 14px;
  border-radius:14px;
  border:1px solid rgba(239,68,68,.35);
  background: rgba(239,68,68,.12);
  color:#ffd0d0;
  font-weight:800;
}

.recent{
  margin-top:20px;
  border:1px solid rgba(255,255,255,.14);
  background: rgba(10,16,30,.40);
  border-radius:16px;
  padding:14px;
}
.recent-title{
  font-weight:1000;
  margin-bottom:10px;
}
.recent-row{
  border:1px solid rgba(255,255,255,.14);
  background: rgba(10,16,30,.45);
  border-radius:14px;
  padding:12px 14px;
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  align-items:center;
}
.pill{
  padding:6px 10px;
  border-radius:999px;
  border:1px solid rgba(255,255,255,.18);
  background: rgba(255,255,255,.06);
  font-weight:900;
}
.pill-ok{
  border-color: rgba(34,197,94,.35);
  background: rgba(34,197,94,.14);
  color:#bbffd2;
}

footer{
  text-align:center;
  padding:14px;
  border-top:1px solid var(--border);
  background: rgba(10,16,30,.45);
  backdrop-filter: blur(10px);
  color:var(--muted);
  font-size:13px;
}

@media (max-width: 900px){
  .form-grid{grid-template-columns:1fr;}
  .page-title{font-size:26px;}
  .brand-text h1{font-size:20px;}
  .brand-text h2{font-size:14px;}
}
</style>
</head>

<body>

<div class="topbar">
  <div class="brand">
    <div class="brand-badge">DP</div>
    <div class="brand-text">
      <h1>AGENT - Cash In</h1>
      <h2>Digital Payment and Security System</h2>
    </div>
  </div>
  <a class="logout" href="?action=logout">Logout</a>
</div>

<div class="container">
  <div class="card">

    <div class="page-title">Cash In</div>
    <div class="page-sub">Enter User ID and Amount to complete Cash In</div>
    <div class="divider"></div>

    <?php if ($error !== ""): ?>
      <div class="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-grid">
        <div class="field">
          <label>User ID:</label>
          <input type="text" name="user_id" placeholder="Enter User ID" value="<?php echo htmlspecialchars($_POST['user_id'] ?? ''); ?>">
        </div>

        <div class="field">
          <label>Amount:</label>
          <input type="number" step="0.01" name="amount" placeholder="Enter Amount" value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>">
        </div>
      </div>

      <div class="fee-row">
        <span>Transaction Fee:</span>
        <div><?php echo $fee; ?> BDT</div>
      </div>

      <div class="actions">
        <button class="btn btn-cashin" type="submit">Cash In</button>
      </div>
    </form>

    <?php if ($recent): ?>
      <div class="recent">
        <div class="recent-title">Recent Cash In:</div>
        <div class="recent-row">
          <span class="pill"><?php echo htmlspecialchars($recent['user_id']); ?></span>
          <span>|</span>
          <span class="pill"><?php echo htmlspecialchars($recent['amount']); ?> BDT</span>
          <span>|</span>
          <span class="pill pill-ok"><?php echo htmlspecialchars($recent['status']); ?></span>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<footer>@Digital Payment and Security System_2026</footer>

</body>
</html>
