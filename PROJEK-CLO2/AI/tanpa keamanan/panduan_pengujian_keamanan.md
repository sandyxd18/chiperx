# Panduan Pengujian Fitur Keamanan

Dokumen ini berisi panduan dan instruksi langkah demi langkah untuk melakukan pengetesan terhadap fitur-fitur keamanan yang telah diimplementasikan pada aplikasi ini. Pastikan Anda melakukan pengujian ini pada lingkungan *development* atau lingkungan tes, dan **bukan** di *production* yang sedang digunakan.

---

## 1. Pengujian HTTPS (SSL/TLS)
**Tujuan:** Memastikan komunikasi antara klien dan server dienkripsi dengan aman untuk mencegah *eavesdropping* atau *man-in-the-middle attack*.

**Langkah Pengujian:**
1. Buka web browser.
2. Akses aplikasi menggunakan protokol HTTP (contoh: `http://localhost` atau `http://<IP-SERVER>`).
   - **Ekspektasi:** Web server (Apache/Nginx) secara otomatis mengalihkan (redirect) permintaan tersebut ke `https://...`.
3. Akses aplikasi langsung menggunakan protokol HTTPS.
   - **Ekspektasi:** Halaman web berhasil dimuat. (Jika menggunakan *Self-Signed Certificate*, browser mungkin akan menampilkan peringatan "Not Secure". Lanjutkan dengan memilih "Accept Risk / Proceed").
4. Klik ikon gembok di sebelah URL (Address Bar) browser.
   - **Ekspektasi:** Anda dapat melihat detail Sertifikat SSL yang digunakan, memastikan koneksi terenkripsi.

---

## 2. Pengujian Password Salting & Hashing
**Tujuan:** Memastikan kata sandi pengguna tidak disimpan dalam bentuk *plaintext* (teks terang) di dalam database.

**Langkah Pengujian:**
1. Lakukan registrasi pengguna baru pada aplikasi, misalnya dengan username `tester1` dan password `P@ssw0rd123`.
2. Lakukan registrasi pengguna kedua dengan username `tester2` dan password yang **sama persis** yaitu `P@ssw0rd123`.
3. Masuk ke console database MySQL/MariaDB (misalnya dengan `docker exec -it <container_db> mysql -u kamsis_user -p kamsis_db`).
4. Jalankan query: `SELECT username, password_hash FROM users;`
   - **Ekspektasi 1:** Kolom `username` tidak menampilkan tulisan `tester1` maupun `tester2`, melainkan string hash (SHA-256) panjang yang menyamarkan username aslinya.
   - **Ekspektasi 2:** Kolom `password_hash` tidak menampilkan tulisan `P@ssw0rd123`, melainkan string hash panjang (contohnya diawali dengan `$2y$` menggunakan `password_hash()` PHP dengan algoritma bcrypt).
   - **Ekspektasi 3:** String hash `password_hash` untuk kedua pengguna tersebut harus **berbeda**, walaupun password aslinya sama. Hal ini membuktikan bahwa fitur *salting* otomatis dari algoritma hash berfungsi dengan baik.

---

## 3. Pengujian Pencegahan SQL Injection (Prepared Statements)
**Tujuan:** Memastikan bahwa manipulasi query database melalui input pengguna berhasil digagalkan.

**Langkah Pengujian:**
1. Buka halaman Login aplikasi.
2. Pada form input **Username**, masukkan payload klasik SQL Injection berikut:
   `admin' OR '1'='1`
3. Pada form input **Password**, isikan bebas (contoh: `bebas123`).
4. Klik tombol untuk Login.
   - **Ekspektasi:** Login harus **gagal** (biasanya muncul pesan "Username/Password salah"). Aplikasi tidak boleh memberikan akses login sebagai admin, dan aplikasi tidak boleh memunculkan pesan *error syntax* dari database di layar.
5. Cobalah hal serupa pada form pencarian (jika ada) dengan memasukkan tanda kutip `'` atau `"`.
   - **Ekspektasi:** Pencarian ditangani secara normal (mencari literal karakter kutip) dan tidak menyebabkan aplikasi menjadi error/crash.

---

## 4. Pengujian Pencegahan XSS (Cross-Site Scripting)
**Tujuan:** Memastikan input pengguna yang mengandung script berbahaya tidak dirender/dieksekusi oleh browser.

**Langkah Pengujian:**
1. Cari fitur di aplikasi yang menerima input dari Anda dan akan menampilkannya kembali ke layar (misalnya: form Profil, Buku Tamu, Komentar, atau nama barang).
2. Masukkan payload XSS berupa script alert ke dalam input tersebut:
   `<script>alert('Celah XSS ditemukan!')</script>`
3. Simpan/Submit inputan tersebut.
4. Pergi ke halaman di mana inputan tadi ditampilkan.
   - **Ekspektasi:** Browser **TIDAK BOLEH** memunculkan kotak *pop-up* (alert). Teks `<script>alert('Celah XSS ditemukan!')</script>` hanya akan ditampilkan sebagai teks biasa di layar (karena karakter khusus telah di-escape menggunakan fungsi seperti `htmlspecialchars()`).

---

## 5. Pengujian Pencegahan Buffer Overflow
**Tujuan:** Mencegah input yang terlalu panjang membebani memori, memicu *crash*, atau perilaku tak terduga dari aplikasi/server.

**Langkah Pengujian:**
1. Gunakan tool seperti Postman, Burp Suite, atau script Python/cURL untuk melakukan request.
2. Buat sebuah request HTTP POST (misal ke endpoint Login atau Register) dengan memberikan input yang tidak wajar besarnya. Contoh, username yang diisi dengan 50.000 karakter huruf "A".
3. Kirim *request* tersebut ke server.
   - **Ekspektasi:** Aplikasi web tetap berjalan normal. Web server mungkin akan merespons dengan HTTP Error `413 Payload Too Large` jika ditolak di tingkat server, atau aplikasi web akan menolak input tersebut dengan pesan validasi "Username terlalu panjang" tanpa menyebabkan layanan berhenti (*crash*).

---

## 6. Pengujian Perlindungan Brute Force (Level Aplikasi)
**Tujuan:** Memastikan mekanisme internal aplikasi mampu membatasi percobaan login yang salah (penerapan *cooldown*).

**Langkah Pengujian:**
1. Buka halaman Login aplikasi.
2. Cobalah untuk melakukan login dengan akun yang valid, namun menggunakan **password yang salah** secara terus-menerus (misalnya sebanyak 5 kali berturut-turut dengan cepat).
   - **Ekspektasi:** Pada percobaan yang melebihi batas, aplikasi harus menolak *request* login (bahkan jika percobaan berikutnya menggunakan password yang benar).
3. Aplikasi akan menampilkan pesan seperti "Terlalu banyak percobaan gagal. Silakan coba lagi dalam X menit".
4. Tunggu waktu *cooldown* sesuai batas waktu yang telah diatur (misal: 3-5 menit).
5. Lakukan login kembali dengan kredensial yang **benar**.
   - **Ekspektasi:** Anda berhasil masuk ke dalam sistem.

---

## 7. Pengujian Fail2ban (Level Sistem/Jaringan)
**Tujuan:** Memastikan Fail2ban berfungsi memantau log aplikasi web/server dan memblokir IP Address penyerang secara otomatis menggunakan firewall.

**Penting:** Lakukan ini dari IP Address sekunder (misalnya dari jaringan seluler, VPN, atau mesin VM yang berbeda) agar IP utama Anda tidak terblokir!

**Langkah Pengujian:**
1. Dari mesin penyerang (IP Beda), lakukan *spamming* percobaan *request* yang tidak sah. Misalnya mencoba login gagal terus-menerus melebihi nilai `maxretry` di konfigurasi `jail.local` dari Fail2ban (misalnya 5-10 kali).
2. Setelah melewati batas tersebut, cobalah buka kembali halaman web atau *refresh* browser dari mesin penyerang.
   - **Ekspektasi:** Akses ke web server akan ditolak sepenuhnya (*Connection Refused* atau *Timeout*). Hal ini karena Firewall sudah men-drop koneksi dari IP tersebut.
3. Dari **mesin utama Anda** (yang aman dan tidak terblokir), masuk ke server/terminal.
4. Cek status `fail2ban` untuk *jail* yang bertugas mengamankan web (contohnya mungkin dinamakan `nginx-limit-req`, `apache-auth`, atau jail kustom untuk aplikasi PHP Anda):
   `sudo fail2ban-client status <nama-jail>`
   - **Ekspektasi:** Anda akan melihat IP penyerang terdaftar di baris `Banned IP list`.
5. (Opsional) Untuk membuka blokir (unban) IP tersebut:
   `sudo fail2ban-client set <nama-jail> unbanip <IP-Penyerang>`

---

## 8. Verifikasi Client-Side Hashing via Wireshark (Packet Sniffing)
**Tujuan:** Membuktikan secara *low-level* pada antarmuka jaringan bahwa password asli (plaintext) tidak pernah dikirimkan ke server karena sudah di-hash secara lokal oleh browser (JavaScript Web Crypto API).

**Catatan Penting:** Karena aplikasi ini sudah didukung HTTPS, secara standar semua lalu lintas dienkripsi oleh TLS. Jika Anda melakukan pengujian ini melalui `https://`, Wireshark hanya akan menampilkan paket *Application Data* yang dienkripsi secara acak. Untuk benar-benar memverifikasi bahwa *browser* mengirimkan hash (dan bukan web server yang menutupi jejaknya), uji menggunakan akses **HTTP biasa** (jika memungkinkan/di-bypass), atau lakukan intersepsi sebelum enkripsi SSL. Panduan di bawah ini berasumsi Anda menguji jalur HTTP agar payload dapat terbaca di Wireshark.

**Langkah Pengujian:**
1. Buka aplikasi **Wireshark** di komputer host Anda (pastikan dengan hak akses `sudo` atau Administrator agar dapat menangkap paket).
2. Pada daftar antarmuka jaringan (Network Interfaces), cari interface bridge milik Docker. Biasanya bernama `docker0` atau diawali dengan `br-` (misal `br-a1b2c3d4`). Anda juga bisa melihat trafik yang aktif (grafik naik turun) saat Anda mengakses kontainer. Pilih interface tersebut lalu klik tombol **Start Capture** (ikon sirip hiu biru).
3. Buka browser Anda, dan arahkan ke halaman **Register** (usahakan melalui `http://` untuk melihat struktur data mentahnya).
4. Isikan **Username**, **Password** (misal `TesSandi123!`), dan **Konfirmasi Password**. Lalu klik **Daftar**.
5. Segera kembali ke jendela Wireshark, dan klik tombol **Stop Capture** (ikon kotak merah) agar paket tidak menumpuk terlalu banyak.
6. Sekarang kita akan mencari paket pendaftaran tersebut. Di kolom **Filter** di bagian atas Wireshark, ketikkan:
   `http.request.method == "POST"`
   Lalu tekan Enter.
7. Wireshark akan menyaring trafik dan hanya menampilkan permintaan POST. Temukan paket yang mengarah ke endpoint registrasi (contoh Info: `POST /register.php HTTP/1.1`).
8. **Klik kanan** pada baris paket tersebut, lalu arahkan ke **Follow** -> klik **HTTP Stream** (atau bisa juga **TCP Stream**).
9. Sebuah jendela baru akan terbuka menampilkan isi percakapan antara browser Anda (klien) dan web server.
   - **Ekspektasi:** Fokuslah pada bagian paling bawah teks berwarna merah muda (data payload yang Anda kirim ke server).
   - Anda **TIDAK AKAN** melihat teks `password=TesSandi123!`.
   - Sebaliknya, Anda akan melihat parameter form seperti ini:
     `username=namauser&password=9b72...&confirm_password=9b72...`
   - String `9b72...` tersebut adalah representasi *hash SHA-256* (berupa 64 karakter heksadesimal acak) dari password Anda. Hal ini mengkonfirmasi bahwa lapisan keamanan *client-side hashing* sukses mencegah password bocor saat transit, bahkan jika jalur jaringannya sedang disadap.
