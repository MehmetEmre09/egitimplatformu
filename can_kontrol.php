<?php
function canlariYenile($db, $user_id) {
    $bekleme_suresi = 60; // Test için 10 saniye
    $max_can = 5;

    // 1. Önce veritabanına göre geçen saniyeyi ve mevcut canı çekiyoruz
    $sorgu = $db->prepare("SELECT can, TIMESTAMPDIFF(SECOND, son_can_yenilenme, NOW()) as saniye_fark FROM kullanicilar WHERE id = ?");
    $sorgu->execute([$user_id]);
    $user = $sorgu->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['can'] < $max_can) {
        $fark = $user['saniye_fark'];

        // Eğer geçen süre bekleme süresinden fazlaysa can ekle
        if ($fark >= $bekleme_suresi) {
            $artacak_can = floor($fark / $bekleme_suresi);
            $yeni_can = $user['can'] + $artacak_can;

            if ($yeni_can > $max_can) $yeni_can = $max_can;

            // Güncelleme yaparken saati de ileriye taşıyoruz
            $update = $db->prepare("UPDATE kullanicilar SET can = ?, son_can_yenilenme = NOW() WHERE id = ?");
            $update->execute([$yeni_can, $user_id]);
        }
    }
}
?>