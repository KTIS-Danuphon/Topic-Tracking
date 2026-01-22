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

$table = 'tb_topics_c050968';
$fields = 'fd_topic_id, fd_topic_title, fd_topic_detail, fd_topic_category, fd_topic_mentioned, fd_topic_status, fd_topic_participant, fd_topic_created_by, fd_topic_importance, fd_topic_private, fd_topic_active, fd_topic_created_at ';
switch ($_SESSION['user_status']) {
    //admin / executive → เห็น user ทุกคน
    case 'admin':
    case 'executive':
        $where = 'WHERE fd_topic_active = "1"   ';
        break;
    //user → เห็นเฉพาะคนอื่นในแผนกเดียวกัน มีสถานะเป็น user และ active
    case 'user':
    default:
        $userId = (int) $_SESSION['user_id'];

        $where  = 'WHERE fd_topic_active = 1 ';
        $where .= 'AND ( ';
        $where .= 'fd_topic_created_by = ' . $userId . ' ';
        $where .= 'OR fd_topic_participant  = "[' . $userId . ']" ';
        $where .= 'OR fd_topic_participant  LIKE "[' . $userId . ',%" ';
        $where .= 'OR fd_topic_participant  LIKE "%,' . $userId . ',%" ';
        $where .= 'OR fd_topic_participant  LIKE "%,' . $userId . ']" ';
        $where .= ') ';
        break;
}
$where .= ' ORDER BY fd_topic_created_at DESC ';

$result_topic = $object->ReadData($table, $fields, $where);
$topic = [];

foreach ($result_topic as $row) {
    $topic[] = [
        'id' =>  $row['fd_topic_id'], //เพิ่ม #Task- หน้า id งาน
        'title' => $row['fd_topic_title'], //หัวข้องงาน
        'description' => $row['fd_topic_detail'], //รายละเอียดงาน
        'category' => $row['fd_topic_category'], //หมวดหมู่งาน
        'status' => $row['fd_topic_status'], //สถานะงาน
        'importance' => $row['fd_topic_importance'], //ความเร่งด่วน
        'created_at' => $row['fd_topic_created_at'], //วันที่สร้างงาน
        'encrypt_id' => $Encrypt->EnCrypt_pass($row['fd_topic_id']), //เข้ารหัส id งาน

    ];
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Topic Tracking - ระบบติดตามงาน</title>
    <link rel="icon" href="ktis.svg" type="image/svg+xml">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <?php include 'style_menu.php'; ?>
    <style>
        /* View Toggle Buttons */
        .view-toggle {
            display: flex;
            gap: 0.5rem;
            background: white;
            padding: 0.25rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .view-toggle-btn {
            padding: 0.5rem 1rem;
            border: none;
            background: transparent;
            color: #64748b;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .view-toggle-btn:hover {
            background: #f1f5f9;
            color: #667eea;
        }

        .view-toggle-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        /* Card View Styles */
        .cards-container {
            display: none;
            gap: 1.5rem;
        }

        .cards-container.active {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        }

        .task-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid #e2e8f0;
        }

        .task-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
        }

        .card-header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .card-task-id {
            font-weight: 600;
            color: #667eea;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .card-priority {
            color: #fbbf24;
            font-size: 0.9rem;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .card-description {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 1rem;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .card-meta-item {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.85rem;
            color: #64748b;
        }

        .card-meta-item i {
            font-size: 1rem;
        }

        /* Table View Styles */
        .table-view {
            display: none;
        }

        .table-view.active {
            display: block;
        }

        .task-table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .task-table {
            margin-bottom: 0;
        }

        .task-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            transform: scale(1.002);
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
            max-width: 250px;
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
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
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

        /* Pagination */
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
        @media (max-width: 768px) {
            .cards-container.active {
                grid-template-columns: 1fr;
            }

            .task-table-container {
                overflow-x: auto;
            }

            .task-table {
                min-width: 800px;
            }
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
                        <!-- View Toggle -->
                        <div class="view-toggle">
                            <button class="view-toggle-btn active" onclick="switchView('table')" id="tableViewBtn">
                                <i class="bi bi-table"></i>
                                <span class="d-none d-sm-inline">ตาราง</span>
                            </button>
                            <button class="view-toggle-btn" onclick="switchView('card')" id="cardViewBtn">
                                <i class="bi bi-grid-3x3-gap"></i>
                                <span class="d-none d-sm-inline">การ์ด</span>
                            </button>
                        </div>

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

            <!-- Table View -->
            <div class="table-view active" id="tableView">
                <div class="task-table-container">
                    <table class="table task-table">
                        <thead>
                            <tr>
                                <th style="width: 8%;">รหัสงาน</th>
                                <th style="width: 32%;">ชื่องาน</th>
                                <th style="width: 14%;">หมวดหมู่</th>
                                <th style="width: 16%;">สถานะ</th>
                                <th style="width: 10%;">ความเร่งด่วน</th>
                                <th style="width: 20%;">วันที่สร้าง</th>
                            </tr>
                        </thead>
                        <tbody id="taskTableBody">
                            <!-- Tasks will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card View -->
            <div class="cards-container" id="cardView">
                <!-- Cards will be loaded here -->
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
        const mockTasks = <?php echo json_encode($topic); ?>;

        let allTasks = [...mockTasks];
        let filteredTasks = [...mockTasks];
        let currentPage = 1;
        let itemsPerPage = 10;
        let currentView = '<?= !empty($_SESSION['style_show']) ? $_SESSION['style_show'] : 'table' ?>'; // 'table' or 'card'

        function getStatusName(status) {
            const statuses = {
                'pending': '<i class="bi bi-clock"></i>รอดำเนินการ',
                'in-progress': '<i class="bi bi-arrow-repeat"></i>กำลังดำเนินการ',
                'completed': '<i class="bi bi-check-circle-fill"></i>เสร็จสิ้น'
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

        function switchView(view) {
            currentView = view;
            if (view === 'table') {
                <?php $_SESSION['style_show'] = 'table'; ?>
            } else {
                <?php $_SESSION['style_show'] = 'card'; ?>
            }

            // Update buttons
            document.getElementById('tableViewBtn').classList.toggle('active', view === 'table');
            document.getElementById('cardViewBtn').classList.toggle('active', view === 'card');

            // Update views
            document.getElementById('tableView').classList.toggle('active', view === 'table');
            document.getElementById('cardView').classList.toggle('active', view === 'card');

            renderTasks();
        }

        function renderTableView(tasksToShow) {
            const tbody = document.getElementById('taskTableBody');

            if (tasksToShow.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="empty-state">
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
                            <i class="bi bi-hash"></i>Task-${task.id.toString().padStart(4, '0')}
                        </div>
                    </td>
                    <td class="task-title-cell">
                        <div class="task-title-text">${task.title}</div>
                        <div class="task-description-preview">${task.description}</div>
                    </td>
                    <td>
                        <span class="category-badge">
                            <i class="bi bi-tag me-1"></i>${task.category}
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
                </tr>
            `).join('');
        }

        function renderCardView(tasksToShow) {
            const cardContainer = document.getElementById('cardView');

            if (tasksToShow.length === 0) {
                cardContainer.innerHTML = `
                    <div class="empty-state" style="grid-column: 1/-1;">
                        <i class="bi bi-inbox"></i>
                        <h5 class="mt-3">ไม่พบงานที่ค้นหา</h5>
                        <p>ลองเปลี่ยนคำค้นหาหรือกรองข้อมูลใหม่</p>
                    </div>
                `;
                return;
            }

            cardContainer.innerHTML = tasksToShow.map(task => `
                <div class="task-card" onclick="viewTask('${task.encrypt_id}')">
                    <div class="card-header-section">
                        <div class="card-task-id">
                            <i class="bi bi-hash"></i>Task-${task.id.toString().padStart(4, '0')}
                        </div>
                        <div class="card-priority" title="${task.importance} ดาว">
                            ${generateStars(task.importance)}
                        </div>
                    </div>
                    
                    <div class="card-title">${task.title}</div>
                    <div class="card-description">${task.description}</div>
                    
                    <div class="card-meta">
                        <div class="card-meta-item">
                            <i class="bi bi-tag"></i>
                            ${task.category}
                        </div>
                        <div class="card-meta-item">
                            <span class="status-badge ${task.status}">
                                ${getStatusName(task.status)}
                            </span>
                        </div>
                        <div class="card-meta-item">
                            <i class="bi bi-calendar3"></i>
                            ${formatDate(task.created_at)}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function renderTasks() {
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const tasksToShow = filteredTasks.slice(start, end);

            if (currentView === 'table') {
                renderTableView(tasksToShow);
            } else {
                renderCardView(tasksToShow);
            }

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
                task.category.toLowerCase().includes(query)
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

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            renderTasks();
            const currentView = '<?= !empty($_SESSION['style_show']) ? $_SESSION['style_show'] : 'table' ?>';
            switchView(currentView);

        });
    </script>
</body>

</html>