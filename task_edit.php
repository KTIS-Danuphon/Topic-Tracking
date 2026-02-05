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

// เช็คสิทธิ์การดูข้อมูล
$table = 'tb_topics_c050968';
$fields = 'fd_topic_id, fd_topic_title, fd_topic_detail, fd_topic_category, fd_topic_mentioned, fd_topic_status, fd_topic_participant, fd_topic_created_by, fd_topic_importance, fd_topic_private, fd_topic_active, fd_topic_created_at ';
switch ($_SESSION['user_status']) {
    //admin / executive → เห็น user ทุกคน
    case 'admin':
    case 'executive':
        $where = 'WHERE fd_topic_active = "1"   ';
        break;
    //user → เห็นเฉพาะคนอื่นในฝ่ายเดียวกัน มีสถานะเป็น user และ active
    case 'user':
    default:
        $userId = (int) $_SESSION['user_id'];

        $where  = 'WHERE fd_topic_id = ' . $TaskID . ' AND fd_topic_active = 1 ';
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
$result_topic_check = $object->ReadData($table, $fields, $where);
if (empty($result_topic_check)) {
    echo "<script>
        alert('ไม่มีงานนี้อยู่ หรือคุณไม่มีสิทธิ์ดูข้อมูลนี้');
        window.location.href = 'tasks.php';
    </script>";
    exit();
}

// ดึงข้อมูลงาน
$table = 'tb_topics_c050968';
$fields = 'fd_topic_id, fd_topic_title, fd_topic_detail, fd_topic_category, fd_topic_mentioned, fd_topic_status, fd_topic_participant, fd_topic_created_by, fd_topic_due_date, fd_topic_importance, fd_topic_private, fd_topic_active, fd_topic_created_at ';
$where = 'WHERE fd_topic_id = "' . $TaskID . '" ';
$result_topic = $object->ReadData($table, $fields, $where);
$result_participant = trim($result_topic[0]['fd_topic_participant'], '[]'); // ลบ [] ออก

$table = 'tb_users_c050968 user';
$fields = 'user.fd_user_id, user.fd_user_fullname, dvs.fd_div_name ';
$where = 'LEFT JOIN tb_divisions_c050968 dvs ON dvs.fd_div_id = user.fd_user_div ';
switch ($_SESSION['user_status']) {
    //admin / executive → เห็น user ทุกคน
    case 'admin':
    case 'executive':
        $where .= 'WHERE user.fd_user_status = "user" AND user.fd_user_active = "1"';
        break;
    //user → เห็นเฉพาะคนอื่นในฝ่ายเดียวกัน มีสถานะเป็น user และ active
    case 'user':
    default:
        $where .= 'WHERE user.fd_user_status = "user" AND user.fd_user_div = "' . $_SESSION['user_div'] . '" AND user.fd_user_active = "1"';
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
$createdBy = $result_topic[0]['fd_topic_created_by'];

foreach ($result_user as $row) {

    // ถ้าเป็นคนสร้างงาน → ไม่ต้องเอาเข้า list
    if ($row['fd_user_id'] == $createdBy) {
        continue;
    }

    $fullname = $row['fd_user_fullname'];
    $nameParts = explode(' ', $fullname);

    $avatar = '';
    foreach ($nameParts as $p) {
        $avatar .= mb_substr($p, 0, 1);
    }

    $users[] = [
        'id'     => $row['fd_user_id'],
        'name'   => $fullname,
        'role'   => $row['fd_div_name'],
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
    <link rel="icon" href="ktis.svg" type="image/svg+xml">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include 'style_menu.php'; ?>
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

            <form id="editTaskForm" action="task_edit_action.php" method="post" enctype="multipart/form-data">
                <input type="hidden" id="taskID" name="taskID" value="<?= $TaskID ?>">
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
                                <option value="" disabled>เลือกหมวดหมู่</option>
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

                    <div class="alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>หมายเหตุ:</strong> ไฟล์เดิมจะถูกเก็บไว้ คุณสามารถเพิ่มไฟล์ใหม่หรือลบไฟล์เดิมได้
                    </div>
                    <div id="existingFilesSection" style="display: none;">
                        <!-- <h6 class="mb-3">ไฟล์เดิม</h6> -->
                        <div id="existingFilesList" class="file-list"></div>
                        <input type="hidden" id="filesToDelete" name="filesToDelete" value="">
                    </div>

                    <h6 class="mb-3 mt-4">เพิ่มไฟล์ใหม่</h6>
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
        const createbyUser = <?= json_encode($result_topic[0]['fd_topic_created_by']) ?>;
        let UserStatus = '';
        if (sessionUserId == createbyUser) {
            UserStatus = 'creator';
        }
    </script>
    <script>
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
    </script>
    <script>
        // const users = <?php echo json_encode($users); ?>;

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

        const users = <?php echo json_encode($users); ?>;

        let mentionedUsers = [];
        let newFiles = [];
        let selectedFiles = [];
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
            console.log('existingFilesToDelete', existingFilesToDelete);
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

            const isPrivileged =
                sessionUserStatus === 'admin' ||
                sessionUserStatus === 'executive' ||
                UserStatus === 'creator';

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
                sessionUserStatus === 'executive' ||
                UserStatus === 'creator';

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
                // ถ้ามีอยู่แล้ว ให้ลบออก
                additionalSelectedUsers.splice(index, 1);
            } else {
                // ถ้ายังไม่มี ให้เพิ่มเข้าไป
                additionalSelectedUsers.push(userId);
            }

            updateAdditionalUsersDisplay();
            updateUserSelectionListCheckboxes(); // อัพเดท checkbox และ class
            updateSelectAllCheckbox(); // เช็คว่าต้องอัพเดท "เลือกทั้งหมด" หรือไม่
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


        // function toggleUserSelection(userId) {
        //     const index = additionalSelectedUsers.indexOf(userId);

        //     if (index > -1) {
        //         // ถ้ามีอยู่แล้ว ให้ลบออก
        //         additionalSelectedUsers.splice(index, 1);
        //     } else {
        //         // ถ้ายังไม่มี ให้เพิ่มเข้าไป
        //         additionalSelectedUsers.push(userId);
        //     }

        //     updateAdditionalUsersDisplay();
        //     updateUserSelectionListCheckboxes(); // อัพเดท checkbox และ class
        //     updateSelectAllCheckbox(); // เช็คว่าต้องอัพเดท "เลือกทั้งหมด" หรือไม่
        // }

        // function updateAdditionalUsersDisplay() {
        //     const container = document.getElementById('additionalUsersContainer');

        //     if (additionalSelectedUsers.length === 0) {
        //         container.innerHTML = '<small class="text-muted">ยังไม่ได้เลือกผู้ใช้เพิ่มเติม</small>';
        //     } else {
        //         const selectedUserObjects = users.filter(u => additionalSelectedUsers.includes(u.id));
        //         container.innerHTML = selectedUserObjects.map(user => `
        //             <div class="tag-item">
        //                 <span>${user.name}</span>
        //                 <span class="tag-remove" onclick="toggleUserSelection(${user.id})">×</span>
        //             </div>
        //         `).join('');
        //     }

        //     // อัพเดท hidden input
        //     document.getElementById('additionalUsersInput').value = JSON.stringify(additionalSelectedUsers);
        // }

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

        document.getElementById('fileInput').addEventListener('change', function(e) {
            console.group('📂 [DEBUG FILE INPUT] change');

            const files = fileInput.files;

            console.log('จำนวนไฟล์ทั้งหมด:', files.length);

            if (files.length === 0) {
                console.log('— ไม่มีไฟล์ —');
            } else {
                Array.from(files).forEach((file, index) => {
                    console.log(`#${index + 1}`, {
                        name: file.name,
                        sizeKB: (file.size / 1024).toFixed(2) + ' KB',
                        type: file.type,
                        lastModified: new Date(file.lastModified).toLocaleString()
                    });
                });
            }

            console.groupEnd();
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

            const index = selectedFiles.findIndex(f => f.name === fileName);
            if (index > -1) {
                totalFileSize -= selectedFiles[index].size;
                selectedFiles.splice(index, 1);
            }

            fileDiv.remove();
            syncInputFiles(); // 🔥 สำคัญ
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
        const form = document.getElementById('editTaskForm');

        form.addEventListener('submit', function(e) {
            e.preventDefault(); // ดักไว้ก่อน

            console.group('🚀 FINAL FILES BEFORE SUBMIT');

            console.log('selectedFiles:', selectedFiles.length);
            console.log('fileInput.files:', fileInput.files.length);

            Array.from(fileInput.files).forEach((f, i) => {
                console.log(`#${i + 1}`, f.name, f.size, f.type);
            });

            console.groupEnd();
            Swal.fire({
                title: 'ยืนยันการบันทึก?',
                text: 'คุณต้องการบันทึกการแก้ไขงานนี้หรือไม่',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'บันทึก',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('mentionedUsersInput').value = JSON.stringify(mentionedUsers.map(u => u.id));
                    document.getElementById('additionalUsersInput').value = JSON.stringify(additionalSelectedUsers);
                    document.getElementById('filesToDelete').value = existingFilesToDelete.join(',');

                    form.submit(); // ✅ submit จริง → POST + FILE ไป action.php
                }
            });
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