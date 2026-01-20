<?php
require_once __DIR__ . '/../models/bootstrap.php';

function authIsAjax(): bool {
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}

function authJson(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function redirectByRole(string $role): string {
    switch ($role) {
        case 'admin':
            return 'index.php?url=Admin/dashboard';
        case 'agent':
            return 'index.php?url=Agent/dashboard';
        case 'customer':
            return 'index.php?url=Customer/dashboard';
        default:
            return 'index.php?url=Customer/dashboard';
    }
}

function loginPage(): void {
    if (isLoggedIn()) {
        header('Location: ' . redirectByRole((string)($_SESSION['role'] ?? 'customer')));
        exit;
    }

    $errors = flashGet('errors', []);
    $success = flashGet('success', '');
    $fieldErrors = flashGet('field_errors', []);

    require __DIR__ . '/../views/auth/login.php';
}

function loginSubmit(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?url=Auth/login');
        exit;
    }

    $role = trim((string)($_POST['role'] ?? ''));
    $identifier = trim((string)($_POST['identifier'] ?? ($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');

    $errors = [];
    $fieldErrors = [];

    if (!in_array($role, ['customer', 'agent', 'admin'], true)) {
        $errors[] = 'Please select a valid role.';
        $fieldErrors['role'] = 'Select a role.';
    }

    if ($identifier === '') {
        $errors[] = 'Please enter Email or User ID.';
        $fieldErrors['identifier'] = 'Required.';
    }

    if ($password === '' || strlen($password) < 4) {
        $errors[] = 'Password must be at least 4 characters.';
        $fieldErrors['password'] = 'Password too short.';
    }

    if (!$errors) {
        try {
            $pdo = db();

            // ✅ FIX: do NOT reuse same named placeholder twice
            $stmt = $pdo->prepare(
                "SELECT id, user_id, name, role, status, phone, email, password
                 FROM users
                 WHERE (email = :id1 OR user_id = :id2)
                 LIMIT 1"
            );
            $stmt->execute([
                ':id1' => $identifier,
                ':id2' => $identifier
            ]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || (string)$user['role'] !== $role) {
                $errors[] = 'Invalid credentials for the selected role.';
            } else {
                $status = (string)($user['status'] ?? 'approved');
                if ($status !== 'approved') {
                    $errors[] = 'Your account is not approved yet.';
                } elseif ($password !== (string)$user['password']) {
                    $errors[] = 'Invalid credentials.';
                } else {
                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['role'] = (string)$user['role'];
                    $_SESSION['name'] = (string)$user['name'];
                    $_SESSION['phone'] = (string)($user['phone'] ?? '');
                    $_SESSION['logged_in'] = true;

                    if ($role === 'admin') {
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_name'] = (string)$user['name'];
                    } elseif ($role === 'agent') {
                        $_SESSION['agent_logged_in'] = true;
                        $_SESSION['agent_name'] = (string)$user['name'];
                    } else {
                        $_SESSION['customer_logged_in'] = true;
                        $_SESSION['customer_name'] = (string)$user['name'];
                    }

                    $redirect = redirectByRole((string)$user['role']);

                    if (authIsAjax()) {
                        authJson(['status' => 'success', 'redirect' => $redirect]);
                    }

                    $_SESSION['success'] = 'Login successful.';
                    header('Location: ' . $redirect);
                    exit;
                }
            }
        } catch (Throwable $e) {
            $showDetails = (bool)ini_get('display_errors');
            $errors[] = $showDetails
                ? ('Server error: ' . $e->getMessage())
                : 'Server error. Please try again.';
        }
    }

    if (authIsAjax()) {
        authJson(['status' => 'error', 'errors' => $errors, 'field_errors' => $fieldErrors], 400);
    }

    $_SESSION['errors'] = $errors;
    $_SESSION['field_errors'] = $fieldErrors;
    header('Location: index.php?url=Auth/login');
    exit;
}

function logoutAction(): void {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['success'] = 'You have been logged out.';
    header('Location: index.php?url=Auth/login');
    exit;
}
