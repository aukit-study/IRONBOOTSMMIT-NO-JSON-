<?php
session_start();
// โหลดข้อมูลผู้ใช้ปัจจุบัน (รวมถึง $USER_DATA_STORAGE)
require_once 'data.php'; 

$user_file_path = 'users.json';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $display_name = trim($_POST['display_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. ตรวจสอบความถูกต้องเบื้องต้น
    if (empty($username) || empty($display_name) || strlen($password) < 6 || $password !== $confirm_password) {
        header("Location: register.php?error=inputinvalid");
        exit();
    }

    // 2. ตรวจสอบว่า Username มีอยู่แล้วหรือไม่
    // 🔴 ใช้ $USER_DATA_STORAGE ที่โหลดมาจาก data.php
    if (isset($USER_DATA_STORAGE[strtolower($username)])) {
        header("Location: register.php?error=userexists");
        exit();
    }

    // 3. เข้ารหัสรหัสผ่าน (Hashing - สำคัญมากสำหรับความปลอดภัย)
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // 4. สร้างข้อมูลผู้ใช้ใหม่
    $new_user_data = [
        'password_hash' => $password_hash,
        'display_name' => $display_name
    ];

    // 5. อัปเดต Array ข้อมูลผู้ใช้ทั้งหมด
    $USER_DATA_STORAGE[strtolower($username)] = $new_user_data;

    // 6. บันทึกข้อมูลกลับไปยังไฟล์ JSON
    // ใช้ JSON_PRETTY_PRINT และ JSON_UNESCAPED_UNICODE เพื่อให้อ่านง่าย
    $json_output = json_encode($USER_DATA_STORAGE, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    if (file_put_contents($user_file_path, $json_output) !== false) {
        // สำเร็จ: ส่งไปหน้า Login พร้อมแจ้งเตือน
        header("Location: login.php?success=registered");
        exit();
    } else {
        // ข้อผิดพลาดในการเขียนไฟล์ (ตรวจสอบ Permission ของ users.json)
        header("Location: register.php?error=filefail");
        exit();
    }

} else {
    header("Location: index.php");
    exit();
}
?>