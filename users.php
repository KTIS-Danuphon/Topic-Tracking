<?php
include 'session_check.php';
date_default_timezone_set('Asia/Bangkok');

require_once 'class/crud.class.php';
require_once 'class/util.class.php';
require_once 'class/encrypt.class.php';

$object   = new CRUD();
$util     = new Util();
$Encrypt  = new Encrypt_data();
$now = new DateTime();
$formatted_now = $now->format('Y-m-d H:i:s');

//ข้อมูลฝ่าย
$table = 'tb_departments_c050968';
$fields = 'fd_dept_id, fd_dept_name';
$where = 'WHERE fd_dept_active = "1"';
$result_department = $object->ReadData($table, $fields, $where);

// ดึงข้อมูลผู้ใช้ที่ login
$table = 'tb_users_c050968 u';
$fields = 'fd_user_id, fd_user_name, fd_user_fullname, fd_user_status, fd_user_dept, fd_user_active';
$where = 'WHERE fd_user_id = "' . $_SESSION['user_id'] . '" AND fd_user_active = "1"';
$result_current = $object->ReadData($table, $fields, $where);

if (!$result_current || count($result_current) === 0) {
    header("Location: auth_login.php");
    exit();
}

$current_user = $result_current[0];
$current_user_id = $current_user['fd_user_id'];
$current_user_role = $current_user['fd_user_status'];

// ดึงข้อมูลผู้ใช้ทั้งหมด
$fields = 'u.fd_user_id, u.fd_user_name, u.fd_user_fullname, u.fd_user_status, dm.fd_dept_name, u.fd_user_active, u.fd_user_created_at, u.fd_user_updated_at';
$where = 'LEFT JOIN tb_departments_c050968 dm ON dm.fd_dept_id = u.fd_user_dept ';
if ($current_user_role === 'admin') {
    // Admin เห็นทุกคน
    $where .= 'ORDER BY fd_user_created_at DESC';
} else {
    // User เห็นแค่ตัวเอง
    $where .= 'WHERE fd_user_id = ' . $current_user_id;
}

$result_user = $object->ReadData($table, $fields, $where);

// นับสถิติสำหรับ Admin
$stats = [
    'admin' => 0,
    'user' => 0,
    'executive' => 0,
    'active' => 0,
    'inactive' => 0
];

if ($current_user_role === 'admin' && $result_user) {
    foreach ($result_user as $row) {
        // นับตามสิทธิ์
        if ($row['fd_user_status'] === 'admin') {
            $stats['admin']++;
        } elseif ($row['fd_user_status'] === 'executive') {
            $stats['executive']++;
        } else {
            $stats['user']++;
        }

        // นับตามสถานะ
        if ($row['fd_user_active'] == '1') {
            $stats['active']++;
        } else {
            $stats['inactive']++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้ - Topic Tracking</title>
    <link rel="icon" href="ktis.svg" type="image/svg+xml">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --navbar-height: 70px;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            overflow-x: hidden;
        }

        /* Top Navbar */
        .top-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--navbar-height);
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 0 2rem;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand i {
            font-size: 1.8rem;
            -webkit-text-fill-color: var(--primary-color);
        }

        .navbar-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .notification-btn {
            position: relative;
            background: transparent;
            border: none;
            font-size: 1.3rem;
            color: #64748b;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .notification-btn:hover {
            background: #f1f5f9;
            color: var(--primary-color);
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: #ef4444;
            color: white;
            font-size: 0.65rem;
            padding: 0.15rem 0.4rem;
            border-radius: 10px;
            font-weight: 600;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .user-profile:hover {
            background: #f1f5f9;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #1e293b;
        }

        .user-role {
            font-size: 0.75rem;
            color: #64748b;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: var(--navbar-height);
            bottom: 0;
            width: var(--sidebar-width);
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            z-index: 999;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            padding: 2rem 1.5rem 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .sidebar-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
        }

        .menu-item {
            margin-bottom: 0.25rem;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.875rem 1.5rem;
            color: #64748b;
            text-decoration: none;
            transition: all 0.3s;
            position: relative;
            font-weight: 500;
        }

        .menu-link:hover {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, transparent 100%);
            color: var(--primary-color);
        }

        .menu-link.active {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.15) 0%, transparent 100%);
            color: var(--primary-color);
            font-weight: 600;
        }

        .menu-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--primary-gradient);
            border-radius: 0 4px 4px 0;
        }

        .menu-link i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .menu-badge {
            margin-left: auto;
            background: #fee2e2;
            color: #dc2626;
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-weight: 600;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1.5rem;
            border-top: 1px solid #e2e8f0;
            background: white;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 1rem;
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: transparent;
            border: 1px solid #e2e8f0;
            color: #64748b;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .logout-btn:hover {
            background: #fef2f2;
            border-color: #fecaca;
            color: #dc2626;
        }

        .logout-btn i {
            font-size: 1.2rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
            padding: 2rem;
            min-height: calc(100vh - var(--navbar-height));
            transition: all 0.3s ease;
        }

        /* Mobile Toggle */
        .mobile-toggle {
            display: none;
            background: transparent;
            border: none;
            font-size: 1.5rem;
            color: #64748b;
            cursor: pointer;
            padding: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .main-content .container-fluid {
                padding-left: 0;
                padding-right: 0;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                left: calc(var(--sidebar-width) * -1);
            }

            .sidebar.show {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-toggle {
                display: block;
            }

            .user-info {
                display: none;
            }

            .top-navbar {
                padding: 0 1rem;
            }
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: var(--navbar-height);
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
        }

        .sidebar-overlay.show {
            display: block;
        }
    </style>
    <style>
        .page-container {
            max-width: 1600px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1rem;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        .stat-icon.admin {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-icon.user {
            background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
            color: white;
        }

        .stat-icon.executive {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .stat-icon.active {
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
            color: white;
        }

        .stat-icon.inactive {
            background: linear-gradient(135deg, #fb923c 0%, #f97316 100%);
            color: white;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
        }

        .search-bar {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .user-table-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .user-table {
            margin-bottom: 0;
        }

        .user-table thead {
            background: var(--primary-gradient);
            color: white;
        }

        .user-table thead th {
            border: none;
            padding: 1rem;
            font-weight: 600;
            white-space: nowrap;
            font-size: 0.9rem;
        }

        .user-table tbody tr {
            transition: all 0.3s;
            cursor: pointer;
        }

        .user-table tbody tr:hover {
            background: #f8fafc;
        }

        .user-table tbody tr.current-user {
            background: rgba(102, 126, 234, 0.05);
            font-weight: 500;
        }

        .user-table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-avatar {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: var(--primary-gradient);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .user-name-cell {
            font-weight: 600;
            color: #1e293b;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-name-cell:hover {
            overflow: visible;
            white-space: normal;
            word-break: break-word;
        }

        .user-email-cell {
            color: #64748b;
            font-size: 0.9rem;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-email-cell:hover {
            overflow: visible;
            white-space: normal;
            word-break: break-all;
        }

        .pagination-container {
            background: white;
            border-radius: 0 0 16px 16px;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .pagination-info {
            color: #64748b;
            font-size: 0.9rem;
        }

        .pagination {
            margin: 0;
        }

        .page-link {
            border-radius: 8px;
            margin: 0 0.25rem;
            border: 2px solid #e2e8f0;
            color: var(--primary-color);
            font-weight: 500;
        }

        .page-link:hover {
            background: rgba(102, 126, 234, 0.1);
            border-color: var(--primary-color);
        }

        .page-item.active .page-link {
            background: var(--primary-gradient);
            border-color: transparent;
        }

        .page-item.disabled .page-link {
            background: #f8fafc;
            border-color: #e2e8f0;
            color: #94a3b8;
        }

        .items-per-page {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .items-per-page select {
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            padding: 0.4rem 0.75rem;
            font-size: 0.9rem;
        }

        .badge-role {
            padding: 0.35rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-role.admin {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-role.user {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-role.executive {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-status {
            padding: 0.35rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-status.active {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-status.inactive {
            background: #fee2e2;
            color: #dc2626;
        }

        .you-badge {
            background: var(--primary-gradient);
            color: white;
            padding: 0.25rem 0.6rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .action-btn-group {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }

        .btn-action-sm {
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-size: 0.85rem;
            transition: all 0.3s;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        @media (max-width: 1200px) {

            .user-table thead th:nth-child(3),
            .user-table tbody td:nth-child(3) {
                display: none;
            }
        }

        @media (max-width: 992px) {

            .user-table thead th:nth-child(4),
            .user-table tbody td:nth-child(4) {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .user-table-container {
                overflow-x: auto;
            }

            .user-table {
                min-width: 800px;
            }
        }

        .modal-content {
            border-radius: 16px;
            border: none;
        }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 16px 16px 0 0;
        }

        .form-label {
            font-weight: 600;
            color: #475569;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            padding: 0.75rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
    </style>
</head>

<body>
    <?php include 'menu.php'; ?>

    <main class="main-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="bi bi-people me-2" style="color: var(--primary-color);"></i>
                    จัดการผู้ใช้
                </h1>
                <p class="text-muted" id="pageDescription">
                    <?php echo ($current_user_role === 'admin') ? 'จัดการข้อมูลผู้ใช้ทั้งหมดในระบบ' : 'จัดการข้อมูลส่วนตัวของคุณ'; ?>
                </p>
            </div>

            <!-- Stats (Admin Only) -->
            <?php if ($current_user_role === 'admin'): ?>
                <div class="stats-card" id="statsSection">
                    <div class="row">
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <div class="stat-icon admin">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div class="stat-number"><?php echo $stats['admin']; ?></div>
                                <div class="stat-label">ผู้ดูแลระบบ</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <div class="stat-icon user">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="stat-number"><?php echo $stats['user']; ?></div>
                                <div class="stat-label">ผู้ใช้ทั่วไป</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <div class="stat-icon active">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div class="stat-number"><?php echo $stats['active']; ?></div>
                                <div class="stat-label">ใช้งานอยู่</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <div class="stat-icon inactive">
                                    <i class="bi bi-x-circle"></i>
                                </div>
                                <div class="stat-number"><?php echo $stats['inactive']; ?></div>
                                <div class="stat-label">ไม่ใช้งาน</div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Search Bar -->
            <div class="search-bar">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">ค้นหาผู้ใช้</label>
                        <input type="text" class="form-control" id="searchInput"
                            placeholder="ค้นหาด้วยชื่อผู้ใช้, ชื่อ-นามสกุล...">
                    </div>
                    <?php if ($current_user_role === 'admin'): ?>
                        <div class="col-md-3">
                            <label class="form-label">สิทธิ์</label>
                            <select class="form-select" id="roleFilter">
                                <option value="">ทั้งหมด</option>
                                <option value="admin">ผู้ดูแลระบบ</option>
                                <option value="user">ผู้ใช้ทั่วไป</option>
                                <option value="executive">ผู้บริหาร</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">สถานะ</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">ทั้งหมด</option>
                                <option value="1">ใช้งานอยู่</option>
                                <option value="0">ไม่ใช้งาน</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($current_user_role === 'admin'): ?>
                    <div class="mt-3">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="bi bi-plus-circle me-2"></i>
                            เพิ่มผู้ใช้ใหม่
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- User List -->
            <div class="user-table-container">
                <div style="overflow-x: auto;">
                    <table class="table user-table" style="min-width: 1200px;">
                        <thead>
                            <tr>
                                <th style="width: 200px;">ชื่อผู้ใช้</th>
                                <th style="width: 200px;">ชื่อ-นามสกุล</th>
                                <th style="width: 180px;">แผนก/ตำแหน่ง</th>
                                <th style="width: 110px;">สิทธิ์</th>
                                <th style="width: 110px;">สถานะ</th>
                                <th style="width: 140px;" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="userList">
                            <!-- Users will be loaded here -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    <div class="pagination-info">
                        แสดง <strong id="startIndex">1</strong> - <strong id="endIndex">10</strong>
                        จาก <strong id="totalUsers">0</strong> คน
                    </div>

                    <nav>
                        <ul class="pagination mb-0" id="paginationControls">
                            <!-- Pagination will be loaded here -->
                        </ul>
                    </nav>

                    <div class="items-per-page">
                        <label class="mb-0">แสดง:</label>
                        <select class="form-select form-select-sm" id="itemsPerPage" onchange="changeItemsPerPage()">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>รายการ</span>
                    </div>
                </div>
            </div>

            <!-- Edit User Modal -->
            <div class="modal fade" id="editUserModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-pencil-square me-2"></i>
                                แก้ไขข้อมูลผู้ใช้
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editUserForm">
                                <input type="hidden" id="editUserId">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ชื่อผู้ใช้ <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="editUsername" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="editFullname" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">แผนก/ตำแหน่ง</label>
                                    <input type="text" class="form-control" id="editDepartment">
                                </div>

                                <?php if ($current_user_role === 'admin'): ?>
                                    <div class="row" id="adminEditFields">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">สิทธิ์การใช้งาน</label>
                                            <select class="form-select" id="editRole">
                                                <option value="user">ผู้ใช้ทั่วไป</option>
                                                <option value="admin">ผู้ดูแลระบบ</option>
                                                <option value="executive">ผู้บริหาร</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">สถานะ</label>
                                            <select class="form-select" id="editStatus">
                                                <option value="1">ใช้งานอยู่</option>
                                                <option value="0">ไม่ใช้งาน</option>
                                            </select>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label class="form-label">รหัสผ่านใหม่ (เว้นว่างหากไม่ต้องการเปลี่ยน)</label>
                                    <input type="password" class="form-control" id="editPassword" placeholder="••••••••">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                    <input type="password" class="form-control" id="editPasswordConfirm" placeholder="••••••••">
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>ยกเลิก
                            </button>
                            <button type="button" class="btn btn-primary" onclick="saveUser()">
                                <i class="bi bi-check-circle me-1"></i>บันทึก
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add User Modal (Admin Only) -->
            <?php if ($current_user_role === 'admin'): ?>
                <div class="modal fade" id="addUserModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="bi bi-person-plus me-2"></i>
                                    เพิ่มผู้ใช้ใหม่
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="addUserForm">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">ชื่อผู้ใช้ <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="addUsername" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="addFullname" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">แผนก</label>
                                        <!-- <input type="text" class="form-control" id="addDepartment"> -->
                                        <select class="form-select" id="addDepartment">
                                            <option value="" selected disabled>-- เลือกแผนก --</option>
                                            <?php
                                            foreach ($result_department as $row) {
                                                echo '<option value="' . $row['fd_dept_id'] . '">' . $row['fd_dept_name'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">สิทธิ์การใช้งาน</label>
                                            <select class="form-select" id="addRole">
                                                <option value="user">ผู้ใช้ทั่วไป</option>
                                                <option value="admin">ผู้ดูแลระบบ</option>
                                                <option value="executive">ผู้บริหาร</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">สถานะ</label>
                                            <select class="form-select" id="addStatus">
                                                <option value="1">ใช้งานอยู่</option>
                                                <option value="0">ไม่ใช้งาน</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">รหัสผ่าน <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="addPassword" value="Ktisgroup" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">ยืนยันรหัสผ่าน <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="addPasswordConfirm" required>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle me-1"></i>ยกเลิก
                                </button>
                                <button type="button" class="btn btn-primary" onclick="addUser()">
                                    <i class="bi bi-check-circle me-1"></i>เพิ่มผู้ใช้
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Current user from PHP
        const currentUser = {
            id: <?php echo $current_user_id; ?>,
            role: '<?php echo $current_user_role; ?>'
        };

        // Users data from database
        let users = <?php echo json_encode($result_user ? $result_user : []); ?>;

        let currentPage = 1;
        let itemsPerPage = 10;
        let filteredUsers = [];

        function initializePage() {
            renderUsers();
        }

        function renderUsers() {
            const tbody = document.getElementById('userList');
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const roleFilter = document.getElementById('roleFilter') ? document.getElementById('roleFilter').value : '';
            const statusFilter = document.getElementById('statusFilter') ? document.getElementById('statusFilter').value : '';

            filteredUsers = users.filter(user => {
                const matchSearch = user.fd_user_fullname.toLowerCase().includes(searchTerm) ||
                    user.fd_user_name.toLowerCase().includes(searchTerm);
                const matchRole = !roleFilter || user.fd_user_status === roleFilter;
                const matchStatus = !statusFilter || user.fd_user_active == statusFilter;

                return matchSearch && matchRole && matchStatus;
            });

            // Pagination
            const totalPages = Math.ceil(filteredUsers.length / itemsPerPage);
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, filteredUsers.length);
            const usersToShow = filteredUsers.slice(startIndex, endIndex);

            if (usersToShow.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="bi bi-person-x"></i>
                            <p class="mt-3">ไม่พบผู้ใช้</p>
                        </td>
                    </tr>
                `;
                document.querySelector('.pagination-container').style.display = 'none';
                return;
            }

            document.querySelector('.pagination-container').style.display = 'flex';

            tbody.innerHTML = usersToShow.map(user => {
                const isCurrentUser = user.fd_user_id == currentUser.id;
                const roleLabel = getRoleLabel(user.fd_user_status);
                const statusLabel = user.fd_user_active == '1' ? 'ใช้งาน' : 'ไม่ใช้งาน';
                const statusClass = user.fd_user_active == '1' ? 'active' : 'inactive';

                return `
                    <tr class="${isCurrentUser ? 'current-user' : ''}">
                        <td>
                            <div class="user-name-cell" title="${user.fd_user_name}">
                                ${user.fd_user_name}
                                ${isCurrentUser ? '<span class="you-badge">คุณ</span>' : ''}
                            </div>
                        </td>
                        <td>
                            <div class="user-email-cell" title="${user.fd_user_fullname}">${user.fd_user_fullname}</div>
                        </td>
                        <td class="department-cell">
                            <span class="text-muted" title="${user.fd_dept_name || '-'}">${user.fd_dept_name || '-'}</span>
                        </td>
                        <td>
                            <span class="badge-role ${user.fd_user_status}">
                                <i class="bi bi-${getRoleIcon(user.fd_user_status)}"></i>
                                ${roleLabel}
                            </span>
                        </td>
                        <td>
                            <span class="badge-status ${statusClass}">
                                <i class="bi bi-circle-fill"></i>
                                ${statusLabel}
                            </span>
                        </td>
                        <td>
                            <div class="action-btn-group">
                                ${canEdit(user.fd_user_id) ? `
                                    <button class="btn btn-sm btn-primary btn-action-sm" onclick="editUser(${user.fd_user_id})" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                ` : ''}
                                ${canDelete(user.fd_user_id) ? `
                                    <button class="btn btn-sm btn-outline-danger btn-action-sm" onclick="deleteUser(${user.fd_user_id})" title="ลบ">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                ` : ''}
                                ${canResetPassword(user.fd_user_id) ? `
                                    <button class="btn btn-sm btn-outline-warning btn-action-sm" onclick="resetPassword(${user.fd_user_id})" title="รีเซ็ตรหัสผ่าน">
                                        <i class="bi bi-key"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            updatePaginationInfo();
            renderPagination();
        }

        function getRoleLabel(role) {
            const roles = {
                'admin': 'Admin',
                'user': 'User',
                'executive': 'Executive'
            };
            return roles[role] || role;
        }

        function getRoleIcon(role) {
            const icons = {
                'admin': 'shield-check',
                'user': 'person',
                'executive': 'briefcase-fill'
            };
            return icons[role] || 'person';
        }

        function canEdit(userId) {
            return currentUser.role === 'admin' || userId == currentUser.id;
        }

        function canDelete(userId) {
            return currentUser.role === 'admin' && userId != currentUser.id;
        }

        function canResetPassword(userId) {
            return currentUser.role === 'admin';
        }

        function updatePaginationInfo() {
            const startIndex = (currentPage - 1) * itemsPerPage + 1;
            const endIndex = Math.min(startIndex + itemsPerPage - 1, filteredUsers.length);

            document.getElementById('startIndex').textContent = filteredUsers.length > 0 ? startIndex : 0;
            document.getElementById('endIndex').textContent = endIndex;
            document.getElementById('totalUsers').textContent = filteredUsers.length;
        }

        function renderPagination() {
            const totalPages = Math.ceil(filteredUsers.length / itemsPerPage);
            const paginationControls = document.getElementById('paginationControls');

            if (totalPages <= 1) {
                paginationControls.innerHTML = '';
                return;
            }

            let html = '';

            // Previous button
            html += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="goToPage(${currentPage - 1}); return false;">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
            `;

            // Page numbers
            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

            if (endPage - startPage + 1 < maxVisiblePages) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            if (startPage > 1) {
                html += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="goToPage(1); return false;">1</a>
                    </li>
                `;
                if (startPage > 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a>
                    </li>
                `;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                html += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="goToPage(${totalPages}); return false;">${totalPages}</a>
                    </li>
                `;
            }

            // Next button
            html += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="goToPage(${currentPage + 1}); return false;">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            `;

            paginationControls.innerHTML = html;
        }

        function goToPage(page) {
            const totalPages = Math.ceil(filteredUsers.length / itemsPerPage);
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            renderUsers();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function changeItemsPerPage() {
            itemsPerPage = parseInt(document.getElementById('itemsPerPage').value);
            currentPage = 1;
            renderUsers();
        }

        function editUser(userId) {
            const user = users.find(u => u.fd_user_id == userId);
            if (!user) return;

            document.getElementById('editUserId').value = user.fd_user_id;
            document.getElementById('editUsername').value = user.fd_user_name;
            document.getElementById('editFullname').value = user.fd_user_fullname;
            document.getElementById('editDepartment').value = user.fd_dept_name || '';

            <?php if ($current_user_role === 'admin'): ?>
                document.getElementById('editRole').value = user.fd_user_status;
                document.getElementById('editStatus').value = user.fd_user_active;
            <?php endif; ?>

            document.getElementById('editPassword').value = '';
            document.getElementById('editPasswordConfirm').value = '';

            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            modal.show();
        }

        function saveUser() {
            const userId = document.getElementById('editUserId').value;
            const password = document.getElementById('editPassword').value;
            const passwordConfirm = document.getElementById('editPasswordConfirm').value;

            if (password && password !== passwordConfirm) {
                alert('รหัสผ่านไม่ตรงกัน');
                return;
            }

            // ส่งข้อมูลไป API
            const formData = {
                action: 'update',
                user_id: userId,
                username: document.getElementById('editUsername').value,
                fullname: document.getElementById('editFullname').value,
                department: document.getElementById('editDepartment').value,
                password: password
            };

            <?php if ($current_user_role === 'admin'): ?>
                formData.role = document.getElementById('editRole').value;
                formData.status = document.getElementById('editStatus').value;
            <?php endif; ?>

            // TODO: ส่งไป API
            console.log('Saving user:', formData);

            bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
            alert('บันทึกข้อมูลสำเร็จ (ต้องเชื่อมต่อ API)');
        }

        <?php if ($current_user_role === 'admin'): ?>

            function addUser() {
                const password = document.getElementById('addPassword').value;
                const passwordConfirm = document.getElementById('addPasswordConfirm').value;

                if (password !== passwordConfirm) {
                    alert('รหัสผ่านไม่ตรงกัน');
                    return;
                }

                const formData = {
                    action: 'create',
                    username: document.getElementById('addUsername').value,
                    fullname: document.getElementById('addFullname').value,
                    department: document.getElementById('addDepartment').value,
                    role: document.getElementById('addRole').value,
                    status: document.getElementById('addStatus').value,
                    password: password
                };

                // TODO: ส่งไป API
                console.log('Adding user:', formData);

                bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
                document.getElementById('addUserForm').reset();
                alert('เพิ่มผู้ใช้สำเร็จ (ต้องเชื่อมต่อ API)');
            }

            function deleteUser(userId) {
                const user = users.find(u => u.fd_user_id == userId);
                if (!user) return;

                if (confirm(`คุณต้องการลบผู้ใช้ "${user.fd_user_fullname}" หรือไม่?`)) {
                    // TODO: ส่งไป API
                    console.log('Deleting user:', userId);
                    alert('ลบผู้ใช้สำเร็จ (ต้องเชื่อมต่อ API)');
                }
            }

            function resetPassword(userId) {
                const user = users.find(u => u.fd_user_id == userId);
                if (!user) return;

                if (confirm(`รีเซ็ตรหัสผ่านของ "${user.fd_user_fullname}" หรือไม่?\n\nรหัสผ่านใหม่จะเป็น: 123456`)) {
                    // TODO: ส่งไป API
                    console.log('Resetting password for user:', userId);
                    alert('รีเซ็ตรหัสผ่านสำเร็จ (ต้องเชื่อมต่อ API)');
                }
            }
        <?php endif; ?>

        // Search and Filter
        document.getElementById('searchInput').addEventListener('input', function() {
            currentPage = 1;
            renderUsers();
        });

        <?php if ($current_user_role === 'admin'): ?>
            document.getElementById('roleFilter').addEventListener('change', function() {
                currentPage = 1;
                renderUsers();
            });
            document.getElementById('statusFilter').addEventListener('change', function() {
                currentPage = 1;
                renderUsers();
            });
        <?php endif; ?>

        // Initialize
        document.addEventListener('DOMContentLoaded', initializePage);
    </script>
</body>

</html>