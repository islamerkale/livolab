<?php
// process_payment.php - ÖDEME İŞLEMİNİ BAŞLAT
header('Content-Type: application/json');
require_once 'payment.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $package = $input['package'] ?? '';
    $price = $input['price'] ?? '';
    $customer_name = $input['customer_name'] ?? '';
    $customer_email = $input['customer_email'] ?? '';
    $customer_phone = $input['customer_phone'] ?? '';
    
    // Paket isimlerini belirle
    $package_names = [
        'gold' => 'GOLD Paket - Online Grup Pilates',
        'diamond' => 'DIAMOND Paket - Özel Pilates Dersleri', 
        'vip' => 'VIP Paket - Premium Pilates Deneyimi'
    ];
    
    $basket_items = [
        [
            'name' => $package_names[$package] ?? 'Pilates Paketi',
            'price' => $price / 100, // Gerçek fiyat
            'quantity' => 1
        ]
    ];
    
    // PayTR entegrasyonu
    $paytr = new PayTRIntegration();
    
    $order_data = [
        'merchant_oid' => 'LIVO_' . time() . '_' . rand(1000, 9999),
        'email' => $customer_email,
        'payment_amount' => $price, // PayTR 100 ile çarpılmış halini istiyor
        'user_name' => $customer_name,
        'user_phone' => $customer_phone,
        'user_address' => 'Online Pilates Dersi',
        'merchant_ok_url' => 'https://siteniz.com/success.php',
        'merchant_fail_url' => 'https://siteniz.com/fail.php',
        'no_installment' => 0,
        'max_installment' => 0,
        'currency' => 'TL',
        'basket' => $basket_items
    ];
    
    $result = $paytr->getToken($order_data);
    
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'error' => 'Geçersiz istek']);
}
?>