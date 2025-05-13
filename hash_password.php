<?php
// hash_password.php
// Kullanımı: http://localhost/hearing_aid_stock/hash_password.php?password=yenisifre
// Yeni bir şifre oluşturacaksınız URL'nin sonundaki yenisifre yazısını değiştirip yapmak
// istediğiniz şifreyi girin. 
// Daha detaylı bilgi için README.md dosyasını okuyabilirsiniz

$password = $_GET['password'] ?? '';
if (!$password) {
    echo "Lütfen ?password=PARAMetre ile çağırın.";
    exit;
}

echo '<h2>Orijinal şifre: <code>' . htmlspecialchars($password) . '</code></h2>';
echo '<h2>Hashed (bcrypt):</h2>';
echo '<pre>' . password_hash($password, PASSWORD_DEFAULT) . '</pre>';
