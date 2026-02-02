<?php
session_start();
include 'check_sesion_api.php';
date_default_timezone_set('Asia/Bangkok');

require_once '../class/crud.class.php';
require_once '../class/util.class.php';
require_once '../class/encrypt.class.php';
$object   = new CRUD();
$util     = new Util();
$Encrypt  = new Encrypt_data();

$now = new DateTime();

$formatted_now = $now->format('Y-m-d H:i:s');
header('Content-Type: application/json');

// รับ JSON
$data = json_decode(file_get_contents("php://input"), true);

$content  = trim($data['content'] ?? '');
$taskID  = $Encrypt->DeCrypt_pass($data['task_id']) ?? '';

$userId   = $_SESSION['user_id'];
$fullname = $_SESSION['user_fullname'];

// ตรวจสอบข้อมูล
if ($content === '' || $taskID === '') {
    echo json_encode([
        'success' => false,
        'code' => 'INVALID_DATA',
        'message' => 'ข้อมูลไม่ครบถ้วน'
    ]);
    exit;
}

// decode Task ID (ตัวอย่าง)
$taskId = base64_decode($taskID);
if (!$taskId) {
    echo json_encode([
        'success' => false,
        'code' => 'TASK_INVALID',
        'message' => 'ไม่พบงานที่ระบุ'
    ]);
    exit;
}

try {

    /* ===============================
       💾 บันทึก comment (ตัวอย่าง)
       =============================== */

    // $commentId = insert_comment($taskId, $userId, $content);
    $commentId = rand(1000, 9999);

    /* ===============================
       💾 บันทึก activity log
       =============================== */

    // insert_activity_log($taskId, 'comment', "เพิ่มความคิดเห็นโดย $fullname");
    $commentData = [
        'fd_task_id' => $taskID,
        'fd_user_id' => $userId,
        'fd_comment_text' => $content,
        'fd_comment_active' => '1',
        'fd_time_create' => $formatted_now,
        'fd_time_update' => $formatted_now
    ];
    $commentID = $object->Insert_Data('tb_comment_c050968', $commentData);
    $typeLog = "comment";

    require_once '../task_log.php';
    Task_log(
        $taskID,
        $typeLog,
        $fullname,
        $object,
        $formatted_now
    );
    $nameParts = explode(' ', $fullname);
    $avatar = '';
    foreach ($nameParts as $p) {
        $avatar .= mb_substr($p, 0, 1); //ตัวแรกของชื่อและสกุล
    }

    echo json_encode([
        'success' => true,
        'message' => 'เพิ่มความคิดเห็นสำเร็จ',
        'comment_id' => $commentID,
        'created_at' => date('c'),
        'author' => [
            'name' => $fullname,
            'avatar' => $avatar
        ]
    ]);
    exit;
} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'code' => 'SERVER_ERROR',
        'message' => 'เกิดข้อผิดพลาดในระบบ'
    ]);
    exit;
}
