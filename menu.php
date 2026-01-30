<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$table = 'tb_notifications_c050968 nt';
$fields = 'COUNT(*) AS count_notification ';
$where = 'LEFT JOIN tb_notification_users_c050968 ntu ON ntu.fd_notification_id = nt.fd_notification_id ';
$where .= 'WHERE nt.fd_is_deleted = "0" AND ntu.fd_user_id = "' . $_SESSION['user_id'] . '" AND ntu.fd_is_read = "0" AND ntu.fd_is_deleted = "0" ';
$result_notification = $object->ReadData($table, $fields, $where);
$count_notification = $result_notification[0]['count_notification'];
?>
<!-- Top Navbar -->
<nav class="top-navbar">
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <a href="#" class="navbar-brand">
        <i class="bi bi-clipboard-check"></i>
        Topic Tracking
    </a>

    <div class="navbar-right">
        <button class="notification-btn" onclick="showNotifications()">
            <i class="bi bi-bell"></i>
            <?= ($count_notification ?? 0) == 0 ? '' : '<span class="notification-badge">' . $count_notification . '</span>' ?>
        </button>

        <div class="user-profile" onclick="toggleUserMenu()">
            <div class="user-avatar">
                <i class="bi bi-person"></i>
            </div>
            <div class="user-info">
                <div class="user-name"><?= $_SESSION['user_fullname'] ?></div>
                <div class="user-role"><?= $_SESSION['user_status_name'] ?></div>
            </div>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-title">เมนูหลัก</div>
    </div>

    <ul class="sidebar-menu">
        <?php if ($_SESSION['user_status'] == "admin") { ?>
            <li class="menu-item">
                <a href="dashboard.php" class="menu-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>แดชบอร์ด</span>
                </a>
            </li>
        <?php } ?>
        <li class="menu-item">
            <a href="tasks.php" class="menu-link <?= in_array($currentPage, ['tasks.php', 'tesk_create.php', 'task_edit.php', 'task_detail.php']) ? 'active' : '' ?>">
                <i class="bi bi-list-task"></i>
                <span>งานทั้งหมด</span>
                <span class="menu-badge">12</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="notifications.php" class="menu-link <?= $currentPage == 'notifications.php' ? 'active' : '' ?>">
                <i class="bi bi-bell"></i>
                <span>แจ้งเตือน</span>
                <span id="badgeUnread_menu" class="menu-badge"><?= ($count_notification ?? 0) == 0 ? '' : $count_notification ?></span>
            </a>
        </li>
        <!-- <li class="menu-item">
            <a href="calendar.php" class="menu-link <?= $currentPage == 'calendar.php' ? 'active' : '' ?>">
                <i class="bi bi-calendar3"></i>
                <span>ปฏิทิน</span>
            </a>
        </li> -->

    </ul>

    <div class="sidebar-header" style="margin-top: 0.5rem;">
        <div class="sidebar-title">จัดการระบบ</div>
    </div>

    <ul class="sidebar-menu">
        <?php if ($_SESSION['user_status'] == "admin") { ?>
            <li class="menu-item">
                <a href="users.php" class="menu-link <?= $currentPage == 'users.php' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>จัดการผู้ใช้</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="categories.php" class="menu-link <?= $currentPage == 'categories.php' ? 'active' : '' ?>">
                    <i class="bi bi-tags"></i>
                    <span>จัดการฝ่าย</span>
                </a>
            </li>
        <?php } ?>
        <li class="menu-item">
            <a href="settings.php" class="menu-link <?= $currentPage == 'settings.php' ? 'active' : '' ?>">
                <i class="bi bi-gear"></i>
                <span>ตั้งค่า</span>
            </a>
        </li>
        <?php if ($_SESSION['user_status'] == "admin") { ?>
            <li class="menu-item">
                <a href="reports.php" class="menu-link <?= $currentPage == 'reports.php' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>รายงาน</span>
                </a>
            </li>
        <?php } ?>
    </ul>

    <div class="sidebar-footer">
        <button class="logout-btn" onclick="logout()">
            <i class="bi bi-box-arrow-right"></i>
            <span>ออกจากระบบ</span>
        </button>
    </div>
</aside>

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function logout() {
        Swal.fire({
            title: 'ออกจากระบบ',
            text: 'คุณต้องการออกจากระบบใช่หรือไม่ ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ใช่, ออกจากระบบ',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        });
    }
</script>