<?php
session_start();
include 'check_sesion_api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'code' => 'SESSION_EXPIRED',
        'message' => 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่'
    ]);
    exit;
}

date_default_timezone_set('Asia/Bangkok');

require_once '../class/crud.class.php';
require_once '../class/util.class.php';
require_once '../class/encrypt.class.php';

$object = new CRUD();
$util = new Util();
$Encrypt = new Encrypt_data();

/* =========================
   Helper: ส่ง JSON และจบการทำงาน
========================= */
function jsonResponse(bool $success, string $message, array $extra = [], int $httpCode = 200)
{
    http_response_code($httpCode);
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra));
    exit;
}

/* =========================
   ตรวจสอบสิทธิ์ Admin / Owner
========================= */
function requireAdminOrOwner($targetUserId)
{
    if ($_SESSION['user_status'] === 'admin') {
        return;
    }

    if ($targetUserId != $_SESSION['user_id']) {
        jsonResponse(false, 'คุณไม่มีสิทธิ์ในการดำเนินการนี้', [], 403);
    }
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {

        case 'toggle_status':
            toggleUserStatus();
            break;

        case 'create':
            createUser();
            break;

        case 'update':
            updateUser();
            break;

        case 'delete':
            deleteUser();
            break;

        case 'reset_password':
            resetPassword();
            break;

        case 'get_user':
            getUser();
            break;

        default:
            jsonResponse(false, 'Invalid action');
    }
} catch (Throwable $e) {
    jsonResponse(false, 'เกิดข้อผิดพลาดภายในระบบ', [
        'error' => $e->getMessage()
    ], 500);
}

/* =========================================================
   FUNCTIONS
========================================================= */

function toggleUserStatus()
{
    global $object;

    if ($_SESSION['user_status'] !== 'admin') {
        jsonResponse(false, 'คุณไม่มีสิทธิ์เปลี่ยนสถานะผู้ใช้', [], 403);
    }

    $userId    = $_POST['user_id'] ?? '';
    $newStatus = $_POST['status'] ?? '';

    if (!$userId || !in_array($newStatus, ['0', '1'], true)) {
        jsonResponse(false, 'ข้อมูลไม่ถูกต้อง');
    }

    if ($userId == $_SESSION['user_id']) {
        jsonResponse(false, 'ไม่สามารถปิดการใช้งานบัญชีของตัวเองได้');
    }

    $result = $object->Update_Data(
        'tb_users_c050968',
        [
            'fd_user_active'     => $newStatus,
            'fd_user_updated_at' => date('Y-m-d H:i:s')
        ],
        ['fd_user_id' => intval($userId)]
    );

    if ($result) {
        jsonResponse(
            true,
            $newStatus === '1'
                ? 'เปิดใช้งานผู้ใช้สำเร็จ'
                : 'ปิดการใช้งานผู้ใช้สำเร็จ'
        );
    }

    jsonResponse(false, 'ไม่สามารถเปลี่ยนสถานะผู้ใช้ได้');
}

function createUser()
{
    global $object, $Encrypt;

    if ($_SESSION['user_status'] !== 'admin') {
        jsonResponse(false, 'คุณไม่มีสิทธิ์สร้างผู้ใช้ใหม่', [], 403);
    }

    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $division = trim($_POST['division'] ?? '');
    $role     = $_POST['role'] ?? 'user';
    $status   = $_POST['status'] ?? '1';

    if (!$username || !$fullname) {
        jsonResponse(false, 'กรุณากรอกข้อมูลให้ครบถ้วน');
    }

    if ($role === 'user' && !$division) {
        jsonResponse(false, 'กรุณาเลือกฝ่ายสำหรับผู้ใช้ทั่วไป');
    }

    $exists = $object->ReadData(
        'tb_users_c050968',
        'fd_user_id',
        "WHERE fd_user_name = '{$username}'"
    );

    if ($exists) {
        jsonResponse(false, 'ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว');
    }

    $now = date('Y-m-d H:i:s');
    $result = $object->Insert_Data('tb_users_c050968', [
        'fd_user_name'       => $username,
        'fd_user_fullname'   => $fullname,
        'fd_user_div'        => $division,
        'fd_user_status'     => $role,
        'fd_user_active'     => $status,
        'fd_user_password'   => $Encrypt->EnCrypt_pass('Ktisgroup'),
        'fd_user_created_at' => $now,
        'fd_user_updated_at' => $now
    ]);

    $result
        ? jsonResponse(true, 'เพิ่มผู้ใช้สำเร็จ', ['user_id' => $result])
        : jsonResponse(false, 'ไม่สามารถเพิ่มผู้ใช้ได้');
}

function updateUser()
{
    global $object, $Encrypt;

    $userId = $_POST['user_id'] ?? '';
    requireAdminOrOwner($userId);

    $fields = [
        'fd_user_name'       => trim($_POST['username'] ?? ''),
        'fd_user_fullname'   => trim($_POST['fullname'] ?? ''),
        'fd_user_div'        => trim($_POST['division'] ?? ''),
        'fd_user_status'        => trim($_POST['role'] ?? ''),
        'fd_user_active'        => trim($_POST['status'] ?? ''),
        'fd_user_updated_at' => date('Y-m-d H:i:s')
    ];

    if (!empty($_POST['password'])) {
        $fields['fd_user_password'] = $Encrypt->EnCrypt_pass($_POST['password']);
    }

    $result = $object->Update_Data(
        'tb_users_c050968',
        $fields,
        ['fd_user_id' => intval($userId)]
    );

    $result
        ? jsonResponse(true, 'บันทึกข้อมูลสำเร็จ')
        : jsonResponse(false, 'ไม่สามารถบันทึกข้อมูลได้');
}

function deleteUser()
{
    global $object;

    if ($_SESSION['user_status'] !== 'admin') {
        jsonResponse(false, 'คุณไม่มีสิทธิ์ลบผู้ใช้', [], 403);
    }

    $userId = $_POST['user_id'] ?? '';

    if ($userId == $_SESSION['user_id']) {
        jsonResponse(false, 'ไม่สามารถลบบัญชีของตัวเองได้');
    }

    $result = $object->Update_Data(
        'tb_users_c050968',
        ['fd_user_active' => '0'],
        ['fd_user_id' => intval($userId)]
    );

    $result
        ? jsonResponse(true, 'ลบผู้ใช้สำเร็จ')
        : jsonResponse(false, 'ไม่สามารถลบผู้ใช้ได้');
}

function resetPassword()
{
    global $object, $Encrypt;

    if ($_SESSION['user_status'] !== 'admin') {
        jsonResponse(false, 'คุณไม่มีสิทธิ์รีเซ็ตรหัสผ่าน', [], 403);
    }

    $userId = $_POST['user_id'] ?? '';
    $newPassword = 'Ktisgroup';

    $result = $object->Update_Data(
        'tb_users_c050968',
        [
            'fd_user_password'   => $Encrypt->EnCrypt_pass($newPassword),
            'fd_user_updated_at' => date('Y-m-d H:i:s')
        ],
        ['fd_user_id' => intval($userId)]
    );

    $result
        ? jsonResponse(true, 'รีเซ็ตรหัสผ่านสำเร็จ', ['new_password' => $newPassword])
        : jsonResponse(false, 'ไม่สามารถรีเซ็ตรหัสผ่านได้');
}

function getUser()
{
    global $object;

    $userId = $_POST['user_id'] ?? '';
    requireAdminOrOwner($userId);

    $result = $object->ReadData(
        'tb_users_c050968 u',
        'u.fd_user_id, u.fd_user_name, u.fd_user_fullname,
         u.fd_user_status, u.fd_user_div, u.fd_user_active',
        'WHERE u.fd_user_id = ' . intval($userId)
    );

    $result
        ? jsonResponse(true, 'success', ['user' => $result[0]])
        : jsonResponse(false, 'ไม่พบข้อมูลผู้ใช้');
}
