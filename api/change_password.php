<?php
session_start();
include 'check_sesion_api.php';
date_default_timezone_set('Asia/Bangkok');

require_once '../class/crud.class.php';
require_once '../class/util.class.php';
require_once '../class/encrypt.class.php';

$object = new CRUD();
$util = new Util();
$Encrypt = new Encrypt_data();


// ตรวจสอบว่ามีข้อมูลที่ส่งมาหรือไม่
if (!isset($_POST['user_id']) || !isset($_POST['new_password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ข้อมูลไม่ครบถ้วน'
    ]);
    exit();
}

$user_id = $_POST['user_id'];
$new_password = $_POST['new_password'];

// ตรวจสอบว่า user_id ตรงกับ session หรือไม่
if ($user_id != $_SESSION['user_id']) {
    echo json_encode([
        'success' => false,
        'message' => 'ไม่มีสิทธิ์เข้าถึงข้อมูลนี้'
    ]);
    exit();
}

// ตรวจสอบความถูกต้องของรหัสผ่าน
if (strlen($new_password) < 8) {
    echo json_encode([
        'success' => false,
        'message' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร'
    ]);
    exit();
}

// ตรวจสอบเงื่อนไขรหัสผ่าน
$hasUppercase = preg_match('/[A-Z]/', $new_password);
$hasLowercase = preg_match('/[a-z]/', $new_password);
$hasNumber = preg_match('/[0-9]/', $new_password);
$hasSpecial = preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password);

if (!$hasUppercase || !$hasLowercase || !$hasNumber || !$hasSpecial) {
    echo json_encode([
        'success' => false,
        'message' => 'รหัสผ่านไม่ตรงตามเงื่อนไขที่กำหนด'
    ]);
    exit();
}

try {
    // เข้ารหัสรหัสผ่านใหม่
    $hashed_password = $Encrypt->EnCrypt_pass($new_password);
    $table = 'tb_users_c050968';
    $fields = array(
        'fd_user_password' => $hashed_password,
    );
    $conditionsEdit = array('fd_user_id' => intval($user_id));
    $result = $object->Update_Data($table, $fields,  $conditionsEdit);

    unset($_SESSION['change_password']);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'เปลี่ยนรหัสผ่านสำเร็จ'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการอัปเดตรหัสผ่านหรือรหัสเดิม'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
