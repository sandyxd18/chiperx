# 🔒 Projek CLO2 - Keamanan Sistem Informasi

Web application sederhana dengan HTML, PHP, dan MySQL yang mengimplementasikan keamanan berlapis, berjalan di atas Docker containers.

## 📋 Daftar Isi

- [Arsitektur](#arsitektur)
- [Fitur Keamanan](#fitur-keamanan)
- [Cara Menjalankan](#cara-menjalankan)
- [Perbandingan Branch](#perbandingan-branch)
- [Struktur File](#struktur-file)

## 🏗️ Arsitektur

```
┌─────────────────────────────────────────────────────┐
│           Docker Network: kamsis-net                 │
│                172.20.0.0/16                        │
│                                                      │
│  ┌──────────────────────┐  ┌──────────────────────┐ │
│  │   web-server          │  │   db-server           │ │
│  │   172.20.0.10         │  │   172.20.0.20         │ │
│  │                       │  │                       │ │
│  │  Apache + SSL/TLS     │  │  MySQL 8.0            │ │
│  │  PHP 8.2              │──│  Port 3306 (internal) │ │
│  │  fail2ban             │  │                       │ │
│  │  Port 443 (HTTPS)     │  │                       │ │
│  └──────────────────────┘  └──────────────────────┘ │
└─────────────────────────────────────────────────────┘
         ▲
         │ HTTPS (port 443)
    ┌────┴─────┐
    │ Browser  │
    └──────────┘
```

## 🛡️ Fitur Keamanan (Branch `dengan-keamanan`)

### 1. HTTPS (SSL/TLS)
- Self-signed SSL certificate
- Redirect otomatis HTTP → HTTPS
- Security headers (HSTS, X-Frame-Options, CSP)

### 2. Password Salting & Hashing
- **Client-side**: SHA-256 hash sebelum dikirim (via Web Crypto API)
- **Server-side**: bcrypt dengan auto salt (`password_hash()`)
- Cost factor: 12

### 3. SQL Injection Prevention
- **Prepared Statements** (PDO) untuk semua query
- **Input Validation**: batasan panjang dan filter karakter SQL
- Karakter yang diblokir: UNION, SELECT, DROP, DELETE, INSERT, UPDATE, --, /*, dll

### 4. XSS Prevention
- `htmlspecialchars()` pada semua output HTML
- Content-Security-Policy header

### 5. Buffer Overflow Prevention
- Batasan panjang input (username: 50, password: 128, komentar: 500)
- Null byte detection
- HTML maxlength attribute

### 6. Brute Force Protection
| Percobaan Gagal | Aksi |
|-----------------|------|
| 1 - 5 | Boleh coba lagi |
| 6 - 10 | Cooldown 10 menit |
| 11 - 15 | Cooldown 15 menit |
| 16 - 20 | Cooldown 30 menit |
| > 20 | IP diblokir oleh fail2ban (1 jam) |

### 7. fail2ban
- Monitor file log login gagal
- Ban IP selama 1 jam setelah melebihi batas
- Filter regex custom untuk format log PHP

## 🚀 Cara Menjalankan

### Prasyarat
- Docker & Docker Compose

### Langkah

```bash
# Clone repository
git clone <repo-url>
cd Projek-CLO2

# Pilih branch
git checkout dengan-keamanan    # Versi aman
# atau
git checkout tanpa-keamanan     # Versi tanpa keamanan

# Jalankan
docker compose up -d --build

# Cek status
docker compose ps
```

### Akses Web
- **Branch `dengan-keamanan`**: https://localhost (accept self-signed cert)
- **Branch `tanpa-keamanan`**: http://localhost

### Default User (Branch tanpa-keamanan saja)
- Username: `admin`
- Password: `admin123`

## 🔄 Perbandingan Branch

| Aspek | `tanpa-keamanan` | `dengan-keamanan` |
|-------|:----------------:|:-----------------:|
| Protokol | HTTP | HTTPS (SSL/TLS) |
| Password Storage | MD5 | bcrypt + salt |
| Password Transmisi | Plain text | SHA-256 hash |
| SQL Query | String concatenation | Prepared Statements |
| Input Validation | ❌ Tidak ada | ✅ Panjang + filter |
| Output Encoding | ❌ echo langsung | ✅ htmlspecialchars() |
| Brute Force | ❌ Tidak ada | ✅ Cooldown bertingkat |
| IP Blocking | ❌ Tidak ada | ✅ fail2ban |
| Docker Network | Default | Custom (172.20.0.0/16) |
| Container IP | Dynamic | Static |

## 📁 Struktur File

```
Projek-CLO2/
├── docker-compose.yml
├── web/
│   ├── Dockerfile
│   ├── apache-ssl.conf          # (hanya di dengan-keamanan)
│   ├── php.ini                  # (hanya di dengan-keamanan)
│   ├── entrypoint.sh            # (hanya di dengan-keamanan)
│   ├── fail2ban/                # (hanya di dengan-keamanan)
│   │   ├── jail.local
│   │   └── filter.d/
│   │       └── php-login.conf
│   └── src/
│       ├── config.php
│       ├── functions.php        # (hanya di dengan-keamanan)
│       ├── index.php
│       ├── login.php
│       ├── register.php
│       ├── comment.php
│       ├── dashboard.php
│       ├── logout.php
│       └── assets/
│           └── style.css
├── db/
│   ├── Dockerfile
│   └── init.sql
├── .gitignore
└── README.md
```
