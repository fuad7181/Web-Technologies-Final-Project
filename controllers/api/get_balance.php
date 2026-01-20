<?php
require_once __DIR__ . '/../../models/bootstrap.php';
requireRoleJson('customer');

try {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT balance FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => currentUserId()]);
    $row = $stmt->fetch();
    $balance = $row ? (float)$row['balance'] : 0.0;
    jsonOut(['success' => true, 'balance' => $balance]);
} catch (Throwable $e) {
    safeServerError();
}
