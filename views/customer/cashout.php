<?php
require __DIR__ . "/../_guard.php";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cash Out</title>
    <link rel="stylesheet" href="<?php echo baseUrl(); ?>/views/assets/css/customer.css">
    <script src="/views/merged_dps/assets/js/ajaxForms.js" defer></script>
</head>

<body>

<div class="container">
    <h2>Cash Out</h2>

    <div class="balance-box">Available Balance: <strong><?= number_format((float)($balance ?? 0), 2) ?></strong></div>

    <!-- Real-time preview (before transaction) -->
    <div class="balance-box" id="cashoutPreview" data-balance="<?= htmlspecialchars((string)($balance ?? 0)) ?>" style="text-align:left; line-height:1.6;">
        <div><strong>Cash Out Preview</strong></div>
        <div>Cashout Amount: <span id="p_amt">0.00</span></div>
        <div>Cashout Charge: <span id="p_fee">0.00</span></div>
        <div>After Cashout: <span id="p_after"><?= number_format((float)($balance ?? 0), 2) ?></span></div>
        <div style="opacity:0.85; font-size:12px; margin-top:4px;">Charge follows existing rule: ৳10 per ৳1000 (rounded up).</div>
    </div>
    <?php if (!empty($success)): ?>
        <div class="msg-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="msg-error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="index.php?url=Customer/cashout" novalidate data-ajax="1">
        <div class="ajax-messages"></div>
        <input type="text" id="agent" name="agent" placeholder="Agent Number (01XXXXXXXXX)">
        <div id="err_agent" class="field-error"></div>

        <input type="text" id="amount" name="amount" placeholder="Amount">
        <div id="err_amount" class="field-error"></div>

        <input type="password" id="acc_password" name="password" placeholder="Account Password">
        <div id="err_password" class="field-error"></div>

        <button type="submit">Cash Out</button>
    </form>

    <script>
        (function () {
            var preview = document.getElementById('cashoutPreview');
            var amountEl = document.getElementById('amount');
            if (!preview || !amountEl) return;

            var balance = parseFloat(preview.getAttribute('data-balance') || '0');
            var pAmt = document.getElementById('p_amt');
            var pFee = document.getElementById('p_fee');
            var pAfter = document.getElementById('p_after');

            function fmt(n) {
                if (!isFinite(n)) n = 0;
                return n.toFixed(2);
            }

            function calcFee(amount) {
                // Existing backend rule: ceil(amount/1000) * 10
                if (!isFinite(amount) || amount <= 0) return 0;
                return Math.ceil(amount / 1000) * 10;
            }

            function update() {
                var amount = parseFloat((amountEl.value || '').toString().replace(/[^0-9.]/g, ''));
                if (!isFinite(amount) || amount < 0) amount = 0;
                var fee = calcFee(amount);
                var after = balance - (amount + fee);

                if (pAmt) pAmt.textContent = fmt(amount);
                if (pFee) pFee.textContent = fmt(fee);
                if (pAfter) pAfter.textContent = fmt(after);
            }

            amountEl.addEventListener('input', update);
            update();
        })();
    </script>

    <div class="back">
        <a href="index.php?url=Customer/dashboard">⬅ Back to Dashboard</a>
    </div>
</div>

</body>
</html>
