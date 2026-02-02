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

$data = json_decode(file_get_contents("php://input"), true);

$taskID = $Encrypt->DeCrypt_pass($data['taskID']);

$table = 'tb_notifications_c050968 n';
$fields = 'nu.fd_notification_user_id';
$where = 'LEFT JOIN tb_notification_users_c050968 nu ON nu.fd_notification_id = n.fd_notification_id ';
$where .= 'WHERE n.fd_task_id = "' . $taskID . '" AND nu.fd_user_id = "' . $_SESSION['user_id'] . '"';
$result_notification = $object->ReadData($table, $fields, $where);

$table = 'tb_notification_users_c050968';
$fields = array(
    'fd_is_read' => "1",
    'fd_read_at' => $formatted_now,
);
foreach ($result_notification as $notification) {
    $notification_userID = $notification['fd_notification_user_id'];
    $conditionsEdit = array('fd_notification_user_id' => $notification_userID);
    $object->Update_Data($table, $fields,  $conditionsEdit);
}
foreach ($result_notification as &$row) { //เอาไอดีไปเข้ารหัสก่อน
    $row['fd_notification_user_id'] = $Encrypt->EnCrypt_pass($row['fd_notification_user_id']);
}
unset($row); // กัน reference ค้าง
echo json_encode(['status' => 'success', 'data' => $result_notification]);
