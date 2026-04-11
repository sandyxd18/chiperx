import os
import sys

BLOCK_SIZE = 8  # Ukuran blok dalam byte

def split_block(data, block_size=BLOCK_SIZE):
    padding_len = block_size - (len(data) % block_size)
    data = data + bytes([padding_len] * padding_len)

    blocks = []
    for i in range(0, len(data), block_size):
        blocks.append(bytearray(data[i:i + block_size]))
    return blocks


def remove_padding(data):
    if len(data) == 0:
        return data
    padding_len = data[-1]
    if padding_len < 1 or padding_len > BLOCK_SIZE:
        return bytes(data)
    if all(b == padding_len for b in data[-padding_len:]):
        return bytes(data[:-padding_len])
    return bytes(data)


def prepare_key(key, block_size=BLOCK_SIZE):
    if isinstance(key, str):
        key = key.encode('utf-8')
    key = bytearray(key)
    while len(key) < block_size:
        key = key + key
    return bytearray(key[:block_size])


def generate_iv(key):
    if isinstance(key, str):
        key = key.encode('utf-8')
    iv = bytearray(BLOCK_SIZE)
    key_bytes = prepare_key(key, BLOCK_SIZE)
    for i in range(BLOCK_SIZE):
        iv[i] = (key_bytes[i] ^ (i * 37 + 99)) % 256
    return iv

# metode kriptografi
def xor_cipher(block, key_block):
    result = bytearray(len(block))
    for i in range(len(block)):
        result[i] = block[i] ^ key_block[i % len(key_block)]
    return result


def transposition_cipher(block, key_block, encrypt=True):
    n = len(block)
    indices = list(range(n))
    indices.sort(key=lambda x: key_block[x % len(key_block)])
    result = bytearray(n)
    for new_pos, old_pos in enumerate(indices):
        if encrypt:
            result[new_pos] = block[old_pos]
        else:
            result[old_pos] = block[new_pos]
    return result

# mode CBC
def cbc_encrypt(data, key):
    key_block = prepare_key(key)
    iv = generate_iv(key)

    blocks = split_block(data)
    encrypted_blocks = []
    prev = iv  # Blok pertama di-XOR dengan IV

    for block in blocks:
        chained = xor_cipher(block, prev)
        step2 = xor_cipher(chained, key_block)
        step3 = transposition_cipher(step2, key_block, encrypt=True)
        encrypted_blocks.append(step3)
        prev = step3

    return bytes(iv) + b''.join(encrypted_blocks)


def cbc_decrypt(data, key):
    key_block = prepare_key(key)

    iv = bytearray(data[:BLOCK_SIZE])
    cipher_data = data[BLOCK_SIZE:]

    blocks = []
    for i in range(0, len(cipher_data), BLOCK_SIZE):
        blocks.append(bytearray(cipher_data[i:i + BLOCK_SIZE]))

    decrypted_blocks = []
    prev = iv

    for block in blocks:
        cipher_block = bytearray(block)
        step1 = transposition_cipher(block, key_block, encrypt=False)
        step2 = xor_cipher(step1, key_block)
        step3 = xor_cipher(step2, prev)
        decrypted_blocks.append(step3)
        prev = cipher_block

    result = b''.join(decrypted_blocks)
    return remove_padding(result)


# fungsi untuk gambar
def encrypt_image(filepath, key):
    with open(filepath, 'rb') as f:
        image_data = f.read()

    ext = os.path.splitext(filepath)[1].lower()  # misal: .png
    basename = os.path.splitext(os.path.basename(filepath))[0]

    encrypted_data = cbc_encrypt(image_data, key)

    output_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'output')
    os.makedirs(output_dir, exist_ok=True)
    ext_bytes = ext.encode('utf-8')
    original_len = len(image_data)

    output_filename = f"{basename}_encrypted_cbc.enc"
    output_path = os.path.join(output_dir, output_filename)

    with open(output_path, 'wb') as f:
        f.write(len(ext_bytes).to_bytes(4, 'big'))
        f.write(ext_bytes)
        f.write(original_len.to_bytes(4, 'big'))
        f.write(encrypted_data)

    return output_path


def decrypt_image(filepath, key):
    with open(filepath, 'rb') as f:
        ext_len = int.from_bytes(f.read(4), 'big')
        ext = f.read(ext_len).decode('utf-8')
        original_len = int.from_bytes(f.read(4), 'big')
        encrypted_data = f.read()

    decrypted_data = cbc_decrypt(encrypted_data, key)
    decrypted_data = decrypted_data[:original_len]

    output_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'output')
    os.makedirs(output_dir, exist_ok=True)

    basename = os.path.splitext(os.path.basename(filepath))[0]
    basename = basename.replace('_encrypted_cbc', '')
    output_filename = f"{basename}_decrypted_cbc{ext}"
    output_path = os.path.join(output_dir, output_filename)

    with open(output_path, 'wb') as f:
        f.write(decrypted_data)

    return output_path


# fungsi untuk input text
def get_text_input():
    print("\n  Pilih sumber input teks:")
    print("  [1] Input teks (ketik manual)")
    print("  [2] Input dari file .txt")
    choice = input("  Pilihan Anda: ").strip()

    if choice == '1':
        text = input("  Masukkan teks: ").strip()
        if not text:
            print("  Teks tidak boleh kosong!")
            return None
        return text
    elif choice == '2':
        filepath = input("  Masukkan path file .txt: ").strip()
        if not os.path.exists(filepath):
            print("  File tidak ditemukan!")
            return None
        with open(filepath, 'r', encoding='utf-8') as f:
            text = f.read()
        if not text:
            print("  File kosong!")
            return None
        print(f"  Isi file: {text[:100]}{'...' if len(text) > 100 else ''}")
        return text
    else:
        print("  Pilihan tidak valid!")
        return None


# fungsi tampilan
def display_hex(data, label="Data"):
    hex_str = data.hex()
    formatted = ' '.join(hex_str[i:i+2] for i in range(0, len(hex_str), 2))
    print(f"  {label} (hex): {formatted}")


def display_results(plaintext, ciphertext, decrypted, mode_name):
    print(f"\n  {'='*55}")
    print(f"  HASIL {mode_name}")
    print(f"  {'='*55}")

    if isinstance(plaintext, str):
        print(f"  Plaintext      : {plaintext}")
        display_hex(plaintext.encode('utf-8'), "Plaintext")
    else:
        print(f"  Plaintext      : {plaintext.decode('utf-8', errors='replace')}")
        display_hex(plaintext, "Plaintext")

    print(f"  Ciphertext     : {ciphertext.decode('utf-8', errors='replace')}")
    display_hex(ciphertext, "Ciphertext")

    try:
        dec_str = decrypted.decode('utf-8')
        print(f"  Hasil Dekripsi : {dec_str}")
    except UnicodeDecodeError:
        print(f"  Hasil Dekripsi : {decrypted.decode('utf-8', errors='replace')}")
    display_hex(decrypted, "Dekripsi")

    if isinstance(plaintext, str):
        original = plaintext.encode('utf-8')
    else:
        original = plaintext
    if original == decrypted:
        print(f"\n  VERIFIKASI BERHASIL: Plaintext == Hasil Dekripsi")
    else:
        print(f"\n  VERIFIKASI GAGAL: Plaintext != Hasil Dekripsi")

    print(f"  {'='*55}")


# handler

def handle_encrypt_text():
    print(f"\n  ENKRIPSI TEKS (MODE CBC)")
    text = get_text_input()
    if text is None:
        return

    key = input("  Masukkan key enkripsi: ").strip()
    if not key:
        print("Key tidak boleh kosong!")
        return

    print(f"\n  Mode          : CBC (Cipher Block Chaining)")
    print(f"  Metode        : XOR Cipher + Transposition Cipher")
    print(f"  Block Size    : {BLOCK_SIZE} byte")
    print(f"  Key           : {key}")

    plaintext_bytes = text.encode('utf-8')
    ciphertext = cbc_encrypt(plaintext_bytes, key)

    print(f"  HASIL ENKRIPSI CBC")
    print(f"  Plaintext      : {text}")
    print(f"  Ciphertext     : {ciphertext.decode('utf-8', errors='replace')}")
    display_hex(ciphertext, "Ciphertext")
    print(f"  Catatan: 8 byte pertama dari ciphertext adalah IV")


def handle_decrypt_text():
    print(f"\n  DEKRIPSI TEKS (MODE CBC)")

    print("  Masukkan ciphertext (termasuk IV di awal) dalam format hex")
    hex_input = input("  Ciphertext+IV (hex): ").strip().replace(' ', '')

    try:
        ciphertext = bytes.fromhex(hex_input)
    except ValueError:
        print("  Format hex tidak valid!")
        return

    if len(ciphertext) < BLOCK_SIZE:
        print(f"  Ciphertext terlalu pendek (minimal {BLOCK_SIZE} byte untuk IV)")
        return

    key = input("  Masukkan key dekripsi: ").strip()
    if not key:
        print("  Key tidak boleh kosong!")
        return

    print(f"\n  Mode          : CBC (Cipher Block Chaining)")
    print(f"  Metode        : XOR Cipher + Transposition Cipher")
    print(f"  Key           : {key}")

    display_hex(bytearray(ciphertext[:BLOCK_SIZE]), "IV")

    decrypted = cbc_decrypt(ciphertext, key)

    print(f"\n  {'='*55}")
    print(f"  HASIL DEKRIPSI CBC")
    print(f"  {'='*55}")
    display_hex(ciphertext, "Ciphertext+IV")
    try:
        dec_str = decrypted.decode('utf-8')
        print(f"  Hasil Dekripsi : {dec_str}")
    except UnicodeDecodeError:
        print(f"  Hasil Dekripsi : {decrypted.decode('utf-8', errors='replace')}")
    display_hex(decrypted, "Dekripsi")
    print(f"  {'='*55}")


def handle_encrypt_image():
    print(f"\n  ENKRIPSI GAMBAR (MODE CBC)")
    filepath = input("Masukkan path file gambar (png/jpg): ").strip()

    if not os.path.exists(filepath):
        print("File tidak ditemukan!")
        return

    ext = os.path.splitext(filepath)[1].lower()
    if ext not in ['.png', '.jpg', '.jpeg', '.bmp']:
        print("  File bukan format gambar umum, tetap lanjutkan.")

    key = input("Masukkan key enkripsi: ").strip()
    if not key:
        print("Key tidak boleh kosong!")
        return

    print(f"\n  Mengenkripsi gambar dengan mode CBC")
    print(f"  Mode          : CBC (Cipher Block Chaining)")
    print(f"  File input    : {filepath}")
    print(f"  Ukuran file   : {os.path.getsize(filepath)} bytes")
    print(f"  Key           : {key}")

    iv = generate_iv(key)
    display_hex(iv, "IV")

    output_path = encrypt_image(filepath, key)

    print(f"\n  Enkripsi gambar berhasil!")
    print(f"  File output   : {output_path}")
    print(f"  Ukuran output : {os.path.getsize(output_path)} bytes")


def handle_decrypt_image():
    print(f"\n  DEKRIPSI GAMBAR (MODE CBC)")
    filepath = input("Masukkan path file terenkripsi (.enc): ").strip()

    if not os.path.exists(filepath):
        print("File tidak ditemukan!")
        return

    key = input("Masukkan key dekripsi: ").strip()
    if not key:
        print("Key tidak boleh kosong!")
        return

    print(f"\n  Mendekripsi gambar dengan mode CBC")
    print(f"  Mode          : CBC (Cipher Block Chaining)")
    print(f"  File input    : {filepath}")
    print(f"  Key           : {key}")

    output_path = decrypt_image(filepath, key)

    print(f"\n  Dekripsi gambar berhasil!")
    print(f"  File output   : {output_path}")
    print(f"  Ukuran output : {os.path.getsize(output_path)} bytes")


# menu utama
def show_banner():
    print("\n=== PROGRAM BLOCK CIPHER ===")
    print("Enkripsi & Dekripsi - Block Cipher")
    print(f"Mode: CBC (Cipher Block Chaining) | Block Size: {BLOCK_SIZE} Byte")
    print(f"Metode: XOR Cipher + Transposition Cipher")
    print()


def show_menu_operation():
    print("\n--- PILIH OPERASI ---")
    print("[1] Enkripsi")
    print("[2] Dekripsi")
    print("[0] Kembali")
    return input("Pilihan Anda: ").strip()


def show_menu_input_type():
    print("\n--- PILIH JENIS INPUT ---")
    print("[1] Teks")
    print("[2] Gambar (PNG/JPG)")
    print("[0] Kembali")
    return input("Pilihan Anda: ").strip()


def main():
    show_banner()

    while True:
        # langkah 1: pilih operasi
        op_choice = show_menu_operation()

        if op_choice == '0':
            print("\n  Terima kasih! Program selesai.\n")
            break
        elif op_choice not in ['1', '2']:
            print("Pilihan tidak valid!")
            continue

        operation = 'encrypt' if op_choice == '1' else 'decrypt'
        op_name = 'Enkripsi' if operation == 'encrypt' else 'Dekripsi'
        print(f"\n  >> Operasi dipilih: {op_name}")

        # langkah 2: pilih jenis input
        input_choice = show_menu_input_type()

        if input_choice == '0':
            continue  # Kembali ke pilihan operasi
        elif input_choice not in ['1', '2']:
            print("Pilihan tidak valid!")
            continue

        # jalankan operasi
        if input_choice == '1':
            # Teks
            if operation == 'encrypt':
                handle_encrypt_text()
            else:
                handle_decrypt_text()
        elif input_choice == '2':
            # Gambar
            if operation == 'encrypt':
                handle_encrypt_image()
            else:
                handle_decrypt_image()

        input("\n  Tekan Enter untuk kembali ke menu...")

# panggil fungsi main
if __name__ == '__main__':
    main()
