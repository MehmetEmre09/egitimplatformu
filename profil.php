<?php 
include 'baglan.php'; 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: giris.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. KULLANICI GENEL BİLGİLERİNİ ÇEK
$user_query = $db->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$user_query->execute([$user_id]);
$user = $user_query->fetch(PDO::FETCH_ASSOC);

// 2. LİDERLİK SIRALAMASINI BUL
$siralama_sorgu = $db->query("SELECT id FROM kullanicilar ORDER BY xp DESC");
$siralama = 0;
$sayac = 1;
while($row = $siralama_sorgu->fetch()) {
    if($row['id'] == $user_id) { $siralama = $sayac; break; }
    $sayac++;
}

// 3. EN YÜKSEK SEVİYELİ DERSİ BUL
$en_iyi_ders_query = $db->prepare("SELECT d.ders_adi, i.xp FROM kullanici_ders_ilerleme i JOIN dersler d ON i.ders_id = d.id WHERE i.user_id = ? ORDER BY i.xp DESC LIMIT 1");
$en_iyi_ders_query->execute([$user_id]);
$en_iyi_ders = $en_iyi_ders_query->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim | Yazılım Akademisi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .profile-header { background: linear-gradient(135deg, #1cb0f6 0%, #58cc02 100%); color: white; padding: 60px 0; border-radius: 0 0 50px 50px; margin-bottom: -50px; }
        .profile-card { background: white; border-radius: 25px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .stat-box { border-right: 1px solid #eee; text-align: center; }
        .stat-box:last-child { border-right: none; }
        .stat-value { font-size: 1.5rem; font-weight: bold; display: block; }
        .stat-label { font-size: 0.8rem; color: #888; text-transform: uppercase; }
        .badge-item { background: #f8f9fa; border-radius: 15px; padding: 15px; margin-bottom: 10px; border: 1px solid #eee; transition: 0.3s; }
        .badge-item:hover { background: #fff; transform: scale(1.02); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .back-btn { position: absolute; top: 20px; left: 20px; color: white; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="profile-header text-center position-relative">
    <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Panele Dön</a>
    <div class="container">
        <div class="mb-3">
            <i class="fas fa-user-circle fa-5x"></i>
        </div>
        <h2 class="fw-bold"><?php echo htmlspecialchars($user['kullanici_adi']); ?></h2>
        <p class="opacity-75">Geleceğin Yazılım Uzmanı</p>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card profile-card p-4 mb-4">
                <div class="row">
                    <div class="col-4 stat-box">
                        <span class="stat-value text-primary"><?php echo $user['xp']; ?></span>
                        <span class="stat-label">Toplam XP</span>
                    </div>
                    <div class="col-4 stat-box">
                        <span class="stat-value text-warning">#<?php echo $siralama; ?></span>
                        <span class="stat-label">Sıralama</span>
                    </div>
                    <div class="col-4 stat-box">
                        <span class="stat-value text-danger"><?php echo $user['can']; ?>/5</span>
                        <span class="stat-label">Mevcut Can</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h5 class="fw-bold mb-3"><i class="fas fa-medal text-warning"></i> Rozetlerin ve Uzmanlıklar</h5>
                    <?php
                    $diller_sorgu = $db->query("SELECT * FROM dersler");
                    while($d = $diller_sorgu->fetch(PDO::FETCH_ASSOC)) {
                        $id = $d['id'];
                        $prog_sorgu = $db->prepare("SELECT xp FROM kullanici_ders_ilerleme WHERE user_id = ? AND ders_id = ?");
                        $prog_sorgu->execute([$user_id, $id]);
                        $pxp = ($res = $prog_sorgu->fetch()) ? $res['xp'] : 0;
                        $plvl = floor($pxp / 100) + 1;
                        ?>
                        <div class="badge-item d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <i class="fas fa-award fa-2x <?php echo $plvl >= 5 ? 'text-warning' : 'text-light'; ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-bold"><?php echo $d['ders_adi']; ?></h6>
                                <small class="text-muted">Seviye <?php echo $plvl; ?> / 7</small>
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar bg-info" style="width: <?php echo ($plvl/7)*100; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="col-md-6">
                    <h5 class="fw-bold mb-3"><i class="fas fa-info-circle text-primary"></i> Gelişim Özeti</h5>
                    <div class="card border-0 shadow-sm p-4 rounded-4" style="background: #eef9ff;">
                        <p class="mb-2"><b>Favori Alanın:</b> <?php echo $en_iyi_ders ? $en_iyi_ders['ders_adi'] : 'Henüz Başlamadı'; ?></p>
                        <p class="mb-2"><b>Akademi Durumu:</b> 
                            <?php 
                            if($user['xp'] < 500) echo "Çaylak Yazılımcı";
                            else if($user['xp'] < 1500) echo "Gelişmiş Geliştirici";
                            else echo "Kıdemli Kod Üstadı";
                            ?>
                        </p>
                        <hr>
                        <small class="text-muted d-block mt-3 italic">
                            "Yazılım öğrenmek bir maratondur. Bugün kazandığın her XP, yarınki kariyerinin temelidir."
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>