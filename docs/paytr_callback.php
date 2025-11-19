<?php
// paytr_callback.php - ÖDEME SONUÇLARI (SADECE EMAIL GÖNDER)
class PayTRCallback {
    private $merchant_key = "XXXXXXXX";        // PayTR'den alacağın
    private $merchant_salt = "XXXXXXXX";       // PayTR'den alacağın

    public function handleCallback() {
        $post = $_POST;
        
        // Basit kontrol
        if (!isset($post['merchant_oid']) || !isset($post['status'])) {
            die("Eksik bilgi!");
        }

        // Hash doğrulama (güvenlik için)
        $hash = base64_encode(hash_hmac('sha256', 
            $post['merchant_oid'] . $this->merchant_salt . $post['status'] . $post['total_amount'], 
            $this->merchant_key, true));

        if ($hash != $post['hash']) {
            die("Güvenlik hatası!");
        }

        // Ödeme başarılı ise EMAIL GÖNDER
        if ($post['status'] == 'success') {
            $this->sendSuccessEmail($post);
        }

        // PayTR'ye her zaman OK de
        echo "OK";
    }

    private function sendSuccessEmail($data) {
        $order_id = $data['merchant_oid'];
        $amount = $data['total_amount'] / 100;
        
        $to = "livolab25@gmail.com"; // KENDİ EMAIL'İNE
        $subject = "🎉 YENİ ÖDEME - $order_id";
        $message = "
            YENİ ÖDEME ALINDI!
            
            🤑 TEBRİKLER! Bir ödeme aldın.
            
            📦 Sipariş No: $order_id
            💰 Tutar: $amount TL
            📅 Tarih: " . date('d.m.Y H:i:s') . "
            
            🚀 Hemen müşterin ile iletişime geç!
            
            Ödeme detayları:
            - Durum: Başarılı ✅
            - Ödeme Tipi: " . ($data['payment_type'] ?? 'Kart') . "
            - Para Birimi: " . ($data['currency'] ?? 'TL') . "
        ";
        
        // Email gönder
        mail($to, $subject, $message);
        
        // Log (opsiyonel)
        error_log("ÖDEME ALINDI: $order_id - $amount TL");
    }
}

$callback = new PayTRCallback();
$callback->handleCallback();
?>