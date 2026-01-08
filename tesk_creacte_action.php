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

// รับข้อมูลจาก POST (ถ้ามี)
$formData = isset($_POST) ? $_POST : [];

// ถ้าไม่มีข้อมูลให้กลับไปหน้าแรก
if (empty($formData)) {
    header('Location: tesk_create.php');
    exit();
}

$taskTitle = $util->testInput($formData['taskTitle']); // หัวข้อเรื่อง
$taskCategory = $util->testInput($formData['taskCategory']); // หมวดหมู่
$taskDescription = $util->testInput($formData['taskDescription']); // รายละเอียด
$taskStatus = $util->testInput($formData['taskStatus']); // สถานะ
$taskDueDate = $util->testInput($formData['taskDueDate']); // กำหนดวันครบกำหนด
$taskImportance = $util->testInput($formData['taskImportance']); // ความสำคัญ ดาว
$mentionedUsers = $formData['mentionedUsers']; // ผู้ที่ถูกกล่าวถึง
$additionalUsers = $formData['additionalUsers']; // ผู้เข้าร่วมเพิ่มเติม
$createdBy = $_SESSION['user_id']; // ผู้สร้างงาน (จาก session)

// $password  = $Encrypt->EnCrypt_pass($util->testInput($_POST['password']));
$table = 'tb_topics_c050968';
$fields = 'fd_topic_id, fd_topic_title, fd_topic_category, fd_topic_created_at';
$where = 'WHERE fd_topic_title = "' . $taskTitle . '" AND fd_topic_category = "' . $taskCategory . '" AND fd_topic_created_at = "' . $formatted_now . '"';
$result_task = $object->ReadData($table, $fields, $where);
$iconType = 'warning';
if (!empty($result_task)) {
    // พบข้อมูลซ้ำ
    $taskID = $result_task[0]['fd_topic_id'];
    $iconType = 'warning';
    $errorMessage = 'พบข้อมูลซ้ำในระบบ';
} else {
    // บันทึกข้อมูลใหม่
    $fields_data = [
        'fd_topic_title' => $taskTitle,
        'fd_topic_category' => $taskCategory,
        'fd_topic_detail' => $taskDescription,
        'fd_topic_mentioned' => $mentionedUsers,
        'fd_topic_status' => $taskStatus,
        'fd_topic_participant' => $additionalUsers,
        'fd_topic_created_by' => $createdBy,
        'fd_topic_importance' => $taskImportance,
        'fd_topic_private' => '1',
        'fd_topic_active' => '1',
        'fd_topic_created_at' => $formatted_now,
        'fd_topic_updated_at' => $formatted_now,
    ];

    $taskID = $object->Insert_Data($table, $fields_data);

    if (!$taskID) {
        throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
    }

    $iconType = 'success';
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กำลังบันทึกข้อมูล... - ระบบติดตามงาน</title>
    <link rel="icon" href="ktis.svg" type="image/svg+xml">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --success-color: #10b981;
            --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            --info-color: #3b82f6;
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --warning-color: #f59e0b;
            --error-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            --error-color: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--info-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            transition: background 0.6s ease;
        }

        body.success {
            background: var(--success-gradient);
        }

        body.info {
            background: var(--info-gradient);
        }

        body.warning {
            background: var(--warning-gradient);
        }

        body.error {
            background: var(--error-gradient);
        }

        .saving-container {
            text-align: center;
            color: white;
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .saving-icon-wrapper {
            width: 100px;
            height: 100px;
            margin: 0 auto 2rem;
            position: relative;
        }

        .circle-bg {
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            position: absolute;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.5;
            }
        }

        .saving-icon {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
        }

        .saving-icon i {
            font-size: 3rem;
            animation: bounce 1s ease-in-out infinite;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        /* Result Circle & Icon Base */
        .result-circle {
            width: 100%;
            height: 100%;
            border: 4px solid white;
            border-radius: 50%;
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            transform: scale(0);
        }

        .result-icon {
            font-size: 3.5rem;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            opacity: 0;
        }

        /* Success Animation */
        .state-success .result-circle {
            animation: scaleInCircle 0.3s ease-out forwards;
        }

        .state-success .result-icon {
            animation: scaleIn 0.3s ease-out 0.2s forwards;
        }

        @keyframes scaleInCircle {
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes scaleIn {
            to {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
        }

        /* Info Animation */
        .state-info .result-circle {
            animation: scaleInCircle 0.3s ease-out forwards;
        }

        .state-info .result-icon {
            animation: scaleInBounce 0.5s ease-out 0.2s forwards;
        }

        @keyframes scaleInBounce {
            0% {
                transform: translate(-50%, -50%) scale(0);
                opacity: 0;
            }

            50% {
                transform: translate(-50%, -50%) scale(1.2);
                opacity: 1;
            }

            100% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
        }

        /* Warning Animation */
        .state-warning .result-circle {
            animation: scaleInCircle 0.3s ease-out forwards;
        }

        .state-warning .result-icon {
            animation: scaleInShake 0.6s ease-out 0.2s forwards;
        }

        @keyframes scaleInShake {
            0% {
                transform: translate(-50%, -50%) scale(0);
                opacity: 0;
            }

            40% {
                transform: translate(-50%, -50%) scale(1.1);
                opacity: 1;
            }

            50% {
                transform: translate(-50%, -50%) scale(1.1) rotate(-5deg);
            }

            60% {
                transform: translate(-50%, -50%) scale(1.1) rotate(5deg);
            }

            70% {
                transform: translate(-50%, -50%) scale(1.1) rotate(-5deg);
            }

            80% {
                transform: translate(-50%, -50%) scale(1.1) rotate(5deg);
            }

            100% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
        }

        /* Error Animation */
        .state-error .result-circle {
            animation: scaleInCircle 0.3s ease-out forwards;
        }

        .state-error .result-icon {
            animation: scaleInShake 0.5s ease-out 0.2s forwards;
        }

        /* Error X Mark */
        .error-x {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
        }

        .state-error .error-x {
            animation: fadeIn 0.3s ease-out 0.5s forwards;
        }

        .error-x::before,
        .error-x::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 4px;
            background: white;
            border-radius: 2px;
        }

        .error-x::before {
            transform: translate(-50%, -50%) rotate(45deg);
        }

        .error-x::after {
            transform: translate(-50%, -50%) rotate(-45deg);
        }

        .state-error .error-x::before {
            animation: drawLine 0.3s ease-out 0.6s forwards;
        }

        .state-error .error-x::after {
            animation: drawLine 0.3s ease-out 0.7s forwards;
        }

        @keyframes drawLine {
            to {
                width: 60%;
            }
        }

        .saving-text {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .saving-text.loading {
            animation: fadeInOut 2s ease-in-out infinite;
        }

        @keyframes fadeInOut {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .saving-subtext {
            font-size: 1rem;
            opacity: 0.9;
            font-weight: 400;
        }

        .saving-dots {
            display: inline-block;
        }

        .saving-dots span {
            animation: blink 1.4s infinite;
            animation-fill-mode: both;
        }

        .saving-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .saving-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes blink {

            0%,
            80%,
            100% {
                opacity: 0;
            }

            40% {
                opacity: 1;
            }
        }

        .progress-steps {
            margin-top: 2.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .step-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .step.active .step-icon {
            background: white;
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
        }

        body.success .step.active .step-icon,
        body.success .step.completed .step-icon {
            color: var(--success-color);
        }

        body.info .step.active .step-icon,
        body.info .step.completed .step-icon {
            color: var(--info-color);
        }

        body.warning .step.active .step-icon,
        body.warning .step.completed .step-icon {
            color: var(--warning-color);
        }

        body.error .step.active .step-icon,
        body.error .step.completed .step-icon {
            color: var(--error-color);
        }

        .step.completed .step-icon {
            background: white;
        }

        .step.error-step .step-icon {
            background: white;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .step-label {
            font-size: 0.85rem;
            opacity: 0.7;
        }

        .step.active .step-label {
            opacity: 1;
            font-weight: 600;
        }

        .step-connector {
            width: 60px;
            height: 2px;
            background: rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .step-connector-progress {
            height: 100%;
            background: white;
            width: 0;
            transition: width 0.5s ease;
        }

        .step.completed+.step-connector .step-connector-progress {
            width: 100%;
        }

        /* Result Actions */
        .result-actions {
            margin-top: 2rem;
            display: none;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: slideUp 0.5s ease-out;
        }

        .result-actions.show {
            display: flex;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-action {
            padding: 0.875rem 1.75rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary-white {
            background: white;
            color: var(--error-color);
        }

        body.success .btn-primary-white {
            color: var(--success-color);
        }

        body.info .btn-primary-white {
            color: var(--info-color);
        }

        body.warning .btn-primary-white {
            color: var(--warning-color);
        }

        .btn-primary-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: white;
            transform: translateY(-2px);
        }

        /* Result Details */
        .result-details {
            margin-top: 1.5rem;
            padding: 1.25rem;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: none;
            animation: slideUp 0.5s ease-out;
        }

        .result-details.show {
            display: block;
        }

        .result-message {
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 0.5rem;
        }

        .result-code {
            font-size: 0.8rem;
            opacity: 0.7;
            font-family: 'Courier New', monospace;
        }

        /* Test Buttons */
        .test-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 1000;
        }

        .test-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            color: white;
        }

        .test-btn.btn-success {
            background: var(--success-color);
        }

        .test-btn.btn-info {
            background: var(--info-color);
        }

        .test-btn.btn-warning {
            background: var(--warning-color);
        }

        .test-btn.btn-error {
            background: var(--error-color);
        }

        .test-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        /* Floating particles effect */
        .particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: white;
            border-radius: 50%;
            opacity: 0.4;
            animation: float 5s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) translateX(0) rotate(0deg);
                opacity: 0.4;
            }

            50% {
                transform: translateY(-120px) translateX(80px) rotate(180deg);
                opacity: 0;
            }
        }

        .particle:nth-child(1) {
            left: 15%;
            animation-delay: 0s;
            animation-duration: 4s;
        }

        .particle:nth-child(2) {
            left: 35%;
            animation-delay: 0.5s;
            animation-duration: 5s;
        }

        .particle:nth-child(3) {
            left: 55%;
            animation-delay: 1s;
            animation-duration: 4.5s;
        }

        .particle:nth-child(4) {
            left: 75%;
            animation-delay: 1.5s;
            animation-duration: 5.5s;
        }

        .particle:nth-child(5) {
            left: 25%;
            animation-delay: 2s;
            animation-duration: 4s;
        }

        .particle:nth-child(6) {
            left: 65%;
            animation-delay: 2.5s;
            animation-duration: 5s;
        }

        .particle:nth-child(7) {
            left: 45%;
            animation-delay: 3s;
            animation-duration: 4.5s;
        }

        .particle:nth-child(8) {
            left: 85%;
            animation-delay: 3.5s;
            animation-duration: 5.5s;
        }

        @media (max-width: 576px) {
            .saving-text {
                font-size: 1.5rem;
            }

            .saving-subtext {
                font-size: 0.9rem;
            }

            .saving-icon-wrapper {
                width: 80px;
                height: 80px;
            }

            .saving-icon i {
                font-size: 2.5rem;
            }

            .result-icon {
                font-size: 3rem;
            }

            .progress-steps {
                gap: 0.5rem;
                flex-wrap: wrap;
            }

            .step-connector {
                width: 40px;
            }

            .step-icon {
                width: 35px;
                height: 35px;
                font-size: 1rem;
            }

            .step-label {
                font-size: 0.75rem;
            }

            .result-actions {
                flex-direction: column;
                width: 100%;
                padding: 0 1rem;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }

            .test-buttons {
                top: 10px;
                right: 10px;
                gap: 6px;
            }

            .test-btn {
                padding: 6px 12px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>

<body>
    <!-- Test Buttons -->
    <!-- <div class="test-buttons">
        <button class="test-btn btn-success" onclick="testResult('success')">✓ Success</button>
        <button class="test-btn btn-info" onclick="testResult('info')">ℹ Info</button>
        <button class="test-btn btn-warning" onclick="testResult('warning')">⚠ Warning</button>
        <button class="test-btn btn-error" onclick="testResult('error')">✕ Error</button>
    </div> -->

    <!-- Floating particles -->
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>

    <!-- Saving Container -->
    <div class="saving-container">
        <div class="saving-icon-wrapper">
            <div class="circle-bg"></div>
            <div class="saving-icon">
                <i class="bi bi-cloud-arrow-up"></i>
            </div>
            <!-- Result Elements -->
            <div class="result-circle"></div>
            <i class="result-icon bi bi-check-circle-fill"></i>
            <div class="error-x"></div>
        </div>

        <h2 class="saving-text loading">
            <span id="savingText">กำลังบันทึกข้อมูล</span>
            <span class="saving-dots">
                <span>.</span><span>.</span><span>.</span>
            </span>
        </h2>

        <p class="saving-subtext" id="savingSubtext">กรุณารอสักครู่</p>

        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="step active" id="step1">
                <div class="step-icon">
                    <i class="bi bi-file-earmark-check"></i>
                </div>
                <div class="step-label">ตรวจสอบข้อมูล</div>
            </div>

            <div class="step-connector">
                <div class="step-connector-progress"></div>
            </div>

            <div class="step" id="step2">
                <div class="step-icon">
                    <i class="bi bi-cloud-upload"></i>
                </div>
                <div class="step-label">อัพโหลดข้อมูล</div>
            </div>

            <div class="step-connector">
                <div class="step-connector-progress"></div>
            </div>

            <div class="step" id="step3">
                <div class="step-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="step-label">บันทึกสำเร็จ</div>
            </div>
        </div>

        <!-- Result Details (Hidden by default) -->
        <!-- <div class="result-details" id="resultDetails">
            <div class="result-message" id="resultMessage">
                ข้อความแจ้งเตือน
            </div>
            <div class="result-code" id="resultCode">
                รหัส: CODE_001
            </div>
        </div> -->

        <!-- Result Actions (Hidden by default) -->
        <!-- <div class="result-actions" id="resultActions">
            <button class="btn-action btn-primary-white" onclick="handlePrimaryAction()">
                <i class="bi bi-arrow-left"></i>
                <span id="primaryBtnText">ดำเนินการต่อ</span>
            </button>
            <a href="index.php" class="btn-action btn-outline">
                <i class="bi bi-house"></i>
                กลับหน้าแรก
            </a>
        </div> -->
    </div>

    <script>
        const iconType = '<?php echo $iconType; ?>';

        // Configuration for each state
        const stateConfig = {
            success: {
                icon: 'bi-check-circle-fill',
                title: 'บันทึกข้อมูลสำเร็จ',
                subtitle: 'ข้อมูลของคุณถูกบันทึกเรียบร้อยแล้ว',
                message: 'การบันทึกข้อมูลเสร็จสมบูรณ์',
                code: 'SUCCESS_200',
                stepLabel: 'บันทึกสำเร็จ',
                primaryBtn: 'ดูรายละเอียด',
                primaryIcon: 'bi-eye'
            },
            info: {
                icon: 'bi-info-circle-fill',
                title: 'ข้อมูลสำคัญ',
                subtitle: 'มีข้อมูลที่ต้องการแจ้งให้คุณทราบ',
                message: 'กรุณาตรวจสอบข้อมูลก่อนดำเนินการต่อ',
                code: 'INFO_100',
                stepLabel: 'ข้อมูลเพิ่มเติม',
                primaryBtn: 'รับทราบ',
                primaryIcon: 'bi-check-lg'
            },
            warning: {
                icon: 'bi-exclamation-triangle-fill',
                title: 'คำเตือน!',
                subtitle: 'พบข้อมูลซ้ำในระบบ',
                message: 'มีบางข้อมูลที่อาจต้องแก้ไข แต่สามารถดำเนินการต่อได้',
                code: 'WARNING_300',
                stepLabel: 'ตรวจพบคำเตือน',
                primaryBtn: 'ดำเนินการต่อ',
                primaryIcon: 'bi-arrow-right'
            },
            error: {
                icon: 'bi-exclamation-circle-fill',
                title: 'เกิดข้อผิดพลาด!',
                subtitle: 'ไม่สามารถบันทึกข้อมูลได้',
                message: 'เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง',
                code: 'ERROR_500',
                stepLabel: 'เกิดข้อผิดพลาด',
                primaryBtn: 'ลองอีกครั้ง',
                primaryIcon: 'bi-arrow-clockwise'
            }
        };

        // ฟังก์ชันแสดงผลลัพธ์
        function showResult(type) {
            console.log('Showing result for type:', type);
            const config = stateConfig[type];
            if (!config) return;

            // เปลี่ยนสีพื้นหลัง
            document.body.className = type;

            // ซ่อน loading elements
            document.querySelector('.circle-bg').style.display = 'none';
            document.querySelector('.saving-icon').style.display = 'none';
            document.querySelector('.saving-dots').style.display = 'none';
            document.querySelector('.saving-text').classList.remove('loading');

            // แสดง result icon
            const wrapper = document.querySelector('.saving-icon-wrapper');
            wrapper.className = 'saving-icon-wrapper state-' + type;

            const resultIcon = document.querySelector('.result-icon');
            resultIcon.className = 'result-icon ' + config.icon;

            // เปลี่ยนข้อความ
            document.getElementById('savingText').textContent = config.title;
            document.getElementById('savingSubtext').textContent = config.subtitle;

            // แสดงรายละเอียด
            // document.getElementById('resultMessage').textContent = config.message;
            // document.getElementById('resultCode').textContent = `รหัส: ${config.code}`;
            // document.getElementById('resultDetails').classList.add('show');

            // แสดงปุ่ม action
            // document.getElementById('primaryBtnText').textContent = config.primaryBtn;
            // document.querySelector('#resultActions .btn-primary-white i').className = config.primaryIcon;
            // document.getElementById('resultActions').classList.add('show');

            // อัพเดท step
            const step3 = document.getElementById('step3');
            step3.querySelector('.step-label').textContent = config.stepLabel;
            if (type === 'error' || type === 'warning') {
                step3.classList.add('error-step');
            }
        }

        // ฟังก์ชันจำลองการบันทึก
        async function savingProcess(forceType = null) {
            try {
                // Step 1: ตรวจสอบข้อมูล
                await new Promise(resolve => setTimeout(resolve, 500));
                document.getElementById('step1').classList.add('completed');
                document.getElementById('step2').classList.add('active');

                // Step 2: อัพโหลดข้อมูล
                await new Promise(resolve => setTimeout(resolve, 500));
                document.getElementById('step2').classList.add('completed');
                document.getElementById('step3').classList.add('active');

                // Step 3: แสดงผลลัพธ์
                await new Promise(resolve => setTimeout(resolve, 500));

                // ใช้ type ที่กำหนดหรือสุ่ม
                let resultType = forceType;
                if (!resultType) {
                    const types = ['success', 'info', 'warning', 'error'];
                    const random = Math.random();
                    if (random < 0.5) resultType = 'success';
                    else if (random < 0.7) resultType = 'info';
                    else if (random < 0.85) resultType = 'warning';
                    else resultType = 'error';
                }

                showResult(resultType);

                // Auto redirect สำหรับ success (ถ้าต้องการ)
                if (resultType === 'success' || resultType === 'warning') {
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    window.location.href = 'task_detail.php';
                }

            } catch (error) {
                console.error('Process error:', error);
                showResult('error');
            }
        }

        // ฟังก์ชัน handle primary action
        function handlePrimaryAction() {
            const currentState = document.body.className;

            if (currentState === 'success') {
                window.location.href = 'task_detail.php';
            } else if (currentState === 'info' || currentState === 'warning') {
                window.location.href = 'index.php';
            } else if (currentState === 'error') {
                window.history.back();
            }
        }

        // ฟังก์ชันทดสอบ
        function testResult(type) {
            savingProcess(type);
            // Reset หน้า
            // window.location.href = 'task_saving.php?type=' + type;
        }

        // เริ่มกระบวนการ
        if (iconType) {
            console.log('Using predefined icon type:', iconType);
            savingProcess(iconType);
        } else {
            savingProcess();
        }

        /*
        // สำหรับ API จริง:
        async function saveTaskData() {
            try {
                document.getElementById('step1').classList.add('active');
                await new Promise(resolve => setTimeout(resolve, 500));
                
                document.getElementById('step1').classList.add('completed');
                document.getElementById('step2').classList.add('active');
                
                const formData = new FormData();
                <?php if (!empty($formData)): ?>
                <?php foreach ($formData as $key => $value): ?>
                formData.append('<?php echo $key; ?>', '<?php echo htmlspecialchars($value, ENT_QUOTES); ?>');
                <?php endforeach; ?>
                <?php endif; ?>
                
                const response = await fetch('api/save_task.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                document.getElementById('step2').classList.add('completed');
                document.getElementById('step3').classList.add('active');
                
                if (result.success) {
                    showResult('success');
                    await new Promise(resolve => setTimeout(resolve, 2000));
                    window.location.href = `task_detail.php?taskID=${result.taskID}`;
                } else if (result.warning) {
                    showResult('warning');
                } else {
                    throw new Error(result.message || 'เกิดข้อผิดพลาด');
                }
                
            } catch (error) {
                console.error('Save error:', error);
                showResult('error');
            }
        }
        
        saveTaskData();
        */
    </script>
</body>

</html>