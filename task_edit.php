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
$TaskID = intval($Encrypt->DeCrypt_pass($_GET['taskID']));
$TaskID_Encode = $_GET['taskID'];
if (empty($TaskID)) {
    header('Location: tasks.php');
    exit();
}
// ดึงข้อมูลงาน
$table = 'tb_topics_c050968';
$fields = 'fd_topic_id, fd_topic_title, fd_topic_detail, fd_topic_category, fd_topic_mentioned, fd_topic_status, fd_topic_participant, fd_topic_created_by, fd_topic_due_date, fd_topic_importance, fd_topic_private, fd_topic_active, fd_topic_created_at ';
$where = 'WHERE fd_topic_id = "' . $TaskID . '" ';
$result_topic = $object->ReadData($table, $fields, $where);
$result_participant = trim($result_topic[0]['fd_topic_participant'], '[]'); // ลบ [] ออก

$table = 'tb_users_c050968 user';
$fields = 'user.fd_user_id, user.fd_user_fullname, dept.fd_dept_name ';
$where = 'LEFT JOIN tb_departments_c050968 dept ON dept.fd_dept_id = user.fd_user_dept ';
switch ($_SESSION['user_status']) {
    //admin / executive → เห็น user ทุกคน
    case 'admin':
    case 'executive':
        $where .= 'WHERE user.fd_user_status = "user" AND user.fd_user_active = "1"';
        break;
    //user → เห็นเฉพาะคนอื่นในแผนกเดียวกัน มีสถานะเป็น user และ active
    case 'user':
    default:
        $where .= 'WHERE user.fd_user_status = "user" AND user.fd_user_dept = "' . $_SESSION['user_dept'] . '" AND user.fd_user_active = "1"';
        break;
}
$result_user = $object->ReadData($table, $fields, $where);
foreach ($result_user as &$row) {
    $fullname = $row['fd_user_fullname'];
    $nameParts = preg_split('/\s+/', trim($fullname));

    $avatar = '';
    foreach ($nameParts as $p) {
        $avatar .= mb_substr($p, 0, 1, 'UTF-8');
    }

    $row['avatar'] = $avatar;
}
unset($row); // สำคัญ ป้องกัน bug

$table = 'tb_topic_files_c050968';
$fields = 'fd_file_id, fd_file_original_name , fd_file_path, fd_file_size, fd_file_type ';
$where = 'WHERE fd_file_task_id = "' . $TaskID . '" AND fd_file_active = "1" ';
$result_files = $object->ReadData($table, $fields, $where);

//json
//งาน + ไฟล์เดิม
$task = $result_topic[0];
$existingFiles = [];
if (is_array($result_files)) {
    foreach ($result_files as $file) {
        $existingFiles[] = [
            'id'   => $file['fd_file_id'],
            'name' => $file['fd_file_original_name'],
            'size' => $file['fd_file_size'],
            'type' => $file['fd_file_type'],
            'path' => $file['fd_file_path']
        ];
    }
}
$existingTask = [
    'id' => (int)$task['fd_topic_id'],
    'title' => $task['fd_topic_title'],
    'description' => $task['fd_topic_detail'],
    'category' => $task['fd_topic_category'],
    'status' => $task['fd_topic_status'],
    'importance' => (int)$task['fd_topic_importance'],
    'due_date' => $task['fd_topic_due_date'] ?? null,
    'mentionedUsers' => $task['fd_topic_mentioned'] ? json_decode($task['fd_topic_mentioned'], true) : [],
    'additionalUsers' => $task['fd_topic_participant'] ? json_decode($task['fd_topic_participant'], true) : [],
];
$existingTask['existingFiles'] = $existingFiles;

// เตรียมข้อมูลผู้ใช้สำหรับ mention
$users = [];
foreach ($result_user as $row) {
    $fullname = $row['fd_user_fullname'];
    $nameParts = explode(' ', $fullname);

    $avatar = '';
    foreach ($nameParts as $p) {
        $avatar .= mb_substr($p, 0, 1); //ตัวแรกของชื่อและสกุล
    }

    $users[] = [
        'id'     => $row['fd_user_id'],
        'name'   => $fullname,
        'role'   => $row['fd_dept_name'], // ชื่อฝ่ายงาน
        'avatar' => $avatar
    ];
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขงาน - Topic Tracking</title>
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
        .form-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-section-title i {
            color: var(--primary-color);
        }

        .form-label {
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        .mention-textarea-wrapper {
            position: relative;
        }

        .mention-dropdown {
            position: absolute;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            max-height: 280px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            min-width: 280px;
        }

        .mention-dropdown.show {
            display: block;
        }

        .mention-item {
            padding: 0.875rem 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
            border-bottom: 1px solid #f1f5f9;
        }

        .mention-item:last-child {
            border-bottom: none;
        }

        .mention-item:hover,
        .mention-item.selected {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, transparent 100%);
        }

        .mention-avatar {
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

        .mention-info {
            flex: 1;
        }

        .mention-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
        }

        .mention-role {
            font-size: 0.8rem;
            color: #64748b;
        }

        .file-upload-area {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            background: #f8fafc;
            transition: all 0.3s;
            cursor: pointer;
        }

        .file-upload-area:hover {
            border-color: var(--primary-color);
            background: #f1f5f9;
        }

        .file-upload-area.dragover {
            border-color: var(--primary-color);
            background: rgba(102, 126, 234, 0.1);
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 3rem;
            color: #94a3b8;
            margin-bottom: 1rem;
        }

        .file-list {
            margin-top: 1.5rem;
        }

        .file-item {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
        }

        .file-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .file-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .file-details {
            flex: 1;
            min-width: 0;
        }

        .file-name {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .file-size {
            font-size: 0.85rem;
            color: #64748b;
        }

        .file-badge {
            padding: 0.25rem 0.6rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .file-badge.existing {
            background: #dbeafe;
            color: #1e40af;
        }

        .file-badge.new {
            background: #d1fae5;
            color: #065f46;
        }

        .btn-remove-file {
            background: transparent;
            border: none;
            color: #ef4444;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .btn-remove-file:hover {
            background: #fee2e2;
        }

        .star-rating {
            display: flex;
            gap: 0.5rem;
            font-size: 2rem;
        }

        .star {
            cursor: pointer;
            color: #cbd5e1;
            transition: all 0.2s;
        }

        .star:hover,
        .star.active {
            color: #fbbf24;
            transform: scale(1.1);
        }

        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            min-height: 50px;
            background: #f8fafc;
        }

        .tag-item {
            background: var(--primary-gradient);
            color: white;
            padding: 0.4rem 0.75rem;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .tag-remove {
            cursor: pointer;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        .tag-remove:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .user-selection-list {
            max-height: 300px;
            overflow-y: auto;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
        }

        .user-selection-item {
            padding: 0.875rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            border-bottom: 1px solid #e2e8f0;
        }

        .user-selection-item:last-child {
            border-bottom: none;
        }

        .user-selection-item:hover {
            background: #f1f5f9;
        }

        .user-selection-item.selected {
            background: rgba(102, 126, 234, 0.1);
        }

        .user-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--primary-color);
        }

        .user-selection-avatar {
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

        .user-selection-info {
            flex: 1;
        }

        .user-selection-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
        }

        .user-selection-role {
            font-size: 0.8rem;
            color: #64748b;
        }

        .no-results {
            padding: 2rem;
            text-align: center;
            color: #94a3b8;
        }

        .action-buttons {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 16px;
            box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-submit {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.875rem 2.5rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .file-size-info {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 0.5rem;
        }

        .size-warning {
            color: #ef4444;
            font-weight: 600;
        }

        .size-ok {
            color: #10b981;
        }

        .alert-info {
            background: #e0e7ff;
            border: 1px solid #c7d2fe;
            color: #4338ca;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .user-selection-item.disabled {
            /* padding: 0.875rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            border-bottom: 1px solid #e2e8f0; */
            opacity: 0.45;
            cursor: not-allowed;
            pointer-events: none;
        }

        .tag-item.disabled {
            opacity: 0.6;
        }

        .tag-item.disabled .tag-remove {
            display: none;
        }
    </style>
    <!-- รอลบ⬇️ -->
    <style>
        /* เพิ่มหลัง .btn-remove-file:hover */

        .file-item.pending-delete {
            opacity: 0.6;
            background: #fef2f2;
            border-color: #fecaca;
        }

        .file-badge.pending {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-undo-delete {
            background: transparent;
            border: none;
            color: #10b981;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .btn-undo-delete:hover {
            background: #d1fae5;
        }
    </style>
</head>

<body>
    <?php include 'menu.php'; ?>

    <main class="main-content">
        <div class="container-fluid">
            <div class="mb-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <!-- <li class="breadcrumb-item"><a href="index.php">หน้าแรก</a></li> -->
                        <li class="breadcrumb-item"><a href="tasks.php">งานทั้งหมด</a></li>
                        <li class="breadcrumb-item"><a href="#" id="taskDetailLink">รายละเอียดงาน</a></li>
                        <li class="breadcrumb-item active">แก้ไขงาน</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-pencil-square me-2" style="color: var(--primary-color);"></i>
                    แก้ไขงาน
                </h2>
                <p class="text-muted">แก้ไขข้อมูลงานของคุณ</p>
            </div>

            <div class="alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>หมายเหตุ:</strong> ไฟล์เดิมจะถูกเก็บไว้ คุณสามารถเพิ่มไฟล์ใหม่หรือลบไฟล์เดิมได้
            </div>

            <form id="editTaskForm">
                <div class="form-card">
                    <div class="form-section-title">
                        <i class="bi bi-info-circle"></i>
                        ข้อมูลพื้นฐาน
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">
                                ชื่อหัวข้องาน <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="taskTitle"
                                placeholder="ระบุชื่อหัวข้องาน..." required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                หมวดหมู่ <span class="text-danger">ไม่บังคับ</span>
                            </label>
                            <!-- <select class="form-select" id="taskCategory" name="taskCategory" required>
                                <option value="">เลือกหมวดหมู่</option>
                                <option value="development">พัฒนาระบบ</option>
                                <option value="design">ออกแบบ</option>
                                <option value="marketing">การตลาด</option>
                                <option value="meeting">ประชุม</option>
                                <option value="other">อื่นๆ</option>
                            </select> -->
                            <input type="text" class="form-control" id="taskCategory" name="taskCategory" placeholder="ระบุชื่อหมวดหมู่">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            รายละเอียด <span class="text-danger">*</span>
                        </label>
                        <div class="mention-textarea-wrapper">
                            <textarea class="form-control" id="taskDescription" rows="6"
                                placeholder="พิมพ์ @ เพื่อแท็กผู้ใช้งาน..." required></textarea>
                            <div class="mention-dropdown" id="mentionDropdown"></div>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            พิมพ์ @ ตามด้วยชื่อเพื่อแท็กผู้ใช้งาน
                        </small>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                สถานะ <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="taskStatus" name="taskStatus" required>
                                <option value="">เลือกสถานะ</option>
                                <option value="pending">รอดำเนินการ</option>
                                <option value="in-progress">กำลังดำเนินการ</option>
                                <option value="completed">เสร็จสิ้น</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                วันที่ครบกำหนด(ไม่บังคับ)
                            </label>
                            <input type="date" class="form-control" id="taskDueDate" name="taskDueDate">
                            <small class="text-muted d-block mt-2">
                                ไม่มีวันครบกำหนดถ้าปล่อยว่างไว้
                            </small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                ความเร่งด่วน <span class="text-danger">*</span>
                            </label>
                            <div class="star-rating" id="starRating">
                                <span class="star" data-value="1">★</span>
                                <span class="star" data-value="2">★</span>
                                <span class="star" data-value="3">★</span>
                                <span class="star" data-value="4">★</span>
                                <span class="star" data-value="5">★</span>
                            </div>
                            <input type="hidden" id="taskImportance" name="taskImportance" value="0" required>
                            <small class="text-muted d-block mt-2" id="importanceLabel">
                                กรุณาเลือกระดับความเร่งด่วน
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-card">
                    <div class="form-section-title">
                        <i class="bi bi-people"></i>
                        ผู้ที่เกี่ยวข้อง
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ผู้ที่ถูกแท็กในคำอธิบาย</label>
                        <div class="tags-container" id="mentionedUsersContainer">
                            <small class="text-muted">ยังไม่มีผู้ใช้ที่ถูกแท็ก</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">เพิ่มผู้เกี่ยวข้องเพิ่มเติม (ไม่บังคับ)</label>

                        <div class="mb-2">
                            <input type="text" class="form-control" id="userSearchInput"
                                placeholder="🔍 พิมพ์ชื่อเพื่อค้นหาผู้ใช้...">
                        </div>

                        <div class="user-selection-list" id="userSelectionList"></div>

                        <div class="mt-3">
                            <label class="form-label text-muted small">ผู้เกี่ยวข้องที่เลือก:</label>
                            <div class="tags-container" id="additionalUsersContainer">
                                <small class="text-muted">ยังไม่ได้เลือกผู้ใช้เพิ่มเติม</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-card">
                    <div class="form-section-title">
                        <i class="bi bi-paperclip"></i>
                        ไฟล์แนบ
                    </div>

                    <div id="existingFilesSection" style="display: none;">
                        <h6 class="mb-3">ไฟล์เดิม</h6>
                        <div id="existingFilesList" class="file-list"></div>
                    </div>

                    <h6 class="mb-3 mt-4">เพิ่มไฟล์ใหม่</h6>
                    <div class="file-upload-area" id="fileUploadArea">
                        <i class="bi bi-cloud-upload upload-icon"></i>
                        <h5>ลากไฟล์มาวางที่นี่ หรือคลิกเพื่อเลือกไฟล์</h5>
                        <p class="text-muted mb-2">รองรับไฟล์ทุกประเภท</p>
                        <p class="text-muted mb-0">
                            <small>แต่ละไฟล์ไม่เกิน 2 MB / รวมทั้งหมดไม่เกิน 7 MB</small>
                        </p>
                        <input type="file" id="fileInput" multiple hidden>
                    </div>

                    <div class="file-size-info text-center">
                        ขนาดไฟล์ใหม่รวม: <span id="totalSize" class="size-ok">0 MB</span> / 7 MB
                    </div>

                    <div class="file-list" id="newFilesList"></div>
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn btn-outline-secondary" onclick="cancelEdit()">
                        <i class="bi bi-x-circle me-2"></i>
                        ยกเลิก
                    </button>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-submit">
                            <i class="bi bi-check-circle me-2"></i>
                            บันทึกการแก้ไข
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sessionUserId = <?= json_encode($_SESSION['user_id']) ?>;
        const sessionUserStatus = <?= json_encode($_SESSION['user_status']) ?>;
    </script>
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
        const users = <?= json_encode($users, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>;
        // const users = [{
        //         id: 1,
        //         name: 'สมชาย ใจดี',
        //         role: 'Developer',
        //         avatar: 'SC'
        //     },
        //     {
        //         id: 2,
        //         name: 'สมหญิง สวยงาม',
        //         role: 'Designer',
        //         avatar: 'SS'
        //     },
        //     {
        //         id: 3,
        //         name: 'วิชัย รักงาน',
        //         role: 'Project Manager',
        //         avatar: 'WR'
        //     },
        //     {
        //         id: 4,
        //         name: 'มานี มีเงิน',
        //         role: 'Marketing',
        //         avatar: 'MM'
        //     },
        //     {
        //         id: 5,
        //         name: 'ประเสริฐ ดีเด่น',
        //         role: 'Developer',
        //         avatar: 'PD'
        //     }
        // ];

        // Mock existing task data
        const existingTask = <?= json_encode($existingTask, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>;
        console.log(existingTask);
        // const existingTask = {
        //     id: 1,
        //     title: "พัฒนาระบบ Login ใหม่",
        //     description: "ออกแบบและพัฒนาระบบ Login ที่รองรับ OAuth 2.0\n\nต้องทำให้รองรับ:\n- Google Login\n- Facebook Login\n- Email/Password\n\n@สมชาย ใจดี @สมหญิง สวยงาม",
        //     category: "development",
        //     status: "in-progress",
        //     importance: 5,
        //     existingFiles: [{
        //             id: 1,
        //             name: 'login-mockup.pdf',
        //             size: 1024000,
        //             type: 'application/pdf'
        //         },
        //         {
        //             id: 2,
        //             name: 'design-spec.docx',
        //             size: 512000,
        //             type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        //         }
        //     ],
        //     mentionedUsers: [1, 2],
        //     additionalUsers: [3]
        // };

        let mentionedUsers = [];
        let newFiles = [];
        let additionalSelectedUsers = [];
        let totalFileSize = 0;
        let existingFilesToDelete = [];
        const MAX_FILE_SIZE = 2 * 1024 * 1024;
        const MAX_TOTAL_SIZE = 7 * 1024 * 1024;

        const textarea = document.getElementById('taskDescription');
        const mentionDropdown = document.getElementById('mentionDropdown');
        let currentMentionStart = -1;
        let selectedMentionIndex = 0;

        // Load existing task data
        function loadExistingTask() {
            document.getElementById('taskTitle').value = existingTask.title;
            document.getElementById('taskCategory').value = existingTask.category;
            document.getElementById('taskDescription').value = existingTask.description;
            document.getElementById('taskStatus').value = existingTask.status;
            document.getElementById('taskDueDate').value = existingTask.due_date;
            document.getElementById('taskImportance').value = existingTask.importance;
            updateStars(existingTask.importance);

            const importanceLabels = {
                1: 'ไม่เร่งด่วน',
                2: 'เร่งด่วนน้อย',
                3: 'ปานกลาง',
                4: 'ค่อนข้างเร่งด่วน',
                5: 'เร่งด่วนมาก!'
            };
            document.getElementById('importanceLabel').textContent = importanceLabels[existingTask.importance];

            // Load mentioned users
            existingTask.mentionedUsers.forEach(userId => {
                const user = users.find(u => u.id === userId);
                if (user && !mentionedUsers.find(mu => mu.id === user.id)) {
                    mentionedUsers.push(user);
                }
            });
            updateMentionedUsers();

            // Load additional users
            additionalSelectedUsers = [...existingTask.additionalUsers];
            updateAdditionalUsersDisplay();

            // Load existing files
            if (existingTask.existingFiles.length > 0) {
                document.getElementById('existingFilesSection').style.display = 'block';
                displayExistingFiles();
            }

            // Update breadcrumb link
            document.getElementById('taskDetailLink').href = `task_detail.php?taskID=<?= $TaskID_Encode ?>`;
        }

        function displayExistingFiles() {
            const container = document.getElementById('existingFilesList');
            container.innerHTML = existingTask.existingFiles.map(file => {
                const isPendingDelete = existingFilesToDelete.includes(file.id);

                return `
                    <div class="file-item ${isPendingDelete ? 'pending-delete' : ''}" data-file-id="${file.id}">
                        <div class="file-icon">
                            <i class="bi bi-${getFileIcon(file.type)}"></i>
                        </div>
                        <div class="file-details">
                            <div class="file-name" title="${file.name}">${file.name}</div>
                            <div class="file-size">${formatFileSize(file.size)}</div>
                        </div>
                        ${isPendingDelete ? `
                            <span class="file-badge pending">กำลังรอลบ</span>
                            <button type="button" class="btn-undo-delete" onclick="undoDeleteFile(${file.id})" title="ยกเลิกการลบ">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        ` : `
                            <span class="file-badge existing">ไฟล์เดิม</span>
                            <button type="button" class="btn-remove-file" onclick="removeExistingFile(${file.id})" title="ลบไฟล์">
                                <i class="bi bi-trash"></i>
                            </button>
                        `}
                    </div>
                `;
            }).join('');
        }

        function removeExistingFile(fileId) {
            existingFilesToDelete.push(fileId);
            displayExistingFiles();
        }

        function undoDeleteFile(fileId) {
            const index = existingFilesToDelete.indexOf(fileId);
            if (index > -1) {
                existingFilesToDelete.splice(index, 1);
                displayExistingFiles();
            }
        }

        // Mention System (same as create page)
        textarea.addEventListener('input', function() {
            const text = this.value;
            const cursorPos = this.selectionStart;

            let atPos = text.lastIndexOf('@', cursorPos - 1);

            if (atPos !== -1) {
                const textAfterAt = text.substring(atPos + 1, cursorPos);
                const charBeforeAt = atPos > 0 ? text.charAt(atPos - 1) : ' ';

                if ((charBeforeAt === ' ' || charBeforeAt === '\n' || atPos === 0) &&
                    !textAfterAt.includes(' ') && !textAfterAt.includes('\n')) {
                    currentMentionStart = atPos;
                    showMentionDropdown(textAfterAt);
                } else {
                    hideMentionDropdown();
                }
            } else {
                hideMentionDropdown();
            }

            updateMentionedUsers();
        });

        function showMentionDropdown(query) {
            const filteredUsers = users.filter(user =>
                user.name.toLowerCase().includes(query.toLowerCase()) &&
                !mentionedUsers.some(mu => mu.id === user.id)
            );

            if (filteredUsers.length === 0) {
                hideMentionDropdown();
                return;
            }

            mentionDropdown.innerHTML = filteredUsers.map((user, index) => `
            <div class="mention-item ${index === 0 ? 'selected' : ''}" onclick="selectUser(${user.id})">
                <div class="mention-avatar">${user.avatar}</div>
                <div class="mention-info">
                    <div class="mention-name">${user.name}</div>
                    <div class="mention-role">${user.role}</div>
                </div>
            </div>
        `).join('');

            const rect = textarea.getBoundingClientRect();
            mentionDropdown.style.top = `${rect.bottom - rect.top + 5}px`;
            mentionDropdown.style.left = '0';
            mentionDropdown.classList.add('show');
            selectedMentionIndex = 0;
        }

        function hideMentionDropdown() {
            mentionDropdown.classList.remove('show');
            currentMentionStart = -1;
        }

        function selectUser(userId) {
            const user = users.find(u => u.id === userId);
            if (!user) return;

            const text = textarea.value;
            const beforeMention = text.substring(0, currentMentionStart);
            const afterMention = text.substring(textarea.selectionStart);
            const mentionText = `@${user.name} `;

            textarea.value = beforeMention + mentionText + afterMention;
            const newPos = currentMentionStart + mentionText.length;
            textarea.setSelectionRange(newPos, newPos);
            textarea.focus();

            if (!mentionedUsers.some(u => u.id === user.id)) {
                mentionedUsers.push(user);
            }

            updateMentionedUsers();
            hideMentionDropdown();
        }

        function updateMentionedUsers() {
            const text = textarea.value;
            mentionedUsers = mentionedUsers.filter(user =>
                text.includes(`@${user.name}`)
            );

            const container = document.getElementById('mentionedUsersContainer');

            if (mentionedUsers.length === 0) {
                container.innerHTML = '<small class="text-muted">ยังไม่มีผู้ใช้ที่ถูกแท็ก</small>';
                return;
            }

            container.innerHTML = mentionedUsers.map(user => `
            <div class="tag-item">
                <span>${user.name}</span>
                <span class="tag-remove" onclick="removeMentionedUser(${user.id})">×</span>
            </div>
        `).join('');
        }

        function removeMentionedUser(userId) {
            const user = mentionedUsers.find(u => u.id === userId);
            if (!user) return;

            let text = textarea.value;
            const mentionPattern = `@${user.name}`;

            while (text.includes(mentionPattern)) {
                const pos = text.indexOf(mentionPattern);
                let endPos = pos + mentionPattern.length;

                if (text.charAt(endPos) === ' ') {
                    endPos++;
                }

                text = text.substring(0, pos) + text.substring(endPos);
            }

            textarea.value = text;
            mentionedUsers = mentionedUsers.filter(u => u.id !== userId);
            updateMentionedUsers();
            textarea.focus();
        }

        // Additional Users
        function loadAdditionalUsers() {
            renderUserList(users);
        }

        function renderUserList(userList) {

            const isPrivileged =
                sessionUserStatus === 'admin' ||
                sessionUserStatus === 'executive';

            const container = document.getElementById('userSelectionList');

            container.innerHTML = userList.map(user => {
                const isSelf = user.id === sessionUserId;
                const disabled = !isPrivileged && !isSelf;

                return `
                <div class="user-selection-item ${disabled ? 'disabled' : ''}"
                     ${!disabled ? `onclick="toggleUserSelection(${user.id})"` : ''}>

                    <input type="checkbox"
                           class="user-checkbox"
                           ${additionalSelectedUsers.includes(user.id) ? 'checked' : ''}
                           ${disabled ? 'disabled' : ''}
                           onclick="event.stopPropagation(); toggleUserSelection(${user.id})">
                    <div class="user-selection-avatar">${user.avatar}</div>

                    <div class="user-selection-info">
                        <div class="user-selection-name">
                            ${user.name}
                            ${disabled ? '<small class="text-muted">(เลือกไม่ได้)</small>' : ''}
                        </div>
                        <div class="user-selection-role">${user.role}</div>
                    </div>
                </div>`;
            }).join('');
        }

        function toggleUserSelection(userId) {

            const isPrivileged =
                sessionUserStatus === 'admin' ||
                sessionUserStatus === 'executive';

            // 🔒 user ทั่วไป เลือกได้เฉพาะตัวเอง
            if (!isPrivileged && userId !== sessionUserId) {
                return;
            }

            const index = additionalSelectedUsers.indexOf(userId);

            // ⚠️ กำลังจะเอาตัวเองออก
            if (userId === sessionUserId && index > -1) {
                const confirmed = confirm(
                    'หากคุณนำตัวเองออกจากผู้เกี่ยวข้อง คุณจะไม่สามารถเห็นงานนี้ได้อีก\n\nต้องการดำเนินการต่อหรือไม่?'
                );

                if (!confirmed) return;
            }

            if (index > -1) {
                additionalSelectedUsers.splice(index, 1);
            } else {
                additionalSelectedUsers.push(userId);
            }

            updateAdditionalUsersDisplay();
            renderUserList(filterUsersBySearch());
        }

        function updateAdditionalUsersDisplay() {
            const container = document.getElementById('additionalUsersContainer');

            if (additionalSelectedUsers.length === 0) {
                container.innerHTML = '<small class="text-muted">ยังไม่ได้เลือกผู้ใช้เพิ่มเติม</small>';
                return;
            }

            const isPrivileged =
                sessionUserStatus === 'admin' ||
                sessionUserStatus === 'executive';

            const selectedUserObjects = users.filter(u =>
                additionalSelectedUsers.includes(u.id)
            );

            container.innerHTML = selectedUserObjects.map(user => {
                const canRemove = isPrivileged || user.id === sessionUserId;

                return `
                <div class="tag-item ${!canRemove ? 'disabled' : ''}">
                    <span>${user.name}</span>
                    ${canRemove ? `
                        <span class="tag-remove"
                              onclick="toggleUserSelection(${user.id})">×</span>
                    ` : `
                        <span class="text-muted small ms-1">(ลบไม่ได้)</span>
                    `}
                </div>
                `;
            }).join('');


        }

        document.getElementById('userSearchInput').addEventListener('input', function() {
            const filteredUsers = filterUsersBySearch();
            renderUserList(filteredUsers);
        });

        function filterUsersBySearch() {
            const query = document.getElementById('userSearchInput').value.toLowerCase();
            if (!query) return users;

            return users.filter(user =>
                user.name.toLowerCase().includes(query) ||
                user.role.toLowerCase().includes(query)
            );
        }

        // Star Rating
        const stars = document.querySelectorAll('.star');
        const importanceInput = document.getElementById('taskImportance');
        const importanceLabel = document.getElementById('importanceLabel');

        const importanceLabels = {
            1: 'ไม่เร่งด่วน',
            2: 'เร่งด่วนน้อย',
            3: 'ปานกลาง',
            4: 'ค่อนข้างเร่งด่วน',
            5: 'เร่งด่วนมาก!'
        };

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const value = parseInt(this.dataset.value);
                importanceInput.value = value;
                updateStars(value);
                importanceLabel.textContent = importanceLabels[value];
                importanceLabel.style.color = value >= 4 ? '#ef4444' : '#64748b';
            });

            star.addEventListener('mouseenter', function() {
                const value = parseInt(this.dataset.value);
                updateStars(value);
            });
        });

        document.getElementById('starRating').addEventListener('mouseleave', function() {
            const currentValue = parseInt(importanceInput.value) || 0;
            updateStars(currentValue);
        });

        function updateStars(value) {
            stars.forEach((star, index) => {
                if (index < value) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }

        // File Upload
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileInput = document.getElementById('fileInput');
        const newFilesList = document.getElementById('newFilesList');

        fileUploadArea.addEventListener('click', () => fileInput.click());

        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });

        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('dragover');
        });

        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            for (let file of files) {
                if (file.size > MAX_FILE_SIZE) {
                    alert(`ไฟล์ "${file.name}" มีขนาดใหญ่เกิน 2 MB`);
                    continue;
                }

                if (totalFileSize + file.size > MAX_TOTAL_SIZE) {
                    alert('ขนาดไฟล์รวมเกิน 7 MB แล้ว');
                    break;
                }

                newFiles.push(file);
                totalFileSize += file.size;
                addFileToList(file);
            }

            updateTotalSize();
            fileInput.value = '';
        }

        function addFileToList(file) {
            const fileId = Date.now() + Math.random();
            const fileDiv = document.createElement('div');
            fileDiv.className = 'file-item';
            fileDiv.dataset.fileId = fileId;
            fileDiv.dataset.fileName = file.name;

            const icon = getFileIcon(file.type);

            fileDiv.innerHTML = `
            <div class="file-icon">
                <i class="bi bi-${icon}"></i>
            </div>
            <div class="file-details">
                <div class="file-name" title="${file.name}">${file.name}</div>
                <div class="file-size">${formatFileSize(file.size)}</div>
            </div>
            <span class="file-badge new">ใหม่</span>
            <button type="button" class="btn-remove-file" onclick="removeNewFile('${fileId}')">
                <i class="bi bi-trash"></i>
            </button>
        `;

            newFilesList.appendChild(fileDiv);
        }

        function removeNewFile(fileId) {
            const fileDiv = document.querySelector(`#newFilesList [data-file-id="${fileId}"]`);
            if (!fileDiv) return;

            const fileName = fileDiv.dataset.fileName;
            const fileIndex = newFiles.findIndex(f => f.name === fileName);

            if (fileIndex > -1) {
                totalFileSize -= newFiles[fileIndex].size;
                newFiles.splice(fileIndex, 1);
            }

            fileDiv.remove();
            updateTotalSize();
        }

        function updateTotalSize() {
            const totalSizeElement = document.getElementById('totalSize');
            const sizeMB = (totalFileSize / (1024 * 1024)).toFixed(2);
            totalSizeElement.textContent = `${sizeMB} MB`;

            if (totalFileSize > MAX_TOTAL_SIZE * 0.8) {
                totalSizeElement.className = 'size-warning';
            } else {
                totalSizeElement.className = 'size-ok';
            }
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
            if (mimeType.startsWith('video/')) return 'file-earmark-play';
            if (mimeType.includes('pdf')) return 'file-earmark-pdf';
            if (mimeType.includes('word')) return 'file-earmark-word';
            if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'file-earmark-excel';
            if (mimeType.includes('zip') || mimeType.includes('rar')) return 'file-earmark-zip';
            return 'file-earmark';
        }

        // Form Submission
        document.getElementById('editTaskForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!importanceInput.value || importanceInput.value === '0') {
                alert('กรุณาเลือกระดับความเร่งด่วน');
                return;
            }

            const formData = new FormData();
            formData.append('taskId', existingTask.id);
            formData.append('title', document.getElementById('taskTitle').value);
            formData.append('category', document.getElementById('taskCategory').value);
            formData.append('description', document.getElementById('taskDescription').value);
            formData.append('status', document.getElementById('taskStatus').value);
            formData.append('importance', importanceInput.value);
            formData.append('mentionedUsers', JSON.stringify(mentionedUsers.map(u => u.id)));
            formData.append('additionalUsers', JSON.stringify(additionalSelectedUsers));
            formData.append('deletedFiles', JSON.stringify(existingFilesToDelete));

            newFiles.forEach((file, index) => {
                formData.append(`files[${index}]`, file);
            });

            const submitBtn = document.querySelector('.btn-submit');
            submitBtn.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i>กำลังบันทึก...';
            submitBtn.disabled = true;

            setTimeout(() => {
                alert('บันทึกการแก้ไขสำเร็จ!');
                window.location.href = 'task_detail.php?taskID=abc123';
            }, 1500);
        });

        function cancelEdit() {
            if (confirm('คุณต้องการยกเลิกการแก้ไขหรือไม่? การเปลี่ยนแปลงจะไม่ถูกบันทึก')) {
                window.location.href = 'task_detail.php?taskID=abc123';
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadExistingTask();
            loadAdditionalUsers();
        });

        document.addEventListener('click', (e) => {
            if (!mentionDropdown.contains(e.target) && e.target !== textarea) {
                hideMentionDropdown();
            }
        });
    </script>
</body>

</html>