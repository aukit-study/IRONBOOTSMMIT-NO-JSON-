<?php
include 'header.php';
global $ALL_ORDERS_HISTORY; // ดึงข้อมูล Order History ทั้งหมดมาเพื่อบันทึก

// 1. ตรวจสอบและรับข้อมูล
$name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : 'Customer';
$delivery_method = isset($_POST['order_delivery_method']) ? htmlspecialchars($_POST['order_delivery_method']) : 'Online Payment';
$payment_method = isset($_POST['final_payment_method']) ? htmlspecialchars($_POST['final_payment_method']) : 'Credit Card';
$order_total = isset($_POST['order_total_price']) ? (float)$_POST['order_total_price'] : 0;
$ordered_items_json = isset($_POST['ordered_items_json']) ? $_POST['ordered_items_json'] : '[]';
$current_username = strtolower($_SESSION['username'] ?? 'guest'); // ใช้ username ที่ login อยู่

// 2. แปลง JSON รายการสินค้าที่สั่งซื้อกลับมา
// 🔴 NEW: ใช้ stripslashes และ json_decode
$ordered_items = json_decode(stripslashes($ordered_items_json), true);

// 3. เตรียมข้อมูลคำสั่งซื้อใหม่ (เพื่อบันทึก)
$new_order_id = 'ORD' . date('YmdHis') . rand(10, 99);
$new_order_data = [
    'order_id' => $new_order_id,
    'date' => date('Y-m-d H:i:s'),
    'status' => 'ordered', // สถานะเริ่มต้น
    'total' => $order_total,
    'delivery' => $delivery_method,
    'payment' => $payment_method,
    'recipient' => $name,
    'items' => []
];

// 4. เตรียมรายการสินค้าในรูปแบบที่เก็บใน JSON (รวมข้อมูลสินค้าเพิ่มเติมที่หายไป)
foreach ($ordered_items as $product_id => $quantity) {
    if (!isset($products[$product_id])) continue;
    $product_info = $products[$product_id];
    $item_total = $product_info['price'] * $quantity;

    $new_order_data['items'][] = [
        'id' => $product_id,
        'name' => $product_info['name'],
        'price' => $product_info['price'], // ใช้ราคาเดิมจาก data.php
        'size' => '42.5 EU', // NOTE: ค่า Size จำลอง
        'qty' => $quantity,
        'total' => $item_total
    ];
}

// ===============================================
// 5. บันทึกคำสั่งซื้อลงใน JSON (Controller Logic)
// ===============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($new_order_data['items']) && $current_username !== 'guest') {
    
    // บันทึกข้อมูลคำสั่งซื้อใหม่
    if (!isset($ALL_ORDERS_HISTORY[$current_username])) {
        $ALL_ORDERS_HISTORY[$current_username] = [];
    }
    // เพิ่มคำสั่งซื้อใหม่เข้าไป (เรียงลำดับจากล่าสุดอยู่ด้านหน้า)
    array_unshift($ALL_ORDERS_HISTORY[$current_username], $new_order_data); 

    // บันทึกข้อมูลทั้งหมดกลับไปยัง orders.json
    $json_output = json_encode($ALL_ORDERS_HISTORY, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $orders_file_path = 'orders.json';
    
    if (file_put_contents($orders_file_path, $json_output) === false) {
        // Log Error (ถ้ามีฐานข้อมูลจะเก็บ log)
        error_log("Failed to write order data to orders.json for user: {$current_username}");
    }
}


// 6. ล้างตะกร้าสินค้าจริง (เพราะการสั่งซื้อถือว่าสำเร็จ)
if (isset($_SESSION['cart'])) {
    unset($_SESSION['cart']); 
}
?>

<div class="processing-wrapper">
    <div class="processing-screen" id="processing">
        <h1>Processing Your Order...</h1>
        <p>Please wait while we finalize your payment and order details.</p>
        <div class="spinner"></div>
    </div>

    <div class="success-screen hidden" id="success">
        <h1 class="success-header">Your payment has been completed successfully</h1>
        <p class="thank-you-text">Thank you for trusting ironboots</p>

        <div class="order-details-container">
            <h3>Order Details (<?php echo $new_order_id; ?>)</h3>
            
            <?php 
            // 🟢 วนลูปแสดงรายละเอียดสินค้าที่เพิ่งสั่งซื้อไป
            if (!empty($new_order_data['items'])): 
                foreach ($new_order_data['items'] as $item):
                    $product = $products[$item['id']]; 
            ?>
                <div class="order-item-row">
                    <div class="item-left">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $item['name']; ?>" class="order-item-image">
                    </div>
                    <div class="item-right">
                        <p class="item-name"><?php echo $item['name']; ?></p>
                        <p class="item-meta">SIZE : <?php echo $item['size']; ?></p> 
                        <p class="item-meta">Quantity : <?php echo $item['qty']; ?></p>
                    </div>
                    <span class="item-total"><?php echo format_price($item['total']); ?></span>
                </div>
            <?php endforeach; 
            endif; ?>
        </div>
        
        <hr class="order-divider">

        <div class="order-summary-footer">
            <p><strong>Order Total:</strong> <?php echo format_price($new_order_data['total']); ?></p>
        </div>

        <div class="action-buttons">
            <a href="index.php" class="btn secondary-btn">BACK TO HOME</a>
            <a href="order_history.php" class="btn primary-btn view-history-btn">VIEW ORDER HISTORY</a>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // 🟢 JavaScript: จำลองการประมวลผล 2 วินาที
    setTimeout(function() {
        $('#processing').fadeOut(500, function() {
            $('#success').removeClass('hidden').fadeIn(500);
        });
    }, 2000); // 2000ms = 2 วินาที
});
</script>

<?php include 'footer.php'; ?>