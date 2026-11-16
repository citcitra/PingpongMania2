# 🏓 PingPong Mania
### Sistem Mesin Pelontar Bola Ping Pong Otomatis Berbasis Web dan Raspberry Pi Pico WH

PingPong Mania merupakan proyek pengembangan sistem mesin pelontar bola ping pong otomatis untuk mendukung latihan tenis meja. Sistem ini menggabungkan dashboard berbasis web menggunakan Laravel dengan perangkat keras yang dikendalikan oleh Raspberry Pi Pico WH.

Pengguna dapat mengatur mode latihan, kecepatan pelontaran, interval waktu, serta arah pelontaran melalui dashboard. Sistem juga dikembangkan untuk memantau kondisi perangkat dan mendukung pencatatan riwayat latihan.

---

## 1. Teknologi yang Digunakan

### Perangkat Lunak
- **Framework:** Laravel 5.8
- **Bahasa pemrograman backend:** PHP 7.4
- **Database:** MySQL
- **Frontend:** Blade Template, HTML, CSS, dan JavaScript
- **Komunikasi perangkat:** HTTP melalui jaringan Wi-Fi
- **IDE mikrokontroler:** Arduino IDE

### Perangkat Keras
- Raspberry Pi Pico WH sebagai pengendali utama.
- Driver motor L298N.
- Motor DC gearbox untuk mekanisme pelontar.
- Servo untuk pengaturan arah pelontaran.
- Servo untuk mekanisme feeder atau pengumpan bola.
- Sensor inframerah untuk mendeteksi bola.
- Catu daya untuk motor dan rangkaian.

### Pemetaan Pin Raspberry Pi Pico WH

| Komponen | GPIO Pico WH |
|---|---|
| L298N IN1 | GP9 |
| L298N IN2 | GP10 |
| L298N IN3 | GP11 |
| L298N IN4 | GP12 |
| L298N ENA | GP6 |
| L298N ENB | GP7 |
| Servo pengarah | GP15 |
| Servo feeder | GP14 |
| Sensor bola 1 | GP2 |
| Sensor bola 2 | GP3 |
| Sensor bola 3 | GP4 |

---

## 2. Persiapan Lingkungan Pengembangan

Proyek ini masih menggunakan Laravel 5.8 sehingga lingkungan pengembangan perlu disesuaikan dengan versi PHP yang kompatibel.

### Persyaratan
- Windows 10/11.
- PHP 7.4.
- Composer.
- MySQL atau MariaDB.
- XAMPP atau layanan database MySQL yang sesuai.
- Arduino IDE.
- Browser.
- Raspberry Pi Pico WH.

**Catatan kompatibilitas:** PHP 7.4 sudah tidak mendapatkan dukungan keamanan. Versi tersebut digunakan untuk menjaga kompatibilitas proyek Laravel 5.8 selama pengembangan lokal. Upgrade framework dan PHP perlu direncanakan sebelum sistem digunakan secara publik.

---

## 3. Instalasi dan Menjalankan Laravel

Lokasi proyek saat ini:

```text
D:\PingPong-Mania
```

### Langkah instalasi

1. Buka CMD.
2. Masuk ke direktori proyek:

```cmd
cd /d D:\PingPong-Mania
```

3. Pastikan versi PHP:

```cmd
php -v
```

Pastikan menggunakan PHP 7.4 yang telah dikonfigurasi.

4. Instal dependency apabila diperlukan:

```cmd
composer install
```

5. Siapkan file `.env` berdasarkan `.env.example` jika belum tersedia.
6. Atur konfigurasi database pada `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pingpong_mania
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan nama database dan kredensial dengan konfigurasi MySQL lokal.

7. Jalankan migrasi apabila database belum disiapkan:

```cmd
php artisan migrate
```

8. Bersihkan cache konfigurasi:

```cmd
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

9. Jalankan aplikasi:

```cmd
php artisan serve
```

10. Buka aplikasi melalui browser:

```text
http://127.0.0.1:8000
```

---

## 4. Integrasi Laravel dengan Raspberry Pi Pico WH

### 4.1 Konfigurasi Access Point

Raspberry Pi Pico WH saat ini dikonfigurasi sebagai Access Point (AP), sehingga perangkat dapat membentuk jaringan Wi-Fi sendiri.

Konfigurasi jaringan:

| Konfigurasi | Nilai |
|---|---|
| Nama Wi-Fi (SSID) | PicoWH_kel2 |
| Password | kelompok2 |
| IP Address Pico WH | 192.168.4.1 |
