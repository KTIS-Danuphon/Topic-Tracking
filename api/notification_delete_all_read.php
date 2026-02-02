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

$table = 'tb_notification_users_c050968';
$fields = array(
    'fd_is_deleted ' => "1",
    'fd_deleted_at' => $formatted_now,
);
$conditionsEdit = array('fd_user_id' => $_SESSION['user_id'], 'fd_is_read' => "1");
$object->Update_Data($table, $fields,  $conditionsEdit);

echo json_encode(['status' => 'success']);
