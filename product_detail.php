<?php
include 'header.php';

// 1. รับค่า product ID จาก URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. ตรวจสอบว่ามีสินค้านี้อยู่ใน $products หรือไม่
if (!isset($products[$product_id])) {
    echo "<h1>Product Not Found</h1>";
    include 'footer.php';
    exit();
}

$product = $products[$product_id];

// 3. กำหนดตัวแปรสำหรับสถานะสินค้าและไซส์ (จำลอง)
$stock_status = "IN STOCK"; 
// กำหนดไซส์ที่พร้อมใช้งานสำหรับสินค้านี้ (จำลอง)
// 🟢 เพิ่มไซส์ใน data.php เพื่อให้ครบตามตารางในรูปภาพ
$available_sizes = [40.5, 41, 41.5, 42, 42.5, 43, 43.5, 44, 44.5, 45]; 


?>

<div class="product-detail-layout">
    <div class="product-image">
        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
    </div>
    
    <div class="product-info">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        
        <p class="stock-status">STOCK STATUS : <?php echo htmlspecialchars($stock_status); ?></p>
        
        <hr>

        <h2 class="price-display"><?php echo format_price($product['price']); ?></h2>
        
        <div class="size-selection-area">
            <p class="size-label">SIZE :</p>
            <div class="size-options">
                <?php foreach ($available_sizes as $size): ?>
                    <div class="size-box" data-size="<?php echo $size; ?>">
                        <?php echo $size; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <a href="#" class="size-chart-link" onclick="document.getElementById('size-chart-section').scrollIntoView({ behavior: 'smooth' }); return false;">
                Size Chart
            </a>
        </div>
        
        <form action="cart.php" method="POST" id="add-to-cart-form">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <input type="hidden" name="selected_size" id="selected-size" required> 
            
            <div class="quantity-control-area">
                <p class="quantity-label">Quantity :</p>
                <div class="quantity-control">
                    <button type="button" class="qty-btn" data-type="minus">-</button>
                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="10" required readonly>
                    <button type="button" class="qty-btn" data-type="plus">+</button>
                </div>
            </div>
            
            <button type="submit" name="add_to_cart" class="btn primary-btn add-to-cart-btn">ADD TO CART</button>
            <p id="size-error" style="color: red; margin-top: 10px; display: none;">Please select a size before adding to cart.</p>
        </form>
    </div>
</div>

<hr style="margin: 50px 0;">

<div id="size-chart-section" class="reviews-section">
    <h2>Size Chart</h2>

    <table class="size-chart-table">
        <thead>
            <tr>
                <th>US</th>
                <th>UK</th>
                <th>EU</th>
                <th>JP (mm)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($size_chart as $size): ?>
                <tr>
                    <td><?php echo $size['US']; ?></td>
                    <td><?php echo $size['UK'] ?? 'N/A'; ?></td> <td><?php echo $size['EU']; ?></td>
                    <td><?php echo $size['JP']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<hr style="margin: 50px 0;">

<div class="product-reviews-section">
    <?php display_product_reviews($product_id); // เรียกใช้ฟังก์ชัน ?>
</div>



<script>
$(document).ready(function() {
    var selectedSize = null;

    // 1. Quantity Control (ปุ่ม + และ -)
    $('.qty-btn').on('click', function() {
        var $input = $(this).siblings('#quantity');
        var currentVal = parseInt($input.val());
        var type = $(this).data('type');

        if (type === 'plus') {
            if (currentVal < $input.attr('max')) {
                $input.val(currentVal + 1);
            }
        } else if (type === 'minus') {
            if (currentVal > $input.attr('min')) {
                $input.val(currentVal - 1);
            }
        }
    });

    // 2. Size Selection (ปุ่มไซส์)
    $('.size-box').on('click', function() {
        $('.size-box').removeClass('selected');
        $(this).addClass('selected');
        
        // บันทึกไซส์ที่เลือกไว้ใน Hidden Input
        selectedSize = $(this).data('size');
        $('#selected-size').val(selectedSize);
        $('#size-error').hide(); 
    });

    // 3. Form Validation (ตรวจสอบก่อน Add to Cart)
    $('#add-to-cart-form').on('submit', function(e) {
        if (!selectedSize) {
            e.preventDefault(); // หยุดการส่งฟอร์ม
            $('#size-error').show();
        }
    });
});
</script>

<?php include 'footer.php'; ?>