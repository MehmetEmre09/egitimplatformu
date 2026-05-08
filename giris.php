<?php
include 'baglan.php';
session_start(); // Kullanıcıyı girişte "hatırlamak" için session başlattık

if ($_POST) {
    $email = $_POST['email'];
    $sifre = $_POST['sifre'];

    $sorgu = $db->prepare("SELECT * FROM kullanicilar WHERE email = ?");
    $sorgu->execute([$email]);
    $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

    // Kullanıcı varsa ve şifre doğruysa (maskelenmiş şifreyi çözer)
    if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {
        $_SESSION['user_id'] = $kullanici['id'];
        $_SESSION['user_name'] = $kullanici['kullanici_adi'];
        
        header("Location: index.php"); // Başarılıysa ana sayfaya gönder
        exit();
    } else {
        echo "<script>alert('E-posta veya şifre hatalı!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap | Eğitim Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1cb0f6; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { background: white; padding: 40px; border-radius: 30px; box-shadow: 0 10px 0 rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .btn-login { background: #58cc02; color: white; border: none; border-bottom: 4px solid #46a302; border-radius: 15px; font-weight: bold; }
        .btn-login:hover { background: #58cc02; transform: translateY(2px); border-bottom: 2px solid #46a302; }
    </style>
</head>
<body>

<div class="login-card text-center">
    <h2 class="fw-bold mb-4" style="color: #4b4b4b;">Tekrar Hoş Geldin!</h2>
    <form method="POST">
        <input type="email" name="email" class="form-control mb-3 p-3" placeholder="E-posta" required style="border-radius: 15px;">
        <input type="password" name="sifre" class="form-control mb-4 p-3" placeholder="Şifre" required style="border-radius: 15px;">
        <button type="submit" class="btn btn-login w-100 p-3">GİRİŞ YAP</button>
    </form>
    <p class="mt-3 text-muted">Henüz hesabın yok mu? <a href="kayit.php" class="text-decoration-none">Kayıt Ol</a></p>
</div>

</body>
</html>