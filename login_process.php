<?php
session_start();

// ตรวจสอบว่ามีการส่งข้อมูลมาแบบ POST หรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. รับข้อมูลจากฟอร์ม (ทั้ง username และ password)
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 2. ตรวจสอบข้อมูล (จำลองการเช็กกับฐานข้อมูล)
    // ในระบบจริง เราจะเช็กข้อมูลนี้กับ Database
    // ตอนนี้เราจะสมมติว่า: Username คือ "admin" และ Password คือ "1234456"
    if ($username === 'admin' && $password === '123456') {
        
        // 3. ถ้าข้อมูลถูกต้อง ให้สร้าง Session
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;

        // 4. ส่งผู้ใช้กลับไปที่หน้าแรก
        header("Location: index.php");
        exit();

    } elseif($username === 'user' && $password === '123456') {
        // 3. ถ้าข้อมูลถูกต้อง ให้สร้าง Session
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;

        // 4. ส่งผู้ใช้กลับไปที่หน้าแรก
        header("Location: index.php");
        exit();

    } else {
        // 5. ถ้าข้อมูลไม่ถูกต้อง ให้ส่งกลับไปหน้า login พร้อมข้อความ error
        header("Location: login.php?error=wrongcredentials");
        exit();
    }
} else {
    // ถ้าเข้าถึงไฟล์นี้โดยตรง ให้กลับไปหน้าแรก
    header("Location: index.php");
    exit();
}
?>