<?php
session_start();
require_once __DIR__ . '/../../models/helpers/auth.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role   = $_POST['role'] ?? 'agent'; // agent/customer
    $userId = trim($_POST['user_id'] ?? '');

    $name   = trim($_POST['name'] ?? '');
    $phone  = trim($_POST['phone'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $dob    = trim($_POST['dob'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $pass   = $_POST['password'] ?? '';
    $cpass  = $_POST['confirm_password'] ?? '';

    if (!in_array($role, ['agent','customer'], true)) {
        $role = 'agent';
    }

    if ($userId === "" || $name === "" || $phone === "" || $email === "" || $dob === "" || $gender === "" || $pass === "" || $cpass === "") {
        $error = "Please fill in all fields.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,32}$/', $userId)) {
        $error = "User ID must be 3-32 chars (letters/numbers/_).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($pass !== $cpass) {
        $error = "Password and Confirm Password do not match.";
    } elseif (strlen($pass) < 4) {
        $error = "Password must be at least 4 characters.";
    } else {
        // Check duplicates
        $stmt = $pdo->prepare('SELECT id FROM users WHERE user_id = ? OR email = ? LIMIT 1');
        $stmt->execute([$userId, $email]);
        $exists = $stmt->fetch();

        if ($exists) {
            $error = "User ID or Email already exists.";
        } else {
            $status = ($role === 'agent') ? 'pending' : 'approved';
            $stmt = $pdo->prepare('INSERT INTO users (user_id,name,phone,email,dob,gender,role,status,password) VALUES (?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$userId, $name, $phone, $email, $dob, $gender, $role, $status, $pass]);

            if ($role === 'agent') {
                $success = "Agent account created! Waiting for Admin approval. After approval, you can login with your User ID and password.";
            } else {
                $success = "Customer account created successfully! You can login now.";
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
<title>Sign Up - Digital Payment and Security System</title>

<style>
:root{
  --text:#eaf0ff;
  --muted:#9aa8c7;
  --accent:#4f7cff;
  --red:#ef4444;
  --green:#22c55e;
  --border:rgba(255,255,255,.15);
}
*{box-sizing:border-box}
html,body{height:100%}

body{
  margin:0;
  font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;
  background:
    radial-gradient(1200px 700px at 15% 20%, rgba(79,124,255,0.45), transparent 60%),
    radial-gradient(900px 600px at 85% 30%, rgba(56,189,248,0.35), transparent 55%),
    radial-gradient(700px 500px at 40% 90%, rgba(167,139,250,0.25), transparent 55%),
    linear-gradient(135deg, #060b18, #0b1635, #071022);
  color:var(--text);
  display:flex;
  flex-direction:column;
}

.container{
  flex:1;
  display:flex;
  justify-content:center;
  align-items:center;
  padding:24px;
}

.card{
  width:min(520px, 94vw);
  border:1px solid var(--border);
  border-radius:22px;
  background: rgba(15,25,45,.60);
  backdrop-filter: blur(12px);
  padding:34px 30px;
  box-shadow: 0 20px 50px rgba(0,0,0,.55);
}

.title{
  text-align:center;
  font-size:30px;
  font-weight:1000;
  letter-spacing:1px;
  margin:0 0 6px;
  background: linear-gradient(90deg, #60a5fa, #a78bfa, #34d399);
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
}
.subtitle{
  text-align:center;
  margin:0 0 18px;
  color:var(--muted);
  font-size:14px;
  font-weight:600;
}

.alert{
  margin-bottom:14px;
  padding:12px 14px;
  border-radius:14px;
  font-weight:800;
  text-align:center;
}
.alert-error{
  border:1px solid rgba(239,68,68,.35);
  background: rgba(239,68,68,.12);
  color:#ffd0d0;
}
.alert-success{
  border:1px solid rgba(34,197,94,.35);
  background: rgba(34,197,94,.12);
  color:#c9ffe0;
}

.grid{
  display:grid;
  grid-template-columns: 1fr 1fr;
  gap:12px;
}
.field{margin-bottom:12px;}
.field label{
  display:block;
  margin-bottom:6px;
  font-size:13px;
  font-weight:800;
  color:var(--muted);
}
.field input{
  width:100%;
  padding:12px 14px;
  border-radius:14px;
  border:1px solid rgba(255,255,255,.18);
  background: rgba(10,16,30,.55);
  color:var(--text);
  outline:none;
  font-size:14px;
}
.field input:focus{border-color: rgba(79,124,255,.7);}

.full{grid-column:1/-1;}

.gender-row{
  display:flex;
  gap:14px;
  flex-wrap:wrap;
  padding:10px 12px;
  border-radius:14px;
  border:1px solid rgba(255,255,255,.18);
  background: rgba(10,16,30,.55);
}
.gender-row label{
  display:flex;
  align-items:center;
  gap:8px;
  font-weight:800;
  color:var(--text);
  cursor:pointer;
}
.gender-row input{accent-color: var(--accent);}

.btn{
  width:100%;
  padding:12px 16px;
  border:none;
  border-radius:14px;
  cursor:pointer;
  font-weight:900;
  font-size:15px;
  background: linear-gradient(135deg, var(--accent), #8aa6ff);
  color:#06102a;
  margin-top:6px;
}
.btn:hover{opacity:.95}

.links{
  margin-top:16px;
  text-align:center;
  font-size:13px;
  color:var(--muted);
  font-weight:700;
}
.links a{
  color:#93c5fd;
  text-decoration:none;
  font-weight:900;
}
.links a:hover{text-decoration:underline;}

footer{
  text-align:center;
  padding:14px;
  border-top:1px solid var(--border);
  background: rgba(10,16,30,.35);
  color:var(--muted);
  font-size:13px;
}

@media (max-width:700px){
  .grid{grid-template-columns:1fr;}
}
</style>
</head>

<body>
<div class="container">
  <div class="card">
    <h1 class="title">Sign Up</h1>
    <p class="subtitle">Create your account</p>

    <?php if ($error !== ""): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="grid">

        <div class="field full">
          <label>Account Type:</label>
          <div class="gender-row">
            <label>
              <input type="radio" name="role" value="agent" <?php echo (($_POST['role'] ?? 'agent')==='agent')?'checked':''; ?> required>
              Agent (needs admin approval)
            </label>
            <label>
              <input type="radio" name="role" value="customer" <?php echo (($_POST['role'] ?? 'agent')==='customer')?'checked':''; ?> required>
              Customer
            </label>
          </div>
        </div>

        <div class="field full">
          <label>User ID:</label>
          <input type="text" name="user_id" placeholder="e.g. fuad_23" value="<?php echo htmlspecialchars($_POST['user_id'] ?? ''); ?>" required>
        </div>

        <div class="field full">
          <label>User Name:</label>
          <input type="text" name="name" placeholder="Enter your name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
        </div>

        <div class="field">
          <label>Phone Number:</label>
          <input type="text" name="phone" placeholder="01XXXXXXXXX" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
        </div>

        <div class="field">
          <label>Email:</label>
          <input type="email" name="email" placeholder="example@email.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>

        <div class="field">
          <label>Date of Birth:</label>
          <input type="date" name="dob" value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>" required>
        </div>

        <div class="field">
          <label>Gender:</label>
          <div class="gender-row">
            <label>
              <input type="radio" name="gender" value="male" <?php echo (($_POST['gender'] ?? '')==='male')?'checked':''; ?> required>
              Male
            </label>
            <label>
              <input type="radio" name="gender" value="female" <?php echo (($_POST['gender'] ?? '')==='female')?'checked':''; ?> required>
              Female
            </label>
            <label>
              <input type="radio" name="gender" value="other" <?php echo (($_POST['gender'] ?? '')==='other')?'checked':''; ?> required>
              Other
            </label>
          </div>
        </div>

        <div class="field">
          <label>Password:</label>
          <input type="password" name="password" placeholder="Enter password" required>
        </div>

        <div class="field">
          <label>Confirm Password:</label>
          <input type="password" name="confirm_password" placeholder="Confirm password" required>
        </div>

      </div>

      <button type="submit" class="btn">Sign Up</button>
    </form>

    <div class="links">
      Already have an account? <a href="login.php">Login</a>
    </div>

  </div>
</div>

<footer>@Digital Payment and Security System_2026</footer>
</body>
</html>
