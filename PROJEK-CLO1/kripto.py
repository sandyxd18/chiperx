import os

BLOCK_SIZE = 8
CAESAR_SHIFT = 3
RAIL_COUNT = 3
IV = "KOMPUTER"

PBOX = [2, 0, 3, 7, 4, 1, 6, 5]
PBOX_INV = [1, 5, 0, 2, 4, 7, 6, 3]

def sanitize_input(text):
    return ''.join(c for c in text.upper() if c.isalpha())


def add_padding(text):
    remainder = len(text) % BLOCK_SIZE
    if remainder != 0:
        padding_len = BLOCK_SIZE - remainder
        text += 'X' * padding_len
    return text


def remove_padding(text):
    return text.rstrip('X') if text else text


def split_blocks(text):
    text = add_padding(text)
    return [text[i:i + BLOCK_SIZE] for i in range(0, len(text), BLOCK_SIZE)]


def prepare_key(key):
    key = sanitize_input(key)
    if not key:
        key = 'A'
    while len(key) < BLOCK_SIZE:
        key += key
    return key[:BLOCK_SIZE]


def xor_mod26(block_a, block_b):
    result = ''
    for i in range(len(block_a)):
        a = ord(block_a[i]) - ord('A')
        b = ord(block_b[i % len(block_b)]) - ord('A')
        result += chr((a + b) % 26 + ord('A'))
    return result


def xor_mod26_inverse(block_a, block_b):
    result = ''
    for i in range(len(block_a)):
        x = ord(block_a[i]) - ord('A')
        b = ord(block_b[i % len(block_b)]) - ord('A')
        result += chr((x - b + 26) % 26 + ord('A'))
    return result

def vigenere_encrypt(block, key_block):
    result = ''
    for i in range(len(block)):
        p = ord(block[i]) - ord('A')
        k = ord(key_block[i % len(key_block)]) - ord('A')
        result += chr((p + k) % 26 + ord('A'))
    return result


def vigenere_decrypt(block, key_block):
    result = ''
    for i in range(len(block)):
        c = ord(block[i]) - ord('A')
        k = ord(key_block[i % len(key_block)]) - ord('A')
        result += chr((c - k + 26) % 26 + ord('A'))
    return result

def pbox_permute(block):
    result = [''] * len(block)
    for i in range(len(block)):
        result[i] = block[PBOX[i]]
    return ''.join(result)


def pbox_inverse(block):
    result = [''] * len(block)
    for i in range(len(block)):
        result[i] = block[PBOX_INV[i]]
    return ''.join(result)
    result = [''] * len(block)
    for i in range(len(block)):
        result[i] = block[PBOX[i]]
    return ''.join(result)


def pbox_inverse(block):
    result = [''] * len(block)
    for i in range(len(block)):
        result[i] = block[PBOX_INV[i]]
    return ''.join(result)


def caesar_encrypt(block):
    result = ''
    for c in block:
        val = (ord(c) - ord('A') + CAESAR_SHIFT) % 26
        result += chr(val + ord('A'))
    return result


def caesar_decrypt(block):
    result = ''
    for c in block:
        val = (ord(c) - ord('A') - CAESAR_SHIFT + 26) % 26
        result += chr(val + ord('A'))
    return result

def rail_fence_encrypt(block):
    n = len(block)
    if n == 0:
        return block

    rails = ['' for _ in range(RAIL_COUNT)]
    rail = 0
    direction = 1  # 1 = turun, -1 = naik

    for char in block:
        rails[rail] += char
        if rail == 0:
            direction = 1
        elif rail == RAIL_COUNT - 1:
            direction = -1
        rail += direction

    return ''.join(rails)


def rail_fence_decrypt(block):
    n = len(block)
    if n == 0:
        return block
    rail_lengths = [0] * RAIL_COUNT
    rail = 0
    direction = 1

    for i in range(n):
        rail_lengths[rail] += 1
        if rail == 0:
            direction = 1
        elif rail == RAIL_COUNT - 1:
            direction = -1
        rail += direction

    rails = []
    idx = 0
    for length in rail_lengths:
        rails.append(list(block[idx:idx + length]))
        idx += length

    result = ''
    rail = 0
    direction = 1
    rail_indices = [0] * RAIL_COUNT

    for i in range(n):
        result += rails[rail][rail_indices[rail]]
        rail_indices[rail] += 1
        if rail == 0:
            direction = 1
        elif rail == RAIL_COUNT - 1:
            direction = -1
        rail += direction

    return result

def ecb_encrypt(plaintext, key):
    key_block = prepare_key(key)
    blocks = split_blocks(plaintext)
    ciphertext = ''

    for block in blocks:
        step1 = vigenere_encrypt(block, key_block)
        step2 = pbox_permute(step1)
        ciphertext += step2

    return ciphertext


def ecb_decrypt(ciphertext, key):
    key_block = prepare_key(key)
    blocks = [ciphertext[i:i + BLOCK_SIZE] for i in range(0, len(ciphertext), BLOCK_SIZE)]
    plaintext = ''

    for block in blocks:
        step1 = pbox_inverse(block)
        step2 = vigenere_decrypt(step1, key_block)
        plaintext += step2

    return remove_padding(plaintext)


def cbc_encrypt(plaintext, key):
    blocks = split_blocks(plaintext)
    ciphertext = ''
    prev = IV

    for block in blocks:
        step1 = xor_mod26(block, prev)
        step2 = caesar_encrypt(step1)
        step3 = rail_fence_encrypt(step2)
        ciphertext += step3
        prev = step3

    return ciphertext


def cbc_decrypt(ciphertext, key):
    blocks = [ciphertext[i:i + BLOCK_SIZE] for i in range(0, len(ciphertext), BLOCK_SIZE)]
    plaintext = ''
    prev = IV

    for block in blocks:
        step1 = rail_fence_decrypt(block)
        step2 = caesar_decrypt(step1)
        step3 = xor_mod26_inverse(step2, prev)
        plaintext += step3
        prev = block

    return remove_padding(plaintext)


def text_input():
    print("\n  Pilih sumber input:")
    print("  [1] Ketik manual")
    print("  [2] Baca dari file .txt")
    choice = input("  Pilihan: ").strip()

    if choice == '1':
        text = input("  Masukkan teks: ").strip()
        if not text:
            print("  Teks tidak boleh kosong!")
            return None
        return text
    elif choice == '2':
        filepath = input("  Path file .txt: ").strip()
        if not os.path.exists(filepath):
            print("  File tidak ditemukan!")
            return None
        with open(filepath, 'r', encoding='utf-8') as f:
            text = f.read().strip()
        if not text:
            print("  File kosong!")
            return None
        print(f"  Isi file: {text[:80]}{'...' if len(text) > 80 else ''}")
        return text
    else:
        print("  Pilihan tidak valid!")
        return None


def cipher_input():
    print("\n  Pilih sumber input ciphertext:")
    print("  [1] Ketik manual")
    print("  [2] Baca dari file .txt")
    choice = input("  Pilihan: ").strip()

    if choice == '1':
        text = input("  Ciphertext: ").strip()
        if not text:
            print("  Input tidak boleh kosong!")
            return None
        return text
    elif choice == '2':
        filepath = input("  Path file .txt: ").strip()
        if not os.path.exists(filepath):
            print("  File tidak ditemukan!")
            return None
        with open(filepath, 'r', encoding='utf-8') as f:
            text = f.read().strip()
        if not text:
            print("  File kosong!")
            return None
        print(f"  Isi file: {text[:80]}{'...' if len(text) > 80 else ''}")
        return text
    else:
        print("  Pilihan tidak valid!")
        return None


def handle_encrypt(mode):
    mode_upper = mode.upper()
    print(f"\n  ENKRIPSI TEKS (MODE {mode_upper})")

    raw_text = text_input()
    if raw_text is None:
        return

    plaintext = sanitize_input(raw_text)
    if not plaintext:
        print("  Teks tidak mengandung huruf!")
        return

    key = input("  Masukkan key: ").strip()
    if not key:
        print("  Key tidak boleh kosong!")
        return

    # Info proses
    if mode == 'ecb':
        metode = "Vigenère Cipher + P-Box"
    else:
        metode = f"Caesar Cipher (shift={CAESAR_SHIFT}) + Rail Fence ({RAIL_COUNT} rail)"

    print(f"\n  Mode       : {mode_upper}")
    print(f"  Metode     : {metode}")
    print(f"  Block Size : {BLOCK_SIZE} karakter")
    print(f"  Key        : {prepare_key(key)}")
    if mode == 'cbc':
        print(f"  IV         : {IV}")

    # Proses enkripsi
    if mode == 'ecb':
        ciphertext = ecb_encrypt(plaintext, key)
    else:
        ciphertext = cbc_encrypt(plaintext, key)

    # Tampilkan hasil
    print(f"\n  {'='*55}")
    print(f"  HASIL ENKRIPSI {mode_upper}")
    print(f"  {'='*55}")
    print(f"  Plaintext  : {plaintext}")
    print(f"  Ciphertext : {ciphertext}")
    print(f"  {'='*55}")


def handle_decrypt(mode):
    mode_upper = mode.upper()
    print(f"\n  DEKRIPSI TEKS (MODE {mode_upper})")

    ct = cipher_input()
    if ct is None:
        return

    ct = ''.join(c for c in ct.upper() if c.isalpha())

    if not ct:
        print("  Ciphertext tidak boleh kosong!")
        return

    if len(ct) % BLOCK_SIZE != 0:
        print(f"  Error: Panjang ciphertext harus kelipatan {BLOCK_SIZE}!")
        return

    key = input("  Masukkan key: ").strip()
    if not key:
        print("  Key tidak boleh kosong!")
        return

    if mode == 'ecb':
        metode = "Vigenère Cipher + P-Box"
    else:
        metode = f"Caesar Cipher (shift={CAESAR_SHIFT}) + Rail Fence ({RAIL_COUNT} rail)"

    print(f"\n  Mode       : {mode_upper}")
    print(f"  Metode     : {metode}")
    print(f"  Key        : {prepare_key(key)}")
    if mode == 'cbc':
        print(f"  IV         : {IV}")

    if mode == 'ecb':
        plaintext = ecb_decrypt(ct, key)
    else:
        plaintext = cbc_decrypt(ct, key)
    print(f"\n  {'='*55}")
    print(f"  HASIL DEKRIPSI {mode_upper}")
    print(f"  {'='*55}")
    print(f"  Ciphertext : {ct}")
    print(f"  Plaintext  : {plaintext}")
    print(f"  {'='*55}")


def show_banner():
    print("\n" + "=" * 60)
    print("  PROGRAM BLOCK CIPHER — SIMULASI PEMBELAJARAN")
    print("  Enkripsi & Dekripsi dengan Mode ECB dan CBC")
    print("=" * 60)
    print(f"  Block Size    : {BLOCK_SIZE} karakter")
    print(f"  Alfabet       : A-Z (mod 26)")
    print(f"  ECB           : Vigenère Cipher + P-Box")
    print(f"  CBC           : Caesar (shift={CAESAR_SHIFT}) + Rail Fence ({RAIL_COUNT} rail)")
    print(f"  IV CBC        : {IV}")
    print("=" * 60)


def show_menu_mode():
    print("\n  PILIH MODE")
    print("  [1] ECB (Electronic Codebook)")
    print("  [2] CBC (Cipher Block Chaining)")
    print("  [0] Keluar")
    return input("  Pilihan: ").strip()


def show_menu_operation():
    print("\n  PILIH OPERASI")
    print("  [1] Enkripsi")
    print("  [2] Dekripsi")
    print("  [0] Kembali")
    return input("  Pilihan: ").strip()


def main():
    show_banner()

    while True:
        mode_choice = show_menu_mode()

        if mode_choice == '0':
            print("\n  Terima kasih! Program selesai.\n")
            break
        elif mode_choice == '1':
            mode = 'ecb'
            mode_name = 'ECB (Electronic Codebook)'
        elif mode_choice == '2':
            mode = 'cbc'
            mode_name = 'CBC (Cipher Block Chaining)'
        else:
            print("  Pilihan tidak valid!")
            continue

        print(f"\n  >> Mode: {mode_name}")

        op_choice = show_menu_operation()

        if op_choice == '0':
            continue
        elif op_choice == '1':
            handle_encrypt(mode)
        elif op_choice == '2':
            handle_decrypt(mode)
        else:
            print("  Pilihan tidak valid!")
            continue

        input("\n  Tekan Enter untuk kembali ke menu...")


if __name__ == '__main__':
    main()
