<?php include 'session_check.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Topic Tracking - ระบบติดตามงาน</title>
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

        /* Task Table Styles */
        .task-table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-top: 1.5rem;
        }

        .task-table {
            margin-bottom: 0;
        }

        .task-table thead {
            background: var(--primary-gradient);
            color: white;
        }

        .task-table thead th {
            border: none;
            padding: 1rem;
            font-weight: 600;
            white-space: nowrap;
            font-size: 0.9rem;
        }

        .task-table tbody tr {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .task-table tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.005);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .task-table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        .task-id {
            font-weight: 600;
            color: #667eea;
            font-size: 0.85rem;
        }

        .task-title-cell {
            max-width: 300px;
        }

        .task-title-text {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.25rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .task-description-preview {
            font-size: 0.85rem;
            color: #718096;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            white-space: nowrap;
        }

        .status-badge.pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-badge.in-progress {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-badge.completed {
            background-color: #d1fae5;
            color: #065f46;
        }

        .category-badge {
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            background-color: #e0e7ff;
            color: #4338ca;
            display: inline-block;
        }

        .priority-stars {
            color: #fbbf24;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        .date-text {
            font-size: 0.85rem;
            color: #64748b;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.85rem;
            transition: all 0.2s;
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

        .pagination-container {
            padding: 1.5rem;
            background: white;
            border-radius: 12px;
            margin-top: 1rem;
        }

        .table-info {
            font-size: 0.9rem;
            color: #64748b;
        }

        /* Responsive */
        /* //!มือถือ */
        @media (max-width: 576px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
                /* overflow-x: hidden; */
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

        @media (max-width: 768px) {
            .task-table-container {
                overflow-x: auto;
            }

            .task-table {
                min-width: 800px;
            }

            .task-title-cell {
                max-width: 200px;
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
</head>

<body>
    <?php include 'menu.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="mb-4">
                <h2 class="fw-bold mb-1">งานทั้งหมด</h2>
                <p class="text-muted">จัดการและติดตามงานของคุณ</p>
            </div>

            <!-- Filters and Actions -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="ค้นหางาน..." id="searchInput">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-funnel me-1"></i> กรองข้อมูล
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item active" href="#" onclick="filterTasks('all')">ทั้งหมด</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filterTasks('today')">วันนี้</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filterTasks('week')">สัปดาห์นี้</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filterTasks('month')">เดือนนี้</a></li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-sort-down me-1"></i> เรียงตาม
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="sortTasks('date_desc')">วันที่ล่าสุด</a></li>
                                <li><a class="dropdown-item" href="#" onclick="sortTasks('date_asc')">วันที่เก่าสุด</a></li>
                                <li><a class="dropdown-item" href="#" onclick="sortTasks('title')">ชื่องาน A-Z</a></li>
                            </ul>
                        </div>
                        <a class="btn btn-primary" href="tesk_create.php">
                            <i class="bi bi-plus-lg me-1"></i> สร้างงานใหม่
                        </a>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="task-table-container">
                <table class="table task-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">รหัสงาน</th>
                            <th style="width: 30%;">ชื่องาน</th>
                            <th style="width: 12%;">หมวดหมู่</th>
                            <th style="width: 13%;">สถานะ</th>
                            <th style="width: 12%;">ความเร่งด่วน</th>
                            <th style="width: 13%;">วันที่สร้าง</th>
                            <th style="width: 10%;" class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="taskTableBody">
                        <!-- Tasks will be loaded here -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="table-info">
                            แสดง <strong id="startIndex">1</strong> - <strong id="endIndex">10</strong>
                            จาก <strong id="totalTasks">0</strong> งาน
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <nav>
                            <ul class="pagination justify-content-center mb-0" id="paginationControls">
                                <!-- Pagination will be loaded here -->
                            </ul>
                        </nav>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <select class="form-select form-select-sm d-inline-block w-auto" id="itemsPerPage">
                            <option value="10">10 รายการ</option>
                            <option value="25">25 รายการ</option>
                            <option value="50">50 รายการ</option>
                            <option value="100">100 รายการ</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mock data
        const mockTasks = [{
                id: 1,
                title: "พัฒนาระบบ Login ใหม่",
                description: "ออกแบบและพัฒนาระบบ Login ที่รองรับ OAuth 2.0",
                category: "development",
                status: "in-progress",
                importance: 5,
                created_at: "2024-12-20 10:30:00",
                encrypt_id: "abc123"
            },
            {
                id: 2,
                title: "ออกแบบ UI/UX Dashboard",
                description: "สร้าง mockup และ prototype สำหรับหน้า Dashboard",
                category: "design",
                status: "pending",
                importance: 4,
                created_at: "2024-12-19 14:20:00",
                encrypt_id: "def456"
            },
            {
                id: 3,
                title: "แก้ไข Bug หน้า Profile",
                description: "แก้ไขปัญหาการแสดงผลรูปภาพไม่ถูกต้อง",
                category: "development",
                status: "completed",
                importance: 3,
                created_at: "2024-12-18 09:15:00",
                encrypt_id: "ghi789"
            },
            {
                id: 4,
                title: "ประชุมทีมประจำสัปดาห์",
                description: "ประชุมสรุปงานและวางแผนสัปดาห์หน้า",
                category: "meeting",
                status: "completed",
                importance: 2,
                created_at: "2025-12-01 16:00:00",
                encrypt_id: "jkl012"
            },
            {
                id: 5,
                title: "จัดทำเอกสาร API Documentation",
                description: "เขียนเอกสารคู่มือการใช้งาน API สำหรับ Developer",
                category: "other",
                status: "in-progress",
                importance: 4,
                created_at: "2025-12-09 11:45:00",
                encrypt_id: "mno345"
            },
            {
                id: 6,
                title: "วางแผนการตลาดออนไลน์",
                description: "สร้างกลยุทธ์การตลาดบน Social Media",
                category: "marketing",
                status: "pending",
                importance: 5,
                created_at: "2025-12-21 13:30:00",
                encrypt_id: "pqr678"
            }
        ];

        let allTasks = [...mockTasks];
        let filteredTasks = [...mockTasks];
        let currentPage = 1;
        let itemsPerPage = 10;

        // Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        // Notifications
        function showNotifications() {
            alert('แจ้งเตือนทั้งหมด:\n\n- งานใหม่ถูกมอบหมายให้คุณ\n- มีความคิดเห็นใหม่ในงาน\n- งานใกล้ครบกำหนด 3 งาน');
        }

        // User Menu
        function toggleUserMenu() {
            alert('เมนูผู้ใช้:\n- โปรไฟล์\n- ตั้งค่า\n- ออกจากระบบ');
        }

        // Logout
        function logout() {
            if (confirm('คุณต้องการออกจากระบบหรือไม่?')) {
                window.location.href = 'logout.php';
            }
        }

        function getCategoryName(category) {
            const categories = {
                'development': 'พัฒนาระบบ',
                'design': 'ออกแบบ',
                'marketing': 'การตลาด',
                'meeting': 'ประชุม',
                'other': 'อื่นๆ'
            };
            return categories[category] || 'อื่นๆ';
        }

        function getStatusName(status) {
            const statuses = {
                'pending': 'รอดำเนินการ',
                'in-progress': 'กำลังดำเนินการ',
                'completed': 'เสร็จสิ้น'
            };
            return statuses[status] || 'ไม่ทราบสถานะ';
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            return date.toLocaleDateString('th-TH', options);
        }

        function generateStars(importance) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= importance) {
                    stars += '<i class="bi bi-star-fill"></i>';
                } else {
                    stars += '<i class="bi bi-star"></i>';
                }
            }
            return stars;
        }

        function renderTasks() {
            const tbody = document.getElementById('taskTableBody');
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const tasksToShow = filteredTasks.slice(start, end);

            if (tasksToShow.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <h5 class="mt-3">ไม่พบงานที่ค้นหา</h5>
                            <p>ลองเปลี่ยนคำค้นหาหรือกรองข้อมูลใหม่</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = tasksToShow.map(task => `
                <tr onclick="viewTask('${task.encrypt_id}')">
                    <td>
                        <div class="task-id">
                            <i class="bi bi-hash"></i>${new Date(task.created_at).getFullYear()}-${task.id.toString().padStart(4, '0')}
                        </div>
                    </td>
                    <td class="task-title-cell">
                        <div class="task-title-text">${task.title}</div>
                        <div class="task-description-preview">${task.description}</div>
                    </td>
                    <td>
                        <span class="category-badge">
                            <i class="bi bi-tag me-1"></i>${getCategoryName(task.category)}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge ${task.status}">
                            ${getStatusName(task.status)}
                        </span>
                    </td>
                    <td>
                        <div class="priority-stars" title="${task.importance} ดาว">
                            ${generateStars(task.importance)}
                        </div>
                    </td>
                    <td>
                        <div class="date-text">
                            <i class="bi bi-calendar3 me-1"></i>${formatDate(task.created_at)}
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="action-buttons justify-content-center">
                            <button class="btn btn-sm btn-outline-primary btn-action" onclick="event.stopPropagation(); viewTask('${task.encrypt_id}')" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

            updatePaginationInfo();
            renderPagination();
        }

        function updatePaginationInfo() {
            const start = (currentPage - 1) * itemsPerPage + 1;
            const end = Math.min(start + itemsPerPage - 1, filteredTasks.length);

            document.getElementById('startIndex').textContent = filteredTasks.length > 0 ? start : 0;
            document.getElementById('endIndex').textContent = end;
            document.getElementById('totalTasks').textContent = filteredTasks.length;
        }

        function renderPagination() {
            const totalPages = Math.ceil(filteredTasks.length / itemsPerPage);
            const paginationControls = document.getElementById('paginationControls');

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
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    html += `
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a>
                        </li>
                    `;
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            // Next button
            html += `
                <li class="page-item ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="goToPage(${currentPage + 1}); return false;">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            `;

            paginationControls.innerHTML = html;
        }

        function goToPage(page) {
            const totalPages = Math.ceil(filteredTasks.length / itemsPerPage);
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            renderTasks();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function filterTasks(filter) {
            // Update active dropdown item
            document.querySelectorAll('.dropdown-menu a').forEach(item => {
                item.classList.remove('active');
            });
            event.target.classList.add('active');

            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

            if (filter === 'all') {
                filteredTasks = [...allTasks];
            } else if (filter === 'today') {
                filteredTasks = allTasks.filter(task => {
                    const taskDate = new Date(task.created_at);
                    const taskDay = new Date(taskDate.getFullYear(), taskDate.getMonth(), taskDate.getDate());
                    return taskDay.getTime() === today.getTime();
                });
            } else if (filter === 'week') {
                const dayOfWeek = today.getDay();
                const weekStart = new Date(today);
                weekStart.setDate(today.getDate() - dayOfWeek);
                weekStart.setHours(0, 0, 0, 0);

                const weekEnd = new Date(weekStart);
                weekEnd.setDate(weekStart.getDate() + 6);
                weekEnd.setHours(23, 59, 59, 999);

                filteredTasks = allTasks.filter(task => {
                    const taskDate = new Date(task.created_at);
                    return taskDate >= weekStart && taskDate <= weekEnd;
                });
            } else if (filter === 'month') {
                const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
                const month = today.getMonth();
                const year = today.getFullYear();

                filteredTasks = allTasks.filter(task => {
                    const taskDate = new Date(task.created_at);
                    return taskDate.getFullYear() === year && taskDate.getMonth() === month;
                });
            }

            currentPage = 1;
            renderTasks();
        }

        function sortTasks(sortType) {
            filteredTasks.sort((a, b) => {
                switch (sortType) {
                    case 'date_desc':
                        return new Date(b.created_at) - new Date(a.created_at);
                    case 'date_asc':
                        return new Date(a.created_at) - new Date(b.created_at);
                    case 'title':
                        return a.title.localeCompare(b.title, 'th');
                    case 'priority':
                        return b.importance - a.importance;
                    default:
                        return 0;
                }
            });

            currentPage = 1;
            renderTasks();
        }

        function viewTask(encryptId) {
            window.location.href = `task_detail.php?taskID=${encryptId}`;
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            filteredTasks = allTasks.filter(task =>
                task.title.toLowerCase().includes(query) ||
                task.description.toLowerCase().includes(query) ||
                task.id.toString().includes(query) ||
                getCategoryName(task.category).toLowerCase().includes(query) ||
                getStatusName(task.status).toLowerCase().includes(query)
            );
            currentPage = 1;
            renderTasks();
        });

        // Items per page change
        document.getElementById('itemsPerPage').addEventListener('change', function(e) {
            itemsPerPage = parseInt(e.target.value);
            currentPage = 1;
            renderTasks();
        });

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

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            renderTasks();
        });
    </script>
</body>

</html>