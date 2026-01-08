<?php include 'session_check.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดงาน - Topic Tracking</title>
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
        .detail-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .task-header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }

        .task-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
        }

        .task-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: center;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #64748b;
        }

        .meta-item i {
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-badge.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-badge.in-progress {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-badge.completed {
            background: #d1fae5;
            color: #065f46;
        }

        .category-badge {
            padding: 0.4rem 0.9rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            background: #e0e7ff;
            color: #4338ca;
        }

        .priority-stars {
            color: #fbbf24;
            font-size: 1.2rem;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--primary-color);
        }

        .description-content {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            line-height: 1.8;
            color: #475569;
            white-space: pre-wrap;
        }

        .mention-tag {
            background: var(--primary-gradient);
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
            margin-bottom: 0.75rem;
            transition: all 0.3s;
        }

        .user-card:hover {
            background: #f1f5f9;
            transform: translateX(5px);
        }


        .file-card {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
        }

        .file-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
        }

        .file-icon {
            width: 56px;
            height: 56px;
            border-radius: 10px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            flex-shrink: 0;
        }

        .file-info {
            flex: 1;
            min-width: 0;
        }

        .file-name {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.3rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .file-meta {
            font-size: 0.85rem;
            color: #64748b;
        }

        .btn-download {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .comment-section {
            margin-top: 2rem;
        }

        .comment-input-area {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .comment-textarea {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
            width: 100%;
            resize: vertical;
            min-height: 100px;
            transition: all 0.3s;
        }

        .comment-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        .comment-item {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .comment-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            flex-shrink: 0;
        }

        .comment-author-info {
            flex: 1;
        }

        .comment-author {
            font-weight: 600;
            color: #1e293b;
        }

        .comment-time {
            font-size: 0.85rem;
            color: #64748b;
        }

        .comment-content {
            color: #475569;
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 1.5rem 0;
        }

        .btn-action {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            border: 2px solid;
        }

        .btn-edit {
            background: white;
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-edit:hover {
            background: var(--primary-color);
            color: white;
        }

        .btn-delete {
            background: white;
            border-color: #ef4444;
            color: #ef4444;
        }

        .btn-delete:hover {
            background: #ef4444;
            color: white;
        }

        .btn-complete {
            background: var(--primary-gradient);
            border-color: transparent;
            color: white;
        }

        .btn-complete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .activity-timeline {
            position: relative;
            padding-left: 2.5rem;
            max-height: 500px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .activity-timeline::-webkit-scrollbar {
            width: 6px;
        }

        .activity-timeline::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .activity-timeline::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 10px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 2rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -2rem;
            top: 0;
            width: 2px;
            height: 100%;
            background: #e2e8f0;
        }

        .timeline-icon {
            position: absolute;
            left: -2.5rem;
            top: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            z-index: 1;
        }

        .timeline-content {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1rem;
        }

        .timeline-time {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .timeline-text {
            color: #475569;
        }

        .comments-container {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .comments-container::-webkit-scrollbar {
            width: 8px;
        }

        .comments-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .comments-container::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 10px;
        }

        .load-more-btn {
            background: white;
            border: 2px solid #e2e8f0;
            color: var(--primary-color);
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s;
        }

        .load-more-btn:hover {
            border-color: var(--primary-color);
            background: rgba(102, 126, 234, 0.05);
        }

        .activity-load-more {
            text-align: center;
            padding: 1rem 0;
        }

        .activity-load-more button {
            background: white;
            border: 2px solid #e2e8f0;
            color: var(--primary-color);
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .activity-load-more button:hover {
            border-color: var(--primary-color);
            background: rgba(102, 126, 234, 0.05);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .task-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .task-title {
                font-size: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <?php include 'menu.php'; ?>

    <main class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <!-- <div class="mb-4">
                <h2 class="fw-bold mb-1">Task 2025-0001</h2>
                <p class="text-muted">รายละเอียด</p>
            </div> -->
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <!-- <li class="breadcrumb-item"><a href="index.php">หน้าแรก</a></li> -->
                    <li class="breadcrumb-item"><a href="tasks.php">งานทั้งหมด</a></li>
                    <li class="breadcrumb-item active">รายละเอียดงาน</li>
                </ol>
            </nav>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Task Header -->
                    <div class="detail-card">
                        <div class="task-header">
                            <div class="task-title" id="taskTitle">กำลังโหลด...</div>
                            <div class="task-meta" id="taskMeta">
                                <!-- Meta information will be loaded here -->
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <div class="section-title">
                                <i class="bi bi-file-text"></i>
                                รายละเอียด
                            </div>
                            <div class="description-content" id="taskDescription">
                                กำลังโหลดข้อมูล...
                            </div>
                        </div>

                        <!-- Files -->
                        <div class="mb-4" id="filesSection" style="display: none;">
                            <div class="section-title">
                                <i class="bi bi-paperclip"></i>
                                ไฟล์แนบ (<span id="fileCount">0</span>)
                            </div>
                            <div id="filesList">
                                <!-- Files will be loaded here -->
                            </div>
                        </div>

                        <!-- Comments -->
                        <div class="comment-section">
                            <div class="section-title">
                                <i class="bi bi-chat-dots"></i>
                                ความคิดเห็น (<span id="commentCount">0</span>)
                            </div>

                            <div class="comment-input-area">
                                <textarea class="comment-textarea" id="commentInput"
                                    placeholder="เพิ่มความคิดเห็น..."></textarea>
                                <div class="d-flex justify-content-end mt-3">
                                    <button class="btn btn-primary" onclick="addComment()">
                                        <i class="bi bi-send me-2"></i>
                                        ส่งความคิดเห็น
                                    </button>
                                </div>
                            </div>

                            <div id="commentsList">
                                <!-- Comments will be loaded here -->
                            </div>

                            <button class="load-more-btn" id="loadMoreComments" style="display: none;" onclick="loadMoreComments()">
                                <i class="bi bi-arrow-down-circle me-2"></i>
                                โหลดความคิดเห็นเพิ่มเติม
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Actions -->
                    <div class="detail-card">
                        <div class="section-title">
                            <i class="bi bi-lightning"></i>
                            การจัดการ
                        </div>
                        <div class="action-buttons flex-column">
                            <a href="task_edit.php" class="btn btn-action btn-edit">
                                <!-- <button type="button" class="btn btn-action btn-edit"> -->
                                <i class="bi bi-pencil me-2"></i>
                                แก้ไขงาน
                                <!-- </button> -->
                            </a>
                            <button class="btn btn-action btn-complete">
                                <i class="bi bi-check-circle me-2"></i>
                                เสร็จสิ้น
                            </button>
                            <button class="btn btn-action btn-delete">
                                <i class="bi bi-trash me-2"></i>
                                ลบงาน
                            </button>
                        </div>
                    </div>

                    <!-- Team Members -->
                    <div class="detail-card">
                        <div class="section-title">
                            <i class="bi bi-people"></i>
                            ผู้เกี่ยวข้อง
                        </div>
                        <div id="teamMembers">
                            <!-- Team members will be loaded here -->
                        </div>
                    </div>

                    <!-- Activity -->
                    <div class="detail-card">
                        <div class="section-title">
                            <i class="bi bi-clock-history"></i>
                            กิจกรรม
                        </div>
                        <div class="activity-timeline" id="activityTimeline">
                            <!-- Activity will be loaded here -->
                        </div>
                        <div class="activity-load-more" id="loadMoreActivity" style="display: none;">
                            <button onclick="loadMoreActivity()">
                                <i class="bi bi-arrow-down-circle me-2"></i>
                                โหลดเพิ่มเติม
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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

        // Close sidebar when clicking on menu item (mobile)
        document.querySelectorAll('.menu-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 992) {
                    toggleSidebar();
                }
            });
        });
    </script>
    <script>
        // Mock task data with more comments and activities
        const taskData = {
            id: 1,
            title: "พัฒนาระบบ Login ใหม่",
            description: "ออกแบบและพัฒนาระบบ Login ที่รองรับ OAuth 2.0\n\nต้องทำให้รองรับ:\n- Google Login\n- Facebook Login\n- Email/Password\n\nแท็ก: @สมชาย ใจดี @สมหญิง สวยงาม",
            category: "development",
            status: "in-progress",
            importance: 5,
            created_at: "2024-12-20 10:30:00",
            updated_at: "2024-12-23 14:20:00",
            team: [{
                    id: 1,
                    name: 'สมชาย ใจดี',
                    role: 'Developer',
                    avatar: 'SC'
                },
                {
                    id: 2,
                    name: 'สมหญิง สวยงาม',
                    role: 'Designer',
                    avatar: 'SS'
                },
                {
                    id: 3,
                    name: 'วิชัย รักงาน',
                    role: 'Project Manager',
                    avatar: 'WR'
                }
            ],
            files: [{
                    id: 1,
                    name: 'login-mockup.pdf',
                    size: 1024000,
                    type: 'application/pdf'
                },
                {
                    id: 2,
                    name: 'design-spec.docx',
                    size: 512000,
                    type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                },
                {
                    id: 3,
                    name: 'screenshot.png',
                    size: 2048000,
                    type: 'image/png'
                }
            ],
            allComments: [{
                    id: 1,
                    author: {
                        name: 'สมชาย ใจดี',
                        avatar: 'SC'
                    },
                    content: 'เริ่มทำ mockup เรียบร้อยแล้วครับ รอ review',
                    created_at: '2024-12-21 09:15:00'
                },
                {
                    id: 2,
                    author: {
                        name: 'วิชัย รักงาน',
                        avatar: 'WR'
                    },
                    content: 'ดูดีมากครับ ขอเพิ่ม feature forgot password ด้วยนะครับ',
                    created_at: '2024-12-21 14:30:00'
                },
                {
                    id: 3,
                    author: {
                        name: 'สมหญิง สวยงาม',
                        avatar: 'SS'
                    },
                    content: 'ผมเพิ่ม UI สำหรับ forgot password แล้วครับ',
                    created_at: '2024-12-21 16:45:00'
                },
                {
                    id: 4,
                    author: {
                        name: 'สมชาย ใจดี',
                        avatar: 'SC'
                    },
                    content: 'OAuth Google ทำงานได้แล้วครับ กำลังทดสอบ Facebook',
                    created_at: '2024-12-22 10:20:00'
                },
                {
                    id: 5,
                    author: {
                        name: 'วิชัย รักงาน',
                        avatar: 'WR'
                    },
                    content: 'ดีมากครับ ขอให้ทดสอบ edge cases ด้วยนะครับ',
                    created_at: '2024-12-22 11:30:00'
                },
                {
                    id: 6,
                    author: {
                        name: 'สมหญิง สวยงาม',
                        avatar: 'SS'
                    },
                    content: 'พบ bug ตอน login ด้วย Facebook บนมือถือครับ',
                    created_at: '2024-12-22 14:15:00'
                },
                {
                    id: 7,
                    author: {
                        name: 'สมชาย ใจดี',
                        avatar: 'SC'
                    },
                    content: 'แก้ bug Facebook login เรียบร้อยแล้วครับ',
                    created_at: '2024-12-22 16:30:00'
                },
                {
                    id: 8,
                    author: {
                        name: 'วิชัย รักงาน',
                        avatar: 'WR'
                    },
                    content: 'ขอให้เพิ่ม unit test ด้วยครับ',
                    created_at: '2024-12-23 09:00:00'
                },
                {
                    id: 9,
                    author: {
                        name: 'สมชาย ใจดี',
                        avatar: 'SC'
                    },
                    content: 'เพิ่ม unit test ครบทุก function แล้วครับ coverage 95%',
                    created_at: '2024-12-23 14:20:00'
                },
                {
                    id: 10,
                    author: {
                        name: 'วิชัย รักงาน',
                        avatar: 'WR'
                    },
                    content: 'เยี่ยมมากครับ พร้อม deploy แล้ว',
                    created_at: '2024-12-23 15:45:00'
                }
            ],
            allActivity: [{
                    type: 'created',
                    text: 'สร้างงานโดย สมชาย ใจดี',
                    time: '2024-12-20 10:30:00'
                },
                {
                    type: 'status',
                    text: 'เปลี่ยนสถานะเป็น กำลังดำเนินการ',
                    time: '2024-12-20 11:00:00'
                },
                {
                    type: 'comment',
                    text: 'เพิ่มความคิดเห็นโดย สมชาย ใจดี',
                    time: '2024-12-21 09:15:00'
                },
                {
                    type: 'comment',
                    text: 'เพิ่มความคิดเห็นโดย วิชัย รักงาน',
                    time: '2024-12-21 14:30:00'
                },
                {
                    type: 'comment',
                    text: 'เพิ่มความคิดเห็นโดย สมหญิง สวยงาม',
                    time: '2024-12-21 16:45:00'
                },
                {
                    type: 'file',
                    text: 'อัปโหลดไฟล์ login-mockup.pdf',
                    time: '2024-12-22 09:00:00'
                },
                {
                    type: 'comment',
                    text: 'เพิ่มความคิดเห็นโดย สมชาย ใจดี',
                    time: '2024-12-22 10:20:00'
                },
                {
                    type: 'comment',
                    text: 'เพิ่มความคิดเห็นโดย วิชัย รักงาน',
                    time: '2024-12-22 11:30:00'
                },
                {
                    type: 'file',
                    text: 'อัปโหลดไฟล์ design-spec.docx',
                    time: '2024-12-22 13:00:00'
                },
                {
                    type: 'comment',
                    text: 'เพิ่มความคิดเห็นโดย สมหญิง สวยงาม',
                    time: '2024-12-22 14:15:00'
                },
                {
                    type: 'comment',
                    text: 'เพิ่มความคิดเห็นโดย สมชาย ใจดี',
                    time: '2024-12-22 16:30:00'
                },
                {
                    type: 'file',
                    text: 'อัปโหลดไฟล์ screenshot.png',
                    time: '2024-12-22 17:00:00'
                },
                {
                    type: 'comment',
                    text: 'เพิ่มความคิดเห็นโดย วิชัย รักงาน',
                    time: '2024-12-23 09:00:00'
                },
                {
                    type: 'comment',
                    text: 'เพิ่มความคิดเห็นโดย สมชาย ใจดี',
                    time: '2024-12-23 14:20:00'
                },
                {
                    type: 'comment',
                    text: 'เพิ่มความคิดเห็นโดย วิชัย รักงาน',
                    time: '2024-12-23 15:45:00'
                }
            ]
        };

        // Pagination variables
        let commentsPerPage = 5;
        let currentCommentsPage = 1;
        let activityPerPage = 5;
        let currentActivityPage = 1;

        function loadTaskDetail() {
            // Task Title
            document.getElementById('taskTitle').textContent = taskData.title;

            // Task Meta
            const metaHtml = `
                <span class="status-badge ${taskData.status}">
                    <i class="bi bi-circle-fill"></i>
                    ${getStatusName(taskData.status)}
                </span>
                <span class="category-badge">
                    <i class="bi bi-tag me-1"></i>
                    ${getCategoryName(taskData.category)}
                </span>
                <span class="priority-stars" title="${taskData.importance} ดาว">
                    ${generateStars(taskData.importance)}
                </span>
                <span class="meta-item">
                    <i class="bi bi-hash"></i>
                    TASK-2024-${taskData.id.toString().padStart(4, '0')}
                </span>
                <span class="meta-item">
                    <i class="bi bi-calendar3"></i>
                    ${formatDate(taskData.created_at)}
                </span>
                <span class="meta-item">
                    <i class="bi bi-flag-fill"></i>
                    ${formatDate(taskData.created_at)}
                </span>
            `;
            document.getElementById('taskMeta').innerHTML = metaHtml;

            // Description
            let description = taskData.description;
            taskData.team.forEach(member => {
                const mentionPattern = `@${member.name}`;
                description = description.replace(
                    new RegExp(mentionPattern, 'g'),
                    `<span class="mention-tag">${mentionPattern}</span>`
                );
            });
            document.getElementById('taskDescription').innerHTML = description;

            // Team Members
            const teamHtml = taskData.team.map(member => `
                <div class="user-card">
                    <div class="user-avatar">${member.avatar}</div>
                    <div class="user-info">
                        <div class="user-name">${member.name}</div>
                        <div class="user-role">${member.role}</div>
                    </div>
                </div>
            `).join('');
            document.getElementById('teamMembers').innerHTML = teamHtml;

            // Files
            if (taskData.files.length > 0) {
                document.getElementById('filesSection').style.display = 'block';
                document.getElementById('fileCount').textContent = taskData.files.length;

                const filesHtml = taskData.files.map(file => `
                    <div class="file-card">
                        <div class="file-icon">
                            <i class="bi bi-${getFileIcon(file.type)}"></i>
                        </div>
                        <div class="file-info">
                            <div class="file-name">${file.name}</div>
                            <div class="file-meta">${formatFileSize(file.size)}</div>
                        </div>
                        <button class="btn-download" onclick="downloadFile(${file.id})">
                            <i class="bi bi-download"></i>
                        </button>
                    </div>
                `).join('');
                document.getElementById('filesList').innerHTML = filesHtml;
            }

            // Comments
            loadComments();

            // Activity
            loadActivity();
        }

        function loadComments() {
            const totalComments = taskData.allComments.length;
            document.getElementById('commentCount').textContent = totalComments;

            const commentsToShow = taskData.allComments.slice(0, currentCommentsPage * commentsPerPage);

            if (commentsToShow.length === 0) {
                document.getElementById('commentsList').innerHTML = `
                    <div class="empty-state">
                        <i class="bi bi-chat-dots"></i>
                        <p>ยังไม่มีความคิดเห็น</p>
                    </div>
                `;
                return;
            }

            const commentsHtml = `
                <div class="comments-container">
                    ${commentsToShow.map(comment => `
                        <div class="comment-item">
                            <div class="comment-header">
                                <div class="comment-avatar">${comment.author.avatar}</div>
                                <div class="comment-author-info">
                                    <div class="comment-author">${comment.author.name}</div>
                                    <div class="comment-time">${formatDate(comment.created_at)}</div>
                                </div>
                            </div>
                            <div class="comment-content">${comment.content}</div>
                        </div>
                    `).join('')}
                </div>
            `;

            document.getElementById('commentsList').innerHTML = commentsHtml;

            // Show/hide load more button
            const loadMoreBtn = document.getElementById('loadMoreComments');
            if (commentsToShow.length < totalComments) {
                loadMoreBtn.style.display = 'block';
                loadMoreBtn.innerHTML = `
                    <i class="bi bi-arrow-down-circle me-2"></i>
                    โหลดความคิดเห็นเพิ่มเติม (${totalComments - commentsToShow.length} รายการ)
                `;
            } else {
                loadMoreBtn.style.display = 'none';
            }
        }

        function loadMoreComments() {
            currentCommentsPage++;
            loadComments();
        }

        function addComment() {
            const commentInput = document.getElementById('commentInput');
            const content = commentInput.value.trim();

            if (!content) {
                alert('กรุณาใส่ความคิดเห็น');
                return;
            }

            const newComment = {
                id: taskData.allComments.length + 1,
                author: {
                    name: 'ผู้ใช้ปัจจุบัน',
                    avatar: 'YO'
                },
                content: content,
                created_at: new Date().toISOString()
            };

            taskData.allComments.unshift(newComment);
            currentCommentsPage = 1;
            loadComments();
            commentInput.value = '';

            // Add to activity
            taskData.allActivity.unshift({
                type: 'comment',
                text: 'เพิ่มความคิดเห็นโดย ผู้ใช้ปัจจุบัน',
                time: new Date().toISOString()
            });
            loadActivity();

            alert('เพิ่มความคิดเห็นสำเร็จ');
        }

        function loadActivity() {
            const totalActivity = taskData.allActivity.length;
            const activityToShow = taskData.allActivity.slice(0, currentActivityPage * activityPerPage);

            const activityHtml = activityToShow.map(item => `
                <div class="timeline-item">
                    <div class="timeline-icon">
                        <i class="bi bi-${getActivityIcon(item.type)}"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-time">${formatDate(item.time)}</div>
                        <div class="timeline-text">${item.text}</div>
                    </div>
                </div>
            `).join('');

            document.getElementById('activityTimeline').innerHTML = activityHtml;

            // Show/hide load more button
            const loadMoreBtn = document.getElementById('loadMoreActivity');
            if (activityToShow.length < totalActivity) {
                loadMoreBtn.style.display = 'block';
                loadMoreBtn.querySelector('button').innerHTML = `
                    <i class="bi bi-arrow-down-circle me-2"></i>
                    โหลดเพิ่มเติม (${totalActivity - activityToShow.length} รายการ)
                `;
            } else {
                loadMoreBtn.style.display = 'none';
            }
        }

        function loadMoreActivity() {
            currentActivityPage++;
            loadActivity();
        }

        function downloadFile(fileId) {
            alert(`กำลังดาวน์โหลดไฟล์ ID: ${fileId}`);
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

        function generateStars(importance) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                stars += i <= importance ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
            }
            return stars;
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

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function getFileIcon(mimeType) {
            if (!mimeType) return 'file-earmark';
            if (mimeType.startsWith('image/')) return 'file-earmark-image';
            if (mimeType.includes('pdf')) return 'file-earmark-pdf';
            if (mimeType.includes('word')) return 'file-earmark-word';
            if (mimeType.includes('excel')) return 'file-earmark-excel';
            return 'file-earmark';
        }

        function getActivityIcon(type) {
            const icons = {
                'created': 'plus-circle',
                'status': 'arrow-repeat',
                'comment': 'chat-dots',
                'file': 'paperclip'
            };
            return icons[type] || 'circle';
        }

        // Load task detail on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadTaskDetail();
        });
    </script>
</body>

</html>