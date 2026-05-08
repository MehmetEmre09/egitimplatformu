<?php 
include 'baglan.php'; 
session_start();

if (!isset($_SESSION['user_id'])) { header("Location: giris.php"); exit(); }

$user_id = $_SESSION['user_id'];
$ders_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Can kontrolü
$user_check = $db->prepare("SELECT can FROM kullanicilar WHERE id = ?");
$user_check->execute([$user_id]);
$current_user = $user_check->fetch(PDO::FETCH_ASSOC);

if (!$current_user || $current_user['can'] <= 0) {
    header("Location: index.php?hata=cansiz");
    exit();
}

// Ders bilgisi
$ders_sorgu = $db->prepare("SELECT ders_adi FROM dersler WHERE id = ?");
$ders_sorgu->execute([$ders_id]);
$ders = $ders_sorgu->fetch(PDO::FETCH_ASSOC);
$ders_adi = $ders ? $ders['ders_adi'] : 'Bilinmeyen Ders';

// Seviye hesaplama
$ilerleme_sorgu = $db->prepare("SELECT xp FROM kullanici_ders_ilerleme WHERE user_id = ? AND ders_id = ?");
$ilerleme_sorgu->execute([$user_id, $ders_id]);
$ders_xp_data = $ilerleme_sorgu->fetch(PDO::FETCH_ASSOC);
$ders_xp = $ders_xp_data ? $ders_xp_data['xp'] : 0;
$ders_level = floor($ders_xp / 100) + 1;

// Daha önce doğru cevaplanmış boşluk sorularını bul
$cevaplanan_sorgu = $db->prepare("SELECT soru_id FROM cevaplanan_sorular WHERE user_id = ? AND soru_tipi = 'bosluklular'");
$cevaplanan_sorgu->execute([$user_id]);
$cevaplanan_idler = $cevaplanan_sorgu->fetchAll(PDO::FETCH_COLUMN);

// Seviyeye uygun, cevaplanmamış soru çek
if (!empty($cevaplanan_idler)) {
    $placeholders = implode(',', array_fill(0, count($cevaplanan_idler), '?'));
    $params = array_merge([$ders_id, $ders_level], $cevaplanan_idler);
    $soru_sorgu = $db->prepare("SELECT * FROM bosluklular WHERE ders_id = ? AND soru_seviye = ? AND id NOT IN ($placeholders) ORDER BY RAND() LIMIT 1");
    $soru_sorgu->execute($params);
} else {
    $soru_sorgu = $db->prepare("SELECT * FROM bosluklular WHERE ders_id = ? AND soru_seviye = ? ORDER BY RAND() LIMIT 1");
    $soru_sorgu->execute([$ders_id, $ders_level]);
}
$soru = $soru_sorgu->fetch(PDO::FETCH_ASSOC);

// O seviyede cevaplanmamış soru kalmadıysa, herhangi bir seviyeden cevaplanmamış soru getir
if (!$soru) {
    if (!empty($cevaplanan_idler)) {
        $placeholders = implode(',', array_fill(0, count($cevaplanan_idler), '?'));
        $params = array_merge([$ders_id], $cevaplanan_idler);
        $soru_sorgu = $db->prepare("SELECT * FROM bosluklular WHERE ders_id = ? AND id NOT IN ($placeholders) ORDER BY RAND() LIMIT 1");
        $soru_sorgu->execute($params);
    } else {
        $soru_sorgu = $db->prepare("SELECT * FROM bosluklular WHERE ders_id = ? ORDER BY RAND() LIMIT 1");
        $soru_sorgu->execute([$ders_id]);
    }
    $soru = $soru_sorgu->fetch(PDO::FETCH_ASSOC);
}

// Tüm sorular tamamlandıysa tebrik sayfasını göster
if (!$soru) {
    echo '<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><title>Tebrikler!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>body{background:#0e1117;font-family:"Inter",sans-serif;height:100vh;display:flex;align-items:center;justify-content:center;}</style>
    </head><body><div class="text-center p-5 rounded-4 shadow" style="max-width:500px;background:#161b22;border:1px solid #30363d;">
    <div style="font-size:4rem;">🎉</div>
    <h2 class="fw-bold mt-3" style="color:#58cc02;">Tebrikler!</h2>
    <p style="color:#8b949e;">Bu dersteki tüm boşluk doldurma sorularını tamamladın!</p>
    <a href="index.php" class="btn rounded-pill px-4 mt-3" style="background:linear-gradient(135deg,#58cc02,#46a302);color:white;font-weight:700;"><i class="fas fa-home me-2"></i>Panele Dön</a>
    </div></body></html>';
    exit();
}

// Kod metnini parçala
$kod_parcalari = explode('___BOSLUK___', $soru['kod_metni']);
$toplam_sorgu = $db->prepare("SELECT COUNT(*) as toplam FROM bosluklular WHERE ders_id = ?");
$toplam_sorgu->execute([$ders_id]);
$toplam = $toplam_sorgu->fetch(PDO::FETCH_ASSOC)['toplam'];

$cevaplanan_sayisi_sorgu = $db->prepare("SELECT COUNT(*) as toplam FROM cevaplanan_sorular cs INNER JOIN bosluklular b ON cs.soru_id = b.id WHERE cs.user_id = ? AND cs.soru_tipi = 'bosluklular' AND b.ders_id = ?");
$cevaplanan_sayisi_sorgu->execute([$user_id, $ders_id]);
$cevaplanan_sayisi = $cevaplanan_sayisi_sorgu->fetch(PDO::FETCH_ASSOC)['toplam'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Boşluk Doldur | <?php echo htmlspecialchars($ders_adi); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;600&family=Inter:wght@400;600;800&display=swap');
        
        * { box-sizing: border-box; }
        body { 
            background: #0e1117; 
            font-family: 'Inter', sans-serif; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            overflow: hidden;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(88, 204, 2, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(28, 176, 246, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(139, 92, 246, 0.05) 0%, transparent 50%);
            z-index: 0;
        }

        .game-container {
            position: relative; z-index: 1;
            width: 100%; max-width: 750px; padding: 20px;
        }

        /* Top bar */
        .top-bar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px;
        }
        .top-bar a { color: #8b949e; text-decoration: none; font-weight: 600; transition: 0.3s; }
        .top-bar a:hover { color: #58cc02; }
        .badge-pill {
            padding: 6px 16px; border-radius: 50px; font-weight: 700; font-size: 0.85rem;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .badge-lvl { background: rgba(28,176,246,0.15); color: #1cb0f6; }
        .badge-can { background: rgba(234,43,43,0.15); color: #ea4c4c; }
        .badge-ders { background: rgba(88,204,2,0.15); color: #58cc02; }

        /* Editor card */
        .editor-card {
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 16px 48px rgba(0,0,0,0.4);
            animation: slideUp 0.5s ease-out;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .editor-titlebar {
            background: #1c2129;
            padding: 12px 20px;
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid #30363d;
        }
        .editor-dots { display: flex; gap: 8px; }
        .editor-dots span { width: 12px; height: 12px; border-radius: 50%; }
        .dot-red { background: #ff5f57; }
        .dot-yellow { background: #febc2e; }
        .dot-green { background: #28c840; }
        .editor-title { color: #8b949e; font-size: 0.8rem; font-family: 'Inter', sans-serif; }

        /* Code area */
        .code-area {
            padding: 30px 25px;
            font-family: 'Fira Code', monospace;
            font-size: 1.1rem;
            line-height: 2.2;
            color: #c9d1d9;
            position: relative;
            min-height: 120px;
        }
        .code-area .line-num {
            color: #484f58;
            margin-right: 20px;
            user-select: none;
            min-width: 24px;
            display: inline-block;
            text-align: right;
        }

        /* Input blank */
        .blank-input {
            background: rgba(88,204,2,0.1);
            border: 2px dashed #58cc02;
            border-radius: 8px;
            color: #58cc02;
            font-family: 'Fira Code', monospace;
            font-size: 1rem;
            font-weight: 600;
            padding: 4px 12px;
            width: 180px;
            text-align: center;
            outline: none;
            transition: all 0.3s;
            animation: pulse-border 2s infinite;
        }
        .blank-input:focus {
            background: rgba(88,204,2,0.18);
            border-color: #7ddf40;
            box-shadow: 0 0 16px rgba(88,204,2,0.25);
            animation: none;
        }
        .blank-input::placeholder { color: rgba(88,204,2,0.4); font-weight: 400; }
        @keyframes pulse-border {
            0%, 100% { border-color: #58cc02; }
            50% { border-color: rgba(88,204,2,0.3); }
        }

        /* Result animations */
        .blank-input.correct-input {
            background: rgba(88,204,2,0.25) !important;
            border-color: #58cc02 !important;
            border-style: solid !important;
            color: #58cc02 !important;
            animation: correct-glow 0.6s ease;
        }
        .blank-input.wrong-input {
            background: rgba(234,43,43,0.2) !important;
            border-color: #ea4c4c !important;
            border-style: solid !important;
            color: #ea4c4c !important;
            animation: shake 0.5s ease;
        }
        @keyframes correct-glow {
            0% { box-shadow: 0 0 0 rgba(88,204,2,0); }
            50% { box-shadow: 0 0 30px rgba(88,204,2,0.5); }
            100% { box-shadow: 0 0 10px rgba(88,204,2,0.2); }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-8px); }
            40% { transform: translateX(8px); }
            60% { transform: translateX(-5px); }
            80% { transform: translateX(5px); }
        }

        /* Buttons area */
        .actions-area {
            padding: 20px 25px;
            border-top: 1px solid #30363d;
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 10px;
        }
        .btn-gonder {
            background: linear-gradient(135deg, #58cc02, #46a302);
            color: white; border: none;
            padding: 12px 32px; border-radius: 12px;
            font-weight: 700; font-size: 1rem;
            cursor: pointer; transition: all 0.3s;
            border-bottom: 4px solid #3a8a02;
        }
        .btn-gonder:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(88,204,2,0.3); }
        .btn-gonder:active { transform: translateY(1px); border-bottom-width: 2px; }
        .btn-gonder:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        .btn-ipucu {
            background: rgba(255,193,7,0.12); color: #ffc107;
            border: 1px solid rgba(255,193,7,0.3);
            padding: 10px 20px; border-radius: 12px;
            font-weight: 600; cursor: pointer; transition: 0.3s;
        }
        .btn-ipucu:hover { background: rgba(255,193,7,0.2); }
        .btn-ipucu:disabled { opacity: 0.4; cursor: not-allowed; }

        /* Feedback toast */
        .feedback-toast {
            position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%) translateY(100px);
            padding: 16px 30px; border-radius: 16px;
            font-weight: 700; font-size: 1rem;
            z-index: 100; opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: flex; align-items: center; gap: 10px;
        }
        .feedback-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .feedback-toast.dogru-toast {
            background: linear-gradient(135deg, #1a3a0a, #234d0f);
            color: #58cc02; border: 1px solid #58cc02;
            box-shadow: 0 10px 40px rgba(88,204,2,0.2);
        }
        .feedback-toast.yanlis-toast {
            background: linear-gradient(135deg, #3a0a0a, #4d0f0f);
            color: #ea4c4c; border: 1px solid #ea4c4c;
            box-shadow: 0 10px 40px rgba(234,43,43,0.2);
        }

        .info-footer {
            text-align: center; margin-top: 16px;
            color: #484f58; font-size: 0.8rem;
        }
    </style>
</head>
<body>

<div class="game-container">
    <!-- Top bar -->
    <div class="top-bar">
        <a href="index.php"><i class="fas fa-arrow-left me-2"></i>Panele Dön</a>
        <div class="d-flex gap-2 flex-wrap justify-content-end">
            <span class="badge-pill badge-ders"><i class="fas fa-book"></i> <?php echo htmlspecialchars($ders_adi); ?></span>
            <span class="badge-pill badge-lvl"><i class="fas fa-layer-group"></i> Seviye <?php echo $ders_level; ?></span>
            <span class="badge-pill badge-can"><i class="fas fa-heart"></i> <?php echo $current_user['can']; ?></span>
        </div>
    </div>

    <!-- Editor card -->
    <div class="editor-card" id="editorCard">
        <div class="editor-titlebar">
            <div class="editor-dots">
                <span class="dot-red"></span>
                <span class="dot-yellow"></span>
                <span class="dot-green"></span>
            </div>
            <span class="editor-title"><i class="fas fa-puzzle-piece me-1"></i> bosluk_doldur.code</span>
        </div>

        <div class="code-area">
            <span class="line-num">1</span><?php
            // Kod parçalarını birleştir, boşluk yerine input koy
            $parca_sayisi = count($kod_parcalari);
            for ($i = 0; $i < $parca_sayisi; $i++) {
                // Kod parçasını göster (HTML encode et ama \n'leri <br> yap)
                $parca = htmlspecialchars($kod_parcalari[$i]);
                $parca = str_replace("\n", "<br><span class='line-num'>" . ($i + 2) . "</span>", $parca);
                echo $parca;
                
                // Son parça değilse input ekle
                if ($i < $parca_sayisi - 1) {
                    echo '<input type="text" class="blank-input" id="cevapInput" placeholder="???" autocomplete="off" spellcheck="false">';
                }
            }
            ?>
        </div>

        <div class="actions-area">
            <button class="btn-ipucu" id="ipucuBtn" onclick="ipucuGoster()">
                <i class="fas fa-lightbulb me-1"></i> İpucu Göster
            </button>
            <button class="btn-gonder" id="gonderBtn" onclick="cevapGonder()">
                Kontrol Et <i class="fas fa-check ms-2"></i>
            </button>
        </div>
    </div>

    <div class="info-footer">
        <i class="fas fa-database me-1"></i> Bu derste <b><?php echo $cevaplanan_sayisi; ?></b> / <b><?php echo $toplam; ?></b> boşluk doldurma sorusu tamamlandı.
        <?php if($soru['ipucu']): ?>
        <span id="ipucuMetin" style="display:none;"> | <i class="fas fa-lightbulb text-warning"></i> <?php echo htmlspecialchars($soru['ipucu']); ?></span>
        <?php endif; ?>
    </div>
</div>

<!-- Feedback toast -->
<div class="feedback-toast" id="feedbackToast"></div>

<script>
const dogruSes = new Audio('dogru.mp3');
const yanlisSes = new Audio('yanlis.mp3');
const soruId = <?php echo $soru['id']; ?>;
const dersId = <?php echo $ders_id; ?>;
let cevapVerildi = false;

// Enter tuşu ile gönder
document.getElementById('cevapInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !cevapVerildi) cevapGonder();
});

// Sayfa açılınca input'a odaklan
document.getElementById('cevapInput').focus();

function ipucuGoster() {
    const ipucu = document.getElementById('ipucuMetin');
    if (ipucu) {
        ipucu.style.display = 'inline';
        document.getElementById('ipucuBtn').disabled = true;
        document.getElementById('ipucuBtn').innerHTML = '<i class="fas fa-lightbulb me-1"></i> İpucu Gösterildi';
    }
}

function showToast(mesaj, tip) {
    const toast = document.getElementById('feedbackToast');
    toast.className = 'feedback-toast ' + (tip === 'dogru' ? 'dogru-toast' : 'yanlis-toast');
    const icon = tip === 'dogru' ? '✅' : '❌';
    toast.innerHTML = icon + ' ' + mesaj;
    setTimeout(() => toast.classList.add('show'), 50);
    setTimeout(() => toast.classList.remove('show'), 2500);
}

function cevapGonder() {
    if (cevapVerildi) return;
    
    const input = document.getElementById('cevapInput');
    const cevap = input.value.trim();
    
    if (!cevap) {
        input.style.borderColor = '#ffc107';
        input.placeholder = 'Bir cevap yaz!';
        setTimeout(() => { input.style.borderColor = '#58cc02'; input.placeholder = '???'; }, 1500);
        return;
    }

    cevapVerildi = true;
    document.getElementById('gonderBtn').disabled = true;

    fetch('bosluk_kontrol.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `soru_id=${soruId}&cevap=${encodeURIComponent(cevap)}&ders_id=${dersId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.durum === 'dogru') {
            input.classList.add('correct-input');
            input.value = data.dogru_cevap;
            dogruSes.play();
            showToast(data.mesaj, 'dogru');
            setTimeout(() => location.reload(), 2000);
        } else if (data.durum === 'yanlis') {
            input.classList.add('wrong-input');
            yanlisSes.play();
            showToast(data.mesaj, 'yanlis');
            setTimeout(() => {
                input.value = data.dogru_cevap;
                input.classList.remove('wrong-input');
                input.classList.add('correct-input');
            }, 1500);
            setTimeout(() => location.reload(), 3000);
        } else {
            showToast(data.mesaj, 'yanlis');
            cevapVerildi = false;
            document.getElementById('gonderBtn').disabled = false;
        }
    })
    .catch(() => {
        showToast('Bağlantı hatası!', 'yanlis');
        cevapVerildi = false;
        document.getElementById('gonderBtn').disabled = false;
    });
}
</script>

</body>
</html>
