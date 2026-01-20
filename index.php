<?php
// DPS - merged project (Customer + Agent + Admin)
// Run in XAMPP/WAMP: put this folder inside htdocs, import database.sql, and open index.php

session_start();

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/CustomerController.php';
require_once __DIR__ . '/controllers/AgentController.php';
require_once __DIR__ . '/controllers/AdminController.php';
require_once __DIR__ . '/controllers/SecurityController.php';

$url = $_GET['url'] ?? 'Auth/login';

switch ($url) {
    // ===== AUTH =====
    case 'Auth/login':
        loginPage();
        break;
    case 'Auth/loginSubmit':
        loginSubmit();
        break;
    case 'Auth/logout':
        logoutAction();
        break;

    // ===== CUSTOMER =====
    case 'Customer/dashboard':
        dashboard();
        break;
    case 'Customer/send':
        send();
        break;
    case 'Customer/cashout':
        cashout();
        break;
    case 'Customer/paybill':
        paybill();
        break;
    case 'Customer/loan':
        loan();
        break;
    case 'Customer/profile':
        profile();
        break;
    case 'Customer/updateProfile':
        updateProfile();
        break;
    case 'Customer/editProfile':
        profile();
        break;

    // ===== AGENT =====
    case 'Agent/dashboard':
        agentDashboard();
        break;
    case 'Agent/cashIn':
        agentCashIn();
        break;
    case 'Agent/cashOut':
        agentCashout();
        break;
    case 'Agent/userVerification':
        agentUserVerification();
        break;

    // ===== ADMIN =====
    case 'Admin/dashboard':
        adminDashboard();
        break;
    case 'Admin/manageRoles':
        manageRoles();
        break;

    // ===== SECURITY / ACCOUNT =====
    case 'Security/signup':
        signupPage();
        break;
    case 'Security/forgot':
        forgotPage();
        break;
    case 'Security/reset':
        resetPasswordPage();
        break;
    case 'Security/changePassword':
        changePasswordPage();
        break;
    case 'Security/termsView':
        termsViewPage();
        break;
    case 'Security/termsConditions':
        termsConditionsPage();
        break;
    case 'Security/loanStatus':
        loanStatusPage();
        break;

    default:
        http_response_code(404);
        echo "<h2 style='color:red;text-align:center;'>404 Page Not Found</h2>";
}
