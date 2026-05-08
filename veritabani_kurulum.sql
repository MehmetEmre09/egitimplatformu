-- =============================================
-- EĞİTİM PLATFORMU - TAM VERİTABANI VE SORU PAKETİ
-- phpMyAdmin > SQL sekmesine yapıştırıp çalıştırın
-- =============================================

CREATE DATABASE IF NOT EXISTS egitim_platformu CHARACTER SET utf8 COLLATE utf8_general_ci;
USE egitim_platformu;

-- 1. TABLO YAPILARI
-- ---------------------------------------------

CREATE TABLE IF NOT EXISTS kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_adi VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    sifre VARCHAR(255) NOT NULL,
    xp INT DEFAULT 0,
    can INT DEFAULT 5,
    son_can_yenilenme DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dersler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ders_adi VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS sorular (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ders_id INT NOT NULL,
    soru_seviye INT DEFAULT 1,
    soru_metni TEXT NOT NULL,
    secenek_a VARCHAR(255) NOT NULL,
    secenek_b VARCHAR(255) NOT NULL,
    secenek_c VARCHAR(255) NOT NULL,
    secenek_d VARCHAR(255) NOT NULL,
    dogru_cevap CHAR(1) NOT NULL
);

CREATE TABLE IF NOT EXISTS kullanici_ders_ilerleme (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ders_id INT NOT NULL,
    xp INT DEFAULT 0,
    UNIQUE KEY unique_user_ders (user_id, ders_id)
);

CREATE TABLE IF NOT EXISTS bosluklular (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ders_id INT NOT NULL,
    soru_seviye INT DEFAULT 1,
    kod_metni TEXT NOT NULL,
    dogru_cevap VARCHAR(255) NOT NULL,
    ipucu VARCHAR(255) DEFAULT NULL
);

-- YENİ: Cevaplanan sorular tablosu (soru tekrarını önlemek için)
CREATE TABLE IF NOT EXISTS cevaplanan_sorular (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    soru_tipi ENUM('sorular', 'bosluklular') NOT NULL,
    soru_id INT NOT NULL,
    cevaplama_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cevap (user_id, soru_tipi, soru_id)
);

-- 2. DERS TANIMLAMALARI
-- ---------------------------------------------

INSERT INTO dersler (ders_adi) VALUES
('HTML & CSS'),
('JavaScript (JS)'),
('PHP'),
('Java');

-- 3. ÇOKTAN SEÇMELİ SORULAR (SORULAR TABLOSU)
-- ---------------------------------------------

INSERT INTO sorular (ders_id, soru_seviye, soru_metni, secenek_a, secenek_b, secenek_c, secenek_d, dogru_cevap) VALUES
-- HTML & CSS - Seviye 1
(1, 1, 'HTML''de en büyük başlık etiketi hangisidir?', 'h6', 'h1', 'h3', 'header', 'B'),
(1, 1, 'HTML''de paragraf etiketi hangisidir?', '<paragraph>', '<p>', '<text>', '<pg>', 'B'),
(1, 1, 'HTML sayfasının ana gövde etiketi hangisidir?', '<head>', '<main>', '<body>', '<html>', 'C'),
(1, 1, 'Sırasız liste oluşturmak için hangi etiket kullanılır?', '<ol>', '<ul>', '<list>', '<dl>', 'B'),
(1, 1, 'HTML''de satır sonu (line break) etiketi hangisidir?', '<break>', '<lb>', '<br>', '<newline>', 'C'),

-- HTML & CSS - Seviye 2
(1, 2, 'Bir resim eklemek için hangi etiket kullanılır?', '<img>', '<image>', '<picture>', '<photo>', 'A'),
(1, 2, 'CSS dosyası bağlamak için hangi etiket kullanılır?', '<style>', '<css>', '<link>', '<script>', 'C'),
(1, 2, 'HTML''de tablo başlığı için hangi etiket kullanılır?', '<td>', '<th>', '<tr>', '<thead>', 'B'),
(1, 2, 'Form elemanlarını gruplamak için hangi etiket kullanılır?', '<group>', '<fieldset>', '<section>', '<div>', 'B'),
(1, 2, 'CSS''de bir elemanın dış boşluğunu ayarlayan özellik hangisidir?', 'padding', 'margin', 'border', 'spacing', 'B'),

-- HTML & CSS - Seviye 3
(1, 3, 'CSS''de "id" seçici hangi karakterle başlar?', '.', '#', '&', '*', 'B'),
(1, 3, 'CSS Flexbox''ta elemanları yatayda ortalamak için hangi özellik kullanılır?', 'align-items', 'text-align', 'justify-content', 'vertical-align', 'C'),
(1, 3, 'CSS''de animasyon tanımlamak için hangi kural kullanılır?', '@animate', '@transition', '@keyframes', '@motion', 'C'),
(1, 3, 'Responsive tasarım için hangi CSS kuralı kullanılır?', '@responsive', '@media', '@screen', '@device', 'B'),

-- JavaScript - Seviye 1
(2, 1, 'JavaScript''te değişken tanımlamak için hangisi kullanılır?', 'var', 'int', 'string', 'define', 'A'),
(2, 1, 'Konsola mesaj yazdırmak için hangi komut kullanılır?', 'print()', 'echo()', 'console.log()', 'write()', 'C'),
(2, 1, 'JavaScript''te eşitlik kontrolü için hangisi kullanılır?', '=', '==', ':=', '!=', 'B'),
(2, 1, 'JavaScript''te yorum satırı nasıl yazılır?', '# yorum', '<!-- yorum -->', '// yorum', '** yorum **', 'C'),
(2, 1, 'Bir string''in uzunluğunu bulmak için hangi özellik kullanılır?', '.size', '.count', '.length', '.len', 'C'),

-- JavaScript - Seviye 2
(2, 2, 'Bir diziye eleman eklemek için hangi metot kullanılır?', 'add()', 'push()', 'insert()', 'append()', 'B'),
(2, 2, 'DOM''da bir öğeyi ID ile seçmek için hangisi kullanılır?', 'getElement()', 'querySelector()', 'getElementById()', 'findById()', 'C'),
(2, 2, 'JavaScript''te bir fonksiyonu belirli bir süre sonra çalıştırmak için hangisi kullanılır?', 'delay()', 'wait()', 'setTimeout()', 'sleep()', 'C'),
(2, 2, 'JSON string''i objeye çevirmek için hangi metot kullanılır?', 'JSON.stringify()', 'JSON.parse()', 'JSON.convert()', 'JSON.decode()', 'B'),

-- JavaScript - Seviye 3
(2, 3, 'JavaScript''te asenkron işlem için hangi yapı kullanılır?', 'Thread', 'Promise', 'Callback Only', 'Worker', 'B'),
(2, 3, 'Arrow function sözdizimi hangisidir?', 'function =>', '() => {}', 'fn() {}', 'lambda()', 'B'),
(2, 3, 'Spread operatörü hangi karakterlerle gösterilir?', '**', '&&', '...', '@@', 'C'),

-- PHP - Seviye 1
(3, 1, 'PHP''de ekrana yazdırma komutu hangisidir?', 'print()', 'echo', 'write()', 'display()', 'B'),
(3, 1, 'PHP değişkenleri hangi sembolle başlar?', '#', '@', '$', '&', 'C'),
(3, 1, 'PHP''de tek satırlık yorum nasıl yazılır?', '# yorum', '// yorum', 'Her ikisi de doğru', '<!-- yorum -->', 'C'),
(3, 1, 'PHP''de string birleştirme operatörü hangisidir?', '+', '.', '&', ',', 'B'),

-- PHP - Seviye 2
(3, 2, 'PHP''de güvenli veritabanı sorgusu için ne kullanılır?', 'mysql_query()', 'PDO', 'db_query()', 'SQL()', 'B'),
(3, 2, 'Form verisi almak için hangi süper global kullanılır?', '$_FORM', '$_DATA', '$_POST', '$_INPUT', 'C'),
(3, 2, 'PHP''de bir dosyayı dahil etmek için hangi komut kullanılır?', 'import', 'include', 'use', 'load', 'B'),
(3, 2, 'PHP''de bir değişkenin tanımlı olup olmadığını kontrol eden fonksiyon hangisidir?', 'defined()', 'exists()', 'isset()', 'has()', 'C'),

-- PHP - Seviye 3
(3, 3, 'PHP''de dizideki eleman sayısını hangi fonksiyon verir?', 'size()', 'length()', 'count()', 'total()', 'C'),
(3, 3, 'PHP dosyasını dahil ederken hata oluşursa çalışmayı durduran komut?', 'include', 'require', 'import', 'link', 'B'),
(3, 3, 'PHP''de sınıf oluşturmak için hangi anahtar kelime kullanılır?', 'object', 'struct', 'class', 'type', 'C'),
(3, 3, 'PHP''de hata yakalamak için hangi yapı kullanılır?', 'try-catch', 'if-error', 'handle-exception', 'on-error', 'A'),

-- Java - Seviye 1
(4, 1, 'Java''da ekrana yazdırma komutu hangisidir?', 'print()', 'console.log()', 'System.out.println()', 'echo', 'C'),
(4, 1, 'Java''da tam sayı veri tipi hangisidir?', 'number', 'integer', 'int', 'num', 'C'),
(4, 1, 'Java''da bir string tanımlamak için hangi sınıf kullanılır?', 'str', 'Text', 'String', 'Char', 'C'),
(4, 1, 'Java''da ana programın başlangıç metodu hangisidir?', 'start()', 'run()', 'main()', 'init()', 'C'),

-- Java - Seviye 2
(4, 2, 'Java''da bir sınıf tanımlamak için hangi anahtar kelime kullanılır?', 'define', 'struct', 'class', 'object', 'C'),
(4, 2, 'Java''da bir metodun değer döndürmediğini belirtmek için ne kullanılır?', 'null', 'empty', 'void', 'static', 'C'),
(4, 2, 'Java''da bir dizi oluşturmak için hangi sözdizimi doğrudur?', 'int[] arr = new int[5]', 'array arr = [5]', 'int arr(5)', 'new array(5)', 'A'),
(4, 2, 'Java''da bir arayüz tanımlamak için hangi anahtar kelime kullanılır?', 'abstract', 'interface', 'implements', 'protocol', 'B'),

-- Java - Seviye 3
(4, 3, 'Java''da miras (inheritance) almak için hangi anahtar kelime kullanılır?', 'implements', 'extends', 'inherits', 'import', 'B'),
(4, 3, 'Mantıksal (True/False) veriler için hangi tip kullanılır?', 'bit', 'byte', 'boolean', 'string', 'C'),
(4, 3, 'Java''da koleksiyon elemanları üzerinde gezinmek için hangi yapı kullanılır?', 'for-each', 'while-each', 'loop-in', 'scan', 'A'),
(4, 3, 'Java''da birden fazla arayüzü uygulamak için hangi anahtar kelime kullanılır?', 'extends', 'inherits', 'implements', 'uses', 'C');

-- 4. BOŞLUK DOLDURMA SORULARI (BOSLUKLULAR TABLOSU)
-- Her ders için benzersiz, çeşitli sorular
-- ---------------------------------------------

INSERT INTO bosluklular (ders_id, soru_seviye, kod_metni, dogru_cevap, ipucu) VALUES
-- HTML & CSS - Seviye 1
(1, 1, '<___BOSLUK___>Merhaba Dünya</h1>', 'h1', 'En büyük başlık etiketi'),
(1, 1, '<p ___BOSLUK___="color:red;">Kırmızı metin</p>', 'style', 'Satır içi stil özelliği'),
(1, 1, '<___BOSLUK___ src="resim.jpg" alt="Resim">', 'img', 'Görsel ekleme etiketi'),
(1, 1, '<ul>\n  <___BOSLUK___>Liste elemanı</li>\n</ul>', 'li', 'Liste elemanı etiketi'),
(1, 1, '<___BOSLUK___>Bu bir paragraftır.</p>', 'p', 'Paragraf etiketi'),

-- HTML & CSS - Seviye 2
(1, 2, '<a ___BOSLUK___="https://google.com">Tıkla</a>', 'href', 'Bağlantı adresi özelliği'),
(1, 2, '<input type="text" ___BOSLUK___="Adınız">', 'placeholder', 'Yer tutucu metin özelliği'),
(1, 2, '<___BOSLUK___ action="islem.php" method="POST">', 'form', 'Form etiketi'),
(1, 2, '<link rel="___BOSLUK___" href="style.css">', 'stylesheet', 'CSS dosyası bağlama'),
(1, 2, '<input type="text" ___BOSLUK___="isim">', 'name', 'Form alanı isim özelliği'),

-- HTML & CSS - Seviye 3
(1, 3, '<meta ___BOSLUK___="viewport" content="width=device-width">', 'name', 'Meta etiket özelliği'),
(1, 3, 'display: ___BOSLUK___;  /* Yatay hizalama için */', 'flex', 'Esnek kutu modeli'),
(1, 3, 'position: ___BOSLUK___;  /* Sayfada sabit konum */', 'fixed', 'Sabit konumlandırma'),

-- JavaScript - Seviye 1
(2, 1, '___BOSLUK___.log("Merhaba Dünya");', 'console', 'Konsola yazdırma nesnesi'),
(2, 1, '___BOSLUK___ isim = "Ahmet";', 'let', 'Değişken tanımlama'),
(2, 1, 'let sonuc = 10 ___BOSLUK___ 3;', '+', 'Toplama operatörü'),
(2, 1, 'document.___BOSLUK___("btn")', 'getElementById', 'ID ile eleman seçme metodu'),
(2, 1, '___BOSLUK___(\"Merhaba!\");', 'alert', 'Uyarı penceresi gösterme'),

-- JavaScript - Seviye 2
(2, 2, 'let x = ___BOSLUK___(5.7);', 'Math.floor', 'Aşağı yuvarlama fonksiyonu'),
(2, 2, 'let dizi = [1,2,3]; dizi.___BOSLUK___(4);', 'push', 'Diziye eleman ekleme metodu'),
(2, 2, 'function topla(a, b) {\n  ___BOSLUK___ a + b;\n}', 'return', 'Fonksiyondan değer döndürme'),
(2, 2, 'const veri = JSON.___BOSLUK___(txt);', 'parse', 'JSON objeye çevirme'),
(2, 2, '___BOSLUK___(function() { }, 2000);', 'setTimeout', 'Gecikmeli çalıştırma'),

-- JavaScript - Seviye 3
(2, 3, 'document.___BOSLUK___("click", fonksiyon);', 'addEventListener', 'Olay dinleyicisi ekleme'),
(2, 3, 'const kare = (x) ___BOSLUK___ x * x;', '=>', 'Arrow function operatörü'),
(2, 3, 'async function veriCek() { const res = ___BOSLUK___ fetch(url); }', 'await', 'Asenkron bekleme'),

-- PHP - Seviye 1
(3, 1, '___BOSLUK___ "Merhaba PHP";', 'echo', 'Ekrana yazdırma komutu'),
(3, 1, '$isim = ___BOSLUK___["ad"];', '$_POST', 'Form verisi alma'),
(3, 1, '$sayilar = ___BOSLUK___(1, 5, 3, 2);', 'array', 'Dizi oluşturma fonksiyonu'),
(3, 1, '$toplam = $a ___BOSLUK___ $b;', '+', 'Toplama operatörü'),

-- PHP - Seviye 2
(3, 2, '$db = new ___BOSLUK___("mysql:host=localhost;dbname=test", "root", "");', 'PDO', 'Veritabanı bağlantı sınıfı'),
(3, 2, '$sorgu = $db->___BOSLUK___("SELECT * FROM tablo WHERE id = ?");', 'prepare', 'Güvenli sorgu hazırlama metodu'),
(3, 2, 'if (___BOSLUK___($x)) { echo "Tanımlı"; }', 'isset', 'Tanımlı mı kontrolü'),
(3, 2, '$uzunluk = ___BOSLUK___($dizi);', 'count', 'Dizi eleman sayısı'),

-- PHP - Seviye 3
(3, 3, '___BOSLUK____start();', 'session', 'Oturum başlatma fonksiyonu'),
(3, 3, '$veri = file_get____BOSLUK___("dosya.txt");', 'contents', 'Dosya içeriği okuma'),
(3, 3, 'class Araba ___BOSLUK___ Tasit { }', 'extends', 'Sınıf mirası'),

-- Java - Seviye 1
(4, 1, 'public ___BOSLUK___ void main(String[] args)', 'static', 'Sabit metod tanımlayıcı'),
(4, 1, 'int[] sayilar = ___BOSLUK___ int[5];', 'new', 'Bellekte yer açma'),
(4, 1, 'System.out.___BOSLUK___("Merhaba Java");', 'println', 'Ekrana yazdırma metodu'),
(4, 1, '___BOSLUK___ isim = "Ali";', 'String', 'Metin veri tipi'),

-- Java - Seviye 2
(4, 2, 'Scanner input = new ___BOSLUK___(System.in);', 'Scanner', 'Kullanıcı girdisi sınıfı'),
(4, 2, 'for(int i=0; i<10; ___BOSLUK___)', 'i++', 'Artırma operatörü'),
(4, 2, 'ArrayList<String> liste = new ___BOSLUK___<>();', 'ArrayList', 'Dinamik dizi sınıfı'),
(4, 2, 'public int topla(int a, int b) { ___BOSLUK___ a + b; }', 'return', 'Değer döndürme'),

-- Java - Seviye 3
(4, 3, 'try { } ___BOSLUK___ (Exception e) { }', 'catch', 'Hata yakalama bloğu'),
(4, 3, 'class Kitap ___BOSLUK___ Roman { }', 'extends', 'Miras alma anahtar kelimesi'),
(4, 3, 'public class Hayvan { ___BOSLUK___ void sesCikar(); }', 'abstract', 'Soyut metod tanımlama'),
(4, 3, 'List<String> liste = ___BOSLUK___.asList("a","b","c");', 'Arrays', 'Dizi yardımcı sınıfı');