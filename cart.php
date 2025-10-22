<?php
// ====================================================================
// CART CONTROLLER LOGIC
// ไฟล์นี้จะทำหน้าที่จัดการ Session และ Redirect กลับไปยังหน้าเดิม
// ====================================================================

// 1. เริ่มต้น Session และโหลดข้อมูล (ต้องอยู่บนสุด)
if (session_status() == PHP_SESSION_NONE) {
    session_start();    
}
require_once 'data.php'; 

// 2. กำหนดหน้าที่จะ Redirect กลับไป
// ใช้ HTTP Referer เพื่อหาว่าผู้ใช้มาจากหน้าไหน หรือกลับไปหน้าแรกเป็นค่าเริ่มต้น
$redirect_url = $_SERVER['HTTP_REFERER'] ?? 'index.php';

// ============================================
// LOGIC: เพิ่มสินค้า (POST from product_detail.php)
// ============================================
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $size = $_POST['selected_size']; 

    // 2. 🟢 ตรวจสอบว่ามีการเลือก Size มาหรือไม่
    if (!empty($size)) {
        
        // 3. 🟢 ตรวจสอบว่ามี ID สินค้า และ Size นี้ในตะกร้าหรือยัง
        if (isset($_SESSION['cart'][$product_id][$size])) {
            // ถ้ามีแล้ว ให้บวกจำนวนเพิ่ม
            $_SESSION['cart'][$product_id][$size] += $quantity;
        } else {
            // ถ้ายังไม่มี ให้สร้างขึ้นมาใหม่
            $_SESSION['cart'][$product_id][$size] = $quantity;
        }
    }
    // หมายเหตุ: คุณอาจต้องเพิ่ม else เพื่อแจ้งเตือนหาก $size ว่างเปล่า
}

// ============================================
// LOGIC: อัปเดตจำนวนสินค้า (+ / -)
// ============================================
if (isset($_POST['update_quantity_action'])) {
    $product_id = (int)$_POST['product_id'];
    $size = $_POST['size']; // 🟢 1. รับค่า Size ที่ส่งมาจาก Form
    $action = $_POST['update_quantity_action']; 
    
    // 🟢 2. ตรวจสอบว่ามี ID สินค้า และ Size นี้ อยู่ในตะกร้า
    if (isset($_SESSION['cart'][$product_id][$size])) {
        $current_quantity = $_SESSION['cart'][$product_id][$size];
        
        if ($action === 'increase') {
            $_SESSION['cart'][$product_id][$size] = $current_quantity + 1;
        } elseif ($action === 'decrease') {
            $new_quantity = $current_quantity - 1;
            
            if ($new_quantity > 0) {
                $_SESSION['cart'][$product_id][$size] = $new_quantity;
            } else {
                // 🟢 3. ถ้าจำนวนลดลงจนเหลือ 0 ให้ลบสินค้ารายการ (size) นั้น
                unset($_SESSION['cart'][$product_id][$size]);
                
                // 🟢 4. (แนะนำ) ตรวจสอบว่าถ้า Product ID นี้ไม่เหลือ Size ไหนแล้ว ก็ให้ลบ Product ID ออกจากตะกร้าเลย
                if (empty($_SESSION['cart'][$product_id])) {
                    unset($_SESSION['cart'][$product_id]);
                }
            }
        }
    }
}
// ============================================
// LOGIC: ลบสินค้าทีละรายการ (GET from Side Cart)
// ============================================
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id']) && isset($_GET['size'])) {
    $product_id_to_remove = (int)$_GET['id'];
    $size_to_remove = $_GET['size']; // 🟢 รับค่า size ที่จะลบ
    
    // 1. 🟢 ตรวจสอบว่ามี ID สินค้า และ Size นี้อยู่หรือไม่
    if (isset($_SESSION['cart'][$product_id_to_remove][$size_to_remove])) {
        // 2. 🟢 ลบเฉพาะ Size นั้นออกไป
        unset($_SESSION['cart'][$product_id_to_remove][$size_to_remove]);
        
        // 3. 🟢 (แนะนำ) ตรวจสอบว่าถ้า Product ID นี้ไม่เหลือ Size ไหนแล้ว ก็ให้ลบ Product ID ออกจากตะกร้าเลย
        if (empty($_SESSION['cart'][$product_id_to_remove])) {
            unset($_SESSION['cart'][$product_id_to_remove]);
        }
    }
}

// ============================================
// LOGIC: ล้างตะกร้าทั้งหมด (GET from Clear Cart button)
// ============================================
if (isset($_GET['action']) && $_GET['action'] == 'clear') {
    unset($_SESSION['cart']); 
}

// 🟢 NEW LOGIC: เก็บสถานะว่า Side Cart ควรถูกเปิด
$_SESSION['side_cart_open'] = true;

// 3. REDIRECT กลับไปยังหน้าเดิมหลังจบ Logic ทั้งหมด
header('Location: ' . $redirect_url);
exit();
?>