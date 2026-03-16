# ============================================================
# app.py — Aplikasi Flask untuk Demonstrasi Kriptografi Dasar
# ============================================================

import os
from flask import Flask, render_template, request
from dotenv import load_dotenv
from crypto import (
    caesar_encrypt, caesar_decrypt,
    rail_fence_encrypt, rail_fence_decrypt
)

load_dotenv()

app = Flask(__name__)


# ============================================================
# ROUTE UTAMA
# ============================================================

@app.route('/', methods=['GET', 'POST'])
def index():
    """
    Route utama aplikasi.
    - GET  : Tampilkan halaman form kosong.
    - POST : Baca input form, proses kriptografi, tampilkan hasil.
    """

    result = None
    error = None

    form_data = {
        'algorithm': 'caesar',
        'mode': 'encrypt',
        'text': '',
        'key': '',
    }

    if request.method == 'POST':
        # --- Ambil data dari form ---
        algorithm = request.form.get('algorithm', 'caesar')
        mode = request.form.get('mode', 'encrypt')
        text = request.form.get('text', '').strip()
        key_str = request.form.get('key', '').strip()

        form_data = {
            'algorithm': algorithm,
            'mode': mode,
            'text': text,
            'key': key_str,
        }

        if not text:
            error = "Teks input tidak boleh kosong."
        elif not key_str:
            error = "Key tidak boleh kosong."
        else:
            try:
                key = int(key_str)

                if algorithm == 'railfence' and key < 2:
                    error = "Jumlah rail untuk Rail Fence Cipher minimal 2."
                else:
                    output_text = process_cipher(algorithm, mode, text, key)

                    result = build_result(algorithm, mode, text, output_text, key)

            except ValueError:
                error = "Key harus berupa bilangan bulat (integer)."

    return render_template(
        'index.html',
        result=result,
        error=error,
        form_data=form_data
    )


# ============================================================
# HELPER FUNCTIONS
# ============================================================

def process_cipher(algorithm: str, mode: str, text: str, key: int) -> str:
    if algorithm == 'caesar':
        if mode == 'encrypt':
            return caesar_encrypt(text, key)
        else:
            return caesar_decrypt(text, key)

    elif algorithm == 'railfence':
        if mode == 'encrypt':
            return rail_fence_encrypt(text, key)
        else:
            return rail_fence_decrypt(text, key)

    return text


def build_result(algorithm: str, mode: str, input_text: str,
                 output_text: str, key: int) -> dict:
    algo_names = {
        'caesar': 'Caesar Cipher',
        'railfence': 'Rail Fence Cipher'
    }

    if mode == 'encrypt':
        plaintext = input_text
        ciphertext = output_text
    else:
        ciphertext = input_text
        plaintext = output_text

    return {
        'algorithm': algo_names.get(algorithm, algorithm),
        'mode': 'Enkripsi' if mode == 'encrypt' else 'Dekripsi',
        'plaintext': plaintext,
        'ciphertext': ciphertext,
        'key': key,
    }


# ============================================================
# ENTRY POINT
# ============================================================
 
if __name__ == '__main__':
    debug  = os.getenv('FLASK_DEBUG', 'false').lower() == 'true'
    host   = os.getenv('FLASK_HOST', '127.0.0.1')
    port   = int(os.getenv('FLASK_PORT', '5000'))
 
    app.run(debug=debug, host=host, port=port)