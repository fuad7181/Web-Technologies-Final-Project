<?php
require_once __DIR__ . '/../../models/bootstrap.php';
requireRoleJson('customer');

$in = readInput();
$receiverPhone = trim((string)($in['phone'] ?? $in['receiver'] ?? ''));
$amountRaw = trim((string)($in['amount'] ?? ''));
$password = (string)($in['password'] ?? '');

$errors = [];

if (!isBdPhone($receiverPhone)) {
    $errors['phone'] = 'Receiver phone must be 01XXXXXXXXX.';
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

try {
    $pdo = db();

    $stmt = $pdo->prepare('SELECT id, name, role FROM users WHERE phone = :phone LIMIT 1');
    $stmt->execute([':phone' => $receiverPhone]);
    $receiver = $stmt->fetch();

    if (!$receiver || $receiver['role'] !== 'customer') {
        jsonOut(['success' => false, 'errors' => ['phone' => 'Receiver not found (customer only).']], 422);
    }
    if ((int)$receiver['id'] === currentUserId()) {
        jsonOut(['success' => false, 'errors' => ['phone' => 'You cannot send money to yourself.']], 422);
    }

    $pdo->beginTransaction();

    // Always verify using password (bcrypt)
    $stmt = $pdo->prepare('SELECT id, balance, password FROM users WHERE id = :id FOR UPDATE');
    $stmt->execute([':id' => currentUserId()]);
    $sender = $stmt->fetch();
    if (!$sender) {
        $pdo->rollBack();
        jsonOut(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    if ((string)$password !== (string)((string)($sender['password'] ?? ''))) {
        $pdo->rollBack();
        jsonOut(['success' => false, 'errors' => ['password' => 'Incorrect password.']], 422);
    }

    $stmt = $pdo->prepare('SELECT id, balance FROM users WHERE id = :id FOR UPDATE');
    $stmt->execute([':id' => (int)$receiver['id']]);
    $recvRow = $stmt->fetch();
    if (!$recvRow) {
        $pdo->rollBack();
        jsonOut(['success' => false, 'errors' => ['phone' => 'Receiver not found.']], 422);
    }

    $senderBal = (float)$sender['balance'];
    if ($senderBal < $amount) {
        $pdo->rollBack();
        jsonOut(['success' => false, 'errors' => ['amount' => 'Insufficient balance.']], 422);
    }

    $stmt = $pdo->prepare('UPDATE users SET balance = balance - :amt WHERE id = :id');
    $stmt->execute([':amt' => $amount, ':id' => currentUserId()]);

    $stmt = $pdo->prepare('UPDATE users SET balance = balance + :amt WHERE id = :id');
    $stmt->execute([':amt' => $amount, ':id' => (int)$receiver['id']]);

    $stmt = $pdo->prepare('INSERT INTO transactions (type, amount, fee, sender_id, receiver_id, reference, created_at) VALUES (:type, :amount, :fee, :sid, :rid, :ref, NOW())');
    $stmt->execute([
        ':type' => 'send_money',
        ':amount' => $amount,
        ':fee' => 0,
        ':sid' => currentUserId(),
        ':rid' => (int)$receiver['id'],
        ':ref' => $receiverPhone,
    ]);

    $stmt = $pdo->prepare('SELECT balance FROM users WHERE id = :id');
    $stmt->execute([':id' => currentUserId()]);
    $newBal = (float)($stmt->fetch()['balance'] ?? 0);

    $pdo->commit();
    jsonOut(['success' => true, 'message' => 'Money sent successfully.', 'new_balance' => $newBal]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    safeServerError();
}
