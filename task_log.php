<?php
function Task_log($taskID, $type, $name_create = null, $object, $formatted_now)
{
    $table = 'tb_topic_log_c050968';

    // เช็คว่ามี log งานนี้ในเวลานี้แล้วหรือยัง
    $fields = 'COUNT(*) AS countlog';
    $where = 'WHERE fd_topic_id = "' . intval($taskID) . '"
          AND fd_topic_log_type = "' . $type . '"';

    $check = $object->ReadData($table, $fields, $where);

    if (!empty($check) && $check[0]['countlog'] > 0) {
        return; // มีแล้ว ไม่ insert
    }

    // สร้างข้อความ log
    switch ($type) {
        case 'created':
            $text_log = "สร้างงานโดย " . $name_create;
            break;
        case 'status':
            $text_log = "แก้ไขงานโดย " . $name_create;
            break;
        case 'comment':
            $text_log = "เพิ่มความคิดเห็นโดย " . $name_create;
            break;
        case 'file':
            $text_log = "จัดการไฟล์ไฟล์โดย " . $name_create;
            break;
        case 'delete-task':
            $text_log = "ลบงานโดย " . $name_create;
            break;
        default:
            return;
    }

    // insert
    $fields_insert = [
        'fd_topic_id' => $taskID,
        'fd_topic_log_type' => $type,
        'fd_topic_log_text' => $text_log,
        'fd_topic_log_time' => $formatted_now
    ];

    $object->Insert_Data($table, $fields_insert);
}
