<?php
require_once __DIR__ . '/auth.php';
require_login();
$user = ums_current_user();
$flash = get_flash();
$userInitials = strtoupper(substr($user['name'], 0, 1) . substr(strstr($user['name'], ' '), 1, 1));
$pendingCount = 3; // Mock notification count
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageNames = [
    'index' => 'Dashboard',
    'customers' => 'Customers',
    'meters' => 'Meters',
    'readings' => 'Readings',
    'billing' => 'Billing',
    'payments' => 'Payments',
    'reports' => 'Reports',
    'staff' => 'Staff'
];
$currentPageName = $pageNames[$currentPage] ?? ucfirst($currentPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - <?= $currentPageName ?></title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        // Apply theme before render to prevent flash
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="page-transition">
    <header class="topbar">
        <div class="brand"><?= APP_NAME ?></div>
        <nav class="nav">
            <a href="index.php" data-shortcut="1">Dashboard</a>
            <a href="customers.php" data-shortcut="2">Customers</a>
            <a href="meters.php" data-shortcut="3">Meters</a>
            <a href="readings.php" data-shortcut="4">Readings</a>
            <a href="billing.php" data-shortcut="5">Billing</a>
            <a href="payments.php" data-shortcut="6">Payments</a>
            <a href="reports.php" data-shortcut="7">Reports</a>
            <a href="staff.php" data-shortcut="8">Staff</a>
        </nav>
        <div class="topbar-actions">
            <button class="theme-toggle" id="themeToggle" title="Toggle dark mode (D)">
                <span class="icon-sun">☀️</span>
                <span class="icon-moon">🌙</span>
            </button>
            <button class="notification-bell" id="notificationBell" title="Notifications">
                <span class="bell-icon">🔔</span>
                <?php if ($pendingCount > 0): ?>
                    <span class="notification-badge"><?= $pendingCount ?></span>
                <?php endif; ?>
            </button>
            <div class="avatar" title="<?= htmlspecialchars($user['name']) ?>"><?= $userInitials ?></div>
            <a class="btn btn-secondary magnetic-btn" href="logout.php">Logout</a>
        </div>
    </header>
    
    <!-- Spotlight Search Modal -->
    <div class="spotlight-overlay" id="spotlightOverlay">
        <div class="spotlight-modal">
            <div class="spotlight-input-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" class="spotlight-input" id="spotlightInput" placeholder="Search pages, actions...">
                <span class="spotlight-kbd">ESC</span>
            </div>
            <div class="spotlight-results" id="spotlightResults">
                <a href="index.php" class="spotlight-item">
                    <span class="spotlight-item-icon">📊</span>
                    <div class="spotlight-item-text">
                        <div class="spotlight-item-title">Dashboard</div>
                        <div class="spotlight-item-desc">View metrics and overview</div>
                    </div>
                    <span class="kbd">1</span>
                </a>
                <a href="customers.php" class="spotlight-item">
                    <span class="spotlight-item-icon">👥</span>
                    <div class="spotlight-item-text">
                        <div class="spotlight-item-title">Customers</div>
                        <div class="spotlight-item-desc">Manage customer accounts</div>
                    </div>
                    <span class="kbd">2</span>
                </a>
                <a href="meters.php" class="spotlight-item">
                    <span class="spotlight-item-icon">📟</span>
                    <div class="spotlight-item-text">
                        <div class="spotlight-item-title">Meters</div>
                        <div class="spotlight-item-desc">Assign and manage meters</div>
                    </div>
                    <span class="kbd">3</span>
                </a>
                <a href="readings.php" class="spotlight-item">
                    <span class="spotlight-item-icon">📋</span>
                    <div class="spotlight-item-text">
                        <div class="spotlight-item-title">Readings</div>
                        <div class="spotlight-item-desc">Log meter readings</div>
                    </div>
                    <span class="kbd">4</span>
                </a>
                <a href="billing.php" class="spotlight-item">
                    <span class="spotlight-item-icon">📄</span>
                    <div class="spotlight-item-text">
                        <div class="spotlight-item-title">Billing</div>
                        <div class="spotlight-item-desc">Generate and view bills</div>
                    </div>
                    <span class="kbd">5</span>
                </a>
                <a href="payments.php" class="spotlight-item">
                    <span class="spotlight-item-icon">💳</span>
                    <div class="spotlight-item-text">
                        <div class="spotlight-item-title">Payments</div>
                        <div class="spotlight-item-desc">Record payments</div>
                    </div>
                    <span class="kbd">6</span>
                </a>
                <a href="reports.php" class="spotlight-item">
                    <span class="spotlight-item-icon">📈</span>
                    <div class="spotlight-item-text">
                        <div class="spotlight-item-title">Reports</div>
                        <div class="spotlight-item-desc">Analytics and insights</div>
                    </div>
                    <span class="kbd">7</span>
                </a>
                <a href="staff.php" class="spotlight-item">
                    <span class="spotlight-item-icon">🔐</span>
                    <div class="spotlight-item-text">
                        <div class="spotlight-item-title">Staff</div>
                        <div class="spotlight-item-desc">Manage staff members</div>
                    </div>
                    <span class="kbd">8</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>
    
    <!-- Confetti Container -->
    <div class="confetti-container" id="confettiContainer"></div>
    
    <main class="container">
        <!-- Breadcrumb -->
        <?php if ($currentPage !== 'index'): ?>
        <nav class="breadcrumb">
            <a href="index.php">Dashboard</a>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current"><?= $currentPageName ?></span>
        </nav>
        <?php endif; ?>
        
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>
