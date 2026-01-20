<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function currentUserId(): ?int {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function currentUserRole(): ?string {
    return isset($_SESSION['role']) ? (string)$_SESSION['role'] : null;
}

function requireLoginRedirect(): void {
    if (!isLoggedIn()) {
        $_SESSION['errors'] = ["Please login first."];
        header('Location: index.php?url=Auth/login');
        exit;
    }
}

function requireCustomerRedirect(): void {
    requireLoginRedirect();
    if (currentUserRole() !== 'customer') {
        $_SESSION['errors'] = ["Access denied."];
        header('Location: index.php?url=Auth/login');
        exit;
    }
}

function jsonOut(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function readInput(): array {
    $ctype = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ctype, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
    return $_POST;
}

function requireLoginJson(): void {
    if (!isLoggedIn()) {
        jsonOut(['success' => false, 'message' => 'Unauthorized'], 401);
    }
}

function requireRoleJson(string $role): void {
    requireLoginJson();
    if (currentUserRole() !== $role) {
        jsonOut(['success' => false, 'message' => 'Forbidden'], 403);
    }
}

function safeServerError(): void {
    jsonOut(['success' => false, 'message' => 'Server error. Please try again.'], 500);
}

function flashGet(string $key, $default = null) {
    $v = $_SESSION[$key] ?? $default;
    unset($_SESSION[$key]);
    return $v;
}

function isBdPhone(string $phone): bool {
    return (bool)preg_match('/^01\d{9}$/', $phone);
}

// Returns the base URL path where the app is installed.
// Example:
//   - If script is /merged_dps/index.php => /merged_dps
//   - If script is /index.php           => '' (root)
function baseUrl(): string {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
    return $dir === '/' ? '' : $dir;
}
