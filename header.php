<?php
// บรรทัดนี้สำคัญมาก ต้องอยู่บนสุดเสมอ
if (session_status() == PHP_SESSION_NONE) {
    session_start();    
}
require_once 'data.php';
require_once 'functions.php';

// 🟢 Logic 1: ตรวจสอบชื่อไฟล์ปัจจุบันเพื่อใช้ในการซ่อน Nav Bar
$current_page = basename($_SERVER['PHP_SELF']);
$hide_nav_pages = ['login.php', 'register.php', 'login_process.php', 'logout.php'];

// 🟢 Logic 2: กำหนดค่าแสดงผลสำหรับผู้ใช้ที่ยังไม่ได้ Login เพื่อป้องกัน Warning
$user_display_name = $_SESSION['display_name'] ?? $_SESSION['username'] ?? 'Guest';

// 🟢 Logic 3: คำนวณ Total สำหรับ Side Cart (ใช้ฟังก์ชัน)
$current_cart_total = calculate_cart_total();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IRONBOOTS</title>
    <link rel="icon" type="image/png" href="/images/logo short.png">
    <link rel="stylesheet" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php">
                <img src="images/logo.png" alt="IRONBOOTS Logo"></a>
        </div>

        <div class="user-actions">
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <span>Welcome <?php echo htmlspecialchars($user_display_name); ?>!</span>
                <a href="order_history.php">Order History</a>
                <a style="color: #e22a2aff"href="logout.php">LOGOUT</a>
                
                <a href=""></a>
                <?php else: ?>
                <a href="register.php">REGISTER</a>
                <a href="login.php">LOGIN </a> 
                <a href=""></a>
                <?php endif; ?>
        </div>
    </header>

    <?php if (!in_array($current_page, $hide_nav_pages)): ?>
    <div class="page-nav-bar">
        <nav class="category-nav">
            <ul>
                <?php foreach ($categories as $category): ?>
                    <?php 
                        $anchor_id = strtolower(str_replace(' ', '-', $category));
                    ?>
                    <li>
                        <a href="index.php#<?php echo $anchor_id; ?>" data-target="#<?php echo $anchor_id; ?>">
                            <?php echo $category; ?>
                        </a>
                        
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        
        <div class="content-actions">
            <form action="index.php" method="GET" class="search-form-inline">
                <input type="text" name="search" placeholder="Search..." class="search-input-hidden">
                <button type="submit" class="search-btn-icon">
                    <img src="images/search_icon.png" alt="Search">
                </button>
            </form>
            <a href="#" id="favorite-icon-open">
                 <img src="images/favorite_icon.png" alt="Favorite">
                 <span class="favorite-count">(<?php echo isset($_SESSION['favorites']) ? count($_SESSION['favorites']) : 0; ?>)</span>
            </a>
            <a href="#" id="cart-icon-open">
                 <img src="images/cart_icon.png" alt="Cart">
                 <span class="cart-count">(<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</span>
            </a>
        </div>
    </div>
    <?php endif; // สิ้นสุดเงื่อนไขการซ่อน ?>
    
    <main class="container">
<div id="side-cart-overlay"></div>

<div id="side-cart" class="side-panel"> 
    <div class="cart-header">
        <h2>YOUR CART</h2>
        <button id="cart-icon-close" class="close-btn" data-panel="cart">&times;</button>
    </div>
    
<div class="cart-items-container">
    <?php if (empty($_SESSION['cart'])): ?>
        <p class="empty-message">Your cart is empty.</p>
    <?php else: ?>
        <?php 
        // 🟢 1. Loop แรก: วนหา Product ID
        foreach ($_SESSION['cart'] as $product_id => $sizes):
            if (!isset($products[$product_id])) continue;
            
            $product = $products[$product_id];
            
            // 🟢 2. Loop สอง: วนหา Size และ Quantity ที่อยู่ใน Product ID นั้น
            foreach ($sizes as $size => $quantity):
                $subtotal = $product['price'] * $quantity;
        ?>
            <div class="cart-item">
                <div class="item-details">
                    <div class="item-image">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    </div>
                    <div class="item-info">
                        <p class="item-name"><?php echo $product['name']; ?></p>
                        <p class="item-size">SIZE : <?php echo htmlspecialchars($size); ?> EU</p> 
                    </div>
                    <div class="item-quantity">
                         <div class="quantity-control">
                              <form method="POST" action="cart.php" class="inline-form">
                                 <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                 <input type="hidden" name="size" value="<?php echo htmlspecialchars($size); ?>">
                                 <input type="hidden" name="update_quantity_action" value="decrease">
                                 <button type="submit" class="qty-btn-small" <?php echo ($quantity <= 1) ? 'disabled' : ''; ?>>-</button>
                             </form>
                             <span class="qty-display"><?php echo $quantity; ?></span>
                             <form method="POST" action="cart.php" class="inline-form">
                                 <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                 <input type="hidden" name="size" value="<?php echo htmlspecialchars($size); ?>">
                                 <input type="hidden" name="update_quantity_action" value="increase">
                                 <button type="submit" class="qty-btn-small">+</button>
                             </form>
                         </div>
                    </div>
                </div>
                <div class="item-footer">
                    <span class="item-price"><?php echo format_price($product['price']); ?></span>
                    <a href="cart.php?action=remove&id=<?php echo $product_id; ?>&size=<?php echo urlencode($size); ?>" class="remove-btn">
                        <img src="images/trash_icon.png" alt="Remove">
                    </a>
                </div>
            </div>
        <?php 
            endforeach; // จบ Loop $sizes
        endforeach; // จบ Loop $product_id
        ?>
    <?php endif; ?>
    </div>
    <?php if (!empty($_SESSION['cart'])): ?>
    <div class="cart-footer">
        <div class="total-payment">
            <span class="label">TOTAL PAYMENT :</span>
            <span class="value"><?php echo format_price($current_cart_total); ?></span>
        </div>
        <a href="checkout.php" class="btn checkout-btn primary-btn">PAYMENT</a>
        <a href="cart.php?action=clear" class="btn secondary-btn clear-side-cart-btn" onclick="return confirm('Are you sure you want to clear your entire cart?');" style="margin-top: 10px;">Clear Cart</a>
    </div>
    <?php endif; ?>
</div><div id="side-favorite" class="side-panel">
    <div class="cart-header">
        <h2>YOUR FAVORITES</h2>
        <button id="favorite-icon-close" class="close-btn" data-panel="favorite">&times;</button>
    </div>
    
    <div class="favorite-items-container cart-items-container">
        <?php if (empty($_SESSION['favorites'])): ?>
            <p class="empty-message">Your favorites list is empty.</p>
        <?php else: ?>
            <?php 
            foreach ($_SESSION['favorites'] as $product_id):
                if (!isset($products[$product_id])) continue;
                $product = $products[$product_id];
            ?>
                <div class="cart-item">
                    <div class="item-details">
                        <div class="item-image">
                            <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                        </div>
                        <div class="item-info">
                            <p class="item-name"><?php echo $product['name']; ?></p>
                            <p class="item-price"><?php echo format_price($product['price']); ?></p>
                        </div>
                    </div>
                    <div class="item-footer">
                        <a href="product_detail.php?id=<?php echo $product_id; ?>" class="btn primary-btn small-btn">View</a>
                        <a href="#" class="remove-btn toggle-favorite is-favorite" data-id="<?php echo $product_id; ?>">
                            <img src="images/trash_icon.png" alt="Remove">
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['favorites'])): ?>
    <div class="cart-footer">
        <a href="index.php" class="btn secondary-btn clear-side-cart-btn">Continue Shopping</a>
    </div>
    <?php endif; ?>
</div><script>
$(document).ready(function() {
    
    // 🟢 NEW FUNCTION: ควบคุมการเปิด Panel
    function openPanel(panelId) {
        // ปิด Panel ทั้งหมดก่อน
        $('.side-panel').removeClass('open'); 

        // เปิด Panel ที่ต้องการ
        $('#' + panelId).addClass('open');
        $('#side-cart-overlay').addClass('visible');
        $('body').addClass('no-scroll');
    }
    
    // 🟢 1. LOGIC: ตรวจสอบ Session เพื่อเปิด Side Cart หลัง Redirect
    <?php 
    // ตรวจสอบว่ามี Session ที่บอกให้เปิด Cart หรือไม่
    if (isset($_SESSION['side_cart_open']) && $_SESSION['side_cart_open']): ?>
        openPanel('side-cart');
        <?php unset($_SESSION['side_cart_open']); ?>
    <?php endif; ?>

    // 2. เปิด Side Cart เมื่อคลิก Icon Cart
    $('#cart-icon-open').on('click', function(e) {
        e.preventDefault();
        openPanel('side-cart');
    });

    // 3. เปิด Side Favorite เมื่อคลิก Icon Favorite
    $('#favorite-icon-open').on('click', function(e) {
        e.preventDefault();
        openPanel('side-favorite');
    });
    
    // 4. ปิด Panel (Side Cart หรือ Side Favorite)
    $('#cart-icon-close, #favorite-icon-close, #side-cart-overlay').on('click', function() {
        $('.side-panel').removeClass('open');
        $('#side-cart-overlay').removeClass('visible');
        $('body').removeClass('no-scroll');
    });

    // 5. ป้องกันการคลิกใน Side Panels แล้วปิด
    $('#side-cart, #side-favorite').on('click', function(e) {
        e.stopPropagation();
    });
});
</script>