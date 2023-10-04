<?php
require_once '../../koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    // Ambil nilai parameter post[aksi yg dilakukan] pada body
    $action = test_input($_POST['action']);

    // Menyesuaikan aksi dan mengambil nilai parameter post lainnya
    if ($action == 'detailBooking') {
        // Cek apakah data QR Code kosong
        if (isset($_POST['kodeQr'])) {
            $kodeQr = test_input($_POST['kodeQr']);

            // Mengambil data tiket_booking
            $sql = "SELECT * FROM akun_user au, tiket_booking tb WHERE tb.kode_qr = '$kodeQr' AND tb.id_user = au.id_user";
            $result = mysqli_query($conn, $sql);
            $count = mysqli_num_rows($result);

            // Cek apakah ada data QR Code
            if ($count == 1) {
                $data = mysqli_fetch_array($result);

                // Tambahkan data tiket_booking ke response json
                $response['text_message'] = 'Data booking tersedia.';
                $response['id_booking'] = $data['id_booking'];
                $response['id_user'] = $data['id_user'];
                $response['name_user'] = $data['nama'];
                $response['date_booking'] = $data['tanggal_booking'];
                $response['date_transaksi'] = $data['tanggal_transaksi'];
                $response['count_ticket'] = $data['jumlah_tiket'];
                $response['payment_amount'] = $data['total_bayar'];
                $response['payment_method'] = $data['metode_bayar'];
                $response['code_ticket'] = $data['kode_et'];
            } else {
                $response['text_message'] = 'Data booking tidak tersedia.';
            }
        }
        // Terdapat nilai parameter post yg masih kosong
        else {
            $response['text_message'] = 'Data masukan belum ada.';
        }
    }
    // Terdapat nilai parameter post yg masih kosong
    else {
        $response['text_message'] = 'Data masukan belum ada.';
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
