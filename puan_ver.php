<?php
include 'baglan.php';
session_start();

// Güvenlik: Giriş yapmamış kullanıcıyı veya eksik veriyi engelle
if (!isset($_SESSION['user_id']) || !isset($_POST['islem']) || !isset($_POST['ders_id'])) {
    exit('Erişim engellendi veya eksik veri.');
}

$user_id = $_SESSION['user_id'];
$ders_id = $_POST['ders_id'];
$soru_id = isset($_POST['soru_id']) ? (int)$_POST['soru_id'] : 0;
$is_dogru = ($_POST['islem'] == 'dogru');

try {
    if ($is_dogru) {
        // 1. DERS BAZLI XP GÜNCELLEME (En kritik yer!)
        // Eğer bu kullanıcı bu derste ilk kez puan alıyorsa yeni satır ekler, varsa XP'yi artırır.
        $sql_ders_xp = "INSERT INTO kullanici_ders_ilerleme (user_id, ders_id, xp) 
                        VALUES (:uid, :did, 10) 
                        ON DUPLICATE KEY UPDATE xp = xp + 10";
        $stmt_ders = $db->prepare($sql_ders_xp);
        $stmt_ders->execute([
            'uid' => $user_id, 
            'did' => $ders_id
        ]);

        // 2. GENEL XP GÜNCELLEME (Liderlik tablosu için toplam puan)
        $sql_genel_xp = "UPDATE kullanicilar SET xp = xp + 10 WHERE id = :uid";
        $stmt_genel = $db->prepare($sql_genel_xp);
        $stmt_genel->execute(['uid' => $user_id]);

        // 3. CEVAPLANMIŞ SORU KAYDI (Soru tekrarını önlemek için)
        if ($soru_id > 0) {
            $sql_cevap = "INSERT IGNORE INTO cevaplanan_sorular (user_id, soru_tipi, soru_id) VALUES (:uid, 'sorular', :sid)";
            $stmt_cevap = $db->prepare($sql_cevap);
            $stmt_cevap->execute(['uid' => $user_id, 'sid' => $soru_id]);
        }

        echo "Puan başarıyla eklendi.";
    } else {
        // YANLIŞ CEVAP: Sadece ana tablodaki can miktarını bir azalt
        $sql_can_dus = "UPDATE kullanicilar SET can = can - 1 WHERE id = :uid AND can > 0";
        $stmt_can = $db->prepare($sql_can_dus);
        $stmt_can->execute(['uid' => $user_id]);

        echo "Yanlış cevap, can eksildi.";
    }
} catch (PDOException $e) {
    echo "Hata oluştu: " . $e->getMessage();
}
?>