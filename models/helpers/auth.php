<?php
require_once __DIR__ . '/../bootstrap.php';
// Legacy compatibility: some pages expect a $pdo variable
$pdo = db();


function require_login(string $role = ''): void {
    if (!isset($_SESSION)) session_start();

    // Prefer new session format
    $loggedIn = !empty($_SESSION['user_id']) || !empty($_SESSION['user']);
    if (!$loggedIn) {
        header('Location: index.php?url=Auth/login');
        exit;
    }

    $currentRole = $_SESSION['role'] ?? ($_SESSION['user']['role'] ?? '');
    if ($role !== '' && $currentRole !== $role) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function current_user(): ?array {
    if (!isset($_SESSION)) session_start();

    if (!empty($_SESSION['user'])) {
        return $_SESSION['user'];
    }

    if (!empty($_SESSION['user_id'])) {
        try {
            $pdo = db();
            $stmt = $pdo->prepare('SELECT id, user_id, name, role, status FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([(int)$_SESSION['user_id']]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    return null;
}

function login_user(array $userRow): void {
    if (!isset($_SESSION)) session_start();

    $_SESSION['user'] = [
        'id' => (int)$userRow['id'],
        'user_id' => (string)($userRow['user_id'] ?? ''),
        'name' => (string)($userRow['name'] ?? ''),
        'role' => (string)($userRow['role'] ?? ''),
        'status' => (string)($userRow['status'] ?? 'approved'),
    ];
}

function logout_user(): void {
    if (!isset($_SESSION)) session_start();
    session_unset();
    session_destroy();
}

function fetch_user_by_user_id(PDO $pdo, string $userId): ?array {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row ?: null;
}
