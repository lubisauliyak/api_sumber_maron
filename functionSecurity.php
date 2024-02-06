<?php
// Pembangkitan Kunci Enkripsi Dekripsi
const PRIVATE_KEY = 'W15ata4lam5UMB3RM4R0N@K4rang5uk0'; // 32 chars
const PUBLIC_KEY = '4pp3tiket@5umb3rM4r0n:K4rang5uk0'; // 32 chars
const IV = 'T3lk0m23/24@P3n5'; // 16 chars

// Kunci API Global
// const GLOBAL_API_KEY_USER = '54rNNpSTD4A912E23F839538705E055C2550B916635DED61A86DEA4C87FCC4E4D2457168';
// const GLOBAL_API_KEY_ADMIN = '54rNNpSTD10A6595608283314FF534EF2EAEB0CBDACAC10691F1060DEA5FDE1565D869DE';

// Pilihan Metode Enkripsi dan Dekripsi menggunakan AES 256 bit mode CBC
const METHOD = 'aes-256-cbc';

// Dekripsi request menggunakan library OPENSSL
function decrypJson($text)
{
    return openssl_decrypt($text, METHOD, PUBLIC_KEY, 0, IV);
}

// Dekripsi ciphertext menggunakan library OPENSSL
function decryp($text)
{
    return openssl_decrypt($text, METHOD, PRIVATE_KEY, 0, IV);
}

// Enkripsi plaintext menggunakan library OPENSSL
function encryp($text)
{
    return openssl_encrypt($text, METHOD, PRIVATE_KEY, 0, IV);
}

// Digest text menggunakan library HASH
function digest($text)
{
    return hash('sha256', $text);
}

// Order ID
function order_et($tanggalBooking, $jumlahTiket, $totalBayar, $idTiket)
{
    $orderId = 'SM-' . date("dmY", strtotime($tanggalBooking)) . '-' . $jumlahTiket . '-' . $totalBayar . '-' . $idTiket;
    return $orderId;
}

// Order ID TL
function order_tl($tanggalBooking, $jumlahTiket, $totalBayar, $idTiket)
{
    $orderId = 'TL-' . date("dmY", strtotime($tanggalBooking)) . '-' . $jumlahTiket . '-' . $totalBayar . '-' . $idTiket;
    return $orderId;
}

// Encrypted QR Code
function encrypted_qr($text)
{
    $kodeQr = encryp($text) . 'k/==//5' . digest($text);
    return $kodeQr;
}

// Decrypted QR Code
function decrypted_qr($text)
{
    $splitText = explode('k/==//5', $text);
    $cipherText = $splitText[0];
    $hashAksen = $splitText[1];

    $plainText = decryp($cipherText);
    $hash = digest($plainText);
    if ($hash == $hashAksen) {
        $kodeEt = explode('-', $plainText);
        $resItemTiket['kodeTiket'] = $kodeEt[0];
        $resItemTiket['tanggalBooking'] = substr($kodeEt[1], 4, 4) . '-' . substr($kodeEt[1], 2, 2) . '-' . substr($kodeEt[1], 0, 2);;
        $resItemTiket['jumlahTiket'] = $kodeEt[2];
        $resItemTiket['totalBayar'] = $kodeEt[3];
        $resItemTiket['idTiket'] = $kodeEt[4];
        $resItemTiket['kodeEt'] = $plainText;
        $resItemTiket['kodeQr'] = $text;

        return array('Tiket valid', $resItemTiket);
    } else {
        return array('Tiket invalid');
    }
}

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
