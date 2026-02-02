<?php
header('Content-Type: application/json');

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
