<?php
require_once '../../koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $action = test_input($_POST['action']);
    if ($action == 'detailBooking') {
        if (isset($_POST['kodeQr'])) {
            $kodeQr = test_input($_POST['kodeQr']);

            $sql = "SELECT * FROM akun_user au, tiket_booking tb WHERE tb.kode_qr = '$kodeQr' AND tb.id_user = au.id_user";

            $result = mysqli_query($conn, $sql);
            $count = mysqli_num_rows($result);

            if ($count == 1) {
                $data = mysqli_fetch_array($result);

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
        } else {
            $response['text_message'] = 'Data masukan belum ada.';
        }
    } else {
        $response['text_message'] = 'Data masukan belum ada.';
    }
    echo json_encode($response);
} else {
    $response['text_message'] = 'Kesalahan sistem HTTP.';
    echo json_encode($response);
}

$conn->close();
