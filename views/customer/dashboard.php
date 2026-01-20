<?php
require __DIR__ . "/../_guard.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; font-family: Arial, sans-serif; }

        body {
            background: url("https://thumbs2.imgbox.com/20/87/W2u8fsAJ_t.jpg")
                        no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.65);
            z-index: -1;
        }

        .page { min-height: 100vh; display: flex; flex-direction: column; }

        header {
            background: rgba(0,0,0,0.85);
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left h1 { font-size: 22px; }
        .header-left p { font-size: 14px; color: #ccc; }

        .logout-btn {
            padding: 8px 18px;
            border-radius: 6px;
            text-decoration: none;
            color: #fff;
            background: linear-gradient(135deg, #ff5252, #c62828);
            font-size: 14px;
        }

        .header-right { display: flex; align-items: center; gap: 14px; }

        .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid rgba(255,255,255,0.35);
            box-shadow: 0 8px 18px rgba(0,0,0,0.35);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.12);
            text-decoration: none;
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
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        .logout-btn:hover { opacity: 0.9; }

        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .dashboard {
            width: 100%;
            max-width: 1200px;
            padding: 35px;
            border-radius: 14px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(12px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.45);
            text-align: center;
        }

        .welcome { font-size: 26px; margin-bottom: 10px; font-weight: bold; }
        .welcome a { color: #4fc3f7; text-decoration: none; }
        .welcome a:hover { text-decoration: underline; }

        .balance {
            margin: 20px auto 35px;
            padding: 18px;
            font-size: 22px;
            border-radius: 10px;
            background: rgba(0,0,0,0.35);
            width: fit-content;
        }

        .menu {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: nowrap;
        }

        .menu a {
            padding: 18px 26px;
            text-decoration: none;
            font-size: 16px;
            color: #fff;
            border-radius: 14px;
            background: linear-gradient(135deg, #00c6ff, #0072ff);
            box-shadow: 0 10px 25px rgba(0, 114, 255, 0.6);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            white-space: nowrap;
        }

        .menu a:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow: 0 18px 45px rgba(0, 198, 255, 0.85);
        }

        footer {
            background: rgba(0,0,0,0.85);
            text-align: center;
            padding: 15px;
            font-size: 14px;
            color: #ddd;
        }

        @media (max-width: 900px) {
            .menu { flex-wrap: wrap; }
        }
    </style>
</head>
<body>

<div class="page">

    <!-- HEADER -->
    <header>
        <div class="header-left">
            <h1>Customer Dashboard</h1>
            <p>Digital Payment System</p>
        </div>

        <div class="header-right">
            <a class="avatar" href="index.php?url=Customer/profile" title="Edit Profile">
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

                    // build correct web url for image
                    $webImg = '';
                    if ($img !== '') {
                        $normalized = str_replace('\\', '/', $img);

                        if (strpos($normalized, 'public/uploads/profile/') !== false) {
                            $pos = strpos($normalized, 'public/uploads/profile/');
                            $relative = substr($normalized, $pos); // public/uploads/profile/xxx.png
                            $webImg = rtrim(baseUrl(), '/') . '/' . ltrim($relative, '/');
                        } elseif (strpos($normalized, 'uploads/profile/') !== false) {
                            $pos = strpos($normalized, 'uploads/profile/');
                            $relative = substr($normalized, $pos); // uploads/profile/xxx.png
                            $webImg = rtrim(baseUrl(), '/') . '/public/' . ltrim($relative, '/');
                        } else {
                            $fileName = basename($normalized);
                            if ($fileName !== '') {
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
            </a>

            <a href="index.php?url=Auth/logout" class="logout-btn">Logout</a>
        </div>
    </header>

    <!-- MAIN -->
    <main>
        <div class="dashboard">

            <?php if (!empty($success)): ?>
                <div style="margin: 0 auto 18px; max-width: 720px; padding: 10px 12px; border-radius: 10px; background: rgba(76, 175, 80, 0.18); border: 1px solid rgba(76, 175, 80, 0.35);">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div style="margin: 0 auto 18px; max-width: 720px; padding: 10px 12px; border-radius: 10px; background: rgba(255, 82, 82, 0.18); border: 1px solid rgba(255, 82, 82, 0.35); text-align: left;">
                    <ul style="padding-left: 18px;">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="welcome">
                Welcome,
                <a href="index.php?url=Customer/profile">
                    <?= htmlspecialchars($user['name'] ?? 'Customer') ?>
                </a>
            </div>

            <div class="balance">
                Available Balance: ৳<?= number_format((float)($balance ?? 0), 2) ?>
            </div>

            <div class="menu">
                <a href="index.php?url=Customer/send">💸 Send Money</a>
                <a href="index.php?url=Customer/cashout">🏧 Cash Out</a>
                <a href="index.php?url=Customer/paybill">💡 Pay Bill</a>
                <a href="index.php?url=Customer/loan">🏦 Take Loan</a>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer>
        © <?= date("Y") ?> Digital Payment System & Security
    </footer>

</div>

</body>
</html>
