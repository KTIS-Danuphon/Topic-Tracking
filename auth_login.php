<?php session_start(); ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - Topic Tracking</title>
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
            position: relative;
            overflow: hidden;
        }

        /* Animated Background */
        .bg-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .bg-animation span {
            position: absolute;
            display: block;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.1);
            animation: float 25s linear infinite;
            bottom: -150px;
        }

        .bg-animation span:nth-child(1) {
            left: 10%;
            width: 80px;
            height: 80px;
            animation-delay: 0s;
        }

        .bg-animation span:nth-child(2) {
            left: 20%;
            width: 20px;
            height: 20px;
            animation-delay: 2s;
            animation-duration: 12s;
        }

        .bg-animation span:nth-child(3) {
            left: 50%;
            width: 40px;
            height: 40px;
            animation-delay: 4s;
        }

        .bg-animation span:nth-child(4) {
            left: 70%;
            width: 60px;
            height: 60px;
            animation-delay: 0s;
            animation-duration: 18s;
        }

        .bg-animation span:nth-child(5) {
            left: 85%;
            width: 30px;
            height: 30px;
            animation-delay: 0s;
        }

        .bg-animation span:nth-child(6) {
            left: 90%;
            width: 45px;
            height: 45px;
            animation-delay: 10s;

            animation-duration: 0.5s;
            /* เร็วขึ้น (เดิม 25s) */
            animation-iteration-count: 1;
            /* เล่นครั้งเดียว */
            animation-fill-mode: forwards;
            /* ค้างสถานะสุดท้าย */
        }


        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
                border-radius: 0;
            }

            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
                border-radius: 50%;
            }
        }

        /* Login Container */
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
            padding: 2rem;
        }

        .login-card {
            background: white;
            border-radius: 24px;
            padding: 3rem 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-gradient);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .logo-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-title {
            text-align: center;
            color: #1e293b;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 2rem;
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            height: 60px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        .input-group-text {
            background: transparent;
            border: 2px solid #e2e8f0;
            border-right: none;
            border-radius: 12px 0 0 12px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .forgot-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .divider span {
            padding: 0 1rem;
        }

        .social-login {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .btn-social {
            flex: 1;
            padding: 0.875rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: white;
            color: #64748b;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-social:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .btn-social i {
            font-size: 1.2rem;
        }

        .register-link {
            text-align: center;
            color: #64748b;
            margin-top: 1.5rem;
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* Loading Animation */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .loading-overlay.show {
            display: flex;
        }

        .loader {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--primary-gradient);
            position: relative;
            animation: rotate 1.5s linear infinite;
        }

        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loader::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            background: white;
            border-radius: 50%;
        }

        .loader::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            border-radius: 50%;
        }

        .loading-text {
            margin-top: 2rem;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            animation: fadeInOut 1.5s ease-in-out infinite;
        }

        @keyframes fadeInOut {

            0%,
            100% {
                opacity: 0.5;
            }

            50% {
                opacity: 1;
            }
        }

        .welcome-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .welcome-animation.show {
            display: flex;
        }

        .welcome-icon {
            width: 120px;
            height: 120px;
            background: var(--primary-gradient);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            animation: scaleIn 0.6s ease-out;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .welcome-icon i {
            font-size: 4rem;
            color: white;
        }

        .welcome-text {
            font-size: 2rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: slideUp 0.6s ease-out 0.3s both;
        }

        .welcome-message {
            font-size: 1.2rem;
            color: #64748b;
            margin-top: 1rem;
            animation: slideUp 0.6s ease-out 0.5s both;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 2rem 1.5rem;
            }

            .logo-text {
                font-size: 1.5rem;
            }

            .login-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>

<body>
    <?php include_once('sweetalert2.php'); ?>

    <!-- Animated Background -->
    <div class="bg-animation">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <div class="logo-container">
                <div class="logo-icon">
                    <!-- <i class="bi bi-clipboard-check"></i> -->
                    <style>
                        .icon-ktis {
                            display: inline-block;
                            width: 4em;
                            height: 4em;
                            vertical-align: -0.125em;
                            /* เทียบ bi icon */

                            background-color: #fff;

                            -webkit-mask: url("ktis.svg") no-repeat center / contain;
                            mask: url("ktis.svg") no-repeat center / contain;
                        }
                    </style>
                    <span class="icon-ktis"></span>

                </div>
                <div class="logo-text">Topic Tracking</div>
            </div>

            <h2 class="login-title">ยินดีต้อนรับกลับมา</h2>
            <p class="login-subtitle">เข้าสู่ระบบเพื่อจัดการงานของคุณ</p>

            <form id="loginForm" action="auth_login_action.php" method="POST">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" class="form-control" id="username" name="username"
                            placeholder="ชื่อผู้ใช้" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="รหัสผ่าน" required>
                        <button class="btn btn-outline-secondary" type="button"
                            onclick="togglePassword()" style="border-radius: 0 12px 12px 0;">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="remember-forgot">
                    <div class="form-check">

                        <input class="form-check-input" type="checkbox" id="rememberMe" name="rememberMe" value="1">
                        <label class="form-check-label" for="rememberMe">
                            จดจำฉันไว้
                        </label>
                    </div>
                    <!-- <a href="#" class="forgot-link">ลืมรหัสผ่าน?</a> -->
                </div>

                <button type="submit" class="btn btn-login" id="btn_submit" hidden>
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    เข้าสู่ระบบ
                </button>
                <button type="button" class="btn btn-login" onclick="Formlogin_submit()">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    เข้าสู่ระบบ
                </button>
            </form>

            <!-- <div class="divider">
                <span>หรือเข้าสู่ระบบด้วย</span>
            </div>

            <div class="social-login">
                <button class="btn btn-social" onclick="alert('Google Login - Coming Soon!')">
                    <i class="bi bi-google"></i>
                </button>
                <button class="btn btn-social" onclick="alert('Facebook Login - Coming Soon!')">
                    <i class="bi bi-facebook"></i>
                </button>
                <button class="btn btn-social" onclick="alert('Line Login - Coming Soon!')">
                    <i class="bi bi-line"></i>
                </button>
            </div>

            <div class="register-link">
                ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a>
            </div> -->
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loader"></div>
        <div class="loading-text">กำลังเข้าสู่ระบบ...</div>
    </div>

    <!-- Welcome Animation -->
    <div class="welcome-animation" id="welcomeAnimation">
        <div class="welcome-icon">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="welcome-text">เข้าสู่ระบบสำเร็จ!</div>
        <div class="welcome-message">ยินดีต้อนรับ, <span id="welcomeName">ผู้ใช้</span></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- <script>
        document.querySelectorAll('.bg-animation span').forEach(span => {
            const randomLeft = Math.random() * 100; // 0–100%
            const randomDelay = Math.random() * 5; // 0–5s
            const randomSize = 20 + Math.random() * 100; // 20–120px

            span.style.left = randomLeft + '%';
            span.style.animationDelay = randomDelay + 's';
            span.style.width = randomSize + 'px';
            span.style.height = randomSize + 'px';
        });
    </script> -->
    <script>
        // Toggle Password Visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        // Load Saved Credentials
        document.addEventListener('DOMContentLoaded', function() {
            const savedUsername = localStorage.getItem('savedUsername');
            const savedPassword = localStorage.getItem('savedPassword');

            if (savedUsername && savedPassword) {
                document.getElementById('username').value = savedUsername;
                document.getElementById('password').value = savedPassword;
                document.getElementById('rememberMe').checked = true;
            }
        });

        // Form Submission
        function Formlogin_submit() {
            // document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn_submit = document.getElementById('btn_submit');
            // e.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const rememberMe = document.getElementById('rememberMe').checked;

            // Save credentials if remember me is checked
            if (rememberMe) {
                localStorage.setItem('savedUsername', username);
                localStorage.setItem('savedPassword', password);
            } else {
                localStorage.removeItem('savedUsername');
                localStorage.removeItem('savedPassword');
            }

            // Show loading
            // document.getElementById('loadingOverlay').classList.add('show');

            // Simulate login process
            // setTimeout(() => {
            // Hide loading
            // document.getElementById('loadingOverlay').classList.remove('show');

            // Mock login validation (ในการใช้งานจริงต้องเชื่อมกับ API)
            if (username && password) {
                btn_submit.click();
                // Show welcome animation
                // document.getElementById('welcomeName').textContent = username;
                // document.getElementById('welcomeAnimation').classList.add('show');

                // // Save session
                // sessionStorage.setItem('isLoggedIn', 'true');
                // sessionStorage.setItem('username', username);

                // // Redirect to tasks page after animation
                // setTimeout(() => {
                // window.location.href = 'auth_login_action.php';
                // }, 2000);
            } else {
                alert('กรุณากรอกข้อมูลให้ครบถ้วน');
            }
            // }, 1500);
            // });
        }

        // Enter key on username field focuses password
        document.getElementById('username').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('password').focus();
            }
        });
    </script>
</body>

</html>