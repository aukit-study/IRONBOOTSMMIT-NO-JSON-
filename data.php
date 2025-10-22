<?php
// เริ่มต้น session เพื่อใช้เก็บข้อมูลตะกร้าสินค้า
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}



// ✅ Requirement: Array #2 (2-Dimensional Array) - ข้อมูลสินค้า
$products = [
    1 => ['id' => 1, 'name' => 'IRONBOOTS DYNAMO', 'price' => 4000.00, 'image' => 'images/dynamo.jpg', 'category' => 'SPEED'],
    2 => ['id' => 2, 'name' => 'IRONBOOTS PHANTOM', 'price' => 3500.00, 'image' => 'images/phantom.jpg', 'category' => 'CONTROL'],
    3 => ['id' => 3, 'name' => 'IRONBOOTS SURGE', 'price' => 3500.00, 'image' => 'images/surge.jpg', 'category' => 'SPEED'],
    4 => ['id' => 4, 'name' => 'IRONBOOTS SHIELD', 'price' => 4500.00, 'image' => 'images/shield.jpg', 'category' => 'TOUCH'],
    5 => ['id' => 5, 'name' => 'IRONBOOTS APEX', 'price' => 4000.00, 'image' => 'images/apex.jpg', 'category' => 'CONTROL'],
    6 => ['id' => 6, 'name' => 'IRONBOOTS LEGACY', 'price' => 4500.00, 'image' => 'images/legacy.jpg', 'category' => 'TOUCH'],
    7 => ['id' => 7, 'name' => 'IRONBOOTS IGNITE', 'price' => 3500.00, 'image' => 'images/ignite.jpg', 'category' => 'NEW ARRIVALS'],
    8 => ['id' => 8, 'name' => 'IRONBOOTS VISION', 'price' => 3500.00, 'image' => 'images/vision.jpg', 'category' => 'NEW ARRIVALS'],
    9 => ['id' => 9, 'name' => 'IRONBOOTS ECLIPSE', 'price' => 3800.00, 'image' => 'images/eclipse.jpg', 'category' => 'CONTROL'],
    10 => ['id' => 10, 'name' => 'IRONBOOTS QUANTUM', 'price' => 5200.00, 'image' => 'images/quantum.jpg', 'category' => 'TOUCH'],
    11 => ['id' => 11, 'name' => 'IRONBOOTS PULSAR', 'price' => 2900.00, 'image' => 'images/pulsar.jpg', 'category' => 'SPEED'],
    12 => ['id' => 12, 'name' => 'IRONBOOTS NOVA', 'price' => 4200.00, 'image' => 'images/nova.jpg', 'category' => 'NEW ARRIVALS'],
    13 => ['id' => 13, 'name' => 'IRONBOOTS GHOST', 'price' => 3000.00, 'image' => 'images/ghost.jpg', 'category' => 'BEST SELLERS'],
    14 => ['id' => 14, 'name' => 'IRONBOOTS ATOM', 'price' => 4000.00, 'image' => 'images/atom.jpg', 'category' => 'SPEED'],
    15 => ['id' => 15, 'name' => 'IRONBOOTS VORTEX', 'price' => 4500.00, 'image' => 'images/vortex.jpg', 'category' => 'TOUCH'],
    16 => ['id' => 16, 'name' => 'IRONBOOTS GHOST', 'price' => 3000.00, 'image' => 'images/ghost.jpg', 'category' => 'CONTROL'],
    17 => ['id' => 17, 'name' => 'IRONBOOTS VISION', 'price' => 3500.00, 'image' => 'images/vision.jpg', 'category' => 'BEST SELLERS'],
];

// ✅ Requirement: Array #3 (1-Dimensional Array) - หมวดหมู่สินค้า
$categories = ['NEW ARRIVALS', 'SPEED', 'CONTROL', 'TOUCH', 'BEST SELLERS'];

// 🟢 NEW: Array #4 (Orders Data - สำหรับ View Order History และ Status)
$ALL_ORDERS_HISTORY = [
    'admin' => [
        'ORD1001' => [
            'date' => '2025-10-15',
            'status' => 'delivered',
            'total' => 3600.00,
            'items' => [
                ['id' => 1, 'name' => 'IRONBOOTS DYNAMO', 'price' => 4000.00, 'size' => '42.5 EU', 'qty' => 1, 'discounted_price' => 3600.00]
            ]
        ],
        'ORD1002' => [
            'date' => '2025-10-18',
            'status' => 'in_transit', 
            'total' => 5500.00,
            'items' => [
                ['id' => 15, 'name' => 'IRONBOOTS VORTEX', 'price' => 4500.00, 'size' => '43.0 EU', 'qty' => 1, 'discounted_price' => 4500.00],
                ['id' => 11, 'name' => 'IRONBOOTS PULSAR', 'price' => 1000.00, 'size' => '41.0 EU', 'qty' => 1, 'discounted_price' => 1000.00]
            ]
        ]
    ],
    'user' => [
       'ORD1003' => [
            'date' => '2025-11-01',
            'status' => 'prepared',
            'total' => 7000.00,
            'items' => [
                ['id' => 4, 'name' => 'IRONBOOTS SHIELD', 'price' => 4500.00, 'size' => '42.0 EU', 'qty' => 1, 'discounted_price' => 4500.00],
                ['id' => 7, 'name' => 'IRONBOOTS IGNITE', 'price' => 2500.00, 'size' => '41.5 EU', 'qty' => 1, 'discounted_price' => 2500.00]
            ]
    ]
    ],
];

// กรอง order ของผู้ใช้ที่ login
$order_history = [];
if (isset($_SESSION['username'])) {
    $current_username = strtolower($_SESSION['username']);
    $order_history = $ALL_ORDERS_HISTORY[$current_username] ?? [];
}


// 1. โหลดข้อมูล Provinces และ Districts
$provinces_file_path = 'provinces.json';
$districts_file_path = 'districts.json';

if (file_exists($provinces_file_path) && file_exists($districts_file_path)) {
    $provinces_data = json_decode(file_get_contents($provinces_file_path), true) ?: [];
    $districts_data = json_decode(file_get_contents($districts_file_path), true) ?: [];
} else {
    $provinces_data = [];
    $districts_data = [];
}

// 2. สร้าง Province Code Map (Code -> Name)
$province_code_map = [];
foreach ($provinces_data as $province) {
    $province_code_map[$province['provinceCode']] = $province['provinceNameTh'];
}

// 3. สร้างโครงสร้างข้อมูลที่ใช้ใน Checkout (Province -> District -> Zipcode)
$FULL_ADDRESS_DATA = [];
foreach ($districts_data as $district) {
    $province_name = $province_code_map[$district['provinceCode']] ?? 'Unknown';
    
    if ($province_name !== 'Unknown') {
        if (!isset($FULL_ADDRESS_DATA[$province_name])) {
            $FULL_ADDRESS_DATA[$province_name] = [];
        }
        $FULL_ADDRESS_DATA[$province_name][$district['districtNameTh']] = $district['postalCode'];
    }
}





// ✅ Requirement: Array #6 (2-Dimensional Array) - ตารางไซส์
$size_chart = [
    ['US' => '7', 'UK' => '6', 'EU' => '40', 'JP' => '250'],
    ['US' => '7.5', 'UK' => '6.5', 'EU' => '40.5', 'JP' => '255'],
    ['US' => '8', 'UK' => '7', 'EU' => '41', 'JP' => '260'],
    ['US' => '8.5', 'UK' => '7.5', 'EU' => '41.5', 'JP' => '265'],
    ['US' => '9', 'UK' => '8', 'EU' => '42', 'JP' => '270'],
    ['US' => '9.5', 'UK' => '8.5', 'EU' => '42.5', 'JP' => '275'],
    ['US' => '10', 'UK' => '9', 'EU' => '43', 'JP' => '280'],
    ['US' => '10.5', 'UK' => '9.5', 'EU' => '43.5', 'JP' => '285'],
    ['US' => '11', 'UK' => '10', 'EU' => '44', 'JP' => '290'],
    ['US' => '11.5', 'UK' => '10.5', 'EU' => '44.5', 'JP' => '295'],
    ['US' => '12', 'UK' => '11', 'EU' => '45', 'JP' => '300']
];


// ✅ Requirement: Array #5 (2-Dimensional Array) - รีวิว
$reviews = [
    ['user' => 'สุดหล่อเด็กcamt', 'rating' => 5, 'comment' => 'บริการดีมาก รองเท้าสวยมากแต่เธอสวยกว่า'],
    ['user' => 'ต้นกล้านักบอลcamt', 'rating' => 5, 'comment' => '123 ปลาฉลามขึ้นบก 456 ขึ้นห้องได้เป่าา'],
    ['user' => 'Anonymous', 'rating' => 4, 'comment' => 'Good quality product, fast delivery.']
];
?>

