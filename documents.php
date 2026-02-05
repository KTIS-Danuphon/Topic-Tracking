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
$document_name = $Encrypt->EnCrypt_pass('คู่มือการใช้งานระบบTopicTracking');
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Topic Tracking - คู่มือ</title>
    <link rel="icon" href="ktis.svg" type="image/svg+xml">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <?php include 'style_menu.php'; ?>
    <style>
        body {
            background: #f4f6f9;
            font-family: system-ui, sans-serif;
            padding: 30px;
        }

        .manual-wrapper {
            max-width: 900px;
            margin: auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, .08);
            padding: 32px;
        }

        .manual-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .manual-desc {
            color: #666;
            margin-bottom: 24px;
        }

        .manual-section {
            margin-bottom: 32px;
        }

        .manual-section h3 {
            font-size: 1.25rem;
            font-weight: 600;
            border-left: 4px solid #0d6efd;
            padding-left: 10px;
            margin-bottom: 12px;
        }

        .manual-section p {
            line-height: 1.7;
            color: #333;
        }

        .manual-section ul {
            padding-left: 20px;
        }

        .manual-section li {
            margin-bottom: 6px;
        }


        .manual-wrapper {
            background: #ffffff;
            border-radius: 12px;
            padding: 32px;
            max-width: 900px;
            margin: auto;
            box-shadow: 0 4px 18px rgba(0, 0, 0, .08);
        }

        .manual-title {
            font-size: 1.6rem;
            font-weight: 700;
        }

        .manual-desc {
            color: #666;
            margin-top: 4px;
        }

        .manual-section {
            margin-top: 24px;
        }

        .btn-manual {
            margin-top: 12px;
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            background: #0d6efd;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
            transition: background .2s ease;
        }

        .btn-manual:hover {
            background: #0b5ed7;
        }
    </style>
</head>

<body>
    <?php include 'menu.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid">
            <div class="manual-wrapper">

                <div class="manual-header mb-4">
                    <div class="manual-title">📘 คู่มือการใช้งานระบบ</div>
                    <div class="manual-desc">
                        เอกสารนี้จัดทำขึ้นเพื่ออธิบายวิธีการใช้งานระบบสำหรับผู้ใช้งาน
                    </div>
                </div>

                <div class="manual-section">
                    <p>
                        สามารถเปิดดูคู่มือการใช้งานระบบในรูปแบบไฟล์ PDF ได้โดยกดปุ่มด้านล่าง
                    </p>

                    <button class="btn-manual" onclick="viewFile()">
                        📄 เปิดคู่มือการใช้งาน
                    </button>
                </div>

            </div>
        </div>
    </main>
    <script>
        function viewFile() {
            const fileName = "<?= $document_name ?>";
            // เปิดไฟล์ในแท็บใหม่เพื่อดู
            window.open(
                'openfile_document.php?document_name=' + encodeURIComponent(fileName) +
                '&file_id=' + encodeURIComponent(fileName) +
                '&view=1',
                '_blank'
            );
        }
    </script>

</body>

</html>