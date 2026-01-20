<?php
// Admin - Loan Status (DB-backed)
require_once __DIR__ . '/../../models/bootstrap.php';

requireLoginRedirect();
if (currentUserRole() !== 'admin') {
    $_SESSION['errors'] = ["Access denied."];
    header('Location: index.php?url=Auth/login');
    exit;
}

$flashMsg = flashGet('flash_msg', '');
$flashErr = flashGet('flash_err', '');

// Approve / Reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loanId = isset($_POST['loan_id']) ? (int)$_POST['loan_id'] : 0;
    $action = (string)($_POST['action'] ?? '');

    if ($loanId > 0 && ($action === 'Approved' || $action === 'Rejected')) {
        try {
            $pdo = db();
            $pdo->beginTransaction();

            // Lock loan row
            $stmt = $pdo->prepare('SELECT id, user_id, amount, status FROM loan_requests WHERE id = :id FOR UPDATE');
            $stmt->execute([':id' => $loanId]);
            $loan = $stmt->fetch();

            if (!$loan) {
                $pdo->rollBack();
                $_SESSION['flash_err'] = 'Loan request not found.';
            } elseif ((string)$loan['status'] !== 'pending') {
                $pdo->rollBack();
                $_SESSION['flash_err'] = 'This loan request is already processed.';
            } else {
                // Update status
                $newStatus = strtolower($action) === 'approved' ? 'approved' : 'rejected';
                $stmt = $pdo->prepare('UPDATE loan_requests SET status = :st WHERE id = :id');
                $stmt->execute([':st' => $newStatus, ':id' => $loanId]);

                if ($newStatus === 'approved') {
                    // Credit customer balance
                    $stmt = $pdo->prepare('UPDATE users SET balance = balance + :amt WHERE id = :uid');
                    $stmt->execute([':amt' => (float)$loan['amount'], ':uid' => (int)$loan['user_id']]);

                    // Record transaction (admin -> customer)
                    $stmt = $pdo->prepare('INSERT INTO transactions (type, amount, fee, sender_id, receiver_id, reference, created_at)
                                           VALUES (:type, :amount, :fee, :sid, :rid, :ref, NOW())');
                    $stmt->execute([
                        ':type' => 'loan',
                        ':amount' => (float)$loan['amount'],
                        ':fee' => 0,
                        ':sid' => currentUserId(),
                        ':rid' => (int)$loan['user_id'],
                        ':ref' => 'loan_request#' . $loanId,
                    ]);

                    $_SESSION['flash_msg'] = 'Loan approved and balance credited.';
                } else {
                    $_SESSION['flash_msg'] = 'Loan rejected.';
                }

                $pdo->commit();
            }
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_err'] = 'Server error. Please try again.';
        }
    }

    // Post/Redirect/Get
    header('Location: index.php?url=Security/loanStatus');
    exit;
}

// Fetch requests
$loans = [];
try {
    $pdo = db();
    $stmt = $pdo->query("SELECT lr.id, lr.amount, lr.duration_months, lr.status, lr.created_at,
                                u.user_id AS customer_user_id, u.name AS customer_name
                         FROM loan_requests lr
                         JOIN users u ON u.id = lr.user_id
                         ORDER BY lr.created_at DESC");
    $loans = $stmt->fetchAll();
} catch (Throwable $e) {
    $flashErr = $flashErr ?: 'Unable to load loan requests.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Loan Status</title>

<style>
:root{
  --text:#eaf0ff;
  --muted:#9aa8c7;
  --accent:#4f7cff;
  --border:rgba(255,255,255,.15);
  --green:#22c55e;
  --red:#ef4444;
  --orange:#f59e0b;
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
.brand{display:flex; gap:14px; align-items:center;}
.badge{
  width:52px; height:52px; border-radius:16px;
  background: linear-gradient(135deg,var(--accent),#8aa6ff);
  display:flex; align-items:center; justify-content:center;
  font-weight:1000; font-size:22px; color:#06102a;
}
.brand-text h1{margin:0; font-size:24px; font-weight:1000;}
.brand-text p{margin:4px 0 0; color:var(--muted); font-weight:600; font-size:14px;}

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
.btn-approve{background: rgba(34,197,94,.92); color:#06210f; border-color:transparent;}
.btn-reject{background: rgba(239,68,68,.92); color:#2a0606; border-color:transparent;}

/* MAIN */
.container{
  flex:1;
  display:flex;
  justify-content:center;
  align-items:center;
  padding:24px;
}
.card{
  width:min(1040px, 95vw);
  border:1px solid var(--border);
  border-radius:22px;
  background: rgba(15,25,45,.60);
  backdrop-filter: blur(12px);
  padding:26px;
}
.title{font-size:22px; font-weight:1000; margin:0 0 6px;}
.sub{margin:0 0 16px; color:var(--muted); font-weight:600; font-size:14px;}
.divider{
  height:1px;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.25), transparent);
  margin:14px 0 18px;
}

.notice{
  margin:0 0 14px;
  padding:12px 14px;
  border-radius:14px;
  border:1px solid rgba(255,255,255,.14);
  background: rgba(255,255,255,.06);
  font-weight:800;
}
.notice.ok{border-color: rgba(34,197,94,.35); background: rgba(34,197,94,.12); color:#bbffd2;}
.notice.err{border-color: rgba(239,68,68,.35); background: rgba(239,68,68,.12); color:#ffd0d0;}

.table-wrap{overflow:auto; border-radius:16px; border:1px solid rgba(255,255,255,.14);}
table{width:100%; border-collapse:collapse; min-width:820px;}
th,td{padding:12px 14px; text-align:center; border-bottom:1px solid rgba(255,255,255,.10);}
th{
  background: rgba(10,16,30,.55);
  font-weight:1000;
  color:#cfe0ff;
}
tr:hover td{background: rgba(255,255,255,.03);}

.pill{
  display:inline-block;
  padding:6px 10px;
  border-radius:999px;
  border:1px solid rgba(255,255,255,.18);
  background: rgba(255,255,255,.06);
  font-weight:900;
  text-transform:capitalize;
}
.pending{border-color: rgba(245,158,11,.40); background: rgba(245,158,11,.14); color:#ffe7bf;}
.approved{border-color: rgba(34,197,94,.35); background: rgba(34,197,94,.14); color:#bbffd2;}
.rejected{border-color: rgba(239,68,68,.35); background: rgba(239,68,68,.14); color:#ffd0d0;}

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
      <h1>Admin - Loan Status</h1>
      <p>Digital Payment and Security System</p>
    </div>
  </div>
  <a class="btn" href="index.php?url=Admin/dashboard">⬅ Back</a>
</div>

<div class="container">
  <div class="card">
    <div class="title">Loan Requests</div>
    <div class="sub">Pending loans can be approved or rejected.</div>
    <div class="divider"></div>

    <?php if (!empty($flashMsg)): ?>
      <div class="notice ok"><?php echo htmlspecialchars($flashMsg); ?></div>
    <?php endif; ?>
    <?php if (!empty($flashErr)): ?>
      <div class="notice err"><?php echo htmlspecialchars($flashErr); ?></div>
    <?php endif; ?>

    <div class="table-wrap">
      <table>
        <tr>
          <th>#</th>
          <th>Customer</th>
          <th>User ID</th>
          <th>Amount</th>
          <th>Duration</th>
          <th>Status</th>
          <th>Action</th>
        </tr>

        <?php if (!$loans): ?>
          <tr><td colspan="7" style="color:var(--muted); font-weight:800;">No loan requests found.</td></tr>
        <?php endif; ?>

        <?php foreach ($loans as $i => $l): ?>
          <?php
            $st = (string)($l['status'] ?? 'pending');
            $cls = 'pending';
            if ($st === 'approved') $cls = 'approved';
            if ($st === 'rejected') $cls = 'rejected';
          ?>
          <tr>
            <td><?php echo (int)$i + 1; ?></td>
            <td><?php echo htmlspecialchars((string)($l['customer_name'] ?? '')); ?></td>
            <td><?php echo htmlspecialchars((string)($l['customer_user_id'] ?? '')); ?></td>
            <td><?php echo htmlspecialchars((string)number_format((float)($l['amount'] ?? 0), 2, '.', '')); ?> BDT</td>
            <td><?php echo htmlspecialchars((string)($l['duration_months'] ?? '')); ?> month(s)</td>
            <td><span class="pill <?php echo $cls; ?>"><?php echo htmlspecialchars($st); ?></span></td>
            <td>
              <?php if ($st === 'pending'): ?>
                <form method="post" style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap; margin:0;">
                  <input type="hidden" name="loan_id" value="<?php echo (int)$l['id']; ?>">
                  <button class="btn btn-approve" type="submit" name="action" value="Approved">Approve</button>
                  <button class="btn btn-reject" type="submit" name="action" value="Rejected">Reject</button>
                </form>
              <?php else: ?>
                <span style="color:var(--muted); font-weight:800;">No action</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>

      </table>
    </div>

  </div>
</div>

<div class="footer">@Digital Payment and Security System_2026</div>

</body>
</html>
