<?php
require_once __DIR__ . '/../../models/bootstrap.php';
requireRoleJson('customer');

$in = readInput();
$amountRaw = trim((string)($in['amount'] ?? ''));
$amount = is_numeric($amountRaw) ? (float)$amountRaw : 0;

if ($amount <= 0) {
    jsonOut(['success' => false, 'message' => 'Invalid amount.'], 400);
}

$fee = (int)(ceil($amount / 1000) * 10);
$total = $amount + $fee;

jsonOut(['success' => true, 'fee' => $fee, 'total' => $total]);
