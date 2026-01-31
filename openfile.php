<?php
include 'session_check.php';

require_once 'class/crud.class.php';
require_once 'class/encrypt.class.php';

$object = new CRUD();
$Encrypt = new Encrypt_data();

// รับค่าพารามิเตอร์
$taskID_encrypted = $_GET['taskid'] ?? '';
$fileID_encrypted = $_GET['file_id'] ?? '';
$viewMode = isset($_GET['view']) && $_GET['view'] == '1';

if (empty($taskID_encrypted) || empty($fileID_encrypted)) {
    die('ข้อมูลไม่ครบถ้วน');
}

// Decrypt IDs
$taskID = intval($Encrypt->DeCrypt_pass($taskID_encrypted));
$fileID = intval($Encrypt->DeCrypt_pass($fileID_encrypted));

if (empty($taskID) || empty($fileID)) {
    die('ข้อมูลไม่ถูกต้อง');
}

// เช็คสิทธิ์การเข้าถึงไฟล์
$table = 'tb_topic_files_c050968 f';
$fields = 'f.fd_file_id, f.fd_file_original_name, f.fd_file_saved_name, f.fd_file_path, f.fd_file_size, f.fd_file_type, f.fd_file_task_id';
$where = 'INNER JOIN tb_topics_c050968 t ON t.fd_topic_id = f.fd_file_task_id ';
$where .= 'WHERE f.fd_file_id = "' . $fileID . '" AND f.fd_file_task_id = "' . $taskID . '" AND f.fd_file_active = "1" ';

// เช็คสิทธิ์ตามประเภทผู้ใช้
switch ($_SESSION['user_status']) {
    case 'admin':
    case 'executive':
        break;
    case 'user':
    default:
        $userId = (int) $_SESSION['user_id'];
        $where .= 'AND ( ';
        $where .= 't.fd_topic_created_by = ' . $userId . ' ';
        $where .= 'OR t.fd_topic_participant = "[' . $userId . ']" ';
        $where .= 'OR t.fd_topic_participant LIKE "[' . $userId . ',%" ';
        $where .= 'OR t.fd_topic_participant LIKE "%,' . $userId . ',%" ';
        $where .= 'OR t.fd_topic_participant LIKE "%,' . $userId . ']" ';
        $where .= ') ';
        break;
}

$result = $object->ReadData($table, $fields, $where);

if (empty($result)) {
    die('ไม่พบไฟล์หรือคุณไม่มีสิทธิ์เข้าถึง');
}

$file = $result[0];
$filePath = $file['fd_file_path'] . $file['fd_file_saved_name'];

// เช็คว่าไฟล์มีอยู่จริง
if (!file_exists($filePath)) {
    die('ไม่พบไฟล์ในระบบ: ' . htmlspecialchars($filePath));
}

// เช็คว่าอ่านไฟล์ได้
if (!is_readable($filePath)) {
    die('ไม่สามารถอ่านไฟล์ได้ กรุณาตรวจสอบสิทธิ์');
}

// กำหนด MIME type
$mimeType = $file['fd_file_type'];
if (empty($mimeType) || $mimeType == 'application/octet-stream') {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detectedMime = finfo_file($finfo, $filePath);
    finfo_close($finfo);
    $mimeType = $detectedMime ?: 'application/octet-stream';
}

$fileName = $file['fd_file_original_name'];

// ถ้าเป็นโหมดดาวน์โหลด ส่งไฟล์ออกไปเลย
if (!$viewMode) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . filesize($filePath));
    header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Expires: 0');
    
    readfile($filePath);
    exit();
}

// ถ้าเป็นโหมดแสดง แสดง HTML wrapper
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Topic Tracking - File</title>
    <link rel="icon" href="ktis.svg" type="image/svg+xml">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            overflow: hidden;
        }
        
        .header {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .file-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .file-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        
        .file-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 1.1rem;
        }
        
        .btn-download {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .viewer-container {
            width: 100%;
            height: calc(100vh - 70px);
            background: #f1f5f9;
        }
        
        iframe, object, embed, img {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        img {
            object-fit: contain;
            background: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="file-info">
            <div class="file-icon">📄</div>
            <div class="file-name"><?= htmlspecialchars($fileName) ?></div>
        </div>
        <a href="?taskid=<?= urlencode($taskID_encrypted) ?>&file_id=<?= urlencode($fileID_encrypted) ?>" 
           class="btn-download" download>
            <span>⬇</span> ดาวน์โหลด
        </a>
    </div>
    
    <div class="viewer-container">
        <?php
        // แสดงไฟล์ตามประเภท
        if (strpos($mimeType, 'image/') === 0) {
            // รูปภาพ
            $base64 = base64_encode(file_get_contents($filePath));
            echo '<img src="data:' . $mimeType . ';base64,' . $base64 . '" alt="' . htmlspecialchars($fileName) . '">';
        } elseif ($mimeType === 'application/pdf') {
            // PDF
            $base64 = base64_encode(file_get_contents($filePath));
            echo '<embed src="data:application/pdf;base64,' . $base64 . '" type="application/pdf">';
        } else {
            // ไฟล์อื่นๆ ดาวน์โหลดอัตโนมัติ
            echo '<div style="display: flex; align-items: center; justify-content: center; height: 100%; flex-direction: column; gap: 1rem;">';
            echo '<p style="font-size: 1.2rem; color: #64748b;">ไม่สามารถแสดงตัวอย่างไฟล์ประเภทนี้ได้</p>';
            echo '<a href="?taskid=' . urlencode($taskID_encrypted) . '&file_id=' . urlencode($fileID_encrypted) . '" class="btn-download">ดาวน์โหลดไฟล์</a>';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>