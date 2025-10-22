<?php
// favorite_logic.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'data.php';

// ตรวจสอบว่าเป็นคำสั่งจากฟอร์ม POST/AJAX หรือไม่
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_id'])) {
    // ถ้าเข้าถึงโดยตรงหรือไม่มี ID ให้กลับไปหน้าหลัก
    header('Location: index.php');
    exit();
}

$product_id = (int)$_POST['product_id'];

// ตรวจสอบและเริ่มต้น Session Favorites
if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
}

// ============================================
// LOGIC: จัดการการเพิ่ม/ลบรายการโปรด
// ============================================

$is_favorite = in_array($product_id, $_SESSION['favorites']);

if ($is_favorite) {
    // ถ้ามีอยู่แล้ว: ลบออกจาก Favorites
    $_SESSION['favorites'] = array_diff($_SESSION['favorites'], [$product_id]);
    $new_status = 'removed';
} else {
    // ถ้ายังไม่มี: เพิ่มเข้ารายการโปรด
    $_SESSION['favorites'][] = $product_id;
    $new_status = 'added';
}

// ============================================
// 🔴 ส่งสถานะกลับไปในรูปแบบ JSON
// ============================================

// นับจำนวนรายการโปรดใหม่
$new_count = count($_SESSION['favorites']);
$is_favorite_after_action = !$is_favorite;

header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'action' => $new_status,
    'new_count' => $new_count,
    'is_favorite' => $is_favorite_after_action,
    'product_id' => $product_id
]);
exit();
?>