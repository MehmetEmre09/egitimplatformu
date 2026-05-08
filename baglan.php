<?php
try {
    $db = new PDO("mysql:host=localhost;dbname=egitim_platformu;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Cevaplanan sorular tablosunu otomatik oluştur (yoksa)
    $db->exec("CREATE TABLE IF NOT EXISTS cevaplanan_sorular (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        soru_tipi ENUM('sorular', 'bosluklular') NOT NULL,
        soru_id INT NOT NULL,
        cevaplama_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_cevap (user_id, soru_tipi, soru_id)
    )");
} catch (PDOException $e) {
    die("Bağlantı hatası: " . $e->getMessage());
}
?>