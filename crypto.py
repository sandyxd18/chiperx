# ============================================================
# crypto.py — Implementasi Manual Algoritma Kriptografi
# ============================================================


# ============================================================
# CAESAR CIPHER
# ============================================================

def caesar_encrypt(plaintext: str, key: int) -> str:
    result = []
    key = key % 26

    for char in plaintext:
        if char.isalpha():
            base = ord('A') if char.isupper() else ord('a')
            shifted = (ord(char) - base + key) % 26
            result.append(chr(base + shifted))
        else:
            result.append(char)

    return ''.join(result)


def caesar_decrypt(ciphertext: str, key: int) -> str:
    result = []
    key = key % 26

    for char in ciphertext:
        if char.isalpha():
            base = ord('A') if char.isupper() else ord('a')
            shifted = (ord(char) - base - key + 26) % 26
            result.append(chr(base + shifted))
        else:
            result.append(char)

    return ''.join(result)


# ============================================================
# RAIL FENCE CIPHER
# ============================================================

def rail_fence_encrypt(plaintext: str, num_rails: int) -> str:
    if num_rails < 2:
        return plaintext  # 1 rel berarti tidak ada perubahan

    rails = [[] for _ in range(num_rails)]

    current_rail = 0
    going_down = True  # Arah pergerakan: True = turun, False = naik

    for char in plaintext:
        rails[current_rail].append(char)

        if current_rail == num_rails - 1:
            going_down = False
        elif current_rail == 0:
            going_down = True

        current_rail += 1 if going_down else -1

    return ''.join(''.join(rail) for rail in rails)


def rail_fence_decrypt(ciphertext: str, num_rails: int) -> str:
    if num_rails < 2:
        return ciphertext

    n = len(ciphertext)

    # Langkah 1: Buat array pola zig-zag
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
    rail_indices = [0] * num_rails
    result = []

    for r in pattern:
        result.append(rails[r][rail_indices[r]])
        rail_indices[r] += 1

    return ''.join(result)


# ============================================================
# DEMO / CLI — Dijalankan langsung: python crypto.py
# ============================================================

if __name__ == '__main__':

    DIVIDER = '-' * 48

    def print_result(algo, mode, plaintext, ciphertext, key):
        print(DIVIDER)
        print(f"  Algoritma  : {algo}")
        print(f"  Mode       : {mode}")
        print(f"  Key        : {key}")
        print(f"  Plaintext  : {plaintext}")
        print(f"  Ciphertext : {ciphertext}")
        print(DIVIDER)

    def get_int_input(prompt, min_val=None):
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

    print("\n" + "=" * 48)
    print("   CIPHER LAB — Mode CLI")
    print("   Caesar Cipher & Rail Fence Cipher")
    print("=" * 48)

    while True:
        algo_pilihan = menu_pilihan(
            "Pilih algoritma",
            ["Caesar Cipher", "Rail Fence Cipher", "Keluar"]
        )

        if algo_pilihan == 3:
            print("\n  Sampai jumpa!\n")
            break

        mode_pilihan = menu_pilihan("Pilih mode", ["Enkripsi", "Dekripsi"])

        print()
        label_teks = "  Masukkan plaintext  : " if mode_pilihan == 1 else "  Masukkan ciphertext : "
        teks = input(label_teks).strip()
        if not teks:
            print("  [!] Teks tidak boleh kosong.\n")
            continue

        if algo_pilihan == 1:
            key = get_int_input("  Masukkan key (geser huruf, integer): ")
            algo_nama = "Caesar Cipher"

            if mode_pilihan == 1:
                hasil = caesar_encrypt(teks, key)
                print_result(algo_nama, "Enkripsi", teks, hasil, key)
            else:
                hasil = caesar_decrypt(teks, key)
                print_result(algo_nama, "Dekripsi", hasil, teks, key)

        else:
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