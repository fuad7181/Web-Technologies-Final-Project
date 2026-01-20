<?php require __DIR__ . '/../_guard.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="<?php echo baseUrl(); ?>/assets/css/customer.css">
    <script src="assets/js/validation.js" defer></script>
</head>
<body>

<div class="container">
    <h2>Edit Profile</h2>

    <?php if (!empty($success)): ?>
        <div class="msg-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="msg-error"><ul>
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul></div>
    <?php endif; ?>

    <form method="post" action="index.php?url=Customer/updateProfile" enctype="multipart/form-data" onsubmit="return validateProfileEdit();">
        <input type="text" id="name" name="name" placeholder="Full Name" value="<?= htmlspecialchars($user['name'] ?? '') ?>">
        <div id="err_name" class="field-error"></div>

        <label style="display:block; margin: 10px 0 6px; color:#fff; opacity:0.9;">Profile Picture</label>
        <input type="file" name="profile_image" accept="image/*">
        <div id="err_profile_image" class="field-error"></div>

        <input type="text" id="phone" name="phone" placeholder="Phone (01XXXXXXXXX)" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
        <div id="err_phone" class="field-error"></div>

        <input type="text" id="email" name="email" placeholder="Email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
        <div id="err_email" class="field-error"></div>

        <input type="text" id="address" name="address" placeholder="Address" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
        <div id="err_address" class="field-error"></div>

        <button type="submit">Save Changes</button>
    </form>

    <div class="back">
        <a href="index.php?url=Customer/profile">⬅ Back to Profile</a>
    </div>
</div>

</body>
</html>
