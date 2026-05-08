<?php 
include 'baglan.php'; 
session_start();

if (!isset($_SESSION['user_id'])) { header("Location: giris.php"); exit(); }

$user_id = $_SESSION['user_id'];
$ders_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// --- 1. GENEL KONTROLLER ---
$user_check = $db->prepare("SELECT can FROM kullanicilar WHERE id = ?");
$user_check->execute([$user_id]);
$current_user = $user_check->fetch(PDO::FETCH_ASSOC);

if (!$current_user || $current_user['can'] <= 0) {
    header("Location: index.php?hata=cansiz");
    exit();
}

// --- 2. BU DERSE ÖZEL SEVİYE HESAPLAMA ---
$ilerleme_sorgu = $db->prepare("SELECT xp FROM kullanici_ders_ilerleme WHERE user_id = ? AND ders_id = ?");
$ilerleme_sorgu->execute([$user_id, $ders_id]);
$ders_xp_data = $ilerleme_sorgu->fetch(PDO::FETCH_ASSOC);

$ders_xp = $ders_xp_data ? $ders_xp_data['xp'] : 0;
// Her 100 XP'de bir seviye (0-99: Seviye 1, 100-199: Seviye 2, 200+: Seviye 3)
$ders_level = floor($ders_xp / 100) + 1; 

// --- 3. DAHA ÖNCE DOĞRU CEVAPLANMIŞ SORULARI BUL ---
$cevaplanan_sorgu = $db->prepare("SELECT soru_id FROM cevaplanan_sorular WHERE user_id = ? AND soru_tipi = 'sorular'");
$cevaplanan_sorgu->execute([$user_id]);
$cevaplanan_idler = $cevaplanan_sorgu->fetchAll(PDO::FETCH_COLUMN);

// --- 4. SEVİYEYE UYGUN, CEVAPLANMAMIŞ RASTGELE SORU ÇEK ---
if (!empty($cevaplanan_idler)) {
    $placeholders = implode(',', array_fill(0, count($cevaplanan_idler), '?'));
    $params = array_merge([$ders_id, $ders_level], $cevaplanan_idler);
    $soru_sorgu = $db->prepare("SELECT * FROM sorular WHERE ders_id = ? AND soru_seviye = ? AND id NOT IN ($placeholders) ORDER BY RAND() LIMIT 1");
    $soru_sorgu->execute($params);
} else {
    $soru_sorgu = $db->prepare("SELECT * FROM sorular WHERE ders_id = ? AND soru_seviye = ? ORDER BY RAND() LIMIT 1");
    $soru_sorgu->execute([$ders_id, $ders_level]);
}
$soru = $soru_sorgu->fetch(PDO::FETCH_ASSOC);

// Eğer o seviyede cevaplanmamış soru kalmadıysa, herhangi bir seviyeden cevaplanmamış soru getir
if (!$soru) {
    if (!empty($cevaplanan_idler)) {
        $placeholders = implode(',', array_fill(0, count($cevaplanan_idler), '?'));
        $params = array_merge([$ders_id], $cevaplanan_idler);
        $soru_sorgu = $db->prepare("SELECT * FROM sorular WHERE ders_id = ? AND id NOT IN ($placeholders) ORDER BY RAND() LIMIT 1");
        $soru_sorgu->execute($params);
    } else {
        $soru_sorgu = $db->prepare("SELECT * FROM sorular WHERE ders_id = ? ORDER BY RAND() LIMIT 1");
        $soru_sorgu->execute([$ders_id]);
    }
    $soru = $soru_sorgu->fetch(PDO::FETCH_ASSOC);
}

// Tüm sorular tamamlandıysa tebrik sayfasına yönlendir
if (!$soru) {
    echo '<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><title>Tebrikler!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body{background:#f1f1f1;font-family:"Segoe UI",sans-serif;height:100vh;display:flex;align-items:center;justify-content:center;}</style>
    </head><body><div class="text-center p-5 bg-white rounded-4 shadow" style="max-width:500px;">
    <div style="font-size:4rem;">🎉</div>
    <h2 class="fw-bold mt-3">Tebrikler!</h2>
    <p class="text-muted">Bu dersteki tüm çoktan seçmeli soruları tamamladın!</p>
    <a href="index.php" class="btn btn-success rounded-pill px-4 mt-3"><i class="fas fa-home me-2"></i>Panele Dön</a>
    </div></body></html>';
    exit();
}

// Toplam soru sayısı ve cevaplanmış sayısı (Bilgi amaçlı)
$toplam_soru_sorgu = $db->prepare("SELECT COUNT(*) as toplam FROM sorular WHERE ders_id = ? AND soru_seviye = ?");
$toplam_soru_sorgu->execute([$ders_id, $ders_level]);
$toplam_soru_sayisi = $toplam_soru_sorgu->fetch(PDO::FETCH_ASSOC)['toplam'];

$cevaplanan_sayisi_sorgu = $db->prepare("SELECT COUNT(*) as toplam FROM cevaplanan_sorular cs INNER JOIN sorular s ON cs.soru_id = s.id WHERE cs.user_id = ? AND cs.soru_tipi = 'sorular' AND s.ders_id = ? AND s.soru_seviye = ?");
$cevaplanan_sayisi_sorgu->execute([$user_id, $ders_id, $ders_level]);
$cevaplanan_sayisi = $cevaplanan_sayisi_sorgu->fetch(PDO::FETCH_ASSOC)['toplam'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Soru Çöz | Seviye <?php echo $ders_level; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f1f1f1; font-family: 'Segoe UI', sans-serif; height: 100vh; display: flex; align-items: center; }
        .question-card { background: white; border-radius: 25px; border-bottom: 5px solid #e5e5e5; padding: 30px; width: 100%; position: relative; }
        .option-btn { border: 2px solid #e5e5e5; border-radius: 15px; padding: 15px; margin-bottom: 12px; cursor: pointer; transition: 0.2s; font-weight: bold; color: #4b4b4b; }
        .option-btn:hover { background: #f7f7f7; border-color: #1cb0f6; }
        .correct { background: #d7ffb8 !important; border-color: #58cc02 !important; color: #2d6600 !important; }
        .wrong { background: #ffdfe0 !important; border-color: #ea2b2b !important; color: #a30000 !important; }
        .disabled { pointer-events: none; }
        .lvl-badge { background: #1cb0f6; color: white; padding: 5px 15px; border-radius: 50px; font-weight: bold; font-size: 0.9rem; }
        .can-badge { background: #ffdfe0; color: #ea2b2b; padding: 5px 15px; border-radius: 50px; font-weight: bold; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="question-card shadow-sm text-center">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex gap-2">
                        <span class="lvl-badge"><i class="fas fa-layer-group me-1"></i> SEVİYE <?php echo $ders_level; ?></span>
                        <span class="can-badge"><i class="fas fa-heart me-1"></i> <?php echo $current_user['can']; ?></span>
                    </div>
                    <a href="index.php" class="btn-close shadow-none"></a>
                </div>

                <h3 class="fw-bold mb-5 px-3" style="color: #4b4b4b; line-height: 1.4;">
                    <?php echo htmlspecialchars($soru['soru_metni']); ?>
                </h3>
                
                <div class="options text-start">
                    <?php 
                    $secenekler = ['A' => 'secenek_a', 'B' => 'secenek_b', 'C' => 'secenek_c', 'D' => 'secenek_d'];
                    foreach($secenekler as $key => $col): 
                    ?>
                        <div class="option-btn" data-answer="<?php echo $key; ?>" onclick="cevapKontrol('<?php echo $key; ?>', '<?php echo $soru['dogru_cevap']; ?>', this)">
                            <span class="me-2"><?php echo $key; ?>)</span> <?php echo htmlspecialchars($soru[$col]); ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4 small text-muted">
                    Bu seviyede <b><?php echo $cevaplanan_sayisi; ?></b> / <b><?php echo $toplam_soru_sayisi; ?></b> soru tamamlandı.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const dogruSes = new Audio('dogru.mp3');
const yanlisSes = new Audio('yanlis.mp3');

function cevapKontrol(secilen, dogru, element) {
    const buttons = document.querySelectorAll('.option-btn');
    buttons.forEach(btn => btn.classList.add('disabled'));

    const dersId = <?php echo $ders_id; ?>;
    const soruId = <?php echo $soru['id']; ?>;

    if (secilen === dogru) {
        element.classList.add('correct');
        dogruSes.play();
        
        fetch('puan_ver.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `islem=dogru&ders_id=${dersId}&soru_id=${soruId}`
        }).then(() => {
            setTimeout(() => { window.location.reload(); }, 1200);
        });

    } else {
        element.classList.add('wrong');
        yanlisSes.play();

        // Yanlış yapınca doğru cevabı yeşil yap
        buttons.forEach(btn => {
            if (btn.getAttribute('data-answer') === dogru) {
                btn.classList.add('correct');
            }
        });
        
        fetch('puan_ver.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `islem=yanlis&ders_id=${dersId}&soru_id=${soruId}`
        }).then(() => {
            // Yanlışı görüp öğrenmesi için 2 saniye bekle
            setTimeout(() => { window.location.reload(); }, 2000);
        });
    }
}
</script>

</body>
</html>