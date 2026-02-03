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

// ตรวจสอบว่าเป็น Admin หรือไม่
if (!isset($_SESSION['user_status']) || $_SESSION['user_status'] != 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'คุณไม่มีสิทธิ์ในการดำเนินการนี้'
    ]);
    exit();
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            createDivision();
            break;

        case 'update':
            updateDivision();
            break;

        case 'toggle_status':
            toggleDivisionStatus();
            break;

        case 'get_division':
            getDivision();
            break;

        case 'check_usage':
            checkDivisionUsage();
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}

function createDivision()
{
    global $object;

    $divisionName = trim($_POST['division_name'] ?? '');
    $status = $_POST['status'] ?? '1';

    // Validate
    if (empty($divisionName)) {
        echo json_encode([
            'success' => false,
            'message' => 'กรุณากรอกชื่อฝ่าย'
        ]);
        return;
    }

    // ตรวจสอบว่าชื่อฝ่ายซ้ำหรือไม่
    $table = 'tb_divisions_c050968';
    $fields = 'fd_div_id';
    $where = "WHERE fd_div_name = '" . $divisionName . "'";
    $result = $object->ReadData($table, $fields, $where);

    if ($result && count($result) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'ชื่อฝ่ายนี้มีอยู่ในระบบแล้ว'
        ]);
        return;
    }

    // เตรียมข้อมูลสำหรับบันทึก
    $now = date('Y-m-d H:i:s');
    $data = [
        'fd_div_name' => $divisionName,
        'fd_div_active' => $status,
        'fd_div_create' => $now,
        'fd_div_update' => $now
    ];

    // บันทึกข้อมูล
    $result = $object->Insert_Data($table, $data);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'เพิ่มฝ่ายสำเร็จ',
            'division_id' => $result
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่สามารถเพิ่มฝ่ายได้'
        ]);
    }
}

function updateDivision()
{
    global $object;

    $divisionId = $_POST['division_id'] ?? '';
    $divisionName = trim($_POST['division_name'] ?? '');
    $status = $_POST['status'] ?? '1';

    // Validate
    if (empty($divisionId) || empty($divisionName)) {
        echo json_encode([
            'success' => false,
            'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน'
        ]);
        return;
    }

    // ตรวจสอบว่าชื่อฝ่ายซ้ำหรือไม่ (ยกเว้นตัวเอง)
    $table = 'tb_divisions_c050968';
    $fields = 'fd_div_id';
    $where = "WHERE fd_div_name = '" . $divisionName . "' AND fd_div_id != " . intval($divisionId);
    $result = $object->ReadData($table, $fields, $where);

    if ($result && count($result) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'ชื่อฝ่ายนี้มีอยู่ในระบบแล้ว'
        ]);
        return;
    }

    // เตรียมข้อมูลสำหรับอัปเดต

    $fields = array(
        'fd_div_name' => $divisionName,
        'fd_div_active' => $status,
        'fd_div_update' => date('Y-m-d H:i:s')
    );
    $conditionsEdit = array('fd_div_id' => intval($divisionId));
    $result = $object->Update_Data($table, $fields,  $conditionsEdit);


    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'บันทึกข้อมูลสำเร็จ'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่สามารถบันทึกข้อมูลได้'
        ]);
    }
}

function toggleDivisionStatus()
{
    global $object;

    $divisionId = $_POST['division_id'] ?? '';
    $newStatus = $_POST['status'] ?? '';

    // ตรวจสอบว่ามีผู้ใช้ในฝ่ายนี้หรือไม่ ก่อนปิดการใช้งาน
    if ($newStatus == '0') {
        $table = 'tb_users_c050968';
        $fields = 'COUNT(*) as user_count';
        $where = "WHERE fd_user_div = '" . $divisionId . "' AND fd_user_active = '1'";
        $result = $object->ReadData($table, $fields, $where);

        if ($result && $result[0]['user_count'] > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'ไม่สามารถปิดการใช้งานได้ เนื่องจากยังมีผู้ใช้ที่ใช้งานอยู่ในฝ่ายนี้ (' . $result[0]['user_count'] . ' คน)',
                'has_active_users' => true,
                'active_user_count' => $result[0]['user_count']
            ]);
            return;
        }
    }

    $table = 'tb_divisions_c050968';

    $fields = array(
        'fd_div_active' => $newStatus,
        'fd_div_update' => date('Y-m-d H:i:s')
    );
    $conditionsEdit = array('fd_div_id' => intval($divisionId));
    $result = $object->Update_Data($table, $fields,  $conditionsEdit);
    if ($result) {
        $statusText = $newStatus == '1' ? 'เปิดใช้งาน' : 'ปิดการใช้งาน';
        echo json_encode([
            'success' => true,
            'message' => $statusText . 'ฝ่ายสำเร็จ'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่สามารถเปลี่ยนสถานะฝ่ายได้'
        ]);
    }
}

function getDivision()
{
    global $object;

    $divisionId = $_POST['division_id'] ?? '';

    $table = 'tb_divisions_c050968';
    $fields = 'fd_div_id, fd_div_name, fd_div_active';
    $where = 'WHERE fd_div_id = ' . intval($divisionId);

    $result = $object->ReadData($table, $fields, $where);

    if ($result && count($result) > 0) {
        echo json_encode([
            'success' => true,
            'division' => $result[0]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่พบข้อมูลฝ่าย'
        ]);
    }
}

function checkDivisionUsage()
{
    global $object;

    $divisionId = $_POST['division_id'] ?? '';

    // นับจำนวนผู้ใช้ที่ใช้งานอยู่ในฝ่ายนี้ โดยดูจาก fd_user_div
    $table = 'tb_users_c050968';
    $fields = 'COUNT(*) as total_users, 
               SUM(CASE WHEN fd_user_active = "1" THEN 1 ELSE 0 END) as active_users';
    $where = "WHERE fd_user_div = '" . $divisionId . "'";
    $result = $object->ReadData($table, $fields, $where);

    if ($result) {
        echo json_encode([
            'success' => true,
            'total_users' => intval($result[0]['total_users']),
            'active_users' => intval($result[0]['active_users'])
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่สามารถตรวจสอบข้อมูลได้'
        ]);
    }
}
