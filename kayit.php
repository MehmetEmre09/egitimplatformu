<?php
include 'baglan.php';

if ($_POST) {
    $kadi = $_POST['kullanici_adi'];
    $email = $_POST['email'];
    $sifre = password_hash($_POST['sifre'], PASSWORD_DEFAULT); // Şifreyi şifreledik, güvenlik şart!

    // Veritabanına ekleme sorgusu
    $sorgu = $db->prepare("INSERT INTO kullanicilar SET kullanici_adi = ?, email = ?, sifre = ?, xp = 0, can = 5");
    $ekle = $sorgu->execute([$kadi, $email, $sifre]);

    if ($ekle) {
        echo "<script>alert('Harika! Kayıt oldun. Şimdi giriş yapabilirsin.'); window.location.href='giris.php';</script>";
    } else {
        echo "Bir şeyler ters gitti!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kayıt Ol | Eğitim Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #58cc02; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .register-card { background: white; padding: 40px; border-radius: 30px; box-shadow: 0 10px 0 rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .btn-register { background: #1cb0f6; color: white; border: none; border-bottom: 4px solid #1899d6; border-radius: 15px; font-weight: bold; }
        .btn-register:hover { background: #1cb0f6; transform: translateY(2px); border-bottom: 2px solid #1899d6; }
    </style>
</head>
<body>

<div class="register-card text-center">
    <h2 class="fw-bold mb-4" style="color: #4b4b4b;">Hesap Oluştur</h2>
    <form method="POST">
        <input type="text" name="kullanici_adi" class="form-control mb-3 p-3" placeholder="Kullanıcı Adı" required style="border-radius: 15px;">
        <input type="email" name="email" class="form-control mb-3 p-3" placeholder="E-posta" required style="border-radius: 15px;">
        <input type="password" name="sifre" class="form-control mb-4 p-3" placeholder="Şifre" required style="border-radius: 15px;">
        <button type="submit" class="btn btn-register w-100 p-3">KAYIT OL</button>
    </form>
    <p class="mt-3 text-muted">Zaten hesabın var mı? <a href="giris.php" class="text-decoration-none">Giriş yap</a></p>
</div>

</body>
</html>