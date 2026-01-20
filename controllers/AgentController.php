<?php
require_once __DIR__ . '/../models/bootstrap.php';

function requireAgentRedirect(): void {
    requireLoginRedirect();
    if (currentUserRole() !== 'agent') {
        $_SESSION['errors'] = ["Access denied."];
        header('Location: index.php?url=Auth/login');
        exit;
    }
}

function agentDashboard(): void {
    requireAgentRedirect();
    require __DIR__ . '/../views/agent/agentdashboard.php';
}

function agentCashIn(): void {
    requireAgentRedirect();
    require __DIR__ . '/../views/agent/agentCashIn.php';
}

function agentCashout(): void {
    requireAgentRedirect();
    require __DIR__ . '/../views/agent/agentCashout.php';
}

function agentUserVerification(): void {
    requireAgentRedirect();
    require __DIR__ . '/../views/agent/agentUV.php';
}
