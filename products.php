<?php
include 'header.php';

// 1. รับค่า category จาก URL
$selected_category = isset($_GET['category']) ? urldecode($_GET['category']) : 'ALL';
?>

<h1><?php echo htmlspecialchars($selected_category); ?> SHOES</h1>

<div class="product-grid">
    <?php
    // ✅ Requirement: Repetition Structure #1 (foreach loop)
    // 2. วนลูปแสดงสินค้าทั้งหมด
    $found_products = false;
    foreach ($products as $product) {
        // 3. ตรวจสอบเงื่อนไขการกรอง
        if ($selected_category === 'ALL' || $product['category'] === $selected_category) {
            display_product_card($product); // เรียกใช้ฟังก์ชันที่สร้างเอง
            $found_products = true;
        }
    }
    
    // 4. ถ้าไม่พบสินค้าในหมวดหมู่
    if (!$found_products) {
        echo "<p style='grid-column: 1 / -1; text-align: center; margin: 50px 0;'>ไม่พบสินค้าในหมวดหมู่ '{$selected_category}'</p>";
    }
    ?>
</div>

<?php include 'footer.php'; ?>