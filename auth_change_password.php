<?php
session_start();

if (empty($_SESSION['user_id'])) {
    $_SESSION["SUCCESS_LOGIN"] = array("error", "เซสชันหมดอายุ <br>กรุณาเข้าสู่ระบบใหม่");
    header("Location: auth_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนรหัสผ่าน - ระบบติดตามงาน</title>
    <link rel="icon" href="ktis.svg" type="image/svg+xml">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .change-password-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }

        .change-password-header {
            background: var(--primary-gradient);
            padding: 2rem;
            text-align: center;
            color: white;
        }

        .change-password-header i {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .change-password-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .change-password-header p {
            font-size: 0.95rem;
            opacity: 0.95;
            margin-bottom: 0;
        }

        .change-password-body {
            padding: 2rem;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-warning i {
            color: #ff9800;
            font-size: 1.2rem;
            margin-right: 0.5rem;
        }

        .alert-warning strong {
            color: #856404;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-label .required {
            color: #dc2626;
        }

        .password-input-wrapper {
            position: relative;
        }

        .form-control {
            padding: 0.875rem 3rem 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
            outline: none;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 0.5rem;
            font-size: 1.1rem;
            transition: color 0.3s;
        }

        .toggle-password:hover {
            color: var(--primary-color);
        }

        .password-requirements {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .password-requirements h6 {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.75rem;
        }

        .requirement-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .requirement-item i {
            font-size: 0.9rem;
            color: #cbd5e1;
        }

        .requirement-item.valid i {
            color: #10b981;
        }

        .requirement-item.invalid i {
            color: #ef4444;
        }

        .password-strength {
            margin-top: 0.5rem;
        }

        .strength-label {
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .strength-bar {
            height: 6px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .strength-bar-fill {
            height: 100%;
            transition: all 0.3s;
            border-radius: 10px;
        }

        .strength-weak {
            width: 33%;
            background: #ef4444;
        }

        .strength-medium {
            width: 66%;
            background: #f59e0b;
        }

        .strength-strong {
            width: 100%;
            background: #10b981;
        }

        .btn-submit {
            width: 100%;
            padding: 0.875rem;
            background: var(--primary-gradient);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .error-message {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            color: #991b1b;
            padding: 0.875rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: none;
        }

        .error-message i {
            margin-right: 0.5rem;
        }

        .success-message {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
            padding: 0.875rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: none;
        }

        .success-message i {
            margin-right: 0.5rem;
        }

        .match-indicator {
            font-size: 0.85rem;
            margin-top: 0.5rem;
            padding: 0.5rem;
            border-radius: 6px;
            display: none;
        }

        .match-indicator.match {
            background: #d1fae5;
            color: #065f46;
            display: block;
        }

        .match-indicator.no-match {
            background: #fee2e2;
            color: #991b1b;
            display: block;
        }

        .match-indicator i {
            margin-right: 0.5rem;
        }

        @media (max-width: 576px) {
            .change-password-header {
                padding: 1.5rem;
            }

            .change-password-header i {
                font-size: 2.5rem;
            }

            .change-password-header h2 {
                font-size: 1.5rem;
            }

            .change-password-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="change-password-container">
        <div class="change-password-header">
            <i class="bi bi-shield-lock"></i>
            <h2>เปลี่ยนรหัสผ่าน</h2>
            <p>กรุณาเปลี่ยนรหัสผ่านเริ่มต้นของคุณ</p>
        </div>

        <div class="change-password-body">
            <div class="alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <strong>จำเป็นต้องเปลี่ยนรหัสผ่าน!</strong><br>
                <small>เพื่อความปลอดภัย คุณต้องเปลี่ยนรหัสผ่านเริ่มต้นก่อนเข้าใช้งานระบบ</small>
            </div>

            <div class="error-message" id="errorMessage">
                <i class="bi bi-x-circle-fill"></i>
                <span id="errorText"></span>
            </div>

            <div class="success-message" id="successMessage">
                <i class="bi bi-check-circle-fill"></i>
                <span>เปลี่ยนรหัสผ่านสำเร็จ! กำลังเข้าสู่ระบบ...</span>
            </div>

            <form id="changePasswordForm">
                <div class="form-group">
                    <label class="form-label">
                        รหัสผ่านใหม่ <span class="required">*</span>
                    </label>
                    <div class="password-input-wrapper">
                        <input type="password" class="form-control" id="newPassword" placeholder="ป้อนรหัสผ่านใหม่" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('newPassword')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="passwordStrength" style="display: none;">
                        <div class="strength-label">ความแข็งแรงของรหัสผ่าน: <span id="strengthText">อ่อนแอ</span></div>
                        <div class="strength-bar">
                            <div class="strength-bar-fill" id="strengthBarFill"></div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        ยืนยันรหัสผ่านใหม่ <span class="required">*</span>
                    </label>
                    <div class="password-input-wrapper">
                        <input type="password" class="form-control" id="confirmPassword" placeholder="ป้อนรหัสผ่านใหม่อีกครั้ง" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('confirmPassword')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="match-indicator" id="matchIndicator">
                        <i class="bi bi-check-circle-fill"></i>
                        <span id="matchText">รหัสผ่านตรงกัน</span>
                    </div>
                </div>

                <div class="password-requirements">
                    <h6><i class="bi bi-info-circle"></i> รหัสผ่านต้องประกอบด้วย:</h6>
                    <div class="requirement-item" id="req-length">
                        <i class="bi bi-circle"></i>
                        <span>ความยาวอย่างน้อย 8 ตัวอักษร</span>
                    </div>
                    <div class="requirement-item" id="req-uppercase">
                        <i class="bi bi-circle"></i>
                        <span>ตัวพิมพ์ใหญ่อย่างน้อย 1 ตัว (A-Z)</span>
                    </div>
                    <div class="requirement-item" id="req-lowercase">
                        <i class="bi bi-circle"></i>
                        <span>ตัวพิมพ์เล็กอย่างน้อย 1 ตัว (a-z)</span>
                    </div>
                    <div class="requirement-item" id="req-number">
                        <i class="bi bi-circle"></i>
                        <span>ตัวเลขอย่างน้อย 1 ตัว (0-9)</span>
                    </div>
                    <div class="requirement-item" id="req-special">
                        <i class="bi bi-circle"></i>
                        <span>อักขระพิเศษอย่างน้อย 1 ตัว (!@#$%^&*)</span>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="bi bi-shield-check"></i>
                    <span>เปลี่ยนรหัสผ่าน</span>
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = event.currentTarget.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        // Password validation
        const newPasswordInput = document.getElementById('newPassword');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const matchIndicator = document.getElementById('matchIndicator');
        const matchText = document.getElementById('matchText');

        const requirements = {
            length: {
                element: document.getElementById('req-length'),
                test: (pwd) => pwd.length >= 8
            },
            uppercase: {
                element: document.getElementById('req-uppercase'),
                test: (pwd) => /[A-Z]/.test(pwd)
            },
            lowercase: {
                element: document.getElementById('req-lowercase'),
                test: (pwd) => /[a-z]/.test(pwd)
            },
            number: {
                element: document.getElementById('req-number'),
                test: (pwd) => /[0-9]/.test(pwd)
            },
            special: {
                element: document.getElementById('req-special'),
                test: (pwd) => /[!@#$%^&*(),.?":{}|<>]/.test(pwd)
            }
        };

        // Function to check password match (only when both are 8+ characters)
        function checkPasswordMatch() {
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            // Only check if both passwords are at least 8 characters
            if (newPassword.length >= 8 && confirmPassword.length >= 8) {
                if (newPassword === confirmPassword) {
                    confirmPasswordInput.style.borderColor = '#10b981';
                    confirmPasswordInput.style.backgroundColor = '#f0fdf4';
                    matchIndicator.className = 'match-indicator match';
                    matchIndicator.querySelector('i').className = 'bi bi-check-circle-fill';
                    matchText.textContent = 'รหัสผ่านตรงกัน';
                } else {
                    confirmPasswordInput.style.borderColor = '#ef4444';
                    confirmPasswordInput.style.backgroundColor = '#fef2f2';
                    matchIndicator.className = 'match-indicator no-match';
                    matchIndicator.querySelector('i').className = 'bi bi-x-circle-fill';
                    matchText.textContent = 'รหัสผ่านไม่ตรงกัน';
                }
            } else {
                confirmPasswordInput.style.borderColor = '#e2e8f0';
                confirmPasswordInput.style.backgroundColor = 'white';
                matchIndicator.style.display = 'none';
            }
        }

        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthContainer = document.getElementById('passwordStrength');

            if (password.length > 0) {
                strengthContainer.style.display = 'block';
            } else {
                strengthContainer.style.display = 'none';
            }

            let validCount = 0;

            // Check each requirement
            for (const key in requirements) {
                const req = requirements[key];
                const isValid = req.test(password);

                if (isValid) {
                    req.element.classList.remove('invalid');
                    req.element.classList.add('valid');
                    req.element.querySelector('i').classList.remove('bi-circle', 'bi-x-circle');
                    req.element.querySelector('i').classList.add('bi-check-circle-fill');
                    validCount++;
                } else {
                    req.element.classList.remove('valid');
                    req.element.classList.add('invalid');
                    req.element.querySelector('i').classList.remove('bi-circle', 'bi-check-circle-fill');
                    req.element.querySelector('i').classList.add('bi-x-circle');
                }
            }

            // Update strength bar
            const strengthBarFill = document.getElementById('strengthBarFill');
            const strengthText = document.getElementById('strengthText');

            strengthBarFill.className = 'strength-bar-fill';

            if (validCount <= 2) {
                strengthBarFill.classList.add('strength-weak');
                strengthText.textContent = 'อ่อนแอ';
                strengthText.style.color = '#ef4444';
            } else if (validCount <= 4) {
                strengthBarFill.classList.add('strength-medium');
                strengthText.textContent = 'ปานกลาง';
                strengthText.style.color = '#f59e0b';
            } else {
                strengthBarFill.classList.add('strength-strong');
                strengthText.textContent = 'แข็งแรง';
                strengthText.style.color = '#10b981';
            }

            // Check password match when new password changes
            checkPasswordMatch();
        });

        // Check password match when confirm password changes
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);

        // Form submission
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const errorMessage = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            const successMessage = document.getElementById('successMessage');
            const submitBtn = document.getElementById('submitBtn');

            // Hide previous messages
            errorMessage.style.display = 'none';
            successMessage.style.display = 'none';

            // Check if passwords match
            if (newPassword !== confirmPassword) {
                errorText.textContent = 'รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน';
                errorMessage.style.display = 'block';
                return;
            }

            // Check if new password is same as default
            if (newPassword === "Ktisgroup") {
                errorText.textContent = 'รหัสผ่านใหม่ต้องไม่เหมือนกับรหัสผ่านเริ่มต้น';
                errorMessage.style.display = 'block';
                return;
            }

            // Validate all requirements
            let allValid = true;
            for (const key in requirements) {
                if (!requirements[key].test(newPassword)) {
                    allValid = false;
                    break;
                }
            }

            if (!allValid) {
                errorText.textContent = 'รหัสผ่านไม่ตรงตามเงื่อนไขที่กำหนด';
                errorMessage.style.display = 'block';
                return;
            }

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i><span>กำลังดำเนินการ...</span>';

            // Send API request to update password
            const formData = new FormData();
            formData.append('user_id', '<?php echo $_SESSION['user_id']; ?>');
            formData.append('new_password', newPassword);


            changePassword(formData, {
                submitBtn,
                errorMessage,
                errorText,
                successMessage
            });
        });

        async function changePassword(formData, ui) {
            const {
                submitBtn,
                errorMessage,
                errorText,
                successMessage
            } = ui;
            try {
                // ปิดปุ่ม + แสดง loading (ถ้ามี)
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> กำลังบันทึก...';

                const result = await apiFetch('api/change_password.php', {
                    method: 'POST',
                    body: formData
                });

                if (result.success) {
                    successMessage.style.display = 'block';

                    await Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: 'เปลี่ยนรหัสผ่านเรียบร้อย',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#667eea',
                        timer: 1500
                    });

                    window.location.href = 'tasks.php';
                } else {
                    errorText.textContent = result.message || 'เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน';
                    errorMessage.style.display = 'block';
                }

            } catch (error) {
                console.error('Error:', error);
                errorText.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์';
                errorMessage.style.display = 'block';

            } finally {
                // เปิดปุ่มกลับเสมอ
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-shield-check"></i><span>เปลี่ยนรหัสผ่าน</span>';
            }
        }

        // Prevent going back
        window.history.pushState(null, '', window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, '', window.location.href);
        };

        function apiFetch(url, options = {}) {
            console.log('🔥 apiFetch called →', url);
            return fetch(url, options)
                .then(response => {

                    // ถ้า server ตอบ 401 (optional แต่ดี)
                    if (response.status === 401) {
                        window.location.href = 'session_check.php';
                        throw new Error('SESSION_EXPIRED');
                    }

                    return response.json();
                })
                .then(res => {

                    // 🔴 session หมด (ตาม schema ที่ใช้)
                    if (res.code === 'SESSION_EXPIRED') {
                        alert(res.message || 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่');
                        window.location.href = 'session_check.php';
                        throw new Error('SESSION_EXPIRED');
                    }

                    return res; // ส่งผลลัพธ์ให้ function ที่เรียก
                })
                .catch(err => {
                    if (err.message !== 'SESSION_EXPIRED') {
                        console.error('API ERROR:', err);
                    }
                });
        }
    </script>
</body>

</html>