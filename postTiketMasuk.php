<?php
require_once '../../koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    // Ambil nilai parameter post[aksi yg dilakukan] pada body
    $action = test_input($_POST['action']);

    // Menyesuaikan aksi dan mengambil nilai parameter post lainnya
    if ($action == 'tiketMasuk' && isset($_POST['idBooking']) && isset($_POST['idAdmin'])) {
        $idBooking = test_input($_POST['idBooking']);
        $idAdmin = test_input($_POST['idAdmin']);
        if (empty($idBooking || empty($idAdmin))) {
            $response['text_message'] = 'Kesalahan sistem, ulangi proses scan QR Code.';
        } else {
            $sql = "SELECT * FROM tiket_booking WHERE id_booking = '$idBooking'";
            $result = mysqli_query($conn, $sql);
            $count = mysqli_num_rows($result);

            if ($count == 1) {
                $data = mysqli_fetch_array($result);
                $idUser = $data['id_user'];
                $jumlahTiket = $data['jumlah_tiket'];
                $totalBayar = $data['total_bayar'];
                $metodeBayar = $data['metode_bayar'];
                $tanggalMasuk = date('Y-m-d H:i:s');

                // Memindahkan data dari tiketBooking kedalam tiketMasuk
                $insert = "INSERT INTO tiket_masuk (id_user, id_admin, tanggal_masuk, jumlah_tiket, total_bayar, metode_bayar) VALUES ('$idUser', '$idAdmin', '$tanggalMasuk', '$jumlahTiket', '$totalBayar', '$metodeBayar')";
                $query = mysqli_query($conn, $insert);
                if ($query) {
                    // JANGAN DIHAPUS : Menghapus data lama yang berada di database tiket Booking
                    // $delete = "DELETE FROM tiket_booking WHERE id_booking = '$idBooking'";
                    // $query = mysqli_query($conn, $delete);
                    $response['text_message'] = 'Tiket diterima.';
                } else {
                    $response['text_message'] = 'Tiket gagal diterima.';
                }
            } else {
                $response['text_message'] = 'Kesalahan sistem, data QR Code tidak ditemukan.';
            }
        }
    }
    // Terdapat nilai parameter post yg masih kosong
    else {
        $response['text_message'] = 'Kesalahan sistem, ulangi proses scan QR Code.';
    }
    // Membuat response dalam format json
    echo json_encode($response);
}
// Terdapat kesalahan pada request menggunakan HTTP
else {
    $response['text_message'] = 'Kesalahan Sistem HTTP.';
    echo json_encode($response);
}
// Menutup koneksi kedalam database
$conn->close();
