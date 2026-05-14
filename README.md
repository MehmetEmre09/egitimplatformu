# 🎓 DEV-AKADEMİ — Eğitim Platformu

> Duolingo'dan ilham alınarak geliştirilmiş, **PHP & MySQL** tabanlı interaktif yazılım eğitim platformu. Can sistemi, XP puanı, liderlik tablosu ve boşluk doldurma alıştırmalarıyla öğrenmeyi oyunlaştırır.

---

## 📸 Ekran Görüntüleri


### 🔐 Giriş Sayfası
<img width="654" height="300" alt="resim" src="https://github.com/user-attachments/assets/864d4679-9031-4a41-a945-73437e5fac54" />


> Mavi arka plan üzerinde yuvarlak köşeli beyaz kart. E-posta + şifre girişi, yeşil "GİRİŞ YAP" butonu ve kayıt ol bağlantısı.

---

### 🏠 Ana Panel
<img width="657" height="545" alt="resim" src="https://github.com/user-attachments/assets/2cd8cb04-39a6-4da5-b9e5-9793966f5535" />


> Yeşil navbar'da kullanıcı adı, can sayacı ❤️ ve XP puanı. Hoş geldin kartı + günün teknik ipucu. Ders kartları ve sağda liderlik tablosu.


---

## ✨ Özellikler

| Özellik | Açıklama |
|---|---|
| ❤️ **Can Sistemi** | Her yanlış cevap bir can düşürür. Canlar 60 saniyede yenilenir |
| ⚡ **XP Puanı** | Doğru cevaplar XP kazandırır, seviye atlatır |
| 🏆 **Liderlik Tablosu** | En yüksek XP'ye sahip 5 kullanıcı anlık sıralanır |
| 📝 **Soru Çözme** | Her ders için çoktan seçmeli sorular |
| 🧩 **Boşluk Doldurma** | Kod tamamlama alıştırmaları |
| 🎖️ **Usta Rozeti** | Level 5'e ulaşan kullanıcılara özel rozet |
| 💡 **Günün İpucu** | Her girişte rastgele teknik bilgi gösterimi |
| 👤 **Profil Sayfası** | Kullanıcı istatistikleri ve ilerleme takibi |

---

## 🛠️ Teknolojiler

- **Backend:** PHP 8+ (PDO ile güvenli veritabanı bağlantısı)
- **Veritabanı:** MySQL
- **Frontend:** Bootstrap 5.3, Font Awesome 6
- **Güvenlik:** `password_hash` / `password_verify`, SQL Injection koruması (PDO Prepared Statements), Session yönetimi

---

## 📁 Dosya Yapısı

```
egitimplatformu/
├── baglan.php           # Veritabanı bağlantısı
├── can_kontrol.php      # Can yenileme mantığı
├── index.php            # Ana panel (ders listesi, liderlik tablosu)
├── giris.php            # Kullanıcı girişi
├── kayit.php            # Yeni kullanıcı kaydı
├── cikis.php            # Oturum kapatma
├── ders_detay.php       # Soru çözme ekranı
├── bosluk_doldur.php    # Boşluk doldurma alıştırması
├── bosluk_kontrol.php   # Boşluk cevap kontrolü
├── profil.php           # Kullanıcı profili
├── puan_ver.php         # XP verme işlemi
├── veritabani_kurulum.sql  # Veritabanı şeması
├── dogru.mp3            # Doğru cevap sesi
└── yanlis.mp3           # Yanlış cevap sesi
```



## 🗄️ Veritabanı Yapısı

### `kullanicilar` tablosu
| Sütun | Tür | Açıklama |
|---|---|---|
| `id` | INT | Birincil anahtar |
| `kullanici_adi` | VARCHAR | Kullanıcı adı |
| `email` | VARCHAR | E-posta adresi |
| `sifre` | VARCHAR | Hash'lenmiş şifre |
| `xp` | INT | Toplam XP puanı |
| `can` | INT | Mevcut can sayısı (max 5) |
| `son_can_yenilenme` | DATETIME | Son can yenilenme zamanı |

### `dersler` tablosu
| Sütun | Tür | Açıklama |
|---|---|---|
| `id` | INT | Birincil anahtar |
| `ders_adi` | VARCHAR | Ders adı (HTML, PHP, JS...) |

### `kullanici_ders_ilerleme` tablosu
| Sütun | Tür | Açıklama |
|---|---|---|
| `user_id` | INT | Kullanıcı referansı |
| `ders_id` | INT | Ders referansı |
| `xp` | INT | Bu dersteki XP |

---

## 🎮 Nasıl Çalışır?

```
Kullanıcı giriş yapar
        ↓
Ana panel: dersler listelenir
        ↓
Ders seç → Soru Çöz veya Boşluk Doldur
        ↓
Doğru cevap → XP kazan → Seviye atla
Yanlış cevap → Can kaybet → Can bitince bekle (60sn/can)
        ↓
Liderlik tablosunda yüksel 🏆
```

---

## 📄 Lisans

Bu proje eğitim amaçlı geliştirilmiştir. Dilediğiniz gibi kullanabilir ve geliştirebilirsiniz.

---

> **Geliştirici:** [MehmetEmre09](https://github.com/MehmetEmre09)
