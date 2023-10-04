<?php
require_once '../../koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    // Ambil nilai parameter post[aksi yg dilakukan] pada body
    $action = test_input($_POST['action']);

    // Menyesuaikan aksi dan mengambil nilai parameter post lainnya
    if ($action == 'booking' && isset($_POST['idUser']) && isset($_POST['tanggalBooking']) && isset($_POST['tanggalTransaksi']) && isset($_POST['jumlahTiket']) && isset($_POST['totalBayar']) && isset($_POST['metodeBayar']) && isset($_POST['kodeEt']) && isset($_POST['kodeQr'])) {
        $idUser = test_input($_POST['idUser']);
        $tanggalBooking = test_input($_POST['tanggalBooking']);
        $tanggalTransaksi = test_input($_POST['tanggalTransaksi']);
        $jumlahTiket = test_input($_POST['jumlahTiket']);
        $totalBayar = test_input($_POST['totalBayar']);
        $metodeBayar = test_input($_POST['metodeBayar']);
        $kodeEt = test_input($_POST['kodeEt']);
        $kodeQr = test_input($_POST['kodeQr']);

        // Cek apakah ada data yg masih kosong
        if (empty($idUser) || empty($tanggalBooking) || empty($tanggalTransaksi) || empty($jumlahTiket) || empty($totalBayar) || empty($metodeBayar) || empty($kodeEt) || empty($kodeQr)) {
            $response['text_message'] = 'Data masukan belum lengkap.';
        } else {
            // Menambah data tiket_booking yg baru
            $insert = "INSERT INTO tiket_booking (id_user, tanggal_booking, tanggal_transaksi, jumlah_tiket, total_bayar, metode_bayar, kode_et, kode_qr) VALUES ('$idUser', '$tanggalBooking', '$tanggalTransaksi', '$jumlahTiket', '$totalBayar', '$metodeBayar', '$kodeEt', '$kodeQr')";
            $query = mysqli_query($conn, $insert);

            // Cek apakah query berhasil
            if ($query) {
                $response['text_message'] = 'Booking tiket berhasil.';
            } else {
                $response['text_message'] = 'Booking tiket gagal.';
            }
        }
    }
    // Terdapat nilai parameter post yg masih kosong
    else {
        $response['text_message'] = 'Data masukan belum lengkap.';
    }
    // Membuat response dalam format json
    echo json_encode($response);
}
// Terdapat kesalahan pada request menggunakan HTTP
else {
    $response['text_message'] = 'Kesalahan sistem HTTP.';
    echo json_encode($response);
}
// Menutup koneksi kedalam database
$conn->close();
