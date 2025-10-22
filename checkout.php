<?php
include 'header.php';
// 🟢 NEW: เรียกใช้ไฟล์โหลดข้อมูลที่อยู่ เพื่อให้ตัวแปร $FULL_ADDRESS_DATA และ $provinces_data พร้อมใช้งาน
require_once 'address_data_loader.php'; 

// 🔴 ดึงตัวแปร global ที่จำเป็นทั้งหมด
global  $provinces_data;

// ====================================================================
// *** 1. PromptPay Configuration and Utility Functions (โค้ดเดิม) ***
// ====================================================================

// **IMPORTANT: เปลี่ยนเบอร์โทรศัพท์นี้เป็น PromptPay ID ของร้านค้า (ใช้สำหรับอ้างอิงเท่านั้น)**
const PROMPTPAY_ID = '92541942'; 

// ฟังก์ชันคำนวณ Checksum (CRC16)
function calculateCRC16($data) {
    $crc = 0xFFFF;
    $len = strlen($data);
    for ($i = 0; $i < $len; $i++) {
        $crc ^= (ord($data[$i]) << 8);
        for ($j = 0; $j < 8; $j++) {
            if (($crc & 0x8000) > 0) {
                $crc = ($crc << 1) ^ 0x1021;
            } else {
                $crc = $crc << 1;
            }
        }
    }
    return sprintf('%04X', $crc & 0xFFFF);
}

// ฟังก์ชันสร้าง PromptPay Payload String
function generatePromptPayPayload($promptpay_id, $amount, $ref_id = '') {
    $payload = '000201'; 
    $payload .= '010211'; 
    
    $merchant_info = '2937'; 
    $merchant_info .= '0016A000000677010111'; 
    
    $pp_id_clean = str_replace('-', '', $promptpay_id);
    $pp_type = (strlen($pp_id_clean) == 10 && strpos($pp_id_clean, '-') === false) ? '02' : '03'; 
    $pp_id = $pp_type . str_pad(strlen($pp_id_clean), 2, '0', STR_PAD_LEFT) . $pp_id_clean;
    $merchant_info .= '01' . str_pad(strlen($pp_id), 2, '0', STR_PAD_LEFT) . $pp_id;

    $payload .= $merchant_info;

    $payload .= '5303764'; 
    $amount_str = number_format($amount, 2, '.', '');
    $payload .= '54' . str_pad(strlen($amount_str), 2, '0', STR_PAD_LEFT) . $amount_str;

    $payload .= '5802TH'; 
    if (!empty($ref_id)) {
        $ref_data = '01' . str_pad(strlen($ref_id), 2, '0', STR_PAD_LEFT) . $ref_id;
        $payload .= '62' . str_pad(strlen($ref_data), 2, '0', STR_PAD_LEFT) . $ref_data;
    }
    
    $payload_with_crc_placeholder = $payload . '6304';
    $crc = calculateCRC16($payload_with_crc_placeholder);
    
    return $payload_with_crc_placeholder . strtoupper($crc);
}


// 2. Logic คำนวณราคา
if (empty($_SESSION['cart'])) {
    header('Location: index.php'); 
    exit();
}

$shipping_cost = 50.00; // ค่าส่งคงที่
$is_member = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true; 
$discount_rate = $is_member ? 0.10 : 0.00;

$total_price = calculate_cart_total();
$discount_amount = $total_price * $discount_rate;
$order_total = $total_price - $discount_amount + $shipping_cost; 

// 3. PromptPay Data
$promptpay_phone = '081-123-4567';
$promptpay_name = 'IRONBOOTS SHOP';
$order_ref_id = 'ORD' . time() . rand(100, 999);
$promptpay_payload = generatePromptPayPayload(PROMPTPAY_ID, $order_total, $order_ref_id);

// 4. เตรียม JSON สำหรับส่ง
$cart_items_for_json = [];
foreach ($_SESSION['cart'] as $product_id => $sizes) {
    $product = $products[$product_id];
    
    // 🟢 วน Loop ที่ 2 เพื่อเอา $size และ $quantity
    foreach ($sizes as $size => $quantity) {
        $cart_items_for_json[] = [
            'id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'qty' => $quantity,
            'size' => $size, // 🟢 แก้ไข: ใช้ $size ที่ได้จาก Loop
            'total' => $product['price'] * $quantity
        ];
    }
}
// 🔴 สร้าง Final JSON String ที่มีข้อมูลครบถ้วน
$cart_json = json_encode($cart_items_for_json, JSON_UNESCAPED_UNICODE);
?>

<div class="checkout-layout">
    <div class="checkout-form-area">
        
        <h2 class="section-title">Delivery</h2>
        <div class="delivery-method-group">
            <button type="button" class="method-btn active" data-method="online" id="btn-online-payment">Online Payment</button>
            <button type="button" class="method-btn" data-method="cod" id="btn-cash-delivery">Cash on Delivery</button>
        </div>
        
        <h2 class="section-title">Shipping Address</h2>
        <form action="order_success.php" method="POST" id="checkout-form">
            <input type="hidden" name="order_total_price" value="<?php echo $order_total; ?>">
            <input type="hidden" name="order_delivery_method" id="order-delivery-method" value="online">
            
            <input type="hidden" name="final_payment_method" id="final-payment-method" value="credit_card"> 

            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="country">Country:</label>
                <input type="text" id="country" name="country" value="Thailand" readonly class="locked-input">
            </div>

            <div class="address-row dynamic-address">
                <div class="form-group half-width">
                    <label for="province">Province / State:</label>
                    <select id="province" name="province" required>
                        <option value="">-- Select Province --</option>
                        <?php 
                        // 🟢 ใส่ตัวเลือกจังหวัดจาก $provinces_data
                        if (!empty($provinces_data)) {
                            foreach ($provinces_data as $province) {
                                $name_th = htmlspecialchars($province['provinceNameTh']);
                                echo "<option value=\"{$name_th}\">{$name_th}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group half-width">
                    <label for="district">District / Amphoe:</label>
                    <select id="district" name="district" required disabled>
                        <option value="">-- Select District --</option>
                    </select>
                </div>
            </div>
            
            <div class="address-row dynamic-address">
                <div class="form-group half-width">
                    <label for="subdistrict">Sub-district / Tambon (Manual):</label>
                    <input type="text" id="subdistrict" name="subdistrict" required placeholder="Enter Sub-district">
                </div>
                
                <div class="form-group half-width">
                    <label for="zipcode">Zip/Postal Code:</label>
                    <input type="text" id="zipcode" name="zipcode" required readonly class="locked-input">
                </div>
            </div>


            <div id="payment-method-section">
                <h2 class="section-title">Payment Method</h2>
                <div class="payment-method-group">
                    <label class="payment-option">
                        <input type="radio" name="payment_selection" value="credit_card" checked data-details="card-details">
                        <span>Credit Card</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_selection" value="promptpay" data-details="promptpay-details">
                        <span>Promptpay</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_selection" value="iron_wallet" data-details="wallet-details">
                        <span>Iron Money Wallet</span>
                    </label>
                </div>
                
                <div class="payment-details-area">
                    
                    <div id="card-details" class="payment-detail-content">
                        <div class="form-group">
                            <label for="card_number">Card Number:</label>
                            <input type="text" name="card_number" required>
                        </div>
                        <div class="address-row">
                            <div class="form-group half-width">
                                <label for="expiry">Expiry (MM/YY):</label>
                                <input type="text" name="expiry" placeholder="MM/YY" required>
                            </div>
                            <div class="form-group half-width">
                                <label for="cvc">CVC:</label>
                                <input type="text" name="cvc" required>
                            </div>
                        </div>
                    </div>
                    
                    <div id="promptpay-details" class="payment-detail-content hidden">
                        <p class="text-center bold">Scan QR Code เพื่อชำระเงิน</p>
                        <p class="text-center bold amount-display"><?php echo format_price($order_total); ?></p>
                        
                        <img 
                            src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($promptpay_payload); ?>" 
                            alt="PromptPay QR Code" 
                            class="qrcode-image generated-qr">
                        
                        <div class="promptpay-info">
                            <p><strong>To Account:</strong> <?php echo PROMPTPAY_ID; ?></p>
                            <p><strong>Account Name:</strong> <?php echo $promptpay_name; ?></p>
                            <p><strong>Amount:</strong> <?php echo format_price($order_total); ?></p>
                            <p><strong>Reference ID:</strong> <?php echo $order_ref_id; ?></p>
                            <p class="small-text error">Note: This QR code is dynamically generated but requires manual confirmation.</p>
                        </div>
                    </div>

                    <div id="wallet-details" class="payment-detail-content hidden">
                        <p class="text-center error">Iron Money Wallet: This payment method is currently unavailable.</p>
                        <p class="text-center">Please select Credit Card or Promptpay.</p>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn primary-btn checkout-submit-btn">CHECKOUT</button>
        </form>
    </div>

    <div class="order-summary-area">
        <h2 class="summary-title">Order Summary</h2>
        
        <?php 
        // 🟢 วน Loop ผ่านโครงสร้างใหม่
        foreach ($_SESSION['cart'] as $product_id => $sizes):
            $product = $products[$product_id]; 
            
            // 🟢 วน Loop ที่ 2 เพื่อเอา $size และ $quantity
            foreach ($sizes as $size => $quantity): 
        ?>
            <div class="summary-item-card">
                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                <div class="item-details">
                    <p class="item-name"><?php echo $product['name']; ?></p>
                    <p class="item-size">SIZE : <?php echo htmlspecialchars($size); ?> EU (x<?php echo $quantity; ?>)</p>
                </div>
                <span class="item-price-summary"><?php echo format_price($product['price'] * $quantity); ?></span>
            </div>
        <?php 
            endforeach;
        endforeach;
        ?>

        <hr class="summary-divider">
        <div class="price-details">
            <p>Product Price : <span><?php echo format_price($total_price); ?></span></p>
            <p>Discount : <span>- <?php echo format_price($discount_amount); ?></span></p>
            <p class="member-discount-text">MEMBER -10% Discount</p>
        </div>
        <hr class="summary-divider">
        <div class="order-total-display">
            <h3 class="label">ORDER TOTAL :</h3>
            <h3 class="value"><?php echo format_price($order_total); ?></h3>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // 🟢 1. Logic สำหรับสลับ Delivery Method (Online/COD)
    $('.delivery-method-group .method-btn').on('click', function() {
        var selectedMethod = $(this).data('method');
        
        $('.delivery-method-group .method-btn').removeClass('active');
        $(this).addClass('active');
        $('#order-delivery-method').val(selectedMethod); // สำหรับ PHP POST

        // แสดง/ซ่อน Payment Method Section
        if (selectedMethod === 'online') {
            $('#payment-method-section').slideDown(300);
            // ตั้งค่า Payment Method เป็น Credit Card ทันทีและ Trigger Change
            $('input[name="payment_selection"][value="credit_card"]').prop('checked', true).trigger('change'); 
        } else if (selectedMethod === 'cod') {
            $('#payment-method-section').slideUp(300);
            // 🔴 ล้างค่า Payment Method สำหรับ COD
            $('#final-payment-method').val('cod'); 
            $('.payment-detail-content').addClass('hidden'); 
        }
    });

    // 🟢 2. Logic สำหรับสลับ Payment Details (Card, Promptpay, Wallet)
    $('input[name="payment_selection"]').on('change', function() {
        var selectedValue = $(this).val();
        var targetId = $(this).data('details');
        
        $('.payment-detail-content').addClass('hidden');
        $('#' + targetId).removeClass('hidden');

        // 🔴 อัปเดต Hidden input สำหรับ Form Submission
        $('#final-payment-method').val(selectedValue);
        
        // 🔴 จัดการ Required Attributes สำหรับ Card
        if (selectedValue === 'credit_card') {
             $('input[name="card_number"], input[name="expiry"], input[name="cvc"]').prop('required', true);
        } else {
             $('input[name="card_number"], input[name="expiry"], input[name="cvc"]').prop('required', false);
        }
    });

    // 🟢 3. ตั้งค่าเริ่มต้นเมื่อโหลดหน้า
    $('#btn-online-payment').trigger('click');

        // 🟢 NEW LOGIC: ข้อมูลที่อยู่แบบเต็มจาก PHP Array
    // NOTE: PHP Array $FULL_ADDRESS_DATA ถูกแปลงเป็น JS Object ที่นี่
    const THAI_ADDRESS_DATA = <?php echo json_encode($FULL_ADDRESS_DATA, JSON_UNESCAPED_UNICODE); ?>;

    // ฟังก์ชันรีเซ็ตและปิดใช้งาน Dropdown
    function resetAndDisable(selector, defaultText) {
        $(selector).html('<option value="">' + defaultText + '</option>').prop('disabled', true);
    }

    // 1. เมื่อเลือกจังหวัด (Province)
    $('#province').on('change', function() {
        const selectedProvince = $(this).val();
        const $districtSelect = $('#district');

        resetAndDisable('#district', '-- Select District --');
        $('#subdistrict').val('');
        $('#zipcode').val('');

        if (selectedProvince && THAI_ADDRESS_DATA[selectedProvince]) {
            // 🟢 District Dropdown: ใช้ District Name (Key) และ Zipcode (Value)
            $.each(THAI_ADDRESS_DATA[selectedProvince], function(districtName, postalCode) {
                // 🔴 NOTE: เราใช้ Postal Code เป็น value ของ option เลย
                $districtSelect.append('<option value="' + districtName + '" data-zip="' + postalCode + '">' + districtName + '</option>');
            });
            $districtSelect.prop('disabled', false);
        }
    });

    // 2. เมื่อเลือกอำเภอ (District)
    $('#district').on('change', function() {
        // 🔴 NEW LOGIC: ดึง Zipcode ทันทีจาก District ที่เลือก
        const selectedZipcode = $(this).find('option:selected').data('zip');
        $('#zipcode').val(selectedZipcode || '');
    });

    // 🟢 3. ตั้งค่าเริ่มต้นเมื่อโหลดหน้า
    $('#btn-online-payment').trigger('click');

});


</script>

<?php include 'footer.php'; ?>