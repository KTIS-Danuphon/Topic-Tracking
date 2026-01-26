<?php
session_start();

$_SESSION = [];
session_regenerate_id(true);

$_SESSION['SUCCESS_LOGIN'] = ['success', 'ออกจากระบบสำเร็จ'];

header('Location: auth_login.php');
exit;
