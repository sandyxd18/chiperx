# 🔐 CipherX — Aplikasi Kriptografi Dasar dengan Flask

Aplikasi web sederhana berbasis **Python Flask** untuk mendemonstrasikan algoritma kriptografi klasik secara manual, tanpa menggunakan library kriptografi eksternal.

Dibuat sebagai tugas mata kuliah **Keamanan Sistem**.

---

## ✨ Fitur

- 🔒 **Enkripsi & Dekripsi** teks menggunakan dua algoritma klasik
- 📋 Menampilkan **plaintext**, **ciphertext**, dan **key** yang digunakan
- 🖥️ Antarmuka web sederhana dan responsif
- 📖 Panel referensi algoritma langsung di halaman
- ⚙️ Konfigurasi server melalui **environment variable** (`.env`)

---

## 🧮 Algoritma yang Diimplementasikan

### 1. Caesar Cipher
Algoritma substitusi klasik yang menggeser setiap huruf alfabet sejauh `k` posisi.

| | Rumus |
|---|---|
| **Enkripsi** | `C = (P + k) mod 26` |
| **Dekripsi** | `P = (C - k + 26) mod 26` |

**Karakteristik:**
- Hanya menggeser huruf alfabet (A–Z, a–z)
- Mempertahankan huruf kapital dan huruf kecil
- Spasi dan karakter non-huruf tidak berubah

### 2. Rail Fence Cipher
Algoritma transposisi yang menuliskan teks secara zig-zag melewati sejumlah `n` rel, lalu membaca baris per baris.

**Contoh** (`HELLO WORLD`, 3 rail):
```
Rail 0: H . . . O . . . L .
Rail 1: . E . L . W . R . D
Rail 2: . . L . . . O . . .

Ciphertext: "HOREL OLLWD"
```

---

## 🗂️ Struktur Project

```
project/
│
├── app.py              # Flask app — routing & helper functions
├── crypto.py           # Implementasi manual algoritma kriptografi
├── requirements.txt    # Daftar dependensi Python
├── .env.example        # Template .env untuk referensi
├── .gitignore          # File yang dikecualikan dari Git
├── templates/
│   └── index.html      # Template HTML (Jinja2)
└── static/
    └── style.css       # Stylesheet
```

### Penjelasan File

| File | Fungsi |
|---|---|
| `app.py` | Entry point Flask, route `/`, membaca config dari env, memanggil `crypto.py` |
| `crypto.py` | Implementasi murni Caesar Cipher dan Rail Fence Cipher tanpa library kriptografi |
| `requirements.txt` | Daftar package Python yang dibutuhkan |
| `.env.example` | Template `.env` yang aman untuk di-commit sebagai referensi |
| `templates/index.html` | Halaman web dengan form input dan tampilan hasil |
| `static/style.css` | Styling antarmuka |

---

## 🚀 Cara Menjalankan

### 1. Clone repository

```bash
git clone https://github.com/sandyxd18/chiperx.git
cd chiperx
```

### 2. Buat virtual environment

```bash
python -m venv venv
```

Aktifkan virtual environment:

```bash
# Windows
venv\Scripts\activate

# Mac / Linux
source venv/bin/activate
```

### 3. Install dependensi

```bash
pip install -r requirements.txt
```

### 4. Buat file `.env`

Salin template `.env.example` menjadi `.env`:

```bash
# Mac / Linux
cp .env.example .env

# Windows
copy .env.example .env
```

Isi file `.env` sesuai kebutuhan:

```env
FLASK_DEBUG=true
FLASK_HOST=0.0.0.0
FLASK_PORT=5000
```

| Variable | Keterangan | Default |
|---|---|---|
| `FLASK_DEBUG` | Mode debug (`true` / `false`) | `false` |
| `FLASK_HOST` | Host server (`0.0.0.0` untuk semua interface, `127.0.0.1` untuk lokal) | `127.0.0.1` |
| `FLASK_PORT` | Port server | `5000` |

### 5. Jalankan aplikasi

```bash
python app.py
```

### 6. Buka di browser

```
http://127.0.0.1:5000
```

> Jika `FLASK_HOST=0.0.0.0`, aplikasi dapat diakses dari perangkat lain dalam jaringan yang sama melalui IP lokal mesin Anda.

---

## 📦 Dependensi

| Package | Versi | Kegunaan |
|---|---|---|
| `Flask` | 3.1.0 | Web framework |
| `python-dotenv` | 1.0.1 | Membaca konfigurasi dari file `.env` |

**Tidak menggunakan** library kriptografi seperti `cryptography`, `pycrypto`, `hashlib`, atau sejenisnya. Seluruh logika enkripsi/dekripsi ditulis manual di `crypto.py`.

---

## 🔧 Cara Penggunaan

`crypto.py` dapat digunakan dengan **dua cara** yang berbeda, keduanya independen satu sama lain.

---

### 1. Mode Web (via `app.py`)

Jalankan server Flask lalu buka browser:

```bash
python app.py
# Buka http://127.0.0.1:5000
```

Langkah penggunaan di halaman web:

1. Pilih **algoritma** — Caesar Cipher atau Rail Fence Cipher
2. Pilih **mode** — Enkripsi atau Dekripsi
3. Masukkan **teks** (plaintext untuk enkripsi, ciphertext untuk dekripsi)
4. Masukkan **key**:
   - Caesar Cipher → integer bebas (contoh: `3`)
   - Rail Fence Cipher → integer minimal `2` (contoh: `3`)
5. Klik tombol **Proses**
6. Hasil ditampilkan: plaintext, ciphertext, dan key

---

### 2. Mode CLI (langsung via `crypto.py`)

`crypto.py` dapat dijalankan **langsung dari terminal** tanpa perlu menjalankan Flask sama sekali. Tidak memerlukan dependensi apapun — cukup Python.

```bash
python crypto.py
```

Akan muncul menu interaktif di terminal:

```
================================================
   CIPHER LAB — Mode CLI
   Caesar Cipher & Rail Fence Cipher
================================================

  Pilih algoritma:
    1. Caesar Cipher
    2. Rail Fence Cipher
    3. Keluar
  Pilih [1-3]:
```

Ikuti prompt yang muncul — pilih algoritma, pilih mode (enkripsi/dekripsi), masukkan teks, lalu masukkan key. Hasil akan langsung ditampilkan:

```
------------------------------------------------
  Algoritma  : Caesar Cipher
  Mode       : Enkripsi
  Key        : 3
  Plaintext  : Hello World
  Ciphertext : Khoor Zruog
------------------------------------------------
```

Setelah selesai, program akan menanyakan apakah ingin melanjutkan proses lagi atau keluar.

> **Catatan:** Blok CLI (`if __name__ == '__main__'`) hanya berjalan saat file dieksekusi langsung. Saat `crypto.py` di-import oleh `app.py`, blok ini diabaikan secara otomatis oleh Python.

---

### 3. Mode Import (digunakan di script Python lain)

Fungsi-fungsi di `crypto.py` juga dapat diimpor dan dipanggil langsung dari script Python lain:

```python
from crypto import caesar_encrypt, caesar_decrypt
from crypto import rail_fence_encrypt, rail_fence_decrypt

# Caesar Cipher
ciphertext = caesar_encrypt("Hello World", 3)   # → "Khoor Zruog"
plaintext  = caesar_decrypt("Khoor Zruog", 3)   # → "Hello World"

# Rail Fence Cipher
ciphertext = rail_fence_encrypt("HELLO WORLD", 3)  # → "HOREL OLLWD"
plaintext  = rail_fence_decrypt("HOREL OLLWD", 3)  # → "HELLO WORLD"
```

---

## 💡 Contoh

### Caesar Cipher — Enkripsi
| Field | Value |
|---|---|
| Algoritma | Caesar Cipher |
| Mode | Enkripsi |
| Plaintext | `Hello World` |
| Key | `3` |
| **Ciphertext** | `Khoor Zruog` |

### Caesar Cipher — Dekripsi
| Field | Value |
|---|---|
| Algoritma | Caesar Cipher |
| Mode | Dekripsi |
| Ciphertext | `Khoor Zruog` |
| Key | `3` |
| **Plaintext** | `Hello World` |

### Rail Fence Cipher — Enkripsi
| Field | Value |
|---|---|
| Algoritma | Rail Fence Cipher |
| Mode | Enkripsi |
| Plaintext | `HELLO WORLD` |
| Key (rail) | `3` |
| **Ciphertext** | `HOREL OLLWD` |

### Rail Fence Cipher — Dekripsi
| Field | Value |
|---|---|
| Algoritma | Rail Fence Cipher |
| Mode | Dekripsi |
| Ciphertext | `HOREL OLLWD` |
| Key (rail) | `3` |
| **Plaintext** | `HELLO WORLD` |

---

## 👤 Author

Dibuat untuk tugas kuliah Keamanan Sistem.