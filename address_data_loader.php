<?php
// address_data_loader.php

// 1. โหลดข้อมูล Provinces และ Districts
$provinces_file_path = 'provinces.json';
$districts_file_path = 'districts.json';

// ตรวจสอบว่าไฟล์ถูกส่งมาหรือไม่
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
        
        // บันทึก District Name (Amphoe/Khet) และ Postal Code
        $FULL_ADDRESS_DATA[$province_name][$district['districtNameTh']] = $district['postalCode'];
    }
}
?>