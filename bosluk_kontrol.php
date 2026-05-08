<?php
include 'baglan.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

// Güvenlik kontrolleri
if (!isset($_SESSION['user_id']) || !isset($_POST['soru_id']) || !isset($_POST['cevap']) || !isset($_POST['ders_id'])) {
    echo json_encode(['durum' => 'hata', 'mesaj' => 'Eksik veri veya oturum yok.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$soru_id = (int)$_POST['soru_id'];
$kullanici_cevabi = trim($_POST['cevap']);
$ders_id = (int)$_POST['ders_id'];

// Can kontrolü
$can_sorgu = $db->prepare("SELECT can FROM kullanicilar WHERE id = ?");
$can_sorgu->execute([$user_id]);
$user_data = $can_sorgu->fetch(PDO::FETCH_ASSOC);

if (!$user_data || $user_data['can'] <= 0) {
    echo json_encode(['durum' => 'hata', 'mesaj' => 'Canın kalmadı!']);
    exit();
}

// Doğru cevabı veritabanından çek
$soru_sorgu = $db->prepare("SELECT dogru_cevap FROM bosluklular WHERE id = ?");
$soru_sorgu->execute([$soru_id]);
$soru = $soru_sorgu->fetch(PDO::FETCH_ASSOC);

if (!$soru) {
    echo json_encode(['durum' => 'hata', 'mesaj' => 'Soru bulunamadı.']);
    exit();
}

$dogru_cevap = trim($soru['dogru_cevap']);

// Cevap karşılaştırma (büyük/küçük harf duyarsız, boşlukları temizle)
$dogru_mu = (mb_strtolower($kullanici_cevabi, 'UTF-8') === mb_strtolower($dogru_cevap, 'UTF-8'));

try {
    if ($dogru_mu) {
        // DOĞRU CEVAP: XP ekle
        // 1. Ders bazlı XP
        $sql_ders_xp = "INSERT INTO kullanici_ders_ilerleme (user_id, ders_id, xp) 
                        VALUES (:uid, :did, 10) 
                        ON DUPLICATE KEY UPDATE xp = xp + 10";
        $stmt_ders = $db->prepare($sql_ders_xp);
        $stmt_ders->execute(['uid' => $user_id, 'did' => $ders_id]);

        // 2. Genel XP
        $sql_genel_xp = "UPDATE kullanicilar SET xp = xp + 10 WHERE id = :uid";
        $stmt_genel = $db->prepare($sql_genel_xp);
        $stmt_genel->execute(['uid' => $user_id]);

        // 3. CEVAPLANMIŞ SORU KAYDI (Soru tekrarını önlemek için)
        $sql_cevap = "INSERT IGNORE INTO cevaplanan_sorular (user_id, soru_tipi, soru_id) VALUES (:uid, 'bosluklular', :sid)";
        $stmt_cevap = $db->prepare($sql_cevap);
        $stmt_cevap->execute(['uid' => $user_id, 'sid' => $soru_id]);

        echo json_encode([
            'durum' => 'dogru',
            'mesaj' => 'Harika! Doğru cevap! +10 XP kazandın!',
            'dogru_cevap' => $dogru_cevap
        ]);
    } else {
        // YANLIŞ CEVAP: Can düşür
        $sql_can_dus = "UPDATE kullanicilar SET can = can - 1 WHERE id = :uid AND can > 0";
        $stmt_can = $db->prepare($sql_can_dus);
        $stmt_can->execute(['uid' => $user_id]);

        echo json_encode([
            'durum' => 'yanlis',
            'mesaj' => 'Yanlış cevap! Doğru cevap: ' . $dogru_cevap,
            'dogru_cevap' => $dogru_cevap
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(['durum' => 'hata', 'mesaj' => 'Sunucu hatası: ' . $e->getMessage()]);
}
?>
