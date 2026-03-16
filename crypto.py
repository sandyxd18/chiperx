# ============================================================
# crypto.py — Implementasi Manual Algoritma Kriptografi
# Tanpa library kriptografi eksternal
# ============================================================


# ============================================================
# CAESAR CIPHER
# Algoritma substitusi klasik yang menggeser setiap huruf
# sejauh 'key' posisi dalam alfabet.
# ============================================================

def caesar_encrypt(plaintext: str, key: int) -> str:
    """
    Enkripsi Caesar Cipher.
    Menggeser setiap huruf sebesar 'key' posisi ke kanan dalam alfabet.
    Spasi dan karakter non-huruf dipertahankan apa adanya.
    Huruf kapital tetap kapital, huruf kecil tetap kecil.
    """
    result = []
    # Normalisasi key agar berada di rentang 0-25
    key = key % 26

    for char in plaintext:
        if char.isalpha():
            # Tentukan base ASCII: 'A' (65) untuk kapital, 'a' (97) untuk kecil
            base = ord('A') if char.isupper() else ord('a')
            # Geser karakter: (posisi + key) mod 26, lalu kembalikan ke huruf
            shifted = (ord(char) - base + key) % 26
            result.append(chr(base + shifted))
        else:
            # Karakter bukan huruf (spasi, angka, tanda baca) tidak diubah
            result.append(char)

    return ''.join(result)


def caesar_decrypt(ciphertext: str, key: int) -> str:
    """
    Dekripsi Caesar Cipher.
    Menggeser setiap huruf sebesar 'key' posisi ke kiri (arah kebalikan).
    Ekuivalen dengan enkripsi menggunakan key = 26 - key.
    """
    result = []
    key = key % 26

    for char in ciphertext:
        if char.isalpha():
            base = ord('A') if char.isupper() else ord('a')
            # Geser ke kiri: (posisi - key + 26) mod 26 untuk menghindari nilai negatif
            shifted = (ord(char) - base - key + 26) % 26
            result.append(chr(base + shifted))
        else:
            result.append(char)

    return ''.join(result)


# ============================================================
# RAIL FENCE CIPHER
# Algoritma transposisi yang menuliskan teks secara zig-zag
# melewati sejumlah 'rail' (baris), lalu membaca baris per baris.
# ============================================================

def rail_fence_encrypt(plaintext: str, num_rails: int) -> str:
    """
    Enkripsi Rail Fence Cipher.
    
    Proses:
    1. Buat 'num_rails' buah rel (list kosong).
    2. Tempatkan setiap karakter pada rel sesuai pola zig-zag.
    3. Gabungkan semua rel dari atas ke bawah.

    Contoh plaintext="HELLO WORLD", num_rails=3:
    Rail 0: H . . . O . . . L .
    Rail 1: . E . L . W . R . D
    Rail 2: . . L . . . O . . .
    Ciphertext: "HOL" + "ELWRD" + "LO" = "HOLELWRDLO"
    """
    if num_rails < 2:
        return plaintext  # 1 rel berarti tidak ada perubahan

    # Buat list of list untuk menyimpan karakter di tiap rel
    rails = [[] for _ in range(num_rails)]

    current_rail = 0
    going_down = True  # Arah pergerakan: True = turun, False = naik

    for char in plaintext:
        rails[current_rail].append(char)

        # Balik arah saat mencapai rel teratas atau terbawah
        if current_rail == num_rails - 1:
            going_down = False
        elif current_rail == 0:
            going_down = True

        current_rail += 1 if going_down else -1

    # Gabungkan semua rel menjadi satu string
    return ''.join(''.join(rail) for rail in rails)


def rail_fence_decrypt(ciphertext: str, num_rails: int) -> str:
    """
    Dekripsi Rail Fence Cipher.

    Proses:
    1. Tentukan pola zig-zag untuk mengetahui berapa karakter di tiap rel.
    2. Distribusikan karakter ciphertext ke tiap rel sesuai hitungan tersebut.
    3. Baca karakter mengikuti pola zig-zag untuk mendapatkan plaintext.
    """
    if num_rails < 2:
        return ciphertext

    n = len(ciphertext)

    # Langkah 1: Buat array pola zig-zag (rel mana setiap posisi berada)
    # Contoh: num_rails=3, n=10 → [0,1,2,1,0,1,2,1,0,1]
    pattern = []
    current_rail = 0
    going_down = True

    for i in range(n):
        pattern.append(current_rail)
        if current_rail == num_rails - 1:
            going_down = False
        elif current_rail == 0:
            going_down = True
        current_rail += 1 if going_down else -1

    # Langkah 2: Hitung jumlah karakter di tiap rel
    rail_lengths = [0] * num_rails
    for r in pattern:
        rail_lengths[r] += 1

    # Langkah 3: Potong ciphertext dan distribusikan ke tiap rel
    rails = []
    idx = 0
    for length in rail_lengths:
        rails.append(list(ciphertext[idx:idx + length]))
        idx += length

    # Langkah 4: Baca mengikuti pola zig-zag untuk mendapatkan plaintext
    rail_indices = [0] * num_rails  # Penunjuk posisi baca tiap rel
    result = []

    for r in pattern:
        result.append(rails[r][rail_indices[r]])
        rail_indices[r] += 1

    return ''.join(result)


# ============================================================
# DEMO / CLI — Dijalankan langsung: python crypto.py
# Blok ini TIDAK akan berjalan saat crypto.py di-import oleh app.py
# ============================================================

if __name__ == '__main__':

    DIVIDER = '-' * 48

    def print_result(algo, mode, plaintext, ciphertext, key):
        """Cetak hasil enkripsi/dekripsi ke terminal."""
        print(DIVIDER)
        print(f"  Algoritma  : {algo}")
        print(f"  Mode       : {mode}")
        print(f"  Key        : {key}")
        print(f"  Plaintext  : {plaintext}")
        print(f"  Ciphertext : {ciphertext}")
        print(DIVIDER)

    def get_int_input(prompt, min_val=None):
        """Meminta input integer dari user dengan validasi."""
        while True:
            try:
                value = int(input(prompt))
                if min_val is not None and value < min_val:
                    print(f"  [!] Nilai minimal adalah {min_val}, coba lagi.")
                    continue
                return value
            except ValueError:
                print("  [!] Input harus berupa bilangan bulat, coba lagi.")

    def menu_pilihan(label, opsi):
        """Tampilkan menu bernomor dan kembalikan pilihan user."""
        print(f"\n  {label}:")
        for i, item in enumerate(opsi, 1):
            print(f"    {i}. {item}")
        while True:
            try:
                pilihan = int(input(f"  Pilih [1-{len(opsi)}]: "))
                if 1 <= pilihan <= len(opsi):
                    return pilihan
                print(f"  [!] Masukkan angka antara 1 dan {len(opsi)}.")
            except ValueError:
                print("  [!] Input tidak valid.")

    # --- Header ---
    print("\n" + "=" * 48)
    print("   CIPHER LAB — Mode CLI")
    print("   Caesar Cipher & Rail Fence Cipher")
    print("=" * 48)

    while True:
        # Pilih algoritma
        algo_pilihan = menu_pilihan(
            "Pilih algoritma",
            ["Caesar Cipher", "Rail Fence Cipher", "Keluar"]
        )

        if algo_pilihan == 3:
            print("\n  Sampai jumpa!\n")
            break

        # Pilih mode
        mode_pilihan = menu_pilihan("Pilih mode", ["Enkripsi", "Dekripsi"])

        # Input teks
        print()
        label_teks = "  Masukkan plaintext  : " if mode_pilihan == 1 else "  Masukkan ciphertext : "
        teks = input(label_teks).strip()
        if not teks:
            print("  [!] Teks tidak boleh kosong.\n")
            continue

        # Input key sesuai algoritma
        if algo_pilihan == 1:
            # Caesar Cipher — key bebas (integer)
            key = get_int_input("  Masukkan key (geser huruf, integer): ")
            algo_nama = "Caesar Cipher"

            if mode_pilihan == 1:
                hasil = caesar_encrypt(teks, key)
                print_result(algo_nama, "Enkripsi", teks, hasil, key)
            else:
                hasil = caesar_decrypt(teks, key)
                print_result(algo_nama, "Dekripsi", hasil, teks, key)

        else:
            # Rail Fence Cipher — key minimal 2
            key = get_int_input("  Masukkan jumlah rail (minimal 2): ", min_val=2)
            algo_nama = "Rail Fence Cipher"

            if mode_pilihan == 1:
                hasil = rail_fence_encrypt(teks, key)
                print_result(algo_nama, "Enkripsi", teks, hasil, key)
            else:
                hasil = rail_fence_decrypt(teks, key)
                print_result(algo_nama, "Dekripsi", hasil, teks, key)

        # Tanya lanjut atau tidak
        print()
        lanjut = input("  Proses lagi? (y/n): ").strip().lower()
        if lanjut != 'y':
            print("\n  Sampai jumpa!\n")
            break