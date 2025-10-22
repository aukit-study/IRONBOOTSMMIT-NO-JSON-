<?php 
include 'header.php'; 
global $order_history, $products;

$order_id = isset($_GET['id']) ? $_GET['id'] : key($order_history); // ใช้ Order แรกถ้าไม่มี ID

if (!isset($order_history[$order_id])) {
    echo "<h1 class='text-center'>Order not found!</h1>";
    include 'footer.php';
    exit();
}

$order = $order_history[$order_id];
$current_status = $order['status'];

// สถานะใน Timeline (ต้องตรงกับ key ใน $order_history)
$timeline = [
    'ordered' => 'Order Placed',
    'checked_out' => 'Checkout',
    'prepared' => 'Prepare Products',
    'in_transit' => 'Product is in transit',
    'delivered' => 'Product has been delivered'
];

$found_current = false;
?>

<h1 class="page-header-title">ORDERS STATUS</h1>

<div class="order-status-detail-container">
    <?php 
    $first_item = $order['items'][0];
    $item_product_id = $first_item['id'];
    $product_info = $products[$item_product_id] ?? null;

    if ($product_info):
    ?>
    <div class="item-status-detail-card">
        <img src="<?php echo $product_info['image']; ?>" alt="<?php echo $first_item['name']; ?>" class="item-status-image">
        <div class="item-info-status">
            <p class="item-name-status"><?php echo htmlspecialchars($first_item['name']); ?></p>
            <p class="item-meta-status">SIZE : <?php echo htmlspecialchars($first_item['size']); ?></p>
            <p class="item-meta-status">Quantity : <?php echo htmlspecialchars($first_item['qty']); ?></p>
        </div>
        <span class="item-price-status"><?php echo format_price($first_item['discounted_price']); ?></span>
    </div>
    <?php endif; ?>

    <div class="status-timeline-wrapper">
        <?php foreach ($timeline as $key => $label): 
            $is_completed = $found_current || $key === $current_status;
            $is_active = $key === $current_status;
            
            // เมื่อเจอสถานะปัจจุบัน ให้ตั้งค่า $found_current เป็น true
            if ($key === $current_status) {
                $found_current = true;
            }
        ?>
            <div class="status-step <?php echo $is_completed ? 'completed' : ''; ?> <?php echo $is_active ? 'active' : ''; ?>">
                <div class="step-icon">
                    <?php if ($key === 'ordered'): ?><img src="images/icon_order.png" alt="Order"><?php endif; ?>
                    <?php if ($key === 'checked_out'): ?><img src="images/icon_checkout.png" alt="Checkout"><?php endif; ?>
                    <?php if ($key === 'prepared'): ?><img src="images/icon_prepare.png" alt="Prepare"><?php endif; ?>
                    <?php if ($key === 'in_transit'): ?><img src="images/icon_transit.png" alt="Transit"><?php endif; ?>
                    <?php if ($key === 'delivered'): ?><img src="images/icon_delivered.png" alt="Delivered"><?php endif; ?>
                </div>
                <div class="step-label"><?php echo $label; ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'footer.php'; ?>