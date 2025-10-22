<?php
// ✅ Requirement: User-defined Function #1
// ฟังก์ชันสำหรับแสดงราคาสินค้าในรูปแบบสกุลเงินไทย
function format_price($price) {
    return number_format($price, 2) . ' ฿';
}

// ✅ Requirement: User-defined Function #2
// ฟังก์ชันสำหรับแสดงการ์ดสินค้า 1 ชิ้น
function display_product_card($product) {
    // ตรวจสอบสถานะ Favorites (ต้องเริ่ม session_start() ในไฟล์หลัก)
    $is_favorite = isset($_SESSION['favorites']) && in_array($product['id'], $_SESSION['favorites']);
    $heart_class = $is_favorite ? 'is-favorite' : '';

    echo "<div class='product-card'>";
    echo "<div class='favorite-btn toggle-favorite {$heart_class}' data-id='" . $product['id'] . "'>&hearts;</div>"; 
    echo "<a href='product_detail.php?id=" . $product['id'] . "'>";

    echo "<img src='" . $product['image'] . "' alt='" . $product['name'] . "'>";
    echo "<h3>" . htmlspecialchars($product['name']) . "</h3>";
    echo "<p class='price'>" . format_price($product['price']) . "</p>";
    echo "</a>";
    echo "</div>";
}

// ✅ Requirement: User-defined Function #3
// ฟังก์ชันสำหรับคำนวณราคารวมในตะกร้า
function calculate_cart_total() {
    $total = 0;
    if (!empty($_SESSION['cart'])) {
        global $products; // 🔴 สำคัญ: ทำให้เข้าถึง $products จาก data.php ได้
        
        // 🟢 แก้ไข Loop ให้วนผ่านโครงสร้างใหม่ (product_id => sizes)
        foreach ($_SESSION['cart'] as $product_id => $sizes) {
            if (isset($products[$product_id])) { // ตรวจสอบความปลอดภัย
                
                // 🟢 วน Loop ที่ 2 เพื่อเอา $size และ $quantity
                foreach ($sizes as $size => $quantity) {
                    $total += $products[$product_id]['price'] * $quantity;
                }
            }
        }
    }
    return $total;
}
// ฟังก์ชันสำหรับแสดงรีวิวสินค้าเฉพาะ ID นั้นๆ
function display_product_reviews($product_id) {
    global $product_reviews, $products; // ดึงข้อมูลรีวิวและสินค้ามาใช้

    // 1. ดึงรีวิวเฉพาะสินค้านี้
    $reviews = $product_reviews[$product_id] ?? [];
    $review_count = count($reviews);

    // 2. คำนวณคะแนนเฉลี่ยและสถิติ (คล้ายกับ reviews.php แต่ใช้เฉพาะสินค้านี้)
    $total_rating = 0;
    $rating_counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

    foreach ($reviews as $review) {
        $total_rating += $review['rating'];
        $rating_counts[$review['rating']]++;
    }

    $average_rating = $review_count > 0 ? round($total_rating / $review_count, 1) : 0;
    
    // =====================================
    // 🔴 ส่วนแสดงผลสรุปและปุ่มเขียนรีวิว
    // =====================================
    echo '<h2 class="section-header">Customer Reviews ('. $review_count . ')</h2>';
    echo '<div class="reviews-summary-wrapper">';
    
    // คะแนนเฉลี่ย
    echo '<div class="average-rating-display">';
    echo '<span class="rating-score">'. number_format($average_rating, 1) .'/5</span>';
    echo '<p class="review-total-count">based on '. $review_count .' reviews</p>';
    echo '</div>';

    // กราฟ Breakdown
    echo '<div class="rating-breakdown">';
    for ($i = 5; $i >= 1; $i--): 
        $percent = $review_count > 0 ? ($rating_counts[$i] / $review_count) * 100 : 0;
        echo '<div class="rating-row">';
        echo '<span class="star-label">'. $i .' Star</span>';
        echo '<div class="bar-container"><div class="bar-fill" style="width: '. $percent .'%;"></div></div>';
        echo '<span class="count-label">'. $rating_counts[$i] .'</span>';
        echo '</div>';
    endfor;
    echo '</div>';
    
    // ปุ่มเขียนรีวิว
    echo '<div class="write-review-area">';
    echo '</div>';
    
    echo '</div>'; // End reviews-summary-wrapper
    
    // =====================================
    // 🔴 ส่วนแสดงรายการรีวิวแต่ละอัน
    // =====================================
    echo '<div class="all-reviews-list">';
    if (empty($reviews)) {
        echo '<p class="text-center">Be the first to review this product!</p>';
    } else {
        foreach ($reviews as $review): 
            $product = $products[$product_id];
            
            // แสดงผลเหมือน Card ใน reviews.php
            echo '<div class="review-card-detail product-detail-review-card">';
            
            // Meta (User, Stars)
            echo '<div class="review-meta">';
            echo '<p class="review-user-icon"><img src="images/icon_user_mock.png" alt="User"></p>';
            echo '<p class="review-username">'. htmlspecialchars($review['user']) .'</p>';
            echo '<p class="review-stars">'. str_repeat('⭐', $review['rating']) .'</p>';
            echo '<p class="review-date-meta">'. htmlspecialchars($review['date']) .'</p>';
            echo '</div>';
            
            // Body (Comment)
            echo '<div class="review-body">';
            echo '<p class="review-comment">'. htmlspecialchars($review['comment']) .'</p>';
            echo '<p class="review-size-meta">Size: '. htmlspecialchars($review['size']) .'</p>';
            echo '</div>';

            echo '</div>'; // End review-card-detail
        endforeach; 
    }
    echo '</div>'; // End all-reviews-list
}

?>

