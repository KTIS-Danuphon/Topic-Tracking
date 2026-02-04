<?php
include 'session_check.php';
date_default_timezone_set('Asia/Bangkok');

require_once 'class/crud.class.php';
require_once 'class/util.class.php';

$object = new CRUD();
$util = new Util();
$now = new DateTime();
$formatted_now = $now->format('Y-m-d H:i:s');

// ดึงข้อมูลผู้ใช้ที่ login
$table = 'tb_users_c050968 u';
$fields = 'fd_user_id, fd_user_name, fd_user_fullname, fd_user_status, fd_user_active';
$where = 'WHERE fd_user_id = "' . $_SESSION['user_id'] . '" AND fd_user_active = "1"';
$result_current = $object->ReadData($table, $fields, $where);

if (!$result_current || count($result_current) === 0) {
    header("Location: auth_login.php");
    exit();
}
$isAdmin = in_array($_SESSION['user_status'], ['admin', 'executive']);
if (!$isAdmin) {
    echo "<script>
        alert('คุณไม่มีสิทธิ์ดูข้อมูลนี้');
        window.location.href = 'tasks.php';
    </script>";
    exit();
}

$current_user = $result_current[0];

// ดึงข้อมูลฝ่ายทั้งหมดพร้อมนับจำนวนผู้ใช้
$table = 'tb_divisions_c050968 d';
$fields = 'd.fd_div_id, d.fd_div_name, d.fd_div_active, d.fd_div_create, d.fd_div_update,
           COUNT(u.fd_user_id) as total_users,
           SUM(CASE WHEN u.fd_user_active = "1" THEN 1 ELSE 0 END) as active_users';
$where = 'LEFT JOIN tb_users_c050968 u ON u.fd_user_div = d.fd_div_id
          GROUP BY d.fd_div_id, d.fd_div_name, d.fd_div_active, d.fd_div_create, d.fd_div_update
          ORDER BY d.fd_div_create DESC';
$result_divisions = $object->ReadData($table, $fields, $where);

// นับสถิติ
$stats = [
    'total' => 0,
    'active' => 0,
    'inactive' => 0
];

if ($result_divisions) {
    $stats['total'] = count($result_divisions);
    foreach ($result_divisions as $row) {
        if ($row['fd_div_active'] == '1') {
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
    <title>จัดการฝ่าย - Topic Tracking</title>
    <link rel="icon" href="ktis.svg" type="image/svg+xml">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include 'style_menu.php'; ?>

    <style>
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

        .stat-icon.total {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-icon.active {
            background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
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

        .division-table-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .division-table {
            margin-bottom: 0;
        }

        .division-table thead {
            background: var(--primary-gradient);
            color: white;
        }

        .division-table thead th {
            border: none;
            padding: 1rem;
            font-weight: 600;
            white-space: nowrap;
            font-size: 0.9rem;
        }

        .division-table tbody tr {
            transition: all 0.3s;
            cursor: pointer;
        }

        .division-table tbody tr:hover {
            background: #f8fafc;
        }

        .division-table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
        }

        .division-name {
            font-weight: 600;
            color: #1e293b;
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

        .user-count-badge {
            background: #e0e7ff;
            color: #4338ca;
            padding: 0.25rem 0.6rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
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

        @media (max-width: 768px) {
            .division-table-container {
                overflow-x: auto;
            }

            .division-table {
                min-width: 800px;
            }
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
                    <i class="bi bi-building me-2" style="color: var(--primary-color);"></i>
                    จัดการฝ่าย
                </h1>
                <p class="text-muted">จัดการข้อมูลฝ่ายและแผนกต่างๆ ในองค์กร</p>
            </div>

            <!-- Stats -->
            <div class="stats-card">
                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-item">
                            <div class="stat-icon total">
                                <i class="bi bi-building"></i>
                            </div>
                            <div class="stat-number" id="statTotal"><?php echo $stats['total']; ?></div>
                            <div class="stat-label">ฝ่ายทั้งหมด</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-item">
                            <div class="stat-icon active">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="stat-number" id="statActive"><?php echo $stats['active']; ?></div>
                            <div class="stat-label">ใช้งานอยู่</div>
                        </div>
                    </div>
                    <div class="col-md-4">
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

            <!-- Search Bar -->
            <div class="search-bar">
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">ค้นหาฝ่าย</label>
                        <input type="text" class="form-control" id="searchInput" placeholder="ค้นหาด้วยชื่อฝ่าย...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">สถานะ</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">ทั้งหมด</option>
                            <option value="1">ใช้งานอยู่</option>
                            <option value="0">ไม่ใช้งาน</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDivisionModal">
                        <i class="bi bi-plus-circle me-2"></i>
                        เพิ่มฝ่ายใหม่
                    </button>
                </div>
            </div>

            <!-- Division List -->
            <div class="division-table-container">
                <div style="overflow-x: auto;">
                    <table class="table division-table">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 80px;">#</th>
                                <th style="width: 300px;">ชื่อฝ่าย</th>
                                <th style="width: 150px;">จำนวนผู้ใช้</th>
                                <th style="width: 120px;">สถานะ</th>
                                <!-- <th style="width: 180px;">วันที่สร้าง</th> -->
                                <!-- <th style="width: 180px;">อัปเดตล่าสุด</th> -->
                                <th style="width: 150px;" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="divisionList">
                            <!-- Divisions will be loaded here -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    <div class="pagination-info">
                        แสดง <strong id="startIndex">1</strong> - <strong id="endIndex">10</strong>
                        จาก <strong id="totalDivisions">0</strong> รายการ
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
                        </select>
                        <span>รายการ</span>
                    </div>
                </div>
            </div>

            <!-- Add Division Modal -->
            <div class="modal fade" id="addDivisionModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-building-add me-2"></i>
                                เพิ่มฝ่ายใหม่
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addDivisionForm">
                                <div class="mb-3">
                                    <label class="form-label">ชื่อฝ่าย <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="addDivisionName" required placeholder="ระบุชื่อฝ่าย">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">สถานะ</label>
                                    <select class="form-select" id="addStatus">
                                        <option value="1" selected>ใช้งานอยู่</option>
                                        <option value="0">ไม่ใช้งาน</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>ยกเลิก
                            </button>
                            <button type="button" class="btn btn-primary" onclick="addDivision()">
                                <i class="bi bi-check-circle me-1"></i>เพิ่มฝ่าย
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Division Modal -->
            <div class="modal fade" id="editDivisionModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-pencil-square me-2"></i>
                                แก้ไขข้อมูลฝ่าย
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editDivisionForm">
                                <input type="hidden" id="editDivisionId">

                                <div class="mb-3">
                                    <label class="form-label">ชื่อฝ่าย <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editDivisionName" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">สถานะ</label>
                                    <select class="form-select" id="editStatus">
                                        <option value="1">ใช้งานอยู่</option>
                                        <option value="0">ไม่ใช้งาน</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>ยกเลิก
                            </button>
                            <button type="button" class="btn btn-primary" onclick="saveDivision()">
                                <i class="bi bi-check-circle me-1"></i>บันทึก
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Divisions data from database (with user counts included)
        let divisions = <?php echo json_encode($result_divisions ? $result_divisions : []); ?>;

        let currentPage = 1;
        let itemsPerPage = 10;
        let filteredDivisions = [];

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
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#667eea'
            });
        }

        function initializePage() {
            renderDivisions();
        }

        function renderDivisions() {
            const tbody = document.getElementById('divisionList');
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;

            filteredDivisions = divisions.filter(division => {
                const matchSearch = division.fd_div_name.toLowerCase().includes(searchTerm);
                const matchStatus = !statusFilter || division.fd_div_active == statusFilter;
                return matchSearch && matchStatus;
            });

            // Pagination
            const totalPages = Math.ceil(filteredDivisions.length / itemsPerPage);
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, filteredDivisions.length);
            const divisionsToShow = filteredDivisions.slice(startIndex, endIndex);

            if (divisionsToShow.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="bi bi-building-x"></i>
                            <p class="mt-3">ไม่พบข้อมูลฝ่าย</p>
                        </td>
                    </tr>
                `;
                document.querySelector('.pagination-container').style.display = 'none';
                return;
            }

            document.querySelector('.pagination-container').style.display = 'flex';

            tbody.innerHTML = divisionsToShow.map((division, index) => {
                const actualIndex = startIndex + index + 1;
                const statusLabel = division.fd_div_active == '1' ? 'ใช้งาน' : 'ไม่ใช้งาน';
                const statusClass = division.fd_div_active == '1' ? 'active' : 'inactive';

                // ใช้ข้อมูลจาก PHP ที่ JOIN มาแล้ว
                const totalUsers = parseInt(division.total_users) || 0;
                const activeUsers = parseInt(division.active_users) || 0;
                const userCountText = activeUsers > 0 ?
                    `${activeUsers} คน (${totalUsers} ทั้งหมด)` :
                    `${totalUsers} คน`;

                return `
                    <tr>
                        <td class="text-center">${actualIndex}</td>
                        <td>
                            <div class="division-name">${division.fd_div_name}</div>
                        </td>
                        <td>
                            <span class="user-count-badge">
                                <i class="bi bi-people-fill me-1"></i>
                                ${userCountText}
                            </span>
                        </td>
                        <td>
                            <span class="badge-status ${statusClass}">
                                <i class="bi bi-circle-fill"></i>
                                ${statusLabel}
                            </span>
                        </td>
                        <!-- <td class="text-muted small">${formatDateTime(division.fd_div_create)}</td>
                        <td class="text-muted small">${formatDateTime(division.fd_div_update)}</td> -->
                        <td>
                            <div class="action-btn-group">
                                <button class="btn btn-sm btn-primary btn-action-sm" 
                                        onclick="editDivision(${division.fd_div_id})" 
                                        title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm ${division.fd_div_active == '1' ? 'btn-outline-warning' : 'btn-outline-success'} btn-action-sm" 
                                        onclick="toggleDivisionStatus(${division.fd_div_id}, ${division.fd_div_active})" 
                                        title="${division.fd_div_active == '1' ? 'ปิดการใช้งาน' : 'เปิดการใช้งาน'}">
                                    <i class="bi bi-${division.fd_div_active == '1' ? 'toggle-off' : 'toggle-on'}"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            updatePaginationInfo();
            renderPagination();
        }

        function formatDateTime(dateTimeString) {
            if (!dateTimeString) return '-';
            const date = new Date(dateTimeString);
            const options = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            };
            return date.toLocaleDateString('th-TH', options);
        }

        function updatePaginationInfo() {
            const startIndex = (currentPage - 1) * itemsPerPage + 1;
            const endIndex = Math.min(startIndex + itemsPerPage - 1, filteredDivisions.length);

            document.getElementById('startIndex').textContent = filteredDivisions.length > 0 ? startIndex : 0;
            document.getElementById('endIndex').textContent = endIndex;
            document.getElementById('totalDivisions').textContent = filteredDivisions.length;
        }

        function renderPagination() {
            const totalPages = Math.ceil(filteredDivisions.length / itemsPerPage);
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
            const totalPages = Math.ceil(filteredDivisions.length / itemsPerPage);
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            renderDivisions();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function changeItemsPerPage() {
            itemsPerPage = parseInt(document.getElementById('itemsPerPage').value);
            currentPage = 1;
            renderDivisions();
        }

        async function addDivision() {
            const divisionName = document.getElementById('addDivisionName').value.trim();

            if (!divisionName) {
                showAlert('กรุณากรอกชื่อฝ่าย', 'danger');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'create');
            formData.append('division_name', divisionName);
            formData.append('status', document.getElementById('addStatus').value);

            showLoading();

            try {
                const result = await apiFetch('api/division_management.php', {
                    method: 'POST',
                    body: formData
                });

                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addDivisionModal')).hide();

                    await Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: 'เพิ่มฝ่ายสำเร็จ',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#667eea',
                        timer: 1000
                    });

                    document.getElementById('addDivisionForm').reset();
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

        function editDivision(divisionId) {
            const division = divisions.find(d => d.fd_div_id == divisionId);
            if (!division) return;

            document.getElementById('editDivisionId').value = division.fd_div_id;
            document.getElementById('editDivisionName').value = division.fd_div_name;
            document.getElementById('editStatus').value = division.fd_div_active;

            const modal = new bootstrap.Modal(document.getElementById('editDivisionModal'));
            modal.show();
        }

        async function saveDivision() {
            const divisionId = document.getElementById('editDivisionId').value;
            const divisionName = document.getElementById('editDivisionName').value.trim();

            if (!divisionName) {
                showAlert('กรุณากรอกชื่อฝ่าย', 'danger');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('division_id', divisionId);
            formData.append('division_name', divisionName);
            formData.append('status', document.getElementById('editStatus').value);

            showLoading();

            try {
                const result = await apiFetch('api/division_management.php', {
                    method: 'POST',
                    body: formData
                });

                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editDivisionModal')).hide();

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

        async function toggleDivisionStatus(divisionId, currentStatus) {
            const division = divisions.find(d => d.fd_div_id == divisionId);
            if (!division) return;

            const newStatus = currentStatus == '1' ? '0' : '1';
            const actionText = newStatus == '1' ? 'เปิดการใช้งาน' : 'ปิดการใช้งาน';
            const icon = newStatus == '1' ? 'question' : 'warning';

            const result = await Swal.fire({
                title: `ยืนยัน${actionText}?`,
                html: `คุณต้องการ${actionText}ฝ่าย<br><strong>"${division.fd_div_name}"</strong> หรือไม่?`,
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
            formData.append('division_id', divisionId);
            formData.append('status', newStatus);

            showLoading();

            try {
                const apiResult = await apiFetch('api/division_management.php', {
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
                    // แสดงข้อความเตือนถ้ามีผู้ใช้อยู่ในฝ่าย
                    if (apiResult.has_active_users) {
                        hideLoading();
                        await Swal.fire({
                            icon: 'warning',
                            title: 'ไม่สามารถดำเนินการได้',
                            html: apiResult.message,
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#667eea',
                        });
                    } else {
                        showAlert(apiResult.message || 'เกิดข้อผิดพลาด', 'danger');
                    }
                }
            } catch (error) {
                showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'danger');
                console.error('Error:', error);
            } finally {
                hideLoading();
            }
        }

        // Search and Filter
        document.getElementById('searchInput').addEventListener('input', function() {
            currentPage = 1;
            renderDivisions();
        });

        document.getElementById('statusFilter').addEventListener('change', function() {
            currentPage = 1;
            renderDivisions();
        });

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