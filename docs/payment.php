<?php
// payment.php - PAYTR ENTEGRASYON SINIFI
class PayTRIntegration {
    private $merchant_id = "XXXXXX";           // PayTR'den alacağın
    private $merchant_key = "XXXXXXXX";        // PayTR'den alacağın  
    private $merchant_salt = "XXXXXXXX";       // PayTR'den alacağın
    private $test_mode = 1;                    // Test modu: 1 (Canlıya geçince 0 yap)

    public function getToken($order_data) {
        // Token oluştur
        $post_data = [
            'merchant_id' => $this->merchant_id,
            'user_ip' => $this->getUserIP(),
            'merchant_oid' => $order_data['merchant_oid'],
            'email' => $order_data['email'],
            'payment_amount' => $order_data['payment_amount'],
            'paytr_token' => $this->generateToken($order_data),
            'user_basket' => $this->prepareBasket($order_data['basket']),
            'debug_on' => 1,
            'no_installment' => $order_data['no_installment'] ?? 0,
            'max_installment' => $order_data['max_installment'] ?? 0,
            'user_name' => $order_data['user_name'] ?? '',
            'user_phone' => $order_data['user_phone'] ?? '',
            'user_address' => $order_data['user_address'] ?? 'Online Pilates Dersi',
            'merchant_ok_url' => $order_data['merchant_ok_url'] ?? 'https://siteniz.com/success.php',
            'merchant_fail_url' => $order_data['merchant_fail_url'] ?? 'https://siteniz.com/fail.php',
            'timeout_limit' => $order_data['timeout_limit'] ?? 30,
            'currency' => $order_data['currency'] ?? 'TL',
            'test_mode' => $this->test_mode,
            'lang' => $order_data['lang'] ?? 'tr'
        ];

        // PayTR'ye istek gönder
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            return ['success' => false, 'error' => curl_error($ch)];
        }
        
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($result['status'] == 'success') {
            return ['success' => true, 'token' => $result['token']];
        } else {
            return ['success' => false, 'error' => $result['reason']];
        }
    }

    private function generateToken($order_data) {
        $hash_str = $this->merchant_id . 
                   $this->getUserIP() . 
                   $order_data['merchant_oid'] . 
                   $order_data['email'] . 
                   $order_data['payment_amount'] . 
                   $this->prepareBasket($order_data['basket']) . 
                   $order_data['no_installment'] . 
                   $order_data['max_installment'] . 
                   $order_data['currency'] . 
                   $this->test_mode . 
                   $this->merchant_salt;

        return base64_encode(hash_hmac('sha256', $hash_str, $this->merchant_key, true));
    }

    private function prepareBasket($basket_items) {
        $basket = [];
        foreach ($basket_items as $item) {
            $basket[] = [$item['name'], $item['price'], $item['quantity']];
        }
        return base64_encode(json_encode($basket));
    }

    private function getUserIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}
?>