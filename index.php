<?php 
include 'header.php'; 

// 1. ตรวจสอบและรับค่า Search Term
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$is_searching = !empty($search_term);

// 2. จัดกลุ่มสินค้าตาม Category (โค้ดเดิม)
$products_to_display = $products; // เริ่มต้นด้วยสินค้าทั้งหมด

// 🟢 NEW LOGIC: ถ้ามีการค้นหา ให้กรองสินค้า
if ($is_searching) {
    $filtered_products = [];
    $search_term_lower = strtolower($search_term);

    foreach ($products as $id => $product) {
        // ตรวจสอบชื่อสินค้าหรือหมวดหมู่ว่ามีคำค้นหาอยู่หรือไม่
        if (strpos(strtolower($product['name']), $search_term_lower) !== false ||
            strpos(strtolower($product['category']), $search_term_lower) !== false) {
            
            $filtered_products[$id] = $product;
        }
    }
    $products_to_display = $filtered_products;
}

// 3. จัดกลุ่มสินค้าที่ต้องแสดงผลตาม Category
$products_by_category = [];
foreach ($products_to_display as $product) {
    // ใช้หมวดหมู่เป็น Key ใน Array ใหม่
    $products_by_category[$product['category']][] = $product;
}

// 4. กำหนด Title ของหน้า
$page_title = $is_searching ? "Search Results for \"{$search_term}\"" : "Featured Products";
?>

<div class="promo-banner">
    <?php if ($is_searching): ?>
        <h1 class="search-results-title"><?php echo $page_title; ?></h1>
        <?php if (empty($products_to_display)): ?>
            <p>Sorry, no products matched your search term.</p>
        <?php endif; ?>
    <?php else: ?>
        <img src="images/promo_banner.jpg" alt="10.10 Promotion" style="width:100%;">
        <h1>IRONBOOTS PROMOTION</h1>
        <p>Get discounts up to 20%! Shop now and experience the difference.</p>
        <a href="#new-arrivals" class="btn">Shop Now</a> 
    <?php endif; ?>
</div>

<?php 
// 5. วนลูปแสดงผลลัพธ์ (มีการปรับ Logic เล็กน้อย)

// ถ้ากำลังค้นหา: ให้วนลูปผ่านหมวดหมู่ที่มีผลลัพธ์เท่านั้น
$categories_to_loop = $is_searching ? array_keys($products_by_category) : $categories;

foreach ($categories_to_loop as $category): 
    $anchor_id = strtolower(str_replace(' ', '-', $category));

    // 🔴 ถ้ามีการค้นหา จะใช้เฉพาะสินค้าที่ถูกกรองแล้วเท่านั้น
    if (isset($products_by_category[$category]) && !empty($products_by_category[$category])):
?>
        <section id="<?php echo htmlspecialchars($anchor_id); ?>" class="category-section">
            
            <h2 class="category-title"><?php echo htmlspecialchars($category); ?></h2>

            <div class="product-grid">
                <?php
                // วนลูปแสดงสินค้าในหมวดหมู่นี้
                foreach ($products_by_category[$category] as $product) {
                    display_product_card($product); // เรียกใช้ฟังก์ชันที่สร้างเอง
                }
                ?>
            </div>
            </section>
        
        <hr style="margin: 40px 0;">

<?php 
    endif;
endforeach; 

?>

<script>
$(document).ready(function() {
    
    // ----------------------------------------------------
    // 🟢 1. SCROLL SPY LOGIC (ปรับแก้ตามการค้นหา)
    // ----------------------------------------------------

    var navHeight = $('header').outerHeight() + $('.page-nav-bar').outerHeight();
    var offset = navHeight + 50; 
    
    // 🔴 NEW: ซ่อน Scroll Spy ถ้ากำลังค้นหา
    <?php if ($is_searching): ?>
        $('.page-nav-bar').hide();
        // ลบ Scroll Spy logic ออกไปเมื่อค้นหา
    <?php else: ?>
        // ... (โค้ด Scroll Spy เดิม) ...
        function checkScroll() {
            // ... (โค้ด checkScroll เดิม) ...
        }
        $(window).scroll(checkScroll);
        checkScroll();
        
        // ... (โค้ด Smooth Scroll เดิม) ...
        $('a[href*="#"]:not([href="#"])').on('click', function(e) {
            if (this.hash !== "" && $(this.hash).length) {
                e.preventDefault();
                var hash = this.hash;
                $('html, body').animate({
                    scrollTop: $(hash).offset().top - navHeight 
                }, 800);
            }
        });
    <?php endif; ?>


    // ----------------------------------------------------
    // 🟢 2. FAVORITE AJAX LOGIC (โค้ดเดิม)
    // ----------------------------------------------------
    // ... (โค้ด Favorites เดิม) ...
    $(document).on('click', '.toggle-favorite', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var productId = $button.data('id');
        
        $.ajax({
            type: "POST",
            url: "favorite_logic.php", 
            data: { product_id: productId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $button.toggleClass('is-favorite', response.is_favorite);
                    $('.favorite-count').text('(' + response.new_count + ')');
                }
            },
            error: function() {
                alert("Could not update favorites.");
            }
        });
    });
    
    // ----------------------------------------------------
    // 🟢 3. NEW: Logic เปิด/ปิดช่องค้นหา
    // ----------------------------------------------------
    $('.search-btn-icon').on('click', function(e) {
        var $input = $(this).siblings('.search-input-hidden');
        
        // ถ้าช่องค้นหายังไม่เปิด และไม่มีการกรอกข้อความ ให้เปิดช่อง
        if (!$input.hasClass('visible') && $input.val() === '') {
            e.preventDefault(); // หยุดการส่งฟอร์มชั่วคราว
            $input.addClass('visible').focus();
            
            // ปรับตำแหน่งปุ่ม Account ให้ชิดขวา
            $('.user-actions').css('margin-left', 'auto');
        } 
        // ถ้าช่องค้นหาเปิดอยู่ หรือมีการกรอกข้อความแล้ว ให้ส่งฟอร์ม (Submit)
        // หรือถ้าไม่มีการกรอกข้อความ แต่กดซ้ำ ให้ปิดช่อง (Optional: สามารถปรับให้กดซ้ำแล้วส่งฟอร์มได้เลย)
    });
});
</script>
<h1>Website Reviews </h1>

<div class="reviews-container">
    <?php foreach ($reviews as $review): ?>
        <div class="review-card">
            <h4><?php echo htmlspecialchars($review['user']); ?></h4>
            <div class="rating">
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <?php if ($i < $review['rating']): ?>
                        <span>★</span> <?php else: ?>
                        <span>☆</span> <?php endif; ?>
                <?php endfor; ?>
            </div>
            <p>"<?php echo htmlspecialchars($review['comment']); ?>"</p>
        </div>
    <?php endforeach; ?>
</div>

<style>
.review-card { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
.rating { color: orange; font-size: 1.2em; }
</style>
<?php include 'footer.php'; ?>