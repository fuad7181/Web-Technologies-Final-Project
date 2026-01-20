<?php
// require guard if you use it in your project
// require __DIR__ . "/../_guard.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #0f2027;
            background: linear-gradient(to right, #2c5364, #203a43, #0f2027);
            color: #fff;
            padding: 40px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: rgba(255,255,255,0.1);
            padding: 30px;
            border-radius: 12px;
        }

        h2 { margin-bottom: 15px; }

        .balance {
            margin-bottom: 20px;
            font-size: 18px;
        }

        .alert {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .alert-success { background: rgba(76,175,80,0.25); }
        .alert-danger { background: rgba(244,67,54,0.25); }

        .form-group { margin-bottom: 15px; }

        label {
            display: block;
            margin-bottom: 6px;
        }

        input {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: none;
        }

        button {
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            background: #00c6ff;
            color: #000;
            font-weight: bold;
            cursor: pointer;
        }

        a {
            color: #4fc3f7;
            text-decoration: none;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 14px;
        }

        .avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid rgba(255,255,255,0.35);
            box-shadow: 0 10px 20px rgba(0,0,0,0.35);
            background: rgba(255,255,255,0.12);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .avatar .initials {
            font-weight: bold;
            color: #fff;
            font-size: 18px;
            letter-spacing: 0.5px;
        }

        .help {
            font-size: 12px;
            opacity: 0.85;
            margin-top: 6px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="profile-header">
        <div class="avatar">
            <?php
                $img  = trim((string)($user['profile_image'] ?? ''));
                $name = (string)($user['name'] ?? '');

                // initials
                $initials = 'U';
                if ($name !== '') {
                    $parts = preg_split('/\s+/', trim($name));
                    $first = $parts[0] ?? '';
                    $last  = $parts[count($parts)-1] ?? '';
                    $initials = strtoupper(substr($first, 0, 1) . substr($last, 0, 1));
                    $initials = $initials ?: 'U';
                }

                // Build correct web URL for image
                $webImg = '';

                if ($img !== '') {
                    // Normalize slashes
                    $normalized = str_replace('\\', '/', $img);

                    // If DB already stores something like "uploads/profile/xxx.png" or "public/uploads/profile/xxx.png"
                    if (strpos($normalized, 'public/uploads/profile/') !== false) {
                        $pos = strpos($normalized, 'public/uploads/profile/');
                        $relative = substr($normalized, $pos); // public/uploads/profile/xxx.png
                        $webImg = rtrim(baseUrl(), '/') . '/' . ltrim($relative, '/');
                    } elseif (strpos($normalized, 'uploads/profile/') !== false) {
                        $pos = strpos($normalized, 'uploads/profile/');
                        $relative = substr($normalized, $pos); // uploads/profile/xxx.png
                        // IMPORTANT: add /public/ because uploads folder is inside public
                        $webImg = rtrim(baseUrl(), '/') . '/public/' . ltrim($relative, '/');
                    } else {
                        // Otherwise extract filename from full path
                        $fileName = basename($normalized);
                        if ($fileName !== '') {
                            // IMPORTANT: add /public/ because uploads folder is inside public
                            $webImg = rtrim(baseUrl(), '/') . '/public/uploads/profile/' . $fileName;
                        }
                    }
                }
            ?>

            <?php if ($webImg !== ''): ?>
                <img src="<?= htmlspecialchars($webImg) ?>" alt="Profile">
            <?php else: ?>
                <span class="initials"><?= htmlspecialchars($initials) ?></span>
            <?php endif; ?>
        </div>

        <div>
            <h2 style="margin:0;">My Profile</h2>
            <div style="opacity:0.9; font-size: 13px;">Upload a profile picture from here — it will show on your dashboard.</div>
        </div>
    </div>

    <div class="balance">
        <strong>Available Balance:</strong>
        ৳<?= number_format((float)($balance ?? 0), 2) ?>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?url=Customer/updateProfile" enctype="multipart/form-data">
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="text" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Profile Picture</label>
            <input type="file" name="profile_image" accept="image/*">
            <div class="help">Allowed: JPG, PNG, GIF, WEBP. Max 2MB.</div>

            <?php if (!empty($fieldErrors['profile_image'] ?? '')): ?>
                <div style="color:#ffb3b3; font-size: 12px; margin-top: 6px;">
                    <?= htmlspecialchars($fieldErrors['profile_image']) ?>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit">Update Profile</button>
    </form>

    <p style="margin-top:20px;">
        <a href="index.php?url=Customer/dashboard">← Back to Dashboard</a>
    </p>
</div>

</body>
</html>
