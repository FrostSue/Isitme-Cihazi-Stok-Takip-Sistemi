# İşitme Cihazı Stok Yönetim Sistemi

## Admin Ekleme Talimatları

- Sistemde **admin eklemek** ya da **üye olmak** için herhangi bir kullanıcı arayüzü bulunmamaktadır.
- Admin eklemek için `hash_password.php` dosyasındaki talimatları uygulayarak bir şifre hash’i üretin.
- **Dikkat:** Her cihazda hash değeri farklı olacağı için, veritabanında mevcut bir admin kaydı varsa:
  - Yeni bir şifre hash’i üretin,
  - Eski hash’i veritabanında yenisi ile değiştirin.

## SQL Admin Ekleme Kod Satırı
INSERT INTO admins (username, password_hash, full_name) VALUES 
('admin', 'hashedpassword', 'fullname'); 


### Varsayılan Giriş Bilgileri
- **Kullanıcı Adı:** `admin`  
- **Şifre:** `admin123`

Bu bilgileri değiştirmek isterseniz, aynı yöntemi kullanarak yeni bir hash üretip veritabanında güncelleme yapabilirsiniz.

## Giriş Sonrası Ürün Yüklenmeme Durumu
- Giriş yaptıktan sonra ürünler yüklenmiyorsa:
  - Sayfa tamamen yüklendikten sonra **5 saniye bekleyin**,
  - Ardından **CTRL + F5** tuşlarına basarak önbelleği yenileyin.

## Ürün Görselleri Hakkında
- Ürün görselleri **lokal dosya** olarak saklanmaktadır.
- Ürün ekleme ya da düzenleme işlemlerinde:
  - Bilgisayarınızdan dosya yükleyebilir ya da
  - Görselin **URL adresini** kullanarak da ekleme yapabilirsiniz.

---

İşitme Cihazı Stok Yönetim Sistemi

İşitme cihazı satıcıları için özel olarak tasarlanmış kapsamlı bir web tabanlı envanter yönetim sistemidir. Bu sistem, işitme cihazı ürünlerinin takibini, stok yönetimini, satış kayıtlarını ve iş analizlerini verimli şekilde gerçekleştirmenizi sağlar.

## İçindekiler

- [Genel Bakış](#genel-bakis)
- [Özellikler](#ozellikler)
- [Kullanılan Teknolojiler](#kullanilan-teknolojiler)
- [Sistem Gereksinimleri](#sistem-gereksinimleri)
- [Kurulum](#kurulum)
- [Veritabanı Kurulumu](#veritabani-kurulumu)
- [Proje Yapısı](#proje-yapisi)
- [Kullanıcı Kılavuzu](#kullanici-kilavuzu)
  - [Giriş](#giris)
  - [Ürün Yönetimi](#urun-yonetimi)
  - [Stok Yönetimi](#stok-yonetimi)
  - [Satış Arayüzü](#satis-arayuzu)
- [Güvenlik Özellikleri](#guvenlik-ozellikleri)
- [Arayüz ve Kullanılabilirlik](#arayuz-ve-kullanilabilirlik)
- [API Dokümantasyonu](#api-dokumantasyonu)
- [Sorun Giderme](#sorun-giderme)
- [Katkı Sağlama](#katki-saglama)
- [Lisans](#lisans)

## Genel Bakış

İşitme Cihazı Stok Yönetim Sistemi, işitme cihazı perakendecilerinin envanter operasyonlarını kolaylaştırmak için tasarlanmıştır. Güvenli kimlik doğrulama, kapsamlı ürün yönetimi, gerçek zamanlı stok takibi ve satış kaydı işlevselliği ile kullanıcı dostu bir arayüz sağlar. Sistem, modern animasyonlar, duyarlı tasarım ve sezgisel gezinme gibi kullanıcı deneyimini artıran özellikler içerir.

## Özellikler

### Temel Özellikler
- **Kullanıcı Doğrulama**: Oturum yönetimi ile güvenli giriş
- **Ürün Yönetimi**: İşitme cihazı ürünlerini ekleme, düzenleme, görüntüleme ve silme
- **Kategori ve Marka Organizasyonu**: Ürünleri kategori ve markalara göre düzenleme
- **Stok Takibi**: Ürün envanter seviyelerinin gerçek zamanlı izlenmesi
- **Satış Kaydı**: Ürün satışlarını kaydetme ve izleme
- **Düşük Stok Uyarıları**: Düşük stoklu ürünler için görsel uyarılar
- **Arama ve Filtreleme**: Gelişmiş arama ve filtreleme özellikleri

### Arayüz ve Kullanılabilirlik Özellikleri
- **Modern Arayüz**: Temiz, profesyonel tasarım ve tutarlı stil
- **Duyarlı Tasarım**: Farklı ekran boyutlarına uyum sağlar
- **Etkileşimli Animasyonlar**: Akıcı geçişler ve ilgi çekici görsel efektler
- **Sezgisel Gezinme**: Bilgi hiyerarşisine sahip kullanıcı dostu yerleşim
- **Görsel Geri Bildirim**: Uyarı bildirimi ve durum göstergeleri
- **Erişilebilirlik**: Daha iyi kullanılabilirlik için tasarım önlemleri

## Kullanılan Teknolojiler

## Sistem Gereksinimleri

- PHP 7.4 veya daha yeni sürüme sahip bir web sunucusu
- MySQL 5.7 veya daha yeni sürüm
- Modern bir web tarayıcısı (Chrome, Firefox, Safari, Edge)
- Yerel geliştirme için XAMPP 7.4 veya üzeri
- Uygulama için en az 256MB RAM
- Veritabanı hariç 50MB disk alanı

## Kurulum

1. **XAMPP Kurulumu**:
   - [Resmi web sitesinden](https://www.apachefriends.org/) XAMPP'i indirip kurun.
   - Apache ve MySQL servislerini başlatın.

2. **Projeyi klonlayın veya indirin**:
   - `hearing_aid_stock` klasörünü XAMPP'in `htdocs` dizinine yerleştirin.

3. **Veritabanı bağlantısını yapılandırın**:
   - `db_connection.php` dosyasını açın ve gerekirse veritabanı bilgilerini güncelleyin.

4. **Veritabanını içe aktarın**:
   - http://localhost/phpmyadmin adresine gidin.
   - `hearing_aid_stock` adında yeni bir veritabanı oluşturun.
   - Proje dizinindeki `db_admin.sql` dosyasını içe aktarın.

5. **Uygulamaya erişin**:
   - Web tarayıcısında http://localhost/hearing_aid_stock adresine gidin.
   - Varsayılan bilgilerle giriş yapın:
     - Kullanıcı Adı: admin
     - Şifre: admin123

6. **Varsayılan giriş bilgilerini değiştirin**:
   - Güvenlik amacıyla admin şifresini hemen değiştirin.

## Veritabanı Yapısı

Sistem iki ana veritabanı kullanır:

1. **Ürün Veritabanı**:
   - `products`: Ürün bilgilerini saklar
   - `categories`: Ürün kategorilerini saklar
   - `brands`: Marka bilgilerini saklar

2. **Admin Veritabanı**:
   - `admins`: Kullanıcı giriş bilgilerini saklar
   - `sessions`: Aktif oturumları izler

## Proje Yapısı

Proje modüler bir yapıya sahiptir:

```
hearing_aid_stock/
│
├── animations.css           # Global animasyonlar
├── animations.js            # Animasyon betikleri
├── header_animation.js      # Başlık animasyonları
├── style.css                # Ana stil dosyası
│
├── login.php                # Giriş sayfası
├── logout.php               # Oturum sonlandırma
├── check_session.php        # Oturum doğrulama
│
├── product_list.php         # Ürün listeleme
├── add_product.php          # Ürün ekleme/düzenleme
├── selling_interface.php    # Satış arayüzü
│
├── db_connection.php        # Veritabanı bağlantısı
│
├── get_brands.php           # Marka API'si
├── get_categories.php       # Kategori API'si
├── get_products.php         # Ürün API'si
├── sell_product.php         # Satış işlemleri
├── update_stock.php         # Stok güncelleme
│
└── db_admin.sql             # Veritabanı başlangıç scripti
```

## Kullanıcı Kılavuzu

### Giriş
1. http://localhost/hearing_aid_stock adresine gidin
2. Kullanıcı adı ve şifre girin
3. Kimlik doğrulaması yapıldıktan sonra ürün sayfasına yönlendirilirsiniz
4. 30 dakika işlem yapılmazsa oturum sona erer

### Ürün Yönetimi

**Ürünleri Görüntüleme**:
- Ana panelde toplam ürün, kategori, marka ve düşük stok bilgileri gösterilir
- Liste görseller, isim, kategori, marka, fiyat ve stok içerir
- Sütun başlıklarına tıklayarak sıralama yapılabilir
- Arama kutusu ve filtreler ile ürün araması yapılabilir

**Ürün Ekleme**:
1. "Yeni Ürün Ekle" butonuna tıklayın
2. Kategori ve marka seçin (gerekirse yeni marka oluşturun)
3. Ürün detaylarını girin
4. Görsel yükleyin veya URL girin
5. "Kaydet" ile ekleyin

**Ürün Düzenleme**:
1. Ürünü bulun
2. Kalem ikonuna tıklayın
3. Değişiklikleri yapın
4. "Kaydet" ile güncelleyin

**Hızlı Düzenleme**:
- Fiyat veya stok alanına tıklayarak anlık düzenleme yapılabilir
- Yeni değeri girin ve Enter'a basın

**Ürün Silme**:
1. Ürünü bulun
2. Çöp kutusu ikonuna tıklayın
3. Onay kutusunda silmeyi onaylayın

### Stok Yönetimi

**Stok Seviyelerini Görüntüleme**:
- Stok bilgisi ürün listesinde yer alır
- Düşük stoklar sarı, tükenenler kırmızı renkte gösterilir

**Stok Ekleme**:
1. Ürünü bulun
2. İşlem sütunundaki "+" ikonuna tıklayın
3. Eklenecek miktarı girin
4. Güncellemeyi onaylayın

**Stok Durumuna Göre Filtreleme**:
- "Az Stoklu Ürünler" kartına tıklayarak filtreleme yapılabilir
- "Stokta Olmayan" kartına tıklayarak tükenen ürünler listelenir

### Satış Arayüzü

1. "Satış Arayüzü"ne tıklayın
2. Kategori, marka ve ürün seçin
3. Ürün detaylarını inceleyin
4. "Bu Ürünü Sat" ile satışı kaydedin
5. Stok otomatik azalır ve işlem geçmişe eklenir

## Güvenlik Özellikleri

- **Şifre Hashleme**: PHP `password_hash` fonksiyonu kullanılır
- **CSRF Koruması**: Tüm formlarda token doğrulaması
- **Oturum Yönetimi**: Süreli ve güvenli oturumlar
- **SQL Injection Koruması**: Hazır ifadeler
- **XSS Önleme**: Çıktı kaçışlama
- **Girdi Doğrulama**: Sunucu taraflı kontroller

## API Dokümantasyonu

- **GET /get_categories.php**: Kategorileri getirir
- **GET /get_brands.php?category_id=X**: Kategoriye göre markaları getirir
- **GET /get_products.php**: Filtreleme ile ürünleri getirir
- **POST /sell_product.php**: Satışı kaydeder ve stok günceller
- **POST /update_stock.php**: Stok bilgisini günceller
- **POST /delete_product.php**: Ürünü siler

## Sorun Giderme

**Yaygın Sorunlar**:
1. **Giriş Sorunları**: Veritabanı bağlantısını kontrol edin, çerezlerin açık olduğundan emin olun
2. **Görsel Sorunları**: Yükleme klasörünün yazma izni olup olmadığını kontrol edin
3. **Stok Kaydı Sorunu**: Hata loglarını kontrol edin
4. **Animasyon Hataları**: CSS ve JS dosyalarının düzgün yüklendiğini kontrol edin

## Katkı Sağlama

1. Depoyu çatallayın
2. Yeni bir dal oluşturun
3. Değişiklikleri yapın
4. Test edin
5. Açıklayıcı bir çekme isteği gönderin

## Lisans

Bu proje MIT lisansı altındadır. Detaylar için LICENSE dosyasına bakın.

---
© 2023 İşitme Cihazı Stok Yönetim Sistemi. Tüm hakları saklıdır.
