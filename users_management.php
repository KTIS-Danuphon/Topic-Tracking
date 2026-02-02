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
$table = 'tb_divisions_c050968';
$fields = 'fd_div_id, fd_div_name';
$where = 'WHERE fd_div_active = "1"';
$result_division = $object->ReadData($table, $fields, $where);

// ดึงข้อมูลผู้ใช้ที่ login
$table = 'tb_users_c050968 u';
$fields = 'fd_user_id, fd_user_name, fd_user_fullname, fd_user_status, fd_user_div, fd_user_active';
$where = 'WHERE fd_user_id = "' . $_SESSION['user_id'] . '" AND fd_user_active = "1"';
$result_current = $object->ReadData($table, $fields, $where);

if (!$result_current || count($result_current) === 0) {
    header("Location: auth_login.php");
    exit();
}

// if ($_SESSION['user_status'] != "admin") {
//     echo "<script>
//         alert('คุณไม่มีสิทธิ์ดูข้อมูลนี้');
//         window.location.href = 'tasks.php';
//     </script>";
//     exit();
// }

$current_user = $result_current[0];
$current_user_id = $current_user['fd_user_id'];
$current_user_role = $current_user['fd_user_status'];

// ดึงข้อมูลผู้ใช้ทั้งหมด
$fields = 'u.fd_user_id, u.fd_user_name, u.fd_user_fullname, u.fd_user_status, u.fd_user_div, dm.fd_div_name, u.fd_user_active, u.fd_user_created_at, u.fd_user_updated_at';
$where = 'LEFT JOIN tb_divisions_c050968 dm ON dm.fd_div_id = u.fd_user_div ';
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include 'style_menu.php'; ?>

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

        /* Loading Spinner */
        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            display: none;
        }

        .spinner-overlay.show {
            display: flex;
        }

        .spinner-border-custom {
            width: 3rem;
            height: 3rem;
            border-width: 0.3rem;
        }
    </style>
</head>

<body>
    <?php include 'menu.php'; ?>

    <!-- Loading Spinner -->
    <div class="spinner-overlay" id="loadingSpinner">
        <div class="spinner-border spinner-border-custom text-light" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

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
                                <div class="stat-number" id="statAdmin"><?php echo $stats['admin']; ?></div>
                                <div class="stat-label">ผู้ดูแลระบบ</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <div class="stat-icon user">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="stat-number" id="statUser"><?php echo $stats['user']; ?></div>
                                <div class="stat-label">ผู้ใช้ทั่วไป</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <div class="stat-icon active">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div class="stat-number" id="statActive"><?php echo $stats['active']; ?></div>
                                <div class="stat-label">ใช้งานอยู่</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <div class="stat-icon inactive">
                                    <i class="bi bi-x-circle"></i>
                                </div>
                                <div class="stat-number" id="statInactive"><?php echo $stats['inactive']; ?></div>
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
                                <th style="width: 180px;">ฝ่าย/ตำแหน่ง</th>
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
                                <input type="hidden" id="editUserDivId">

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
                                    <label class="form-label">ฝ่าย/ตำแหน่ง</label>
                                    <select class="form-select" id="editDivision">
                                        <option value="">-- เลือกฝ่าย --</option>
                                        <?php
                                        foreach ($result_division as $row) {
                                            echo '<option value="' . $row['fd_div_id'] . '">' . $row['fd_div_name'] . '</option>';
                                        }
                                        ?>
                                    </select>
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
                                <!-- แจ้งเตือนรหัสผ่านเริ่มต้น -->
                                <div class="alert alert-info d-flex align-items-center mb-3">
                                    <i class="bi bi-info-circle me-2 fs-5"></i>
                                    <div>
                                        <strong>รหัสผ่านเริ่มต้น:</strong> Ktisgroup<br>
                                        <small>ผู้ใช้สามารถเปลี่ยนรหัสผ่านได้ภายหลัง</small>
                                    </div>
                                </div>

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

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">สิทธิ์การใช้งาน <span class="text-danger">*</span></label>
                                            <select class="form-select" id="addRole" onchange="toggleDivisionRequirement()">
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

                                    <div class="mb-3" id="addDivisionContainer">
                                        <label class="form-label">ฝ่าย <span class="text-danger" id="addDivisionRequired">*</span></label>
                                        <select class="form-select" id="addDivision">
                                            <option value="">-- เลือกฝ่าย --</option>
                                            <?php
                                            foreach ($result_division as $row) {
                                                echo '<option value="' . $row['fd_div_id'] . '">' . $row['fd_div_name'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                        <small class="text-muted" id="divisionHelp">ผู้ดูแลระบบและผู้บริหารไม่จำเป็นต้องระบุฝ่าย</small>
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

        function showLoading() {
            document.getElementById('loadingSpinner').classList.add('show');
        }

        function hideLoading() {
            document.getElementById('loadingSpinner').classList.remove('show');
        }

        function showAlert(message, type = 'success') {
            const icon = type === 'danger' ? 'error' : type;
            Swal.fire({
                icon: icon,
                title: type === 'success' ? 'สำเร็จ!' : 'เกิดข้อผิดพลาด!',
                text: message,
                confirmButtonText: 'ตรงกรุณา',
                confirmButtonColor: '#667eea'
            });
        }

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
                        <td class="division-cell">
                            <span class="text-muted" title="${user.fd_div_name || '-'}">${user.fd_div_name || '-'}</span>
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
                                ${canToggleStatus(user.fd_user_id) ? `
                                    <button class="btn btn-sm ${user.fd_user_active == '1' ? 'btn-outline-warning' : 'btn-outline-success'} btn-action-sm" 
                                            onclick="toggleUserStatus(${user.fd_user_id}, ${user.fd_user_active})" 
                                            title="${user.fd_user_active == '1' ? 'ปิดการใช้งาน' : 'เปิดการใช้งาน'}">
                                        <i class="bi bi-${user.fd_user_active == '1' ? 'toggle-off' : 'toggle-on'}"></i>
                                    </button>
                                ` : ''}
                                ${canResetPassword(user.fd_user_id) ? `
                                    <button class="btn btn-sm btn-outline-info btn-action-sm" onclick="resetPassword(${user.fd_user_id})" title="รีเซ็ตรหัสผ่าน">
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

        function canToggleStatus(userId) {
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
            document.getElementById('editUserDivId').value = user.fd_user_div || '';
            document.getElementById('editDivision').value = user.fd_user_div || '';

            <?php if ($current_user_role === 'admin'): ?>
                document.getElementById('editRole').value = user.fd_user_status;
                document.getElementById('editStatus').value = user.fd_user_active;
            <?php endif; ?>

            document.getElementById('editPassword').value = '';
            document.getElementById('editPasswordConfirm').value = '';

            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            modal.show();
        }

        async function saveUser() {
            const userId = document.getElementById('editUserId').value;
            const password = document.getElementById('editPassword').value;
            const passwordConfirm = document.getElementById('editPasswordConfirm').value;

            if (password && password !== passwordConfirm) {
                showAlert('รหัสผ่านไม่ตรงกัน', 'danger');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('user_id', userId);
            formData.append('username', document.getElementById('editUsername').value);
            formData.append('fullname', document.getElementById('editFullname').value);
            formData.append('division', document.getElementById('editDivision').value);

            if (password) {
                formData.append('password', password);
            }

            <?php if ($current_user_role === 'admin'): ?>
                formData.append('role', document.getElementById('editRole').value);
                formData.append('status', document.getElementById('editStatus').value);
            <?php endif; ?>

            showLoading();

            try {
                const result = await apiFetch('api/user_management.php', {
                    method: 'POST',
                    body: formData
                });

                // const result = await response.json();

                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();

                    await Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: 'บันทึกข้อมูลสำเร็จ',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#667eea',
                        timer: 1000
                    });

                    location.reload();
                } else {
                    showAlert(result.message || 'เกิดข้อผิดพลาด', 'danger');
                }
            } catch (error) {
                showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'danger');
                console.error('Error:', error);
            } finally {
                hideLoading();
            }
        }

        <?php if ($current_user_role === 'admin'): ?>

            function toggleDivisionRequirement() {
                const role = document.getElementById('addRole').value;
                const divisionRequired = document.getElementById('addDivisionRequired');
                const divisionHelp = document.getElementById('divisionHelp');

                if (role === 'admin' || role === 'executive') {
                    divisionRequired.style.display = 'none';
                    divisionHelp.style.display = 'block';
                } else {
                    divisionRequired.style.display = 'inline';
                    divisionHelp.style.display = 'none';
                }
            }

            async function addUser() {
                const username = document.getElementById('addUsername').value.trim();
                const fullname = document.getElementById('addFullname').value.trim();
                const role = document.getElementById('addRole').value;
                const division = document.getElementById('addDivision').value;

                // Validate
                if (!username || !fullname) {
                    showAlert('กรุณากรอกชื่อผู้ใช้และชื่อ-นามสกุล', 'danger');
                    return;
                }

                // ตรวจสอบว่า user ต้องมีฝ่าย
                if (role === 'user' && !division) {
                    showAlert('กรุณาเลือกฝ่ายสำหรับผู้ใช้ทั่วไป', 'danger');
                    return;
                }

                const formData = new FormData();
                formData.append('action', 'create');
                formData.append('username', username);
                formData.append('fullname', fullname);
                formData.append('division', division);
                formData.append('role', role);
                formData.append('status', document.getElementById('addStatus').value);

                showLoading();

                try {
                    const result = await apiFetch('api/user_management.php', {
                        method: 'POST',
                        body: formData
                    });

                    // const result = await response.json();

                    if (result.success) {
                        bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();

                        await Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            html: `เพิ่มผู้ใช้สำเร็จ<br><br><strong>ชื่อผู้ใช้:</strong> ${username}<br><strong>รหัสผ่านเริ่มต้น:</strong> Ktisgroup`,
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#667eea',
                            timer: 1000
                        });

                        document.getElementById('addUserForm').reset();
                        location.reload();
                    } else {
                        showAlert(result.message || 'เกิดข้อผิดพลาด', 'danger');
                    }
                } catch (error) {
                    showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'danger');
                    console.error('Error:', error);
                } finally {
                    hideLoading();
                }
            }

            async function toggleUserStatus(userId, currentStatus) {
                const user = users.find(u => u.fd_user_id == userId);
                if (!user) return;

                const newStatus = currentStatus == '1' ? '0' : '1';
                const actionText = newStatus == '1' ? 'เปิดการใช้งาน' : 'ปิดการใช้งาน';
                const icon = newStatus == '1' ? 'question' : 'warning';

                const result = await Swal.fire({
                    title: `ยืนยัน${actionText}?`,
                    html: `คุณต้องการ${actionText}ผู้ใช้<br><strong>"${user.fd_user_fullname}"</strong> หรือไม่?`,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonColor: newStatus == '1' ? '#10b981' : '#f59e0b',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: `ใช่, ${actionText}`,
                    cancelButtonText: 'ยกเลิก'
                });

                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('action', 'toggle_status');
                formData.append('user_id', userId);
                formData.append('status', newStatus);

                showLoading();

                try {
                    const apiResult = await apiFetch('api/user_management.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (apiResult.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: apiResult.message,
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#667eea',
                            timer: 1000
                        });

                        location.reload();
                    } else {
                        showAlert(apiResult.message || 'เกิดข้อผิดพลาด', 'danger');
                    }
                } catch (error) {
                    showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'danger');
                    console.error('Error:', error);
                } finally {
                    hideLoading();
                }
            }

            async function resetPassword(userId) {
                const user = users.find(u => u.fd_user_id == userId);
                if (!user) return;

                const result = await Swal.fire({
                    title: 'ยืนยันการรีเซ็ตรหัสผ่าน?',
                    html: `คุณต้องการรีเซ็ตรหัสผ่านของ<br><strong>"${user.fd_user_fullname}"</strong><br><br>รหัสผ่านใหม่จะเป็น: <strong>Ktisgroup</strong>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#667eea',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'ใช่, รีเซ็ตรหัสผ่าน',
                    cancelButtonText: 'ยกเลิก'
                });

                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('action', 'reset_password');
                formData.append('user_id', userId);

                showLoading();

                try {
                    const apiResult = await apiFetch('api/user_management.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (apiResult.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            html: `รีเซ็ตรหัสผ่านสำเร็จ<br><br><strong>รหัสผ่านใหม่:</strong> Ktisgroup`,
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#667eea'
                        });
                    } else {
                        showAlert(apiResult.message || 'เกิดข้อผิดพลาด', 'danger');
                    }
                } catch (error) {
                    showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'danger');
                    console.error('Error:', error);
                } finally {
                    hideLoading();
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

        // Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        // Close sidebar when clicking on menu item (mobile)
        document.querySelectorAll('.menu-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 992) {
                    toggleSidebar();
                }
            });
        });

        // Close sidebar on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992) {
                document.getElementById('sidebar').classList.remove('show');
                document.getElementById('sidebarOverlay').classList.remove('show');
            }
        });


        function apiFetch(url, options = {}) {
            console.log('🔥 apiFetch called →', url);
            return fetch(url, options)
                .then(response => {

                    // ถ้า server ตอบ 401 (optional แต่ดี)
                    if (response.status === 401) {
                        window.location.href = 'session_check.php';
                        throw new Error('SESSION_EXPIRED');
                    }

                    return response.json();
                })
                .then(res => {

                    // 🔴 session หมด (ตาม schema ที่ใช้)
                    if (res.code === 'SESSION_EXPIRED') {
                        alert(res.message || 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่');
                        window.location.href = 'session_check.php';
                        throw new Error('SESSION_EXPIRED');
                    }

                    return res; // ส่งผลลัพธ์ให้ function ที่เรียก
                })
                .catch(err => {
                    if (err.message !== 'SESSION_EXPIRED') {
                        console.error('API ERROR:', err);
                    }
                });
        }
    </script>
</body>

</html>