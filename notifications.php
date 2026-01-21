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

$table = 'tb_notifications_c050968 nt';
$fields = 'nt.fd_notification_id, nt.fd_task_id, nt.fd_title, nt.fd_message, nt.fd_icontype, nt.fd_created_at, ntu.fd_is_read  ';
$where = 'LEFT JOIN tb_notification_users_c050968 ntu ON ntu.fd_notification_id = nt.fd_notification_id ';
$where .= 'WHERE nt.fd_is_deleted = "0" AND ntu.fd_user_id = "' . $_SESSION['user_id'] . '" AND ntu.fd_is_deleted = "0" ';
$where .= 'ORDER BY nt.fd_created_at DESC ';
$result_notification = $object->ReadData($table, $fields, $where);
$notification = [];

function timeAgoTH($datetime)//ฟังก์ชันแปลงเวลาเป็นข้อความภาษาไทย
{
    $tz   = new DateTimeZone('Asia/Bangkok');
    $now  = new DateTime('now', $tz);
    $time = new DateTime($datetime, $tz);

    $diff = $now->getTimestamp() - $time->getTimestamp();

    if ($diff < 60) {
        return 'เมื่อสักครู่';
    }

    if ($diff < 3600) {
        return floor($diff / 60) . ' นาทีที่แล้ว';
    }

    if ($diff < 86400) {
        return floor($diff / 3600) . ' ชั่วโมงที่แล้ว';
    }

    if ($diff < 604800) { // 7 วัน
        return floor($diff / 86400) . ' วันที่แล้ว';
    }

    // ---------- มากกว่า 7 วัน ----------
    $thaiMonths = [
        1 => 'ม.ค', 2 => 'ก.พ', 3 => 'มี.ค', 4 => 'เม.ย',
        5 => 'พ.ค', 6 => 'มิ.ย', 7 => 'ก.ค', 8 => 'ส.ค',
        9 => 'ก.ย', 10 => 'ต.ค', 11 => 'พ.ย', 12 => 'ธ.ค'
    ];

    $day   = $time->format('d');
    $month = $thaiMonths[(int)$time->format('m')];
    $year  = (int)$time->format('Y') + 543; // พ.ศ.
    $hour  = $time->format('H:i');

    return "{$day} {$month} {$year} {$hour}";
}

foreach ($result_notification as $row) {
    $is_read = '';
    switch ($row['fd_is_read']) { //สถานะการอ่าน: 0=ยังไม่ได้อ่าน, 1=อ่านแล้ว
        case '0':
            $is_read  = false; // ยังไม่ได้อ่าน
            break;
        case '1':
            $is_read  = true; // อ่านแล้ว
            break;
        default:
            $is_read  = false; // ค่าเริ่มต้น
            break;
    }
    $notification[] = [
        'id' =>  $row['fd_notification_id'], //id แจ้งเตือน
        'type' => $row['fd_icontype'], //ประเภทแจ้งเตือน
        'title' => $row['fd_title'], //หัวข้อแจ้งเตือน
        'message' => $row['fd_message'], //ข้อความแจ้งเตือน
        'time' => timeAgoTH($row['fd_created_at']), //เวลาที่แจ้งเตือน
        'isRead' =>  $is_read, //สถานะการอ่าน
        'taskId' => $row['fd_task_id'], //id งานที่เกี่ยวข้อง
        'encrypt_id' => $Encrypt->EnCrypt_pass($row['fd_task_id']), //เข้ารหัส id งาน
    ];
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การแจ้งเตือน - Topic Tracking</title>
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

        .page-subtitle {
            color: #64748b;
        }

        .notification-filters {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .filter-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .filter-tab {
            padding: 0.6rem 1.25rem;
            border-radius: 20px;
            border: 2px solid #e2e8f0;
            background: white;
            color: #64748b;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-tab:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .filter-tab.active {
            background: var(--primary-gradient);
            border-color: transparent;
            color: white;
        }

        .filter-tab .badge {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            margin-left: 0.5rem;
        }

        .filter-tab:not(.active) .badge {
            background: #e2e8f0;
            color: #64748b;
        }

        .notification-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .notification-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .notification-item {
            display: flex;
            gap: 1rem;
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background: #f8fafc;
        }

        .notification-item.unread {
            background: rgba(102, 126, 234, 0.05);
        }

        .notification-item.unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--primary-gradient);
        }

        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .notification-icon.task {
            background: #dbeafe;
            color: #1e40af;
        }

        .notification-icon.comment {
            background: #d1fae5;
            color: #065f46;
        }

        .notification-icon.mention {
            background: #fef3c7;
            color: #92400e;
        }

        .notification-icon.system {
            background: #e0e7ff;
            color: #4338ca;
        }

        .notification-icon.deadline {
            background: #fee2e2;
            color: #dc2626;
        }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 0.5rem;
        }

        .notification-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .notification-message {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .notification-time {
            font-size: 0.85rem;
            color: #94a3b8;
            white-space: nowrap;
            margin-left: 1rem;
        }

        .notification-actions-btn {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }

        .btn-notification {
            padding: 0.4rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .unread-badge {
            position: absolute;
            top: 0.1rem;
            right: 1rem;
            background: #ef4444;
            color: white;
            font-size: 0.65rem;
            padding: 0.25rem 0.5rem;
            border-radius: 10px;
            font-weight: 600;
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

        .load-more {
            text-align: center;
            padding: 1.5rem;
        }

        .btn-load-more {
            background: white;
            border: 2px solid #e2e8f0;
            color: var(--primary-color);
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-load-more:hover {
            border-color: var(--primary-color);
            background: rgba(102, 126, 234, 0.05);
        }

        @media (max-width: 576px) {
             .unread-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #ef4444;
            color: white;
            font-size: 0.65rem;
            padding: 0.25rem 0.5rem;
            border-radius: 10px;
            font-weight: 600;
        }

            .notification-item {
                flex-direction: row;
                align-items: flex-start;
            }

            .notification-content {
                width: 100%;
            }

            /* ให้ header เรียงบน-ล่าง ไม่แย่งพื้นที่ */
            .notification-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }

            /* เวลาไปอยู่บรรทัดล่าง */
            .notification-time {
                margin-left: 0;
                font-size: 0.75rem;
            }

            /* ข้อความกินเต็ม */
            .notification-message {
                width: 100%;
                display: block;
                word-break: break-word;
            }

            /* ปุ่มเรียงลง */
            .notification-actions-btn {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .notification-icon {
                width: 44px;
                height: 44px;
                font-size: 1.25rem;
            }
        }


        @media (max-width: 992px) {
            .sidebar {
                left: calc(var(--sidebar-width) * -1);
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    <?php include 'menu.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="bi bi-bell me-2" style="color: var(--primary-color);"></i>
                    การแจ้งเตือน
                </h1>
                <p class="page-subtitle">ติดตามการอัปเดตและกิจกรรมทั้งหมด</p>
            </div>

            <!-- Filters -->
            <div class="notification-filters">
                <div class="filter-tabs" id="filterTabs">
                    <button class="filter-tab active" onclick="filterNotifications('all')">
                        ทั้งหมด
                        <span class="badge" id="badgeAll">15</span>
                    </button>
                    <button class="filter-tab" onclick="filterNotifications('unread')">
                        ยังไม่ได้อ่าน
                        <span class="badge" id="badgeUnread">8</span>
                    </button>
                    <button class="filter-tab" onclick="filterNotifications('task')">
                        <i class="bi bi-list-task me-1"></i>
                        งาน
                        <span class="badge" id="badgeTask">5</span>
                    </button>
                    <button class="filter-tab" onclick="filterNotifications('comment')">
                        <i class="bi bi-chat-dots me-1"></i>
                        ความคิดเห็น
                        <span class="badge" id="badgeComment">4</span>
                    </button>
                    <button class="filter-tab" onclick="filterNotifications('mention')">
                        <i class="bi bi-at me-1"></i>
                        การกล่าวถึง
                        <span class="badge" id="badgeMention">3</span>
                    </button>
                    <button class="filter-tab" onclick="filterNotifications('system')">
                        <i class="bi bi-gear me-1"></i>
                        ระบบ
                        <span class="badge" id="badgeSystem">3</span>
                    </button>
                </div>

                <div class="notification-actions">
                    <button class="btn btn-sm btn-primary" onclick="markAllAsRead()">
                        <i class="bi bi-check-all me-1"></i>
                        ทำเครื่องหมายว่าอ่านทั้งหมด
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteAllRead()">
                        <i class="bi bi-trash me-1"></i>
                        ลบที่อ่านแล้ว
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="refreshNotifications()">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        รีเฟรช
                    </button>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="notification-container" id="notificationContainer">
                <!-- Notifications will be loaded here -->
            </div>

            <!-- Load More -->
            <div class="load-more" id="loadMoreSection" style="display: none;">
                <button class="btn-load-more" onclick="loadMoreNotifications()">
                    <i class="bi bi-arrow-down-circle me-2"></i>
                    โหลดเพิ่มเติม
                </button>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }
        // Mock notifications data
        // const allNotifications = [{
        //         id: 1,
        //         type: 'task',
        //         title: 'งานใหม่ถูกมอบหมายให้คุณ',
        //         message: 'สมชาย ใจดี มอบหมายงาน "พัฒนาระบบ Login ใหม่" ให้กับคุณ',
        //         time: '5 นาทีที่แล้ว',
        //         isRead: false,
        //         taskId: 'abc123'
        //     },
        //     {
        //         id: 2,
        //         type: 'comment',
        //         title: 'ความคิดเห็นใหม่ในงานของคุณ',
        //         message: 'วิชัย รักงาน แสดงความคิดเห็นในงาน "ออกแบบ UI Dashboard"',
        //         time: '15 นาทีที่แล้ว',
        //         isRead: false,
        //         taskId: 'def456'
        //     },
        //     {
        //         id: 3,
        //         type: 'mention',
        //         title: 'คุณถูกกล่าวถึงในความคิดเห็น',
        //         message: '@คุณ สมหญิง สวยงาม กล่าวถึงคุณใน: "ขอให้ช่วยตรวจสอบ design ด้วยครับ"',
        //         time: '1 ชั่วโมงที่แล้ว',
        //         isRead: false,
        //         taskId: 'ghi789'
        //     },
        //     {
        //         id: 4,
        //         type: 'deadline',
        //         title: 'งานใกล้ครบกำหนด',
        //         message: 'งาน "จัดทำเอกสาร API Documentation" จะครบกำหนดในอีก 2 วัน',
        //         time: '2 ชั่วโมงที่แล้ว',
        //         isRead: false,
        //         taskId: 'jkl012'
        //     },
        //     {
        //         id: 5,
        //         type: 'task',
        //         title: 'สถานะงานถูกเปลี่ยน',
        //         message: 'งาน "แก้ไข Bug หน้า Profile" ถูกเปลี่ยนสถานะเป็น "เสร็จสิ้น"',
        //         time: '3 ชั่วโมงที่แล้ว',
        //         isRead: true,
        //         taskId: 'mno345'
        //     },
        //     {
        //         id: 6,
        //         type: 'comment',
        //         title: 'ความคิดเห็นใหม่',
        //         message: 'มานี มีเงิน: "ขอให้เพิ่มรายละเอียดในส่วน marketing strategy ด้วยครับ"',
        //         time: '5 ชั่วโมงที่แล้ว',
        //         isRead: false,
        //         taskId: 'pqr678'
        //     },
        //     {
        //         id: 7,
        //         type: 'system',
        //         title: 'อัปเดตระบบเสร็จสิ้น',
        //         message: 'ระบบได้รับการอัปเดตเป็นเวอร์ชัน 2.1.0 เพิ่มฟีเจอร์ใหม่หลายอย่าง',
        //         time: '1 วันที่แล้ว',
        //         isRead: true,
        //         taskId: null
        //     },
        //     {
        //         id: 8,
        //         type: 'mention',
        //         title: 'ถูกแท็กในงานใหม่',
        //         message: '@คุณ ถูกแท็กในงาน "วางแผนการตลาดออนไลน์"',
        //         time: '1 วันที่แล้ว',
        //         isRead: false,
        //         taskId: 'stu901'
        //     },
        //     {
        //         id: 9,
        //         type: 'task',
        //         title: 'งานใหม่จากทีม',
        //         message: 'ทีม Marketing สร้างงาน "วิเคราะห์ข้อมูล Campaign Q4"',
        //         time: '1 วันที่แล้ว',
        //         isRead: true,
        //         taskId: 'vwx234'
        //     },
        //     {
        //         id: 10,
        //         type: 'comment',
        //         title: 'การตอบกลับความคิดเห็น',
        //         message: 'ประเสริฐ ดีเด่น ตอบกลับความคิดเห็นของคุณในงาน "ประชุมทีม"',
        //         time: '2 วันที่แล้ว',
        //         isRead: true,
        //         taskId: 'yza567'
        //     },
        //     {
        //         id: 11,
        //         type: 'system',
        //         title: 'การสำรองข้อมูลเสร็จสิ้น',
        //         message: 'ระบบได้ทำการสำรองข้อมูลอัตโนมัติเรียบร้อยแล้ว',
        //         time: '2 วันที่แล้ว',
        //         isRead: true,
        //         taskId: null
        //     },
        //     {
        //         id: 12,
        //         type: 'deadline',
        //         title: 'เตือนความจำ: งานครบกำหนดพรุ่งนี้',
        //         message: 'งาน "ออกแบบโลโก้ใหม่" จะครบกำหนดในวันพรุ่งนี้',
        //         time: '3 วันที่แล้ว',
        //         isRead: true,
        //         taskId: 'bcd890'
        //     },
        //     {
        //         id: 13,
        //         type: 'task',
        //         title: 'งานถูกลบ',
        //         message: 'วิชัย รักงาน ลบงาน "ทดสอบระบบ Alpha"',
        //         time: '3 วันที่แล้ว',
        //         isRead: true,
        //         taskId: null
        //     },
        //     {
        //         id: 14,
        //         type: 'mention',
        //         title: 'การกล่าวถึงในงาน',
        //         message: '@คุณ ถูกกล่าวถึงในงาน "Review Code Sprint 10"',
        //         time: '4 วันที่แล้ว',
        //         isRead: false,
        //         taskId: 'efg123'
        //     },
        //     {
        //         id: 15,
        //         type: 'system',
        //         title: 'การบำรุงรักษาระบบ',
        //         message: 'ระบบจะปิดบำรุงรักษาในวันเสาร์ที่ 30 ธันวาคม เวลา 02:00-04:00 น.',
        //         time: '5 วันที่แล้ว',
        //         isRead: true,
        //         taskId: null
        //     }
        // ];
        const allNotifications = <?php echo json_encode($notification); ?>;

        let currentFilter = 'all';
        let displayedCount = 10;
        let filteredNotifications = [];

        function getIconClass(type) {
            const icons = {
                'task': 'bi-list-task', //ไอคอนงาน
                'comment': 'bi-chat-dots', //ไอคอนความคิดเห็น
                'mention': 'bi-at', //ไอคอนการกล่าวถึง
                'system': 'bi-gear', //ไอคอนระบบ
                'deadline': 'bi-exclamation-triangle' //
            };
            return icons[type] || 'bi-bell';
        }

        function renderNotifications() {
            const container = document.getElementById('notificationContainer');
            const notificationsToShow = filteredNotifications.slice(0, displayedCount);

            if (notificationsToShow.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="bi bi-bell-slash"></i>
                        <h5 class="mt-3">ไม่มีการแจ้งเตือน</h5>
                        <p>คุณไม่มีการแจ้งเตือนในขณะนี้</p>
                    </div>
                `;
                document.getElementById('loadMoreSection').style.display = 'none';
                return;
            }

            const html = notificationsToShow.map(notification => `
                <div class="notification-item ${!notification.isRead ? 'unread' : ''}" 
                     onclick="handleNotificationClick(${notification.id})">
                    ${!notification.isRead ? '<span class="unread-badge">ใหม่</span>' : ''}
                    <div class="notification-icon ${notification.type}">
                        <i class="bi ${getIconClass(notification.type)}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-header">
                            <div>
                                <div class="notification-title">${notification.title}</div>
                                <div class="notification-message">${notification.message}</div>
                            </div>
                            <div class="notification-time">${notification.time}</div>
                        </div>
                        ${notification.taskId ? `
                            <div class="notification-actions-btn">
                                <button class="btn btn-sm btn-primary btn-notification" 
                                        onclick="event.stopPropagation(); viewTask('${notification.encrypt_id}')">
                                    <i class="bi bi-eye me-1"></i>ดูรายละเอียด
                                </button>
                                <button class="btn btn-sm btn-outline-secondary btn-notification" 
                                        onclick="event.stopPropagation(); markAsRead(${notification.id})">
                                    <i class="bi bi-check me-1"></i>ทำเครื่องหมายว่าอ่าน
                                </button>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `).join('');

            container.innerHTML = html;

            // Show/hide load more button
            if (filteredNotifications.length > displayedCount) {
                document.getElementById('loadMoreSection').style.display = 'block';
            } else {
                document.getElementById('loadMoreSection').style.display = 'none';
            }
        }

        function filterNotifications(filter) {
            currentFilter = filter;
            displayedCount = 10;

            // Update active tab
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.closest('.filter-tab').classList.add('active');

            // Filter notifications
            if (filter === 'all') {
                filteredNotifications = [...allNotifications];
            } else if (filter === 'unread') {
                filteredNotifications = allNotifications.filter(n => !n.isRead);
            } else {
                filteredNotifications = allNotifications.filter(n => n.type === filter);
            }

            renderNotifications();
        }

        function loadMoreNotifications() {
            displayedCount += 10;
            renderNotifications();
        }

        function markAllAsRead() {
            if (confirm('ทำเครื่องหมายว่าอ่านทั้งหมดหรือไม่?')) {
                allNotifications.forEach(n => n.isRead = true);
                updateBadges();
                renderNotifications();
                alert('ทำเครื่องหมายว่าอ่านทั้งหมดเรียบร้อยแล้ว');
            }
        }

        function deleteAllRead() {
            if (confirm('ลบการแจ้งเตือนที่อ่านแล้วทั้งหมดหรือไม่?')) {
                const readCount = allNotifications.filter(n => n.isRead).length;
                allNotifications.splice(0, allNotifications.length,
                    ...allNotifications.filter(n => !n.isRead));
                updateBadges();
                filterNotifications(currentFilter);
                alert(`ลบการแจ้งเตือน ${readCount} รายการเรียบร้อยแล้ว`);
            }
        }

        function refreshNotifications() {
            alert('รีเฟรชการแจ้งเตือนแล้ว');
            filterNotifications(currentFilter);
        }

        function markAsRead(id) {
            const notification = allNotifications.find(n => n.id === id);
            if (notification) {
                notification.isRead = true;
                updateBadges();
                renderNotifications();
            }
        }

        function handleNotificationClick(id) {
            markAsRead(id);
        }

        function viewTask(taskId) {
            window.location.href = `task_detail.php?taskID=${taskId}`; //ไปยังหน้ารายละเอียดงาน
        }

        function updateBadges() {
            document.getElementById('badgeAll').textContent = allNotifications.length;
            document.getElementById('badgeUnread').textContent =
                allNotifications.filter(n => !n.isRead).length;
            document.getElementById('badgeTask').textContent =
                allNotifications.filter(n => n.type === 'task').length;
            document.getElementById('badgeComment').textContent =
                allNotifications.filter(n => n.type === 'comment').length;
            document.getElementById('badgeMention').textContent =
                allNotifications.filter(n => n.type === 'mention').length;
            document.getElementById('badgeSystem').textContent =
                allNotifications.filter(n => n.type === 'system').length;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            filteredNotifications = [...allNotifications];
            updateBadges();
            renderNotifications();
        });
    </script>
</body>

</html>