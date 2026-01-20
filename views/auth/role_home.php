<?php require __DIR__ . '/../_guard.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Account Home</title>
    <link rel="stylesheet" href="<?php echo baseUrl(); ?>/assets/css/customer.css">
</head>
<body>
<div class="container">
    <h2>Welcome</h2>
    <p style="margin-bottom:12px;">
        You are logged in as: <b><?= htmlspecialchars($role) ?></b>
    </p>

    <p style="font-size: 13px; opacity: .85; line-height: 1.5;">
        Customer UI is fully implemented. Agent/Admin UI is not required, but backend supports processing transfers and
        (for admin) viewing loan requests via API.
    </p>

    <div class="back" style="margin-top: 16px;">
        <a href="index.php?url=Auth/logout">Logout</a>
    </div>
</div>
</body>
</html>
