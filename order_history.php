<?php 
include 'header.php'; 
global $order_history, $products;

// ... (โค้ด Logic การดึงข้อมูล Order History และ Status Map เดิม) ...
$status_map = [
    'delivered' => 'Delivered',
    'in_transit' => 'In Transit',
    'prepared' => 'Shipped',
    'checked_out' => 'Processing',
    'ordered' => 'Confirmed'
];
?>

<h1 class="page-header-title">ORDER HISTORY</h1>

<div class="order-history-list">
    <?php if (empty($order_history)): ?>
        <p class="text-center">You have no past orders.</p>
    <?php else: ?>
        <?php foreach ($order_history as $order_id => $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <h2>Order #<?php echo htmlspecialchars($order_id); ?></h2>
                    <span class="order-status-tag status-<?php echo strtolower($order['status']); ?>">
                        <?php echo htmlspecialchars($status_map[$order['status']] ?? 'Unknown'); ?>
                    </span>
                </div>
                <p class="order-date">Date Placed: <?php echo htmlspecialchars($order['date']); ?></p>
                
                <div class="order-items-summary">
                    <?php 
                    $first_item = $order['items'][0];
                    $item_product_id = $first_item['id'];
                    $item_image = $products[$item_product_id]['image'] ?? 'images/default.jpg';
                    ?>
                    <div class="item-visual-summary">
                        <img src="<?php echo $item_image; ?>" alt="<?php echo $first_item['name']; ?>" class="order-item-thumb">
                        <div class="item-text-info">
                            <p class="item-name"><?php echo htmlspecialchars($first_item['name']); ?></p>
                            <p class="item-qty"><?php echo count($order['items']) > 1 ? "+".(count($order['items']) - 1)." more items" : "Qty: 1"; ?></p>
                        </div>
                    </div>
                    <div class="order-actions">
                        <p class="order-total-price">Total: <?php echo format_price($order['total']); ?></p>
                        <a href="order_status.php?id=<?php echo htmlspecialchars($order_id); ?>" class="btn primary-btn small-btn">View Status</a>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>