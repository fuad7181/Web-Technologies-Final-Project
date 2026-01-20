<?php
require_once __DIR__ . '/../../models/bootstrap.php';
requireRoleJson('customer');

$in = readInput();
$agentPhone = trim((string)($in['phone'] ?? $in['agent'] ?? ''));
$amountRaw = trim((string)($in['amount'] ?? ''));
$password = (string)($in['password'] ?? '');

$errors = [];

if (!isBdPhone($agentPhone)) {
    $errors['phone'] = 'Agent phone must be 01XXXXXXXXX.';
}
$amount = is_numeric($amountRaw) ? (float)$amountRaw : 0;
if ($amount <= 0) {
    $errors['amount'] = 'Amount must be greater than 0.';
}
if ($password === '' || strlen($password) < 4) {
    $errors['password'] = 'Password must be at least 4 characters.';
}

if ($errors) {
    jsonOut(['success' => false, 'errors' => $errors], 422);
}

$fee = (int)(ceil($amount / 1000) * 10);
$total = $amount + $fee;

try {
    $pdo = db();

    $stmt = $pdo->prepare('SELECT id, name, role FROM users WHERE phone = :phone LIMIT 1');
    $stmt->execute([':phone' => $agentPhone]);
    $agent = $stmt->fetch();

    if (!$agent || $agent['role'] !== 'agent') {
        jsonOut(['success' => false, 'errors' => ['phone' => 'Agent not found.']], 422);
    }

    $pdo->beginTransaction();

    // Always verify using password (bcrypt)
    $stmt = $pdo->prepare('SELECT id, balance, password FROM users WHERE id = :id FOR UPDATE');
    $stmt->execute([':id' => currentUserId()]);
    $cust = $stmt->fetch();
    if (!$cust) {
        $pdo->rollBack();
        jsonOut(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    if ((string)$password !== (string)((string)($cust['password'] ?? ''))) {
        $pdo->rollBack();
        jsonOut(['success' => false, 'errors' => ['password' => 'Incorrect password.']], 422);
    }

    $stmt = $pdo->prepare('SELECT id, balance FROM users WHERE id = :id FOR UPDATE');
    $stmt->execute([':id' => (int)$agent['id']]);
    $agentRow = $stmt->fetch();
    if (!$agentRow) {
        $pdo->rollBack();
        jsonOut(['success' => false, 'errors' => ['phone' => 'Agent not found.']], 422);
    }

    $custBal = (float)$cust['balance'];
    if ($custBal < $total) {
        $pdo->rollBack();
        jsonOut(['success' => false, 'errors' => ['amount' => 'Insufficient balance for amount + fee.']], 422);
    }

    $stmt = $pdo->prepare('UPDATE users SET balance = balance - :total WHERE id = :id');
    $stmt->execute([':total' => $total, ':id' => currentUserId()]);

    $stmt = $pdo->prepare('UPDATE users SET balance = balance + :amt WHERE id = :id');
    $stmt->execute([':amt' => $amount, ':id' => (int)$agent['id']]);

    $stmt = $pdo->prepare('INSERT INTO transactions (type, amount, fee, sender_id, receiver_id, reference, created_at) VALUES (:type, :amount, :fee, :sid, :rid, :ref, NOW())');
    $stmt->execute([
        ':type' => 'cash_out',
        ':amount' => $amount,
        ':fee' => $fee,
        ':sid' => currentUserId(),
        ':rid' => (int)$agent['id'],
        ':ref' => $agentPhone,
    ]);

    $stmt = $pdo->prepare('SELECT balance FROM users WHERE id = :id');
    $stmt->execute([':id' => currentUserId()]);
    $newBal = (float)($stmt->fetch()['balance'] ?? 0);

    $pdo->commit();
    jsonOut(['success' => true, 'message' => 'Cash out successful.', 'new_balance' => $newBal]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    safeServerError();
}
