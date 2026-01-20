<?php
require_once __DIR__ . '/../../../models/bootstrap.php';
requireRoleJson('admin');

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
if ($limit <= 0 || $limit > 200) {
    $limit = 50;
}

try {
    $pdo = db();
    $sql = "SELECT lr.id, lr.amount, lr.duration_months, lr.status, lr.created_at,
                   u.name AS customer_name, u.phone AS customer_phone, u.email AS customer_email
            FROM loan_requests lr
            JOIN users u ON u.id = lr.user_id
            ORDER BY lr.created_at DESC
            LIMIT :lim";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    jsonOut(['success' => true, 'loan_requests' => $rows]);
} catch (Throwable $e) {
    safeServerError();
}
