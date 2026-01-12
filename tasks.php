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
        $where = 'WHERE fd_topic_creaed_by = "' . $_SESSION['user_id'] . '" AND fd_topic_active = "1" AND fd_topic_importance LIKE "[' . $_SESSION['user_id'] . ',%" OR fd_topic_importance LIKE "%,' . $_SESSION['user_id'] . ']" OR fd_topic_importance LIKE "%,' . $_SESSION['user_id'] . ',%" OR fd_topic_importance = "[' . $_SESSION['user_id'] . ']" ';
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
                            <th style="width: 8%;">รหัสงาน</th>
                            <th style="width: 32%;">ชื่องาน</th>
                            <th style="width: 14%;">หมวดหมู่</th>
                            <th style="width: 16%;">สถานะ</th>
                            <th style="width: 10%;">ความเร่งด่วน</th>
                            <th style="width: 20%;">วันที่สร้าง</th>
                            <!-- <th style="width: 10%;">รหัสงาน</th>
                            <th style="width: 30%;">ชื่องาน</th>
                            <th style="width: 12%;">หมวดหมู่</th>
                            <th style="width: 13%;">สถานะ</th>
                            <th style="width: 12%;">ความเร่งด่วน</th>
                            <th style="width: 13%;">วันที่สร้าง</th>
                            <th style="width: 10%;" class="text-center">จัดการ</th> -->
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
        const mockTasks = <?php echo json_encode($topic); ?>;

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
            //ตัวอย่างสถานะงาน
            // <!-- รอดำเนินการ -->
            // <span class="status-badge pending">
            //     <i class="bi bi-clock"></i>
            //     รอดำเนินการ
            // </span>

            // <!-- กำลังดำเนินการ -->
            // <span class="status-badge in-progress">
            //     <i class="bi bi-arrow-repeat"></i>
            //     กำลังดำเนินการ
            // </span>

            // <!-- เสร็จสิ้น -->
            // <span class="status-badge completed">
            //     <i class="bi bi-check-circle-fill"></i>
            //     เสร็จสิ้น
            // </span>

            // <!-- เลยกำหนด -->
            // <span class="status-badge overdue">
            //     <i class="bi bi-exclamation-circle-fill"></i>
            //     เลยกำหนด 2 วัน
            // </span>

            // <!-- เลยกำหนดมาก -->
            // <span class="status-badge overdue-critical">
            //     <i class="bi bi-exclamation-triangle-fill"></i>
            //     เลยกำหนด 5 วัน
            // </span>
            const showAction = false;
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
                    ${showAction ? `
                    <td class="text-center">
                        <div class="action-buttons justify-content-center">
                            <button class="btn btn-sm btn-outline-primary btn-action"
                                    onclick="event.stopPropagation(); viewTask('${task.encrypt_id}')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </td>
                    ` : ''}
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