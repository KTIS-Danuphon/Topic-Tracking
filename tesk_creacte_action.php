<?php
session_start();
include 'session_check.php';
date_default_timezone_set('Asia/Bangkok');

require_once 'class/crud.class.php';
require_once 'class/util.class.php';
require_once 'class/encrypt.class.php';

$object = new CRUD();
$util = new Util();
$Encrypt = new Encrypt_data();
$now = new DateTime();
$formatted_now = $now->format('Y-m-d H:i:s');

// Initialize response variables
$iconType = 'error';
$taskID = null;
$errorMessage = '';

try {
    // ตรวจสอบว่ามีข้อมูล POST
    if (empty($_POST)) {
        header('Location: task_create.php');
        exit();
    }

    // Validate และ sanitize input
    $taskTitle = $util->testInput($_POST['taskTitle'] ?? '');
    $taskCategory = $util->testInput($_POST['taskCategory'] ?? '');
    $taskDescription = $util->testInput($_POST['taskDescription'] ?? '');
    $taskStatus = $util->testInput($_POST['taskStatus'] ?? 'pending');
    $taskDueDate = $util->testInput($_POST['taskDueDate'] ?? '');
    $taskImportance = $util->testInput($_POST['taskImportance'] ?? '1');
    $mentionedUsers = $_POST['mentionedUsers'] ?? '';
    $additionalUsers = $_POST['additionalUsers'] ?? '';
    $createdBy = $_SESSION['user_id'] ?? null;

    // Validation
    if (empty($taskTitle)) {
        throw new Exception('กรุณาระบุหัวข้อเรื่อง');
    }
    if (strlen($taskTitle) > 200) {
        throw new Exception('หัวข้อเรื่องยาวเกินไป');
    }
    if (empty($taskCategory)) {
        throw new Exception('กรุณาเลือกหมวดหมู่');
    }
    if (empty($createdBy)) {
        throw new Exception('ไม่พบข้อมูล session ผู้ใช้');
    }

    // ตรวจสอบข้อมูลซ้ำ (ไม่ใช้ created_at ในการเช็ค)
    $table = 'tb_topics_c050968';
    $fields = 'fd_topic_id';
    
    // เช็คข้อมูลที่สร้างภายใน 5 นาทีที่ผ่านมา
    $five_minutes_ago = (new DateTime())->modify('-5 minutes')->format('Y-m-d H:i:s');
    $where = "WHERE fd_topic_title = ? 
              AND fd_topic_category = ? 
              AND fd_topic_created_by = ? 
              AND fd_topic_created_at >= ?";
    
    // ใช้ prepared statement (สมมติว่า CRUD class รองรับ)
    $params = [$taskTitle, $taskCategory, $createdBy, $five_minutes_ago];
    $result_task = $object->ReadData($table, $fields, $where, $params);

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

} catch (Exception $e) {
    $iconType = 'error';
    $errorMessage = $e->getMessage();
    error_log("Task Save Error: " . $e->getMessage());
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
        /* คงสไตล์เดิมทั้งหมด */
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

        body.success { background: var(--success-gradient); }
        body.info { background: var(--info-gradient); }
        body.warning { background: var(--warning-gradient); }
        body.error { background: var(--error-gradient); }

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
            0%, 100% {
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
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

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

        .state-success .result-circle,
        .state-info .result-circle,
        .state-warning .result-circle,
        .state-error .result-circle {
            animation: scaleInCircle 0.3s ease-out forwards;
        }

        .state-success .result-icon {
            animation: scaleIn 0.3s ease-out 0.2s forwards;
        }

        .state-warning .result-icon,
        .state-error .result-icon {
            animation: scaleInShake 0.6s ease-out 0.2s forwards;
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

        .saving-text {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .saving-text.loading {
            animation: fadeInOut 2s ease-in-out infinite;
        }

        @keyframes fadeInOut {
            0%, 100% {
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
            0%, 80%, 100% {
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

        .step.completed + .step-connector .step-connector-progress {
            width: 100%;
        }

        .error-message {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            font-size: 0.9rem;
            display: none;
        }

        .error-message.show {
            display: block;
        }

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
            0%, 100% {
                transform: translateY(0) translateX(0) rotate(0deg);
                opacity: 0.4;
            }
            50% {
                transform: translateY(-120px) translateX(80px) rotate(180deg);
                opacity: 0;
            }
        }

        .particle:nth-child(1) { left: 15%; animation-delay: 0s; animation-duration: 4s; }
        .particle:nth-child(2) { left: 35%; animation-delay: 0.5s; animation-duration: 5s; }
        .particle:nth-child(3) { left: 55%; animation-delay: 1s; animation-duration: 4.5s; }
        .particle:nth-child(4) { left: 75%; animation-delay: 1.5s; animation-duration: 5.5s; }

        @media (max-width: 576px) {
            .saving-text {
                font-size: 1.5rem;
            }
            .saving-icon-wrapper {
                width: 80px;
                height: 80px;
            }
            .step-connector {
                width: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>

    <div class="saving-container">
        <div class="saving-icon-wrapper">
            <div class="circle-bg"></div>
            <div class="saving-icon">
                <i class="bi bi-cloud-arrow-up"></i>
            </div>
            <div class="result-circle"></div>
            <i class="result-icon bi bi-check-circle-fill"></i>
        </div>

        <h2 class="saving-text loading">
            <span id="savingText">กำลังบันทึกข้อมูล</span>
            <span class="saving-dots">
                <span>.</span><span>.</span><span>.</span>
            </span>
        </h2>

        <p class="saving-subtext" id="savingSubtext">กรุณารอสักครู่</p>

        <div class="error-message" id="errorMessage">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>

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
    </div>

    <script>
        const iconType = '<?php echo $iconType; ?>';
        const taskID = <?php echo $taskID ? "'$taskID'" : 'null'; ?>;
        const errorMessage = '<?php echo addslashes($errorMessage); ?>';

        const stateConfig = {
            success: {
                icon: 'bi-check-circle-fill',
                title: 'บันทึกข้อมูลสำเร็จ',
                subtitle: 'ข้อมูลของคุณถูกบันทึกเรียบร้อยแล้ว',
                stepLabel: 'บันทึกสำเร็จ'
            },
            warning: {
                icon: 'bi-exclamation-triangle-fill',
                title: 'คำเตือน!',
                subtitle: 'พบข้อมูลซ้ำในระบบ',
                stepLabel: 'ตรวจพบคำเตือน'
            },
            error: {
                icon: 'bi-exclamation-circle-fill',
                title: 'เกิดข้อผิดพลาด!',
                subtitle: 'ไม่สามารถบันทึกข้อมูลได้',
                stepLabel: 'เกิดข้อผิดพลาด'
            }
        };

        function showResult(type) {
            const config = stateConfig[type];
            if (!config) return;

            document.body.className = type;
            document.querySelector('.circle-bg').style.display = 'none';
            document.querySelector('.saving-icon').style.display = 'none';
            document.querySelector('.saving-dots').style.display = 'none';
            document.querySelector('.saving-text').classList.remove('loading');

            const wrapper = document.querySelector('.saving-icon-wrapper');
            wrapper.className = 'saving-icon-wrapper state-' + type;

            const resultIcon = document.querySelector('.result-icon');
            resultIcon.className = 'result-icon ' + config.icon;

            document.getElementById('savingText').textContent = config.title;
            document.getElementById('savingSubtext').textContent = config.subtitle;

            const step3 = document.getElementById('step3');
            step3.querySelector('.step-label').textContent = config.stepLabel;

            if (type === 'error' && errorMessage) {
                document.getElementById('errorMessage').classList.add('show');
            }
        }

        async function savingProcess() {
            try {
                await new Promise(resolve => setTimeout(resolve, 500));
                document.getElementById('step1').classList.add('completed');
                document.getElementById('step2').classList.add('active');

                await new Promise(resolve => setTimeout(resolve, 500));
                document.getElementById('step2').classList.add('completed');
                document.getElementById('step3').classList.add('active');

                await new Promise(resolve => setTimeout(resolve, 500));

                showResult(iconType);

                if (iconType === 'success' || iconType === 'warning') {
                    await new Promise(resolve => setTimeout(resolve, 1500));
                    if (taskID) {
                        window.location.href = 'task_detail.php?taskID=' + taskID;
                    } else {
                        window.location.href = 'index.php';
                    }
                }

            } catch (error) {
                console.error('Process error:', error);
                showResult('error');
            }
        }

        savingProcess();
    </script>
</body>
</html>