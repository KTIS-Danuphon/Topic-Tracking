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

$table = 'tb_users_c050968 user';
$fields = 'user.fd_user_id, user.fd_user_fullname, user.fd_user_status, dvs.fd_div_name';
$where = 'LEFT JOIN tb_divisions_c050968 dvs ON user.fd_user_div = dvs.fd_div_id ';
switch ($_SESSION['user_status']) {
    //admin / executive → เห็น user ทุกคน
    case 'admin':
    case 'executive':
        $where .= 'WHERE user.fd_user_status = "user" AND user.fd_user_active = "1"';
        break;
    //user → เห็นเฉพาะคนอื่นในฝ่ายเดียวกัน มีสถานะเป็น user และ active
    case 'user':
    default:
        $where .= 'WHERE user.fd_user_id != "' . $_SESSION['user_id'] . '" AND user.fd_user_status = "user" AND user.fd_user_div = "' . $_SESSION['user_div'] . '" AND user.fd_user_active = "1"';
        break;
}
$result_user = $object->ReadData($table, $fields, $where);
//ปรับเป้น json 
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
        'role'   => $row['fd_div_name'], // ชื่อฝ่ายงาน
        'avatar' => $avatar
    ];
}

// echo json_encode($users, JSON_UNESCAPED_UNICODE);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้างงานใหม่ - Topic Tracking</title>
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
    </style>
</head>

<body>
    <?php include 'menu.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="mb-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="tasks.php">งานทั้งหมด</a></li>
                        <li class="breadcrumb-item active">สร้างงานใหม่</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-plus-circle me-2" style="color: var(--primary-color);"></i>
                    สร้างงานใหม่
                </h2>
                <p class="text-muted">กรอกข้อมูลเพื่อสร้างงานใหม่</p>
            </div>

            <form id="createTaskForm" action="tesk_creacte_action.php" method="POST" enctype="multipart/form-data">
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
                            <input type="text" class="form-control" id="taskTitle" name="taskTitle"
                                placeholder="ระบุชื่อหัวข้องาน..." required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                หมวดหมู่ <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="taskCategory" name="taskCategory" required>
                                <option value="" selected disabled>เลือกหมวดหมู่</option>
                                <option value="เรื่องประชุม">เรื่องประชุม</option>
                                <option value="โครงการ">โครงการ</option>
                                <option value="ปัญหา">ปัญหา</option>
                                <option value="แผนงาน">แผนงาน</option>
                                <option value="อื่นๆ">อื่นๆ</option>
                            </select>
                            <!-- <input type="text" class="form-control" id="taskCategory" name="taskCategory" placeholder="ระบุชื่อหมวดหมู่"> -->
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            รายละเอียด <span class="text-danger">*</span>
                        </label>
                        <div class="mention-textarea-wrapper">
                            <textarea class="form-control" id="taskDescription" name="taskDescription" rows="6"
                                placeholder="พิมพ์ @ เพื่อแท็กผู้ใช้งาน... (เช่น @สมชาย)" required></textarea>
                            <div class="mention-dropdown" id="mentionDropdown"></div>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            พิมพ์ @ ตามด้วยชื่อเพื่อแท็กผู้ใช้งาน หรือพิมพ์ @all เพื่อแท็กทุกคน (เลือกได้อย่างใดอย่างหนึ่ง)
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
                        <label class="form-label">ผู้ที่ถูกแท็กในคำอธิบาย <span id="mentionedList"></span></label>
                        <div class="tags-container" id="mentionedUsersContainer">
                            <small class="text-muted">ยังไม่มีผู้ใช้ที่ถูกแท็ก</small>
                        </div>
                        <input type="hidden" id="mentionedUsersInput" name="mentionedUsers">
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-lightbulb me-1"></i>
                            หมายเหตุ: สามารถเลือกแท็กแบบ <strong>รายบุคคล</strong> หรือ <strong>@all ทุกคน</strong> เท่านั้น (เลือกได้อย่างใดอย่างหนึ่ง)
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">เพิ่มผู้เกี่ยวข้องเพิ่มเติม <code>*ผู้ไม่เกี่ยวข้องจะไม่สามารถมองเห็นงานนี้ได้ (ยกเว้น แอดมินและผู้บริหาร)</code></label>

                        <div class="mb-2">
                            <input type="text" class="form-control" id="userSearchInput"
                                placeholder="🔍 พิมพ์ชื่อเพื่อค้นหาผู้ใช้...">
                        </div>

                        <!-- เพิ่มปุ่มเลือกทั้งหมด -->
                        <div class="mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAllUsers" onchange="toggleSelectAll()">
                                <label class="form-check-label" for="selectAllUsers">
                                    <strong>เลือกทั้งหมด</strong>
                                </label>
                            </div>
                        </div>

                        <div class="user-selection-list" id="userSelectionList"></div>

                        <div class="mt-3">
                            <label class="form-label text-muted small">ผู้เกี่ยวข้องที่เลือก:</label>
                            <div class="tags-container" id="additionalUsersContainer">
                                <small class="text-muted">ยังไม่ได้เลือกผู้ใช้เพิ่มเติม</small>
                            </div>
                            <input type="hidden" id="additionalUsersInput" name="additionalUsers">
                        </div>
                    </div>
                </div>

                <div class="form-card">
                    <div class="form-section-title">
                        <i class="bi bi-paperclip"></i>
                        ไฟล์แนบ
                    </div>

                    <div class="file-upload-area" id="fileUploadArea">
                        <i class="bi bi-cloud-upload upload-icon"></i>
                        <h5>ลากไฟล์มาวางที่นี่ หรือคลิกเพื่อเลือกไฟล์</h5>
                        <p class="text-muted mb-2">รองรับไฟล์ทุกประเภท</p>
                        <p class="text-muted mb-0">
                            <small>แต่ละไฟล์ไม่เกิน 2 MB / รวมทั้งหมดไม่เกิน 7 MB</small>
                        </p>
                        <input type="file" id="fileInput" name="files[]" multiple hidden>
                    </div>

                    <div class="file-size-info text-center">
                        ขนาดไฟล์รวม: <span id="totalSize" class="size-ok">0 MB</span> / 7 MB
                    </div>

                    <div class="file-list" id="fileList"></div>
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                        <i class="bi bi-x-circle me-2"></i>
                        ยกเลิก
                    </button>
                    <div class="d-flex gap-2">
                        <!-- <button type="button" class="btn btn-outline-primary" onclick="saveDraft()">
                            <i class="bi bi-save me-2"></i>
                            บันทึกแบบร่าง
                        </button> -->
                        <button type="submit" class="btn-submit">
                            <i class="bi bi-check-circle me-2"></i>
                            สร้างงาน
                        </button>
                    </div>
                </div>
            </form>
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
        const users = <?php echo json_encode($users); ?>;

        let mentionedUsers = [];
        let selectedFiles = [];
        let additionalSelectedUsers = [];
        let totalFileSize = 0;
        let isAllMentioned = false; // เพิ่มตัวแปรนี้
        const MAX_FILE_SIZE = 2 * 1024 * 1024;
        const MAX_TOTAL_SIZE = 7 * 1024 * 1024;

        const textarea = document.getElementById('taskDescription');
        const mentionDropdown = document.getElementById('mentionDropdown');
        let currentMentionStart = -1;
        let selectedMentionIndex = 0;

        // Mention System
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
            // ถ้าแท็ก @all แล้ว ไม่ให้แท็กคนอื่นเพิ่ม
            if (isAllMentioned) {
                hideMentionDropdown();
                return;
            }

            const filteredUsers = users.filter(user =>
                user.name.toLowerCase().includes(query.toLowerCase()) &&
                !mentionedUsers.some(mu => mu.id === user.id)
            );

            if (filteredUsers.length === 0) {
                hideMentionDropdown();
                return;
            }
            // ตรวจสอบว่าพิมพ์ @all หรือไม่
            const q = query.toLowerCase();
            const isAllQuery = q === 'all' || 'all'.startsWith(q);
            const isFullMatch = filteredUsers.length === users.length;

            // แสดง @all เฉพาะตอนยังไม่พิมพ์อะไร
            // หรือซ่อน @all ทันทีที่เลือก user ใด user หนึ่ง
            if (isAllQuery && isFullMatch) {
                // mentionDropdown.innerHTML = `
                //     <div class="mention-item selected" onclick="selectAllUsers()">
                //         <div class="mention-avatar" style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);">
                //             <i class="bi bi-people-fill"></i>
                //         </div>
                //         <div class="mention-info">
                //             <div class="mention-name">@all - แท็กทุกคน</div>
                //             <div class="mention-role">แท็กผู้ใช้ทั้งหมด ${users.length} คน</div>
                //         </div>
                //     </div>
                // `;

                mentionDropdown.innerHTML = `
    <div class="mention-item ${filteredUsers.length === 0 ? 'selected' : ''}" onclick="selectAllUsers()">
        <div class="mention-avatar" style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="mention-info">
            <div class="mention-name">@all - แท็กทุกคน</div>
            <div class="mention-role">แท็กผู้ใช้ทั้งหมด ${users.length} คน</div>
        </div>
    </div>
    ${filteredUsers.map((user, index) => `
        <div class="mention-item ${index === 0 && filteredUsers.length > 0 ? 'selected' : ''}" onclick="selectUser(${user.id})">
            <div class="mention-avatar">${user.avatar}</div>
            <div class="mention-info">
                <div class="mention-name">${user.name}</div>
                <div class="mention-role">${user.role}</div>
            </div>
        </div>
    `).join('')}
`;

                const rect = textarea.getBoundingClientRect();
                mentionDropdown.style.top = `${rect.bottom - rect.top + 5}px`;
                mentionDropdown.style.left = '0';
                mentionDropdown.classList.add('show');
                selectedMentionIndex = 0;
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

        function selectAllUsers() {
            // ลบแท็กรายบุคคลทั้งหมดออกก่อน
            let text = textarea.value;
            mentionedUsers.forEach(user => {
                const mentionPattern = `@${user.name}`;
                while (text.includes(mentionPattern)) {
                    const pos = text.indexOf(mentionPattern);
                    let endPos = pos + mentionPattern.length;
                    if (text.charAt(endPos) === ' ') {
                        endPos++;
                    }
                    text = text.substring(0, pos) + text.substring(endPos);
                }
            });

            // เพิ่ม @all เข้าไป
            const beforeMention = text.substring(0, currentMentionStart);
            const afterMention = text.substring(currentMentionStart);
            const mentionText = `@all `;

            textarea.value = beforeMention + mentionText + afterMention;
            textarea.value = beforeMention + mentionText;
            const newPos = currentMentionStart + mentionText.length;
            textarea.setSelectionRange(newPos, newPos);
            textarea.focus();

            // เซ็ตว่าใช้ @all
            isAllMentioned = true;
            mentionedUsers = [...users]; // คัดลอกทุกคน

            updateMentionedUsers();
            hideMentionDropdown();
        }

        function selectUser(userId) {
            const user = users.find(u => u.id === userId);
            if (!user) return;

            // ถ้ามี @all อยู่แล้ว ไม่ให้เพิ่มคนทีละคน
            if (isAllMentioned) {
                alert('⚠️ คุณใช้ @all อยู่แล้ว ไม่สามารถแท็กรายบุคคลเพิ่มได้\nกรุณาลบ @all ก่อนถ้าต้องการแท็กรายบุคคล');
                hideMentionDropdown();
                return;
            }

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

            // ตรวจสอบว่ามี @all หรือไม่
            if (text.includes('@all')) {
                isAllMentioned = true;
                mentionedUsers = [...users]; // แท็กทุกคน
                document.getElementById('mentionedList').innerText = `ทุกคน (${users.length} คน)`;
            } else {
                document.getElementById('mentionedList').innerText = ``;

                isAllMentioned = false;
                // กรองเฉพาะคนที่ยังมีชื่ออยู่ใน text
                mentionedUsers = mentionedUsers.filter(user =>
                    text.includes(`@${user.name}`)
                );
            }

            const container = document.getElementById('mentionedUsersContainer');

            if (mentionedUsers.length === 0) {
                container.innerHTML = '<small class="text-muted">ยังไม่มีผู้ใช้ที่ถูกแท็ก</small>';
            } else {
                // ถ้าเป็น @all แสดงแบบพิเศษ
                if (isAllMentioned) {
                    container.innerHTML = `
                        <div class="tag-item" style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);">
                            <i class="bi bi-people-fill me-1"></i>
                            <span>@all - ทุกคน (${users.length} คน)</span>
                            <span class="tag-remove" onclick="removeAllMention()">×</span>
                        </div>
                    `;

                } else {
                    container.innerHTML = mentionedUsers.map(user => `
                        <div class="tag-item">
                            <span>${user.name}</span>
                            <span class="tag-remove" onclick="removeMentionedUser(${user.id})">×</span>
                        </div>
                    `).join('');
                }
            }

            // อัพเดท hidden input
            if (isAllMentioned) {
                document.getElementById('mentionedUsersInput').value = JSON.stringify(['all']);
            } else {
                document.getElementById('mentionedUsersInput').value = JSON.stringify(mentionedUsers.map(u => u.id));
            }
        }

        // ลบ @all ออก
        // text = text.replace('@all ', '').replace('@all', '');

        function removeAllMention() {
            let text = textarea.value;
            text = text.replace('@all ', '').replace('@all', '');
            textarea.value = text;

            isAllMentioned = false;
            mentionedUsers = [];
            updateMentionedUsers();
            textarea.focus();
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

        textarea.addEventListener('keydown', function(e) {
            if (!mentionDropdown.classList.contains('show')) return;

            const items = mentionDropdown.querySelectorAll('.mention-item');

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedMentionIndex = Math.min(selectedMentionIndex + 1, items.length - 1);
                updateMentionSelection();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedMentionIndex = Math.max(selectedMentionIndex - 1, 0);
                updateMentionSelection();
            } else if (e.key === 'Enter' && items.length > 0) {
                e.preventDefault();
                items[selectedMentionIndex].click();
            } else if (e.key === 'Escape') {
                hideMentionDropdown();
            }
        });

        function updateMentionSelection() {
            const items = mentionDropdown.querySelectorAll('.mention-item');
            items.forEach((item, index) => {
                item.classList.toggle('selected', index === selectedMentionIndex);
            });
        }

        // Additional Users
        function loadAdditionalUsers() {
            renderUserList(users);
        }

        function renderUserList(userList) {
            const container = document.getElementById('userSelectionList');

            if (userList.length === 0) {
                container.innerHTML = '<div class="no-results"><i class="bi bi-search"></i><p class="mt-2">ไม่พบผู้ใช้</p></div>';
                return;
            }

            container.innerHTML = userList.map(user => `
                <div class="user-selection-item ${additionalSelectedUsers.includes(user.id) ? 'selected' : ''}" 
                     onclick="toggleUserSelection(${user.id})">
                    <input type="checkbox" class="user-checkbox" 
                           ${additionalSelectedUsers.includes(user.id) ? 'checked' : ''}
                           onclick="event.stopPropagation(); toggleUserSelection(${user.id})">
                    <div class="user-selection-avatar">${user.avatar}</div>
                    <div class="user-selection-info">
                        <div class="user-selection-name">${user.name}</div>
                        <div class="user-selection-role">${user.role}</div>
                    </div>
                </div>
            `).join('');
        }

        // ฟังก์ชันเลือก/ยกเลิกทั้งหมด
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('selectAllUsers');

            if (selectAllCheckbox.checked) {
                // เลือกทั้งหมด - เพิ่มทุกคนที่ยังไม่ได้เลือก
                additionalSelectedUsers = users.map(u => u.id);
            } else {
                // ยกเลิกทั้งหมด - ล้างรายการ
                additionalSelectedUsers = [];
            }

            updateAdditionalUsersDisplay();
            updateUserSelectionListCheckboxes(); // อัพเดท checkbox และ class
        }

        // ฟังก์ชันตรวจสอบและอัพเดทสถานะ "เลือกทั้งหมด"
        function updateSelectAllCheckbox() {
            const selectAllCheckbox = document.getElementById('selectAllUsers');

            // ถ้าเลือกครบทุกคน ให้ check "เลือกทั้งหมด"
            if (additionalSelectedUsers.length === users.length && users.length > 0) {
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.checked = false;
            }
        }

        // ฟังก์ชันอัพเดท checkbox และ class selected ใน userSelectionList
        function updateUserSelectionListCheckboxes() {
            users.forEach(user => {
                // หา user-selection-item
                const items = document.querySelectorAll('.user-selection-item');

                items.forEach(item => {
                    // เช็คว่า item นี้เป็นของ user คนไหน (ดูจาก onclick attribute)
                    const onclickAttr = item.getAttribute('onclick');
                    if (onclickAttr && onclickAttr.includes(`toggleUserSelection(${user.id})`)) {
                        const checkbox = item.querySelector('.user-checkbox');

                        if (additionalSelectedUsers.includes(user.id)) {
                            // เลือกแล้ว
                            item.classList.add('selected');
                            if (checkbox) checkbox.checked = true;
                        } else {
                            // ยังไม่เลือก
                            item.classList.remove('selected');
                            if (checkbox) checkbox.checked = false;
                        }
                    }
                });
            });
        }


        function toggleUserSelection(userId) {
            const index = additionalSelectedUsers.indexOf(userId);

            if (index > -1) {
                // ถ้ามีอยู่แล้ว ให้ลบออก
                additionalSelectedUsers.splice(index, 1);
            } else {
                // ถ้ายังไม่มี ให้เพิ่มเข้าไป
                additionalSelectedUsers.push(userId);
            }

            updateAdditionalUsersDisplay();
            updateUserSelectionListCheckboxes(); // อัพเดท checkbox และ class
            updateSelectAllCheckbox(); // เช็คว่าต้องอัพเดท "เลือกทั้งหมด" หรือไม่
        }

        function updateAdditionalUsersDisplay() {
            const container = document.getElementById('additionalUsersContainer');

            if (additionalSelectedUsers.length === 0) {
                container.innerHTML = '<small class="text-muted">ยังไม่ได้เลือกผู้ใช้เพิ่มเติม</small>';
            } else {
                const selectedUserObjects = users.filter(u => additionalSelectedUsers.includes(u.id));
                container.innerHTML = selectedUserObjects.map(user => `
                    <div class="tag-item">
                        <span>${user.name}</span>
                        <span class="tag-remove" onclick="toggleUserSelection(${user.id})">×</span>
                    </div>
                `).join('');
            }

            // อัพเดท hidden input
            document.getElementById('additionalUsersInput').value = JSON.stringify(additionalSelectedUsers);
        }

        // ฟังก์ชันสร้างรายการผู้ใช้
        function updateUserSelectionList(userList = users) {
            const container = document.getElementById('userSelectionList');

            container.innerHTML = userList.map(user => `
                <div class="user-selection-item ${additionalSelectedUsers.includes(user.id) ? 'selected' : ''}" 
                     onclick="toggleUserSelection(${user.id})">
                    <input type="checkbox" class="user-checkbox" 
                           ${additionalSelectedUsers.includes(user.id) ? 'checked' : ''}
                           onclick="event.stopPropagation(); toggleUserSelection(${user.id})">
                    <div class="user-selection-avatar">${user.avatar}</div>
                    <div class="user-selection-info">
                        <div class="user-selection-name">${user.name}</div>
                        <div class="user-selection-role">${user.role}</div>
                    </div>
                </div>
            `).join('');

            updateSelectAllCheckbox(); // อัพเดทสถานะ "เลือกทั้งหมด"
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
        const fileList = document.getElementById('fileList');

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

                // กันไฟล์ชื่อซ้ำ
                if (selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                    continue;
                }

                selectedFiles.push(file);
                totalFileSize += file.size;
                addFileToList(file);
            }

            syncInputFiles();
            updateTotalSize();
        }

        function syncInputFiles() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
        }

        function addFileToList(file) {
            const fileId = Date.now() + Math.random();
            const fileDiv = document.createElement('div');
            fileDiv.className = 'file-item';
            fileDiv.dataset.fileId = fileId;

            fileDiv.innerHTML = `
                <div class="file-icon">
                    <i class="bi bi-${getFileIcon(file.type)}"></i>
                </div>
                <div class="file-details">
                    <div class="file-name" title="${file.name}">${file.name}</div>
                    <div class="file-size">${formatFileSize(file.size)}</div>
                </div>
                <button type="button" class="btn-remove-file">
                    <i class="bi bi-trash"></i>
                </button>
            `;

            fileDiv.querySelector('.btn-remove-file').addEventListener('click', () => {
                removeFile(fileId, file);
            });

            fileList.appendChild(fileDiv);
        }

        function removeFile(fileId, file) {
            selectedFiles = selectedFiles.filter(f => f !== file);
            totalFileSize -= file.size;

            document.querySelector(`[data-file-id="${fileId}"]`)?.remove();

            syncInputFiles();
            updateTotalSize();
        }

        function updateTotalSize() {
            const totalSizeElement = document.getElementById('totalSize');
            const sizeMB = (totalFileSize / (1024 * 1024)).toFixed(2);
            totalSizeElement.textContent = `${sizeMB} MB`;

            totalSizeElement.className =
                totalFileSize > MAX_TOTAL_SIZE * 0.8 ? 'size-warning' : 'size-ok';
        }

        function formatFileSize(bytes) {
            const sizes = ['Bytes', 'KB', 'MB'];
            if (bytes === 0) return '0 Bytes';
            const i = Math.floor(Math.log(bytes) / Math.log(1024));
            return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + sizes[i];
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
        document.getElementById('createTaskForm').addEventListener('submit', function(e) {
            // Validate ความเร่งด่วน
            if (!importanceInput.value || importanceInput.value === '0') {
                e.preventDefault();
                alert('❌ กรุณาเลือกระดับความเร่งด่วน');
                document.getElementById('starRating').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                return false;
            }

            // Validate ขนาดไฟล์
            if (totalFileSize > MAX_TOTAL_SIZE) {
                e.preventDefault();
                alert('❌ ขนาดไฟล์รวมเกิน 7 MB กรุณาลบไฟล์บางไฟล์ออก');
                return false;
            }

            // Validate แต่ละไฟล์
            for (let file of selectedFiles) {
                if (file.size > MAX_FILE_SIZE) {
                    e.preventDefault();
                    alert(`❌ ไฟล์ "${file.name}" มีขนาดใหญ่เกิน 2 MB`);
                    return false;
                }
            }

            // อัพเดท hidden inputs ก่อนส่ง
            document.getElementById('mentionedUsersInput').value = JSON.stringify(mentionedUsers.map(u => u.id));
            document.getElementById('additionalUsersInput').value = JSON.stringify(additionalSelectedUsers);

            // แสดงข้อมูลที่จะส่ง (สำหรับ debug)
            console.log('=== ข้อมูลที่จะส่ง ===');
            console.log('Title:', document.getElementById('taskTitle').value);
            console.log('Category:', document.getElementById('taskCategory').value);
            console.log('Description:', document.getElementById('taskDescription').value);
            console.log('Status:', document.getElementById('taskStatus').value);
            console.log('Due Date:', document.getElementById('taskDueDate').value);
            console.log('Importance:', importanceInput.value);
            console.log('Is All Mentioned:', isAllMentioned);
            console.log('Mentioned Users:', isAllMentioned ? ['all'] : mentionedUsers.map(u => u.id));
            console.log('Additional Users:', additionalSelectedUsers);
            console.log('Files:', selectedFiles.map(f => f.name));

            // แสดง loading
            const submitBtn = document.querySelector('.btn-submit');
            submitBtn.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i>กำลังสร้างงาน...';
            submitBtn.disabled = true;

            // ให้ form submit ไปตามปกติ
            return true;
        });

        function saveDraft() {
            alert('บันทึกแบบร่างสำเร็จ!');
        }

        // Initialize
        loadAdditionalUsers();

        document.addEventListener('click', (e) => {
            if (!mentionDropdown.contains(e.target) && e.target !== textarea) {
                hideMentionDropdown();
            }
        });
    </script>
</body>

</html>