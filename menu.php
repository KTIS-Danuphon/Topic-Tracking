<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$table = 'tb_notifications_c050968 nt';
$fields = 'COUNT(*) AS count_notification ';
$where = 'LEFT JOIN tb_notification_users_c050968 ntu ON ntu.fd_notification_id = nt.fd_notification_id ';
$where .= 'WHERE nt.fd_is_deleted = "0" AND ntu.fd_user_id = "' . $_SESSION['user_id'] . '" AND ntu.fd_is_read = "0" AND ntu.fd_is_deleted = "0" ';
$result_notification = $object->ReadData($table, $fields, $where);
$count_notification = $result_notification[0]['count_notification'];
$isAdmin = in_array($_SESSION['user_status'], ['admin', 'executive']);
?>
<!-- Top Navbar -->
<nav class="top-navbar">
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <a href="#" class="navbar-brand">
        <i class="bi bi-clipboard-check"></i>
        Topic Tracking
    </a>

    <div class="navbar-right">
        <button class="notification-btn" onclick="showNotifications()">
            <i class="bi bi-bell"></i>
            <!-- <?= ($count_notification ?? 0) == 0 ? '' : '<span id class="notification-badge badgeUnread_menu">' . $count_notification . '</span>' ?> -->
            <span id="notification_badge"
                <?= ($count_notification ?? 0) > 0 ? 'class="notification-badge"' : '' ?>>
                <?= ($count_notification ?? 0) ?: '' ?>
            </span>
        </button>

        <div class="user-profile" onclick="toggleUserMenu()">
            <div class="user-avatar">
                <i class="bi bi-person"></i>
            </div>
            <div class="user-info">
                <div class="user-name"><?= $_SESSION['user_fullname'] ?></div>
                <div class="user-role"><?= $_SESSION['user_status_name'] ?></div>
            </div>
        </div>
        <!-- <div class="user-dropdown" id="userDropdown">
            <a href="profile.php" class="dropdown-item">
                <i class="bi bi-person-circle"></i>
                โปรไฟล์
            </a>
            <a href="javascript:void(0)" class="dropdown-item" onclick="openChangePasswordModal()">
                <i class="bi bi-key"></i>
                เปลี่ยนรหัสผ่าน
            </a>
            <div class="dropdown-divider"></div>
            <button class="dropdown-item logout" onclick="logout()">
                <i class="bi bi-box-arrow-right"></i>
                ออกจากระบบ
            </button>
        </div> -->

    </div>
</nav>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-title">เมนูหลัก</div>
    </div>

    <ul class="sidebar-menu">
        <?php if ($isAdmin) { ?>
            <!-- <li class="menu-item">
                <a href="dashboard.php" class="menu-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>แดชบอร์ด</span>
                </a>
            </li> -->
        <?php } ?>
        <li class="menu-item">
            <a href="tasks.php" class="menu-link <?= in_array($currentPage, ['tasks.php', 'tesk_create.php', 'task_edit.php', 'task_detail.php']) ? 'active' : '' ?>">
                <i class="bi bi-list-task"></i>
                <span>งานทั้งหมด</span>
                <!-- <span class="menu-badge">12</span> -->
            </a>
        </li>
        <li class="menu-item">
            <a href="notifications.php" class="menu-link <?= $currentPage == 'notifications.php' ? 'active' : '' ?>">
                <i class="bi bi-bell"></i>
                <span>แจ้งเตือน</span>
                <span id="badgeUnread_menu"
                    <?= ($count_notification ?? 0) > 0 ? 'class="menu-badge"' : '' ?>>
                    <?= ($count_notification ?? 0) ?: '' ?>
                </span>

            </a>
        </li>
        <!-- <li class="menu-item">
            <a href="calendar.php" class="menu-link <?= $currentPage == 'calendar.php' ? 'active' : '' ?>">
                <i class="bi bi-calendar3"></i>
                <span>ปฏิทิน</span>
            </a>
        </li> -->

    </ul>

    <?php if ($isAdmin) { ?>
        <div class="sidebar-header" style="margin-top: 0.5rem;">
            <div class="sidebar-title">จัดการระบบ</div>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-item">
                <a href="users_management.php" class="menu-link <?= $currentPage == 'users_management.php' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>จัดการผู้ใช้</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="division_management.php" class="menu-link <?= $currentPage == 'division_management.php' ? 'active' : '' ?>">
                    <i class="bi bi-tags"></i>
                    <span>จัดการฝ่าย</span>
                </a>
            </li>
            <!-- 
            <li class="menu-item">
                <a href="users_management.php" class="menu-link <?= $currentPage == 'settings.php' ? 'active' : '' ?>">
                    <i class="bi bi-gear"></i>
                    <span>ตั้งค่า</span>
                </a>
             <li class="menu-item">
                <a href="reports.php" class="menu-link <?= $currentPage == 'reports.php' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>รายงาน</span>
                </a>
            </li> -->
        </ul>
    <?php } ?>

    <div class="sidebar-header" style="margin-top: 0.5rem;">
        <div class="sidebar-title">เอกสาร</div>
    </div>
    <ul class="sidebar-menu">
        <li class="menu-item">
            <a href="documents.php" class="menu-link <?= in_array($currentPage, ['documents.php']) ? 'active' : '' ?>">
                <i class="bi bi-file-text"></i>
                <span>คู่มือ</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <button class="logout-btn" onclick="logout()">
            <i class="bi bi-box-arrow-right"></i>
            <span>ออกจากระบบ</span>
        </button>
    </div>
</aside>

<!-- Modal เปลี่ยนรหัสผ่าน - เวอร์ชันปรับปรุง -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <!-- Header with Gradient -->
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1.5rem 2rem;">
                <h5 class="modal-title text-white d-flex align-items-center gap-2" style="font-weight: 600;">
                    <i class="bi bi-shield-lock" style="font-size: 1.5rem;"></i>
                    <span>เปลี่ยนรหัสผ่าน</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" style="padding: 2rem;">
                <!-- Alert Messages -->
                <div class="alert alert-danger d-none" id="passwordError" style="border-left: 4px solid #dc2626;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <span id="passwordErrorText"></span>
                </div>

                <div class="alert alert-success d-none" id="passwordSuccess" style="border-left: 4px solid #16a34a;">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <span>เปลี่ยนรหัสผ่านสำเร็จ!</span>
                </div>

                <form id="changePasswordForm">
                    <!-- รหัสผ่านเดิม -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color: #334155;">
                            <i class="bi bi-key-fill me-1"></i>
                            รหัสผ่านเดิม <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-lg" id="oldPassword"
                                placeholder="ป้อนรหัสผ่านเดิม" required
                                style="border: 2px solid #e2e8f0; padding: 0.75rem 1rem;">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('oldPassword', this)"
                                style="border: 2px solid #e2e8f0; border-left: none;">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- รหัสผ่านใหม่ -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: #334155;">
                            <i class="bi bi-shield-check me-1"></i>
                            รหัสผ่านใหม่ <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-lg" id="newPassword"
                                placeholder="ป้อนรหัสผ่านใหม่" required
                                style="border: 2px solid #e2e8f0; padding: 0.75rem 1rem;">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('newPassword', this)"
                                style="border: 2px solid #e2e8f0; border-left: none;">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>

                        <!-- Password Strength Bar -->
                        <div class="mt-2 d-none" id="strengthContainer">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">ความแข็งแรง:</small>
                                <small id="strengthText" class="fw-semibold">อ่อนแอ</small>
                            </div>
                            <div style="height: 6px; background: #e2e8f0; border-radius: 10px; overflow: hidden;">
                                <div id="strengthBar" style="height: 100%; width: 0%; transition: all 0.3s; border-radius: 10px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Password Requirements -->
                    <div class="password-requirements mb-4" style="background: #f8fafc; border-radius: 10px; padding: 1rem;">
                        <div class="fw-semibold mb-2" style="color: #475569; font-size: 0.9rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            รหัสผ่านต้องประกอบด้วย:
                        </div>
                        <div class="requirements-list">
                            <div class="requirement-item" id="req-length">
                                <i class="bi bi-circle-fill"></i>
                                <span>ความยาวอย่างน้อย 8 ตัวอักษร</span>
                            </div>
                            <div class="requirement-item" id="req-uppercase">
                                <i class="bi bi-circle-fill"></i>
                                <span>ตัวพิมพ์ใหญ่ (A-Z) อย่างน้อย 1 ตัว</span>
                            </div>
                            <div class="requirement-item" id="req-lowercase">
                                <i class="bi bi-circle-fill"></i>
                                <span>ตัวพิมพ์เล็ก (a-z) อย่างน้อย 1 ตัว</span>
                            </div>
                            <div class="requirement-item" id="req-number">
                                <i class="bi bi-circle-fill"></i>
                                <span>ตัวเลข (0-9) อย่างน้อย 1 ตัว</span>
                            </div>
                            <div class="requirement-item" id="req-special">
                                <i class="bi bi-circle-fill"></i>
                                <span>อักขระพิเศษ (!@#$%^&*) อย่างน้อย 1 ตัว</span>
                            </div>
                        </div>
                    </div>

                    <!-- ยืนยันรหัสผ่านใหม่ -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: #334155;">
                            <i class="bi bi-check2-square me-1"></i>
                            ยืนยันรหัสผ่านใหม่ <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-lg" id="confirmPassword"
                                placeholder="ป้อนรหัสผ่านใหม่อีกครั้ง" required
                                style="border: 2px solid #e2e8f0; padding: 0.75rem 1rem;">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('confirmPassword', this)"
                                style="border: 2px solid #e2e8f0; border-left: none;">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>

                        <!-- Match Indicator -->
                        <div class="mt-2 d-none" id="matchIndicator">
                            <small>
                                <i class="bi bi-check-circle-fill text-success me-1"></i>
                                <span class="text-success">รหัสผ่านตรงกัน</span>
                            </small>
                        </div>
                        <div class="mt-2 d-none" id="noMatchIndicator">
                            <small>
                                <i class="bi bi-x-circle-fill text-danger me-1"></i>
                                <span class="text-danger">รหัสผ่านไม่ตรงกัน</span>
                            </small>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0" style="padding: 1rem 2rem 1.5rem;">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border: 1px solid #e2e8f0;">
                    <i class="bi bi-x-lg me-1"></i>
                    ยกเลิก
                </button>
                <button type="button" class="btn btn-primary px-4" id="submitChangePassword" disabled
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                    <i class="bi bi-check-lg me-1"></i>
                    <span>บันทึกการเปลี่ยนแปลง</span>
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleUserMenu() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('show');
    }

    // ปิดเมนูเมื่อคลิกที่อื่น
    document.addEventListener('click', function(e) {
        const userProfile = document.querySelector('.user-profile');
        const dropdown = document.getElementById('userDropdown');

        if (!userProfile.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });
</script>
<script>
    let changePasswordModal;

    function openChangePasswordModal() {
        changePasswordModal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
        changePasswordModal.show();

        // Reset form
        document.getElementById('changePasswordForm').reset();
        document.getElementById('passwordError').classList.add('d-none');
        document.getElementById('passwordSuccess').classList.add('d-none');
        document.getElementById('strengthContainer').classList.add('d-none');
        document.getElementById('submitChangePassword').disabled = true;

        // Reset requirements
        document.querySelectorAll('.requirement-item').forEach(item => {
            item.classList.remove('valid', 'invalid');
        });
    }

    function togglePasswordVisibility(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');

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

    const newPasswordInput = document.getElementById('newPassword');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const submitBtn = document.getElementById('submitChangePassword');

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

    function checkPasswordMatch() {
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const matchIndicator = document.getElementById('matchIndicator');
        const noMatchIndicator = document.getElementById('noMatchIndicator');
        const confirmInput = confirmPasswordInput;

        if (newPassword.length >= 8 && confirmPassword.length >= 8) {
            if (newPassword === confirmPassword) {
                confirmInput.style.borderColor = '#16a34a';
                confirmInput.style.backgroundColor = '#f0fdf4';
                matchIndicator.classList.remove('d-none');
                noMatchIndicator.classList.add('d-none');
            } else {
                confirmInput.style.borderColor = '#dc2626';
                confirmInput.style.backgroundColor = '#fef2f2';
                matchIndicator.classList.add('d-none');
                noMatchIndicator.classList.remove('d-none');
            }
        } else {
            confirmInput.style.borderColor = '#e2e8f0';
            confirmInput.style.backgroundColor = 'white';
            matchIndicator.classList.add('d-none');
            noMatchIndicator.classList.add('d-none');
        }
    }

    function validatePassword() {
        const pwd = newPasswordInput.value;
        const strengthContainer = document.getElementById('strengthContainer');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        // Show/hide strength container
        if (pwd.length > 0) {
            strengthContainer.classList.remove('d-none');
        } else {
            strengthContainer.classList.add('d-none');
        }

        let validCount = 0;

        // Check each requirement
        Object.values(requirements).forEach(req => {
            const isValid = req.test(pwd);
            if (isValid) {
                req.element.classList.remove('invalid');
                req.element.classList.add('valid');
                validCount++;
            } else {
                req.element.classList.remove('valid');
                req.element.classList.add('invalid');
            }
        });

        // Update strength bar
        if (validCount <= 2) {
            strengthBar.style.width = '33%';
            strengthBar.style.background = '#dc2626';
            strengthText.textContent = 'อ่อนแอ';
            strengthText.style.color = '#dc2626';
        } else if (validCount <= 4) {
            strengthBar.style.width = '66%';
            strengthBar.style.background = '#f59e0b';
            strengthText.textContent = 'ปานกลาง';
            strengthText.style.color = '#f59e0b';
        } else {
            strengthBar.style.width = '100%';
            strengthBar.style.background = '#16a34a';
            strengthText.textContent = 'แข็งแรง';
            strengthText.style.color = '#16a34a';
        }

        // Check password match
        checkPasswordMatch();

        // Enable/disable submit button
        const allValid = validCount === 5;
        const passwordsMatch = pwd === confirmPasswordInput.value && pwd !== '';
        submitBtn.disabled = !(allValid && passwordsMatch);
    }

    newPasswordInput.addEventListener('input', validatePassword);
    confirmPasswordInput.addEventListener('input', () => {
        checkPasswordMatch();
        validatePassword();
    });

    // Form submission
    document.getElementById('submitChangePassword').addEventListener('click', function() {
        const oldPassword = document.getElementById('oldPassword').value;
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const errorAlert = document.getElementById('passwordError');
        const errorText = document.getElementById('passwordErrorText');
        const successAlert = document.getElementById('passwordSuccess');

        // Hide alerts
        errorAlert.classList.add('d-none');
        successAlert.classList.add('d-none');

        // Validate
        if (!oldPassword) {
            errorText.textContent = 'กรุณาป้อนรหัสผ่านเดิม';
            errorAlert.classList.remove('d-none');
            return;
        }

        if (newPassword !== confirmPassword) {
            errorText.textContent = 'รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน';
            errorAlert.classList.remove('d-none');
            return;
        }

        if (newPassword === oldPassword) {
            errorText.textContent = 'รหัสผ่านใหม่ต้องไม่เหมือนกับรหัสผ่านเดิม';
            errorAlert.classList.remove('d-none');
            return;
        }

        // Disable button and show loading
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>กำลังดำเนินการ...';

        // Send to API
        const formData = new FormData();
        formData.append('old_password', oldPassword);
        formData.append('new_password', newPassword);
        formData.append('user_id', '<?php echo $_SESSION['user_id'] ?? ''; ?>');

        fetch('api_change_password_self.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    successAlert.classList.remove('d-none');
                    document.getElementById('changePasswordForm').reset();

                    // Reset button
                    this.innerHTML = '<i class="bi bi-check-lg me-1"></i>บันทึกสำเร็จ';

                    // Close modal after 2 seconds
                    setTimeout(() => {
                        changePasswordModal.hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }, 1500);
                } else {
                    errorText.textContent = data.message || 'เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน';
                    errorAlert.classList.remove('d-none');

                    // Re-enable button
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-check-lg me-1"></i>บันทึกการเปลี่ยนแปลง';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorText.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์';
                errorAlert.classList.remove('d-none');

                // Re-enable button
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-check-lg me-1"></i>บันทึกการเปลี่ยนแปลง';
            });
    });
</script>


<script>
    function logout() {
        Swal.fire({
            title: 'ออกจากระบบ',
            text: 'คุณต้องการออกจากระบบใช่หรือไม่ ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ใช่, ออกจากระบบ',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        });
    }
</script>