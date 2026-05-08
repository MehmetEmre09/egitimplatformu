<?php 
include 'baglan.php'; 
include 'can_kontrol.php'; 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: giris.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. CANLARI YENİLE VE VERİYİ ÇEK
canlariYenile($db, $user_id); 
$user_query = $db->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$user_query->execute([$user_id]);
$user = $user_query->fetch(PDO::FETCH_ASSOC);

// 2. GÜNÜN İPUCU SİSTEMİ
$ipuclari = [
    "Clean Code için anlamlı değişken isimleri kullanın.",
    "PHP'de PDO kullanmak SQL Injection'ı %100'e yakın önler.",
    "JavaScript'te '===' kullanmak tip kontrolü de sağlar.",
    "HTML'de 'alt' etiketi SEO ve erişilebilirlik için zorunludur.",
    "CSS Grid, karmaşık layoutları yönetmenin en modern yoludur.",
    "Hata ayıklarken console.log() yerine debugger kullanmayı deneyin."
];
$rastgele_ipucu = $ipuclari[array_rand($ipuclari)];

// 3. CAN GERİ SAYIM MANTIĞI
$bekleme_suresi = 60; 
$gecen_sure_sorgu = $db->prepare("SELECT TIMESTAMPDIFF(SECOND, son_can_yenilenme, NOW()) as fark FROM kullanicilar WHERE id = ?");
$gecen_sure_sorgu->execute([$user_id]);
$fark_data = $gecen_sure_sorgu->fetch(PDO::FETCH_ASSOC);
$kalan_saniye = $bekleme_suresi - ($fark_data['fark'] % $bekleme_suresi);
if ($user['can'] >= 5) { $kalan_saniye = 0; }
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akademi Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --soft-green: #58cc02; --dark-green: #46a302; --soft-blue: #1cb0f6; }
        body { background-color: #f7f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: var(--soft-green); border-bottom: 4px solid var(--dark-green); }
        
        /* Profil Linki Tasarımı */
        .profile-link {
            transition: 0.3s;
            padding: 5px 12px;
            border-radius: 50px;
            text-decoration: none !important;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .profile-link:hover { background: rgba(255,255,255,0.15); }
        .profile-icon {
            width: 32px;
            height: 32px;
            background: white;
            color: var(--soft-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-section { background: white; border-radius: 20px; padding: 25px; border: 2px solid #e5e5e5; margin-bottom: 30px; }
        .lesson-card { border: 2px solid #e5e5e5; border-radius: 20px; transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); background: white; position: relative; }
        .lesson-card:hover:not(.disabled-card) { border-color: var(--soft-blue); transform: translateY(-7px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .disabled-card { opacity: 0.6; cursor: not-allowed; background: #eee; }
        .badge-usta { background: #ffc107; color: #000; font-size: 0.7rem; padding: 4px 8px; border-radius: 10px; position: absolute; top: 10px; left: 10px; font-weight: bold; }
        .lvl-tag { background: var(--soft-blue); color: white; padding: 3px 12px; border-radius: 50px; font-weight: bold; font-size: 0.85rem; }
        .progress { height: 8px; border-radius: 10px; background: #e5e5e5; }
        .tip-box { background: #e3f2fd; border-left: 5px solid var(--soft-blue); color: #0d47a1; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark shadow-sm py-3 mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand fw-bold fs-3" href="index.php"><i class="fas fa-code-branch"></i> DEV-AKADEMİ</a>
        <div class="d-flex align-items-center gap-3">
            
            <a href="profil.php" class="profile-link text-white">
                <div class="profile-icon shadow-sm">
                    <i class="fas fa-user"></i>
                </div>
                <span class="fw-bold d-none d-sm-inline"><?php echo htmlspecialchars($user['kullanici_adi']); ?></span>
            </a>

            <div class="text-white fw-bold"><i class="fas fa-heart text-danger"></i> <span id="can-sayac"><?php echo $user['can']; ?></span></div>
            <div class="text-white fw-bold"><i class="fas fa-bolt text-warning"></i> <?php echo $user['xp']; ?> XP</div>
            <a href="cikis.php" class="btn btn-sm btn-outline-light rounded-pill"><i class="fas fa-power-off"></i></a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="hero-section shadow-sm">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h2 class="fw-bold">Selam, <?php echo htmlspecialchars($user['kullanici_adi']); ?>! 👋</h2>
                <p class="text-muted">Bugün yeni bir şeyler öğrenmek için harika bir gün. Kaldığın yerden devam et!</p>
                <div class="tip-box p-3 rounded-3 mb-2">
                    <small class="fw-bold text-uppercase d-block mb-1"><i class="fas fa-lightbulb"></i> Günün Teknik Bilgisi</small>
                    <span><?php echo $rastgele_ipucu; ?></span>
                </div>
            </div>
            <div class="col-md-5 text-end d-none d-md-block">
                <?php if ($user['can'] < 5): ?>
                    <div class="alert alert-warning py-2 rounded-pill d-inline-block">
                        <small><i class="fas fa-clock"></i> Yeni cana kalan: <b id="timer">--:--</b></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <h4 class="fw-bold mb-4"><i class="fas fa-graduation-cap text-success"></i> Eğitim Programın</h4>
            <div class="row">
                <?php
                $sorgu = $db->query("SELECT * FROM dersler");
                while($ders = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                    $d_id = $ders['id'];
                    $ilerleme_sorgu = $db->prepare("SELECT xp FROM kullanici_ders_ilerleme WHERE user_id = ? AND ders_id = ?");
                    $ilerleme_sorgu->execute([$user_id, $d_id]);
                    $d_xp = ($res = $ilerleme_sorgu->fetch()) ? $res['xp'] : 0;
                    
                    $d_lvl = floor($d_xp / 100) + 1;
                    $d_yuzde = ($d_xp % 100);
                    $canYeterliMi = ($user['can'] > 0);

                    // İkon Seçimi
                    $icon = "fa-code";
                    if(strpos($ders['ders_adi'], 'HTML') !== false) $icon = "fa-html5 text-danger";
                    if(strpos($ders['ders_adi'], 'JS') !== false) $icon = "fa-js text-warning";
                    if(strpos($ders['ders_adi'], 'PHP') !== false) $icon = "fa-php text-primary";
                    if(strpos($ders['ders_adi'], 'Java') !== false) $icon = "fa-java text-info";
                    ?>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card lesson-card p-4 h-100 <?php echo !$canYeterliMi ? 'disabled-card':''; ?>">
                            <?php if($d_lvl >= 5): ?>
                                <div class="badge-usta"><i class="fas fa-award"></i> USTA</div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <i class="fab <?php echo $icon; ?> fa-3x"></i>
                                <span class="lvl-tag shadow-sm">Level <?php echo $d_lvl; ?></span>
                            </div>
                            
                            <h4 class="fw-bold"><?php echo $ders['ders_adi']; ?></h4>
                            
                            <div class="mt-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">Gelişim</span>
                                    <span class="fw-bold">%<?php echo $d_yuzde; ?></span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: <?php echo $d_yuzde; ?>%"></div>
                                </div>
                            </div>

                            <?php if($canYeterliMi): ?>
                                <div class="d-flex gap-2 mt-4 flex-wrap">
                                    <a href="ders_detay.php?id=<?php echo $d_id; ?>" class="btn btn-outline-dark rounded-pill fw-bold"><i class="fas fa-play me-1"></i>Soru Çöz</a>
                                    <a href="bosluk_doldur.php?id=<?php echo $d_id; ?>" class="btn btn-outline-primary rounded-pill fw-bold"><i class="fas fa-puzzle-piece me-1"></i>Boşluk Doldur</a>
                                </div>
                            <?php else: ?>
                                <button class="btn btn-secondary rounded-pill fw-bold mt-4" disabled>Can Bekleniyor...</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h5 class="fw-bold mb-3 p-2 border-bottom"><i class="fas fa-fire text-danger"></i> Liderlik Tablosu</h5>
                <div class="list-group list-group-flush">
                    <?php
                    $liderler = $db->query("SELECT kullanici_adi, xp FROM kullanicilar ORDER BY xp DESC LIMIT 5");
                    $sira = 1;
                    while($l = $liderler->fetch(PDO::FETCH_ASSOC)) {
                        echo "<div class='list-group-item d-flex justify-content-between align-items-center border-0 px-1'>
                                <div>
                                    <span class='badge bg-light text-dark me-2'>$sira</span>
                                    <span class='fw-semibold'>{$l['kullanici_adi']}</span>
                                </div>
                                <span class='badge bg-primary rounded-pill'>{$l['xp']} XP</span>
                              </div>";
                        $sira++;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Can Geri Sayım Scripti
    let saniye = <?php echo $kalan_saniye; ?>;
    if (saniye > 0) {
        let timer = document.getElementById('timer');
        let interval = setInterval(() => {
            let dk = Math.floor(saniye / 60);
            let sn = saniye % 60;
            timer.innerText = (dk < 10 ? "0"+dk : dk) + ":" + (sn < 10 ? "0"+sn : sn);
            if (--saniye < 0) {
                clearInterval(interval);
                location.reload();
            }
        }, 1000);
    }
</script>
</body>
</html>