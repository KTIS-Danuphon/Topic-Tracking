<?php
session_start();
if (empty($_SESSION['user_fullname'])) {
    $_SESSION["SUCCESS_LOGIN"] = array("error", "เซสชันหมดอายุ <br>กรุณาเข้าสู่ระบบใหม่");
    header("Location: auth_login.php");
    exit();
}
