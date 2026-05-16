<?php
// includes/sidebar.php – Admin sidebar
// $activePage variable should be set before including this file
$activePage = $activePage ?? '';
?>
<div class="admin-sidebar" id="adminSidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="fas fa-ambulance text-danger"></i>
            <span>Admin Panel</span>
        </div>
        <button class="sidebar-close d-lg-none" id="sidebarClose">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Admin Info -->
    <div class="sidebar-user">
        <div class="sidebar-avatar">
            <i class="fas fa-user-shield"></i>
        </div>
        <div>
            <div class="sidebar-username">
                <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>
            </div>
            <small class="text-success">● Online</small>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="nav-section-label">Main Menu</div>

        <a href="<?= BASE_URL ?>admin/dashboard.php"
           class="sidebar-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>

        <a href="<?= BASE_URL ?>admin/ambulances.php"
           class="sidebar-link <?= $activePage === 'ambulances' ? 'active' : '' ?>">
            <i class="fas fa-ambulance"></i>
            <span>Ambulances</span>
        </a>

        <a href="<?= BASE_URL ?>admin/requests.php"
           class="sidebar-link <?= $activePage === 'requests' ? 'active' : '' ?>">
            <i class="fas fa-bell"></i>
            <span>Requests</span>
            <?php
            // Show pending count badge
            $db = getDB();
            $res = $db->query("SELECT COUNT(*) AS cnt FROM requests WHERE status='pending'");
            $cnt = $res->fetch_assoc()['cnt'] ?? 0;
            $db->close();
            if ($cnt > 0): ?>
                <span class="badge bg-danger ms-auto"><?= $cnt ?></span>
            <?php endif; ?>
        </a>

        <a href="<?= BASE_URL ?>admin/users.php"
           class="sidebar-link <?= $activePage === 'users' ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>

        <div class="nav-section-label mt-3">Account</div>

        <a href="<?= BASE_URL ?>index.php" class="sidebar-link" target="_blank">
            <i class="fas fa-globe"></i>
            <span>View Site</span>
        </a>

        <a href="<?= BASE_URL ?>admin/logout.php" class="sidebar-link text-danger">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>
</div>

<!-- Sidebar overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
