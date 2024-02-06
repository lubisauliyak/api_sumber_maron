<?php
require_once 'koneksi.php';
require_once 'functionSecurity.php';

function login_admin($bodyRequest, $conn)
{
    $email = test_input($bodyRequest['email']);
    if ($email == '') {
        return array('Masukkan e-mail');
    }
    $password = test_input($bodyRequest['password']);
    if ($password == '') {
        return array('Masukkan kata sandi');
    }

    $cekEmail = mysqli_query($conn, "SELECT * FROM akun_admin aa, role_admin ra WHERE aa.id_role_admin = ra.id_role_admin AND aa.email = '$email'");
    if (mysqli_num_rows($cekEmail) == 0) {
        return array('Login gagal, e-mail belum terdaftar');
    }
    $dataAdmin = mysqli_fetch_assoc($cekEmail);
    $passwordAdmin = $dataAdmin['password'];
    if ($passwordAdmin != $password) {
        return array('Login gagal, pastikan kata sandi Anda benar');
    }

    $idAdmin = $dataAdmin['id_admin'];
    $idApiKey = $dataAdmin['id_tokenized'];
    $namaAdmin = $dataAdmin['nama_lengkap_admin'];
    $roleAdmin = $dataAdmin['keterangan_role'];
    $emailAdmin = $dataAdmin['email'];
    $teleponAdmin = $dataAdmin['telepon'];

    $dateTime = new DateTime();
    $usedAt = $dateTime->format('Y-m-d H:i:s');

    $newApiKey = digest($teleponAdmin . $usedAt);
    $updateApiKey = mysqli_query($conn, "UPDATE token SET tokenized = '$newApiKey' WHERE id_tokenized = '$idApiKey'");

    $responseItem['idAdmin'] = $idAdmin;
    $responseItem['nama'] = $namaAdmin;
    $responseItem['role'] = $roleAdmin;
    $responseItem['email'] = $emailAdmin;
    $responseItem['telepon'] = $teleponAdmin;
    $responseItem['apiKey'] = $newApiKey;

    $dataHariLibur = hari_libur($conn);
    $dataSummaryTiket = summary_tiket($conn);

    return array('Login berhasil', $responseItem, $dataHariLibur, $dataSummaryTiket);
}

function hari_libur($conn)
{
    $cekLibur = mysqli_query($conn, "SELECT * FROM hari_libur");
    $hariLibur = array();

    while ($dataLibur = mysqli_fetch_assoc($cekLibur)) {
        $tanggalLibur = $dataLibur['tanggal_hari_libur'];
        $intervalLibur = $dataLibur['interval_hari_libur'];
        for ($i = 0; $i < $intervalLibur; $i++) {
            $tanggal = date('Y-m-d', strtotime($tanggalLibur . ' +' . $i . ' days'));
            $hariLibur[] = $tanggal;
        }
    }

    $filterTanggal = function ($tanggal) {
        $dateTime = new DateTime();
        $tanggalSekarang = $dateTime->format('Y-m-d');

        return $tanggal >= $tanggalSekarang;
    };
    $dataHariLibur = array_values(array_filter($hariLibur, $filterTanggal));

    return $dataHariLibur;
}

function summary_tiket($conn)
{
    $dateTime = new DateTime();
    $tanggalSekarang = $dateTime->format('Y-m-d');

    $pengunjungTunai = 0;
    $tiketTunai = 0;
    $bayarTunai = 0;

    $pengunjungQris = 0;
    $tiketQris = 0;
    $bayarQris = 0;

    $cekTiketDigital = mysqli_query($conn, "SELECT * FROM tiket tk, tiket_digital td WHERE tk.id_tiket = td.id_tiket AND tk.status_tiket = 'Masuk' AND td.status_bayar = 'Lunas' AND tk.tanggal_tiket = '$tanggalSekarang'");
    $pengunjung = 0;
    $tiket = 0;
    $bayar = 0;
    while ($dataDigital = mysqli_fetch_assoc($cekTiketDigital)) {
        $jumlahPengunjung = $dataDigital['jumlah_pengunjung'];
        $diskonTiket = $dataDigital['diskon_tiket'];
        $jumlahTiket = $jumlahPengunjung - $diskonTiket;
        $totalBayar = $dataDigital['total_bayar'];
        $metodeBayar = $dataDigital['metode_bayar'];

        $pengunjung += $jumlahPengunjung;
        $tiket += $jumlahTiket;
        $bayar += $totalBayar;
        if ($metodeBayar == 'Tunai') {
            $pengunjungTunai += $jumlahPengunjung;
            $tiketTunai += $jumlahTiket;
            $bayarTunai += $totalBayar;
        } else if ($metodeBayar == 'QRIS') {
            $pengunjungQris += $jumlahPengunjung;
            $tiketQris += $jumlahTiket;
            $bayarQris += $totalBayar;
        }
    }
    $summaryTiketDigital['label'] = 'Tiket Digital';
    $summaryTiketDigital['pengunjung'] = $pengunjung;
    $summaryTiketDigital['tiket'] = $tiket;
    $summaryTiketDigital['totalBayar'] = $bayar;

    $cekTiketKonvensional = mysqli_query($conn, "SELECT * FROM tiket tk, tiket_konvensional tkv WHERE tk.id_tiket = tkv.id_tiket AND tk.status_tiket = 'Masuk' AND tk.tanggal_tiket = '$tanggalSekarang'");
    $pengunjung = 0;
    $tiket = 0;
    $bayar = 0;
    while ($dataKonvensional = mysqli_fetch_assoc($cekTiketKonvensional)) {
        $jumlahPengunjung = $dataKonvensional['jumlah_pengunjung'];
        $diskonTiket = $dataKonvensional['diskon_tiket'];
        $jumlahTiket = $jumlahPengunjung - $diskonTiket;
        $totalBayar = $dataKonvensional['total_bayar'];
        $metodeBayar = $dataKonvensional['metode_bayar'];

        $pengunjung += $jumlahPengunjung;
        $tiket += $jumlahTiket;
        $bayar += $totalBayar;
        if ($metodeBayar == 'Tunai') {
            $pengunjungTunai += $jumlahPengunjung;
            $tiketTunai += $jumlahTiket;
            $bayarTunai += $totalBayar;
        } else if ($metodeBayar == 'QRIS') {
            $pengunjungQris += $jumlahPengunjung;
            $tiketQris += $jumlahTiket;
            $bayarQris += $totalBayar;
        }
    }
    $summaryTiketKonvensional['label'] = 'Tiket Konvensional';
    $summaryTiketKonvensional['pengunjung'] = $pengunjung;
    $summaryTiketKonvensional['tiket'] = $tiket;
    $summaryTiketKonvensional['totalBayar'] = $bayar;

    $cekTiketKhusus = mysqli_query($conn, "SELECT * FROM tiket tk, tiket_khusus tkh WHERE tk.id_tiket = tkh.id_tiket AND tk.status_tiket = 'Masuk' AND tkh.status_bayar = 'Lunas' AND tk.tanggal_tiket = '$tanggalSekarang'");
    $pengunjung = 0;
    $tiket = 0;
    $bayar = 0;
    while ($dataKhusus = mysqli_fetch_assoc($cekTiketKhusus)) {
        $jumlahPengunjung = $dataKhusus['jumlah_pengunjung'];
        $diskonTiket = $dataKhusus['diskon_tiket'];
        $jumlahTiket = $jumlahPengunjung - $diskonTiket;
        $totalBayar = $dataKhusus['total_bayar'];
        $metodeBayar = $dataKhusus['metode_bayar'];

        $pengunjung += $jumlahPengunjung;
        $tiket += $jumlahTiket;
        $bayar += $totalBayar;
        if ($metodeBayar == 'Tunai') {
            $pengunjungTunai += $jumlahPengunjung;
            $tiketTunai += $jumlahTiket;
            $bayarTunai += $totalBayar;
        } else if ($metodeBayar == 'QRIS') {
            $pengunjungQris += $jumlahPengunjung;
            $tiketQris += $jumlahTiket;
            $bayarQris += $totalBayar;
        }
    }
    $summaryTiketKhusus['label'] = 'Tiket Khusus';
    $summaryTiketKhusus['pengunjung'] = $pengunjung;
    $summaryTiketKhusus['tiket'] = $tiket;
    $summaryTiketKhusus['totalBayar'] = $bayar;

    $summaryAll['label'] = 'Semua';
    $summaryAll['pengunjung'] = $summaryTiketDigital['pengunjung'] + $summaryTiketKonvensional['pengunjung'] + $summaryTiketKhusus['pengunjung'];
    $summaryAll['tiket'] = $summaryTiketDigital['tiket'] + $summaryTiketKonvensional['tiket'] + $summaryTiketKhusus['tiket'];
    $summaryAll['totalBayar'] = $summaryTiketDigital['totalBayar'] + $summaryTiketKonvensional['totalBayar'] + $summaryTiketKhusus['totalBayar'];

    $summaryTunai['label'] = 'Bayar Tunai';
    $summaryTunai['pengunjung'] = $pengunjungTunai;
    $summaryTunai['tiket'] = $tiketTunai;
    $summaryTunai['totalBayar'] = $bayarTunai;

    $summaryQris['label'] = 'Bayar Qris';
    $summaryQris['pengunjung'] = $pengunjungQris;
    $summaryQris['tiket'] = $tiketQris;
    $summaryQris['totalBayar'] = $bayarQris;

    return array($summaryAll, $summaryTiketDigital, $summaryTiketKonvensional, $summaryTiketKhusus, $summaryTunai, $summaryQris);
}

function scan_tiket($bodyRequest, $conn)
{
    $kodeQr = test_input($bodyRequest['kodeQr']);
    if ($kodeQr == '') {
        return array('Masukkan qr code');
    }

    $decryptedQr = decrypted_qr($kodeQr);
    if ($decryptedQr[0] == 'Tiket valid') {
        $dataQr = $decryptedQr[1];
        $idTiket = $dataQr['idTiket'];
    } else {
        return array($decryptedQr[0]);
    }

    $dateTime = new DateTime();
    $tanggalSekarang = $dateTime->format('Y-m-d');
    if ($tanggalSekarang != $dataQr['tanggalBooking']) {
        return array('Tiket tidak dapat digunakan saat ini');
    }

    $kodeTiket = $dataQr['kodeTiket'];
    if ($kodeTiket == 'SM') {
        $cekTiket = mysqli_query($conn, "SELECT * FROM tiket tk, tiket_digital td WHERE tk.id_tiket = td.id_tiket AND tk.id_tiket = '$idTiket' AND tk.kategori_tiket = 'Tiket Digital'");
    } else if ($kodeTiket == 'TL') {
        $cekTiket = mysqli_query($conn, "SELECT * FROM tiket tk, tiket_khusus tkh WHERE tk.id_tiket = tkh.id_tiket AND tk.id_tiket = '$idTiket' AND tk.kategori_tiket = 'Tiket Khusus'");
    } else {
        return array('Tiket tidak ditemukan');
    }
    if (mysqli_num_rows($cekTiket) == 0) {
        return array('Tiket tidak ditemukan');
    }

    $dataTiket = mysqli_fetch_assoc($cekTiket);
    if ($kodeTiket == 'SM') {
        $idUser = $dataTiket['id_user'];
        $cekUser = mysqli_query($conn, "SELECT nama_lengkap_user FROM akun_user WHERE id_user = '$idUser'");
        $dataUser = mysqli_fetch_assoc($cekUser);
        $namaUser = $dataUser['nama_lengkap_user'];
    } else if ($kodeTiket == 'TL') {
        $idKhusus = $dataTiket['id_khusus'];
        $cekKhusus = mysqli_query($conn, "SELECT * FROM akun_khusus WHERE id_khusus = '$idKhusus'");
        $dataKhusus = mysqli_fetch_assoc($cekKhusus);
        $namaKhusus = $dataKhusus['nama_khusus'];
        $namaOperator = $dataKhusus['nama_operator'];
        $namaUser = $namaKhusus . ' [' . $namaOperator . ']';
    }
    $jumlahPengunjung = $dataTiket['jumlah_pengunjung'];
    $diskonTiket = $dataTiket['diskon_tiket'];
    $jumlahTiket = strval($jumlahPengunjung - $diskonTiket);
    $totalBayar = $dataTiket['total_bayar'];
    $tanggalBooking = $dataTiket['tanggal_booking'];
    $statusTiket = $dataTiket['status_tiket'];
    $statusBayar = $dataTiket['status_bayar'];
    $kodeEt = $dataTiket['order_id'];
    if ($statusTiket == 'Masuk' && $statusBayar == 'Lunas') {
        return array('Tiket sudah digunakan');
    }
    if ($statusTiket == 'Dibatalkan Sistem' || $statusBayar == 'Dibatalkan Sistem') {
        return array('Tiket sudah dibatalkan sistem');
    }
    if ($tanggalBooking != $dataQr['tanggalBooking']) {
        return array('Tanggal booking tidak sesuai dengan sistem');
    }
    if ($jumlahTiket != $dataQr['jumlahTiket']) {
        return array('Jumlah tiket tidak sesuai dengan sistem');
    }
    if ($totalBayar != $dataQr['totalBayar']) {
        return array('Total pembayaran tidak sesuai dengan sistem');
    }
    if ($kodeEt != $dataQr['kodeEt']) {
        return array('ID Tiket tidak sesuai dengan sistem');
    }

    $resItemTiket['idTiket'] = $idTiket;
    $resItemTiket['kodeTiket'] = $kodeTiket;
    $resItemTiket['namaUser'] = $namaUser;
    $resItemTiket['jumlahPengunjung'] = $jumlahPengunjung;
    $resItemTiket['jumlahTiket'] = $jumlahTiket;
    $resItemTiket['diskonTiket'] = $diskonTiket;
    $resItemTiket['totalBayar'] = $totalBayar;
    $resItemTiket['tanggalBooking'] = $tanggalBooking;
    $resItemTiket['statusBayar'] = $statusBayar;
    $resItemTiket['kodeEt'] = $kodeEt;

    return array('Tiket valid', $resItemTiket);
}

function reschedule_tiket($bodyRequest, $conn)
{
    $idTiket = test_input($bodyRequest['idTiket']);
    if ($idTiket == '') {
        return array('Masukkan id tiket');
    }
    $tanggalBookingNew = test_input($bodyRequest['tanggalBooking']);
    if ($tanggalBookingNew == '') {
        return array('Masukkan tanggal pemesanan baru');
    }
    $hargaTiket = test_input($bodyRequest['hargaTiket']);
    if ($hargaTiket == '') {
        return array('Masukkan harga tiket');
    }
    $weekTiket = test_input($bodyRequest['weekTiket']);
    if ($weekTiket == '') {
        return array('Masukkan hari tiket');
    }
    $totalBayar = test_input($bodyRequest['totalBayar']);
    if ($totalBayar == '') {
        return array('Masukkan total bayar');
    }

    $cekTiketKhusus = mysqli_query($conn, "SELECT * FROM tiket tk, tiket_khusus tkh WHERE tk.id_tiket = tkh.id_tiket AND tk.id_tiket = '$idTiket'");
    if (mysqli_num_rows($cekTiketKhusus) == 0) {
        return array('Tiket khusus tidak ditemukan');
    }
    $dataTiket = mysqli_fetch_assoc($cekTiketKhusus);
    $tanggalBookingOld = $dataTiket['tanggal_booking'];

    $dateTime = new DateTime();
    $tanggalSekarang = $dateTime->format('Y-m-d');
    $waktuSekarang = $dateTime->format('H:i:s');
    $tanggalSekarangAdd30 = date('Y-m-d', strtotime('+30 days', strtotime($tanggalSekarang)));

    if ($tanggalBookingOld <= $tanggalSekarang && (strtotime('17:00:00')) <= (strtotime($waktuSekarang))) {
        return array('Reschedule tiket tidak diterima, batas maksimal sebelum jam operasional berakhir');
    }

    $jumlahPengunjung = $dataTiket['jumlah_pengunjung'];
    $diskonTiket = $dataTiket['diskon_tiket'];
    $jumlahTiket = strval($jumlahPengunjung - $diskonTiket);
    $statusBayar = $dataTiket['status_bayar'];
    $kodeEtOld = $dataTiket['order_id'];

    $kodeEtNew = order_tl($tanggalBookingNew, $jumlahTiket, $totalBayar, $idTiket);

    $tambahReschedule = mysqli_query($conn, "INSERT INTO record_reschedule (id_tiket, tanggal_booking_old, tanggal_booking_new, order_id_old, order_id_new) VALUES ('$idTiket', '$tanggalBookingOld', '$tanggalBookingNew', '$kodeEtOld', '$kodeEtNew')");
    $idReschedule = mysqli_insert_id($conn);
    $updateTiket = mysqli_query($conn, "UPDATE tiket SET harga_tiket = '$hargaTiket', week_tiket = '$weekTiket', total_bayar = '$totalBayar' WHERE id_tiket = '$idTiket'");
    $updateTiketKhusus = mysqli_query($conn, "UPDATE tiket_khusus SET tanggal_booking = '$tanggalBookingNew', order_id = '$kodeEtNew' WHERE id_tiket = '$idTiket'");

    $idKhusus = $dataTiket['id_khusus'];
    $cekKhusus = mysqli_query($conn, "SELECT * FROM akun_khusus WHERE id_khusus = '$idKhusus'");
    $dataKhusus = mysqli_fetch_assoc($cekKhusus);
    $namaKhusus = $dataKhusus['nama_khusus'];
    $namaOperator = $dataKhusus['nama_operator'];
    $namaUser = $namaKhusus . ' [' . $namaOperator . ']';

    $resItemTiket['idTiket'] = $idTiket;
    $resItemTiket['namaUser'] = $namaUser;
    $resItemTiket['jumlahPengunjung'] = $jumlahPengunjung;
    $resItemTiket['jumlahTiket'] = $jumlahTiket;
    $resItemTiket['diskonTiket'] = $diskonTiket;
    $resItemTiket['totalBayar'] = $totalBayar;
    $resItemTiket['tanggalBooking'] = $tanggalBookingNew;
    $resItemTiket['statusBayar'] = $statusBayar;
    $resItemTiket['kodeEt'] = $kodeEtNew;

    return array('Reschedule tiket khusus berhasil', $resItemTiket);
}

function edit_tiket($bodyRequest, $conn)
{
    $idAdmin = test_input($bodyRequest['idAdmin']);
    if ($idAdmin == '') {
        return array('Masukkan id admin');
    }
    $idTiket = test_input($bodyRequest['idTiket']);
    if ($idTiket == '') {
        return array('Masukkan id tiket');
    }
    $jumlahPengunjung = test_input($bodyRequest['jumlahPengunjung']);
    if ($jumlahPengunjung == '') {
        return array('Masukkan jumlah pengunjung');
    }
    $jumlahTiket = test_input($bodyRequest['jumlahTiket']);
    if ($jumlahTiket == '') {
        return array('Masukkan jumlah tiket');
    }
    $diskonTiket = test_input($bodyRequest['diskonTiket']);
    if ($diskonTiket == '') {
        return array('Masukkan diskon tiket');
    }
    $totalBayar = test_input($bodyRequest['totalBayar']);
    if ($totalBayar == '') {
        return array('Masukkan total bayar');
    }

    $cekTiket = mysqli_query($conn, "SELECT * FROM tiket WHERE id_tiket = '$idTiket' AND status_tiket = 'Booking'");
    if (mysqli_num_rows($cekTiket) == 0) {
        return array('Tiket tidak ditemukan');
    }
    $dataTiket = mysqli_fetch_assoc($cekTiket);
    $kategoriTiket = $dataTiket['kategori_tiket'];
    if ($kategoriTiket == 'Tiket Digital') {
        $cekTiketDigital = mysqli_query($conn, "SELECT * FROM tiket_digital WHERE id_tiket = '$idTiket'");
        if (mysqli_num_rows($cekTiketDigital) == 0) {
            return array('Tiket tidak ditemukan');
        }
        $dataTiket = mysqli_fetch_assoc($cekTiketDigital);
        $idUser = $dataTiket['id_user'];
        $cekUser = mysqli_query($conn, "SELECT nama_lengkap_user FROM akun_user WHERE id_user = '$idUser'");
        $dataUser = mysqli_fetch_assoc($cekUser);
        $namaUser = $dataUser['nama_lengkap_user'];

        $tanggalBooking = $dataTiket['tanggal_booking'];
        $kodeEt = order_et($tanggalBooking, $jumlahTiket, $totalBayar, $idTiket);
        $updateTiketDigital = mysqli_query($conn, "UPDATE tiket_digital SET order_id = '$kodeEt' WHERE id_tiket = '$idTiket'");
    } else if ($kategoriTiket == 'Tiket Khusus') {
        $cekTiketKhusus = mysqli_query($conn, "SELECT * FROM tiket_khusus WHERE id_tiket = '$idTiket'");
        if (mysqli_num_rows($cekTiketKhusus) == 0) {
            return array('Tiket tidak ditemukan');
        }
        $dataTiket = mysqli_fetch_assoc($cekTiketKhusus);
        $idKhusus = $dataTiket['id_khusus'];
        $cekKhusus = mysqli_query($conn, "SELECT * FROM akun_khusus WHERE id_khusus = '$idKhusus'");
        $dataKhusus = mysqli_fetch_assoc($cekKhusus);
        $namaKhusus = $dataKhusus['nama_khusus'];
        $namaOperator = $dataKhusus['nama_operator'];
        $namaUser = $namaKhusus . ' [' . $namaOperator . ']';

        $tanggalBooking = $dataTiket['tanggal_booking'];
        $kodeEt = order_tl($tanggalBooking, $jumlahTiket, $totalBayar, $idTiket);
        $updateTiketKhusus = mysqli_query($conn, "UPDATE tiket_khusus SET order_id = '$kodeEt' WHERE id_tiket = '$idTiket'");
    }
    $statusBayar = $dataTiket['status_bayar'];

    $updateTiket = mysqli_query($conn, "UPDATE tiket SET jumlah_pengunjung = '$jumlahPengunjung', diskon_tiket = '$diskonTiket', total_bayar = '$totalBayar' WHERE id_tiket = '$idTiket'");

    $resItemTiket['idTiket'] = $idTiket;
    $resItemTiket['namaUser'] = $namaUser;
    $resItemTiket['jumlahPengunjung'] = $jumlahPengunjung;
    $resItemTiket['jumlahTiket'] = $jumlahTiket;
    $resItemTiket['diskonTiket'] = $diskonTiket;
    $resItemTiket['totalBayar'] = $totalBayar;
    $resItemTiket['tanggalBooking'] = $tanggalBooking;
    $resItemTiket['statusBayar'] = $statusBayar;
    $resItemTiket['kodeEt'] = $kodeEt;

    return array('Edit tiket berhasil', $resItemTiket);
}

function hapus_tiket($bodyRequest, $conn)
{
    $idTiket = test_input($bodyRequest['idTiket']);
    if ($idTiket == '') {
        return array('Masukkan id tiket');
    }

    $deleteTiket = mysqli_query($conn, "DELETE FROM tiket WHERE id_tiket = '$idTiket'");
    $deleteTiketKhusus = mysqli_query($conn, "DELETE FROM tiket_khusus WHERE id_tiket = '$idTiket'");

    return array('Hapus tiket khusus berhasil');
}

function tukar_tiket($bodyRequest, $conn)
{
    $idAdmin = test_input($bodyRequest['idAdmin']);
    if ($idAdmin == '') {
        return array('Masukkan id admin');
    }
    $idTiket = test_input($bodyRequest['idTiket']);
    if ($idTiket == '') {
        return array('Masukkan id tiket');
    }
    $metodeBayar = test_input($bodyRequest['metodeBayar']);
    if ($metodeBayar == '') {
        return array('Masukkan jenis pembayaran');
    }

    $cekTiket = mysqli_query($conn, "SELECT * FROM tiket WHERE id_tiket = '$idTiket' AND status_tiket = 'Booking'");
    if (mysqli_num_rows($cekTiket) == 0) {
        return array('Tiket tidak ditemukan');
    }

    $dateTime = new DateTime();
    $tanggalTiket = $dateTime->format('Y-m-d');
    $waktuTiket = $dateTime->format('H:i:s');
    $updateTiket = mysqli_query($conn, "UPDATE tiket SET tanggal_tiket = '$tanggalTiket', waktu_tiket = '$waktuTiket', status_tiket = 'Masuk' WHERE id_tiket = '$idTiket'");

    $dataTiket = mysqli_fetch_assoc($cekTiket);
    $kategoriTiket = $dataTiket['kategori_tiket'];
    if ($kategoriTiket == 'Tiket Digital') {
        $updateTiketDigital = mysqli_query($conn, "UPDATE tiket_digital SET id_admin = '$idAdmin', status_bayar = 'Lunas', metode_bayar = '$metodeBayar' WHERE id_tiket = '$idTiket'");
        $cekTiketDigital = mysqli_query($conn, "SELECT * FROM tiket_digital WHERE id_tiket = '$idTiket'");
        $dataDigital = mysqli_fetch_assoc($cekTiketDigital);
        $idUser = $dataDigital['id_user'];
        $cekUser = mysqli_query($conn, "SELECT nama_lengkap_user FROM akun_user WHERE id_user = '$idUser'");
        $dataUser = mysqli_fetch_assoc($cekUser);
        $namaUser = $dataUser['nama_lengkap_user'];
    } else if ($kategoriTiket == 'Tiket Khusus') {
        $updateTiketKhusus = mysqli_query($conn, "UPDATE tiket_khusus SET id_admin = '$idAdmin', status_bayar = 'Lunas', metode_bayar = '$metodeBayar' WHERE id_tiket = '$idTiket'");
        $cekTiketKhusus = mysqli_query($conn, "SELECT * FROM tiket_khusus WHERE id_tiket = '$idTiket'");
        $dataTiketKhusus = mysqli_fetch_assoc($cekTiketKhusus);
        $idKhusus = $dataTiketKhusus['id_khusus'];
        $cekKhusus = mysqli_query($conn, "SELECT * FROM akun_khusus WHERE id_khusus = '$idKhusus'");
        $dataKhusus = mysqli_fetch_assoc($cekKhusus);
        $namaKhusus = $dataKhusus['nama_khusus'];
        $namaOperator = $dataKhusus['nama_operator'];
        $namaUser = $namaKhusus . ' ( ' . $namaOperator . ' )';
    }

    $cekAdmin = mysqli_query($conn, "SELECT nama_lengkap_admin FROM akun_admin WHERE id_admin = '$idAdmin'");
    $dataAdmin = mysqli_fetch_assoc($cekAdmin);
    $namaAdmin = $dataAdmin['nama_lengkap_admin'];

    $jumlahPengunjung = $dataTiket['jumlah_pengunjung'];
    $diskonTiket = $dataTiket['diskon_tiket'];
    $jumlahTiket = $jumlahPengunjung - $diskonTiket;
    $totalBayar = $dataTiket['total_bayar'];

    $resItemTiket['idTiket'] = $idTiket;
    $resItemTiket['kategoriTiket'] = $kategoriTiket;
    $resItemTiket['namaAdmin'] = $namaAdmin;
    $resItemTiket['namaUser'] = $namaUser;
    $resItemTiket['tanggalTiket'] = $tanggalTiket;
    $resItemTiket['waktuTiket'] = $waktuTiket;
    $resItemTiket['jumlahPengunjung'] = $jumlahPengunjung;
    $resItemTiket['jumlahTiket'] = $jumlahTiket;
    $resItemTiket['totalBayar'] = $totalBayar;
    $resItemTiket['metodeBayar'] = $metodeBayar;
    $resItemTiket['statusBayar'] = 'Lunas';

    return array('Penukaran tiket diterima', $resItemTiket);
}

function tiket_konvensional($bodyRequest, $conn)
{
    $idAdmin = test_input($bodyRequest['idAdmin']);
    if ($idAdmin == '') {
        return array('Masukkan id admin');
    }
    $namaPengunjung = test_input($bodyRequest['namaPengunjung']);
    if ($namaPengunjung == '') {
        $namaPengunjung = 'default';
    }
    $teleponPengunjung = test_input($bodyRequest['teleponPengunjung']);
    if ($teleponPengunjung == '') {
        $teleponPengunjung = '000';
    }
    $jumlahPengunjung = test_input($bodyRequest['jumlahPengunjung']);
    if ($jumlahPengunjung == '') {
        return array('Masukkan jumlah pengunjung');
    }
    $jumlahTiket = test_input($bodyRequest['jumlahTiket']);
    if ($jumlahTiket == '') {
        return array('Masukkan jumlah tiket');
    }
    $diskonTiket = test_input($bodyRequest['diskonTiket']);
    if ($diskonTiket == '') {
        return array('Masukkan diskon tiket');
    }
    $hargaTiket = test_input($bodyRequest['hargaTiket']);
    if ($hargaTiket == '') {
        return array('Masukkan harga tiket');
    }
    $weekTiket = test_input($bodyRequest['weekTiket']);
    if ($weekTiket == '') {
        return array('Masukkan hari tiket');
    }
    $totalBayar = test_input($bodyRequest['totalBayar']);
    if ($totalBayar == '') {
        return array('Masukkan total bayar');
    }
    $metodeBayar = test_input($bodyRequest['metodeBayar']);
    if ($metodeBayar == '') {
        return array('Masukkan jenis pembayaran');
    }
    $statusBayar = 'Lunas';

    $dateTime = new DateTime();
    $tanggalTiket = $dateTime->format('Y-m-d');
    $waktuTiket = $dateTime->format('H:i:s');

    $tambahTiket = mysqli_query($conn, "INSERT INTO tiket (jumlah_pengunjung, diskon_tiket, total_bayar, harga_tiket, week_tiket, tanggal_tiket, waktu_tiket, status_tiket, kategori_tiket) VALUES ('$jumlahPengunjung', '$diskonTiket', '$totalBayar', '$hargaTiket', '$weekTiket', '$tanggalTiket', '$waktuTiket', 'Masuk', 'Tiket Konvensional')");
    $idTiket = mysqli_insert_id($conn);

    $tambahTiketKonvensional = mysqli_query($conn, "INSERT INTO tiket_konvensional (id_tiket, id_admin, nama_pengunjung, telepon_pengunjung, metode_bayar) VALUES ('$idTiket', '$idAdmin', '$namaPengunjung', '$teleponPengunjung', '$metodeBayar')");
    $idTiketKonvensional = mysqli_insert_id($conn);

    $cekAdmin = mysqli_query($conn, "SELECT nama_lengkap_admin FROM akun_admin WHERE id_admin = '$idAdmin'");
    $dataAdmin = mysqli_fetch_assoc($cekAdmin);
    $namaAdmin = $dataAdmin['nama_lengkap_admin'];

    $resItemTiket['idTiket'] = $idTiket;
    $resItemTiket['kategoriTiket'] = 'Tiket Konvensional';
    $resItemTiket['namaAdmin'] = $namaAdmin;
    $resItemTiket['namaUser'] = $namaPengunjung;
    $resItemTiket['tanggalTiket'] = $tanggalTiket;
    $resItemTiket['waktuTiket'] = $waktuTiket;
    $resItemTiket['jumlahPengunjung'] = $jumlahPengunjung;
    $resItemTiket['jumlahTiket'] = $jumlahTiket;
    $resItemTiket['totalBayar'] = $totalBayar;
    $resItemTiket['metodeBayar'] = $metodeBayar;
    $resItemTiket['statusBayar'] = $statusBayar;

    return array('Beli tiket konvensional berhasil', $resItemTiket);
}

function data_tiket_khusus($conn)
{
    $cekTiket = mysqli_query($conn, "SELECT * FROM tiket tk, tiket_khusus tkh WHERE tk.id_tiket = tkh.id_tiket AND tk.status_tiket = 'Booking' AND tk.kategori_tiket = 'Tiket Khusus' ORDER BY tkh.tanggal_booking ASC");
    if (mysqli_num_rows($cekTiket) == 0) {
        return array('Data tiket khusus belum ada');
    }

    $summaryTransaksi = 0;
    while ($dataTiket = mysqli_fetch_assoc($cekTiket)) {
        $idTiket = $dataTiket['id_tiket'];
        $idKhusus = $dataTiket['id_khusus'];
        $cekKhusus = mysqli_query($conn, "SELECT * FROM akun_khusus WHERE id_khusus = '$idKhusus'");
        $dataKhusus = mysqli_fetch_assoc($cekKhusus);
        $namaKhusus = $dataKhusus['nama_khusus'];
        $namaOperator = $dataKhusus['nama_operator'];
        $teleponOperator = $dataKhusus['telepon_operator'];
        $tanggalBooking = $dataTiket['tanggal_booking'];
        $jumlahPengunjung = $dataTiket['jumlah_pengunjung'];
        $diskonTiket = $dataTiket['diskon_tiket'];
        $jumlahTiket = $jumlahPengunjung - $diskonTiket;
        $totalBayar = $dataTiket['total_bayar'];
        $statusBayar = $dataTiket['status_bayar'];
        $kodeEt = $dataTiket['order_id'];

        $resItemTiket = array(
            'idTiket' => $idTiket,
            'namaUser' => $namaKhusus . ' ( ' . $namaOperator . ' )',
            'teleponUser' => $teleponOperator,
            'tanggalBooking' => $tanggalBooking,
            'jumlahPengunjung' => $jumlahPengunjung,
            'jumlahTiket' => $jumlahTiket,
            'diskonTiket' => $diskonTiket,
            'totalBayar' => $totalBayar,
            'statusBayar' => $statusBayar,
            'kodeEt' => $kodeEt,
        );
        $dataTiketArray[] = $resItemTiket;
        $summaryTransaksi++;
    }

    return array('Data tiket khusus ditemukan', $summaryTransaksi, $dataTiketArray);
}

function data_tiket_masuk($conn)
{
    $dateTime = new DateTime();
    $tanggalSekarang = $dateTime->format('Y-m-d');
    $cekTiket = mysqli_query($conn, "SELECT * FROM tiket WHERE status_tiket = 'Masuk' AND tanggal_tiket = '$tanggalSekarang' ORDER BY waktu_tiket DESC");
    if (mysqli_num_rows($cekTiket) == 0) {
        return array('Data tiket masuk hari ini belum ada');
    }

    $summaryTransaksi = 0;
    while ($dataTiket = mysqli_fetch_assoc($cekTiket)) {
        $idTiket = $dataTiket['id_tiket'];
        $tanggalTiket = $dataTiket['tanggal_tiket'];
        $waktuTiket = $dataTiket['waktu_tiket'];
        $jumlahPengunjung = $dataTiket['jumlah_pengunjung'];
        $diskonTiket = $dataTiket['diskon_tiket'];
        $jumlahTiket = $jumlahPengunjung - $diskonTiket;
        $totalBayar = $dataTiket['total_bayar'];
        $kategoriTiket = $dataTiket['kategori_tiket'];

        if ($kategoriTiket == 'Tiket Digital') {
            $cekTiketDigital = mysqli_query($conn, "SELECT * FROM tiket_digital WHERE id_tiket = '$idTiket'");
            if (mysqli_num_rows($cekTiketDigital) == 0) {
                continue;
            }
            $dataDigital = mysqli_fetch_assoc($cekTiketDigital);
            $idAdmin = $dataDigital['id_admin'];
            $cekAdmin = mysqli_query($conn, "SELECT nama_lengkap_admin FROM akun_admin WHERE id_admin = '$idAdmin'");
            $dataAdmin = mysqli_fetch_assoc($cekAdmin);
            $namaAdmin = $dataAdmin['nama_lengkap_admin'];
            $idUser = $dataDigital['id_user'];
            $cekUser = mysqli_query($conn, "SELECT nama_lengkap_user FROM akun_user WHERE id_user = '$idUser'");
            $dataUser = mysqli_fetch_assoc($cekUser);
            $namaUser = $dataUser['nama_lengkap_user'];
            $metodeBayar = $dataDigital['metode_bayar'];
            $statusBayar = $dataDigital['status_bayar'];
            $kodeEt = $dataDigital['order_id'];

            $resItemTiket = array(
                'idTiket' => $idTiket,
                'kategoriTiket' => 'Tiket Digital',
                'namaAdmin' => $namaAdmin,
                'namaUser' => $namaUser,
                'tanggalTiket' => $tanggalTiket,
                'waktuTiket' => $waktuTiket,
                'jumlahPengunjung' => $jumlahPengunjung,
                'jumlahTiket' => $jumlahTiket,
                'diskonTiket' => $diskonTiket,
                'totalBayar' => $totalBayar,
                'metodeBayar' => $metodeBayar,
                'statusBayar' => $statusBayar,
                'kodeEt' => $kodeEt
            );
        } else if ($kategoriTiket == 'Tiket Konvensional') {
            $cekTiketKonvensional = mysqli_query($conn, "SELECT * FROM tiket_konvensional WHERE id_tiket = '$idTiket'");
            if (mysqli_num_rows($cekTiketKonvensional) == 0) {
                continue;
            }
            $dataKonvensional = mysqli_fetch_assoc($cekTiketKonvensional);
            $idAdmin = $dataKonvensional['id_admin'];
            $cekAdmin = mysqli_query($conn, "SELECT nama_lengkap_admin FROM akun_admin WHERE id_admin = '$idAdmin'");
            $dataAdmin = mysqli_fetch_assoc($cekAdmin);
            $namaAdmin = $dataAdmin['nama_lengkap_admin'];
            $namaUser = $dataKonvensional['nama_pengunjung'];
            $metodeBayar = $dataKonvensional['metode_bayar'];

            $resItemTiket = array(
                'idTiket' => $idTiket,
                'kategoriTiket' => 'Tiket Konvensional',
                'namaAdmin' => $namaAdmin,
                'namaUser' => $namaUser,
                'tanggalTiket' => $tanggalTiket,
                'waktuTiket' => $waktuTiket,
                'jumlahPengunjung' => $jumlahPengunjung,
                'jumlahTiket' => $jumlahTiket,
                'diskonTiket' => $diskonTiket,
                'totalBayar' => $totalBayar,
                'metodeBayar' => $metodeBayar,
                'statusBayar' => 'Lunas'
            );
        } else if ($kategoriTiket == 'Tiket Khusus') {
            $cekTiketKhusus = mysqli_query($conn, "SELECT * FROM tiket_khusus WHERE id_tiket = '$idTiket'");
            if (mysqli_num_rows($cekTiketKhusus) == 0) {
                continue;
            }
            $dataTiketKhusus = mysqli_fetch_assoc($cekTiketKhusus);
            $idAdmin = $dataTiketKhusus['id_admin'];
            $cekAdmin = mysqli_query($conn, "SELECT nama_lengkap_admin FROM akun_admin WHERE id_admin = '$idAdmin'");
            $dataAdmin = mysqli_fetch_assoc($cekAdmin);
            $namaAdmin = $dataAdmin['nama_lengkap_admin'];
            $idKhusus = $dataTiketKhusus['id_khusus'];
            $cekKhusus = mysqli_query($conn, "SELECT * FROM akun_khusus WHERE id_khusus = '$idKhusus'");
            $dataKhusus = mysqli_fetch_assoc($cekKhusus);
            $namaKhusus = $dataKhusus['nama_khusus'];
            $namaOperator = $dataKhusus['nama_operator'];
            $metodeBayar = $dataTiketKhusus['metode_bayar'];
            $statusBayar = $dataTiketKhusus['status_bayar'];
            $kodeEt = $dataTiketKhusus['order_id'];

            $resItemTiket = array(
                'idTiket' => $idTiket,
                'kategoriTiket' => 'Tiket Khusus',
                'namaAdmin' => $namaAdmin,
                'namaUser' => $namaKhusus . ' ( ' . $namaOperator . ' )',
                'tanggalTiket' => $tanggalTiket,
                'waktuTiket' => $waktuTiket,
                'jumlahPengunjung' => $jumlahPengunjung,
                'jumlahTiket' => $jumlahTiket,
                'diskonTiket' => $diskonTiket,
                'totalBayar' => $totalBayar,
                'metodeBayar' => $metodeBayar,
                'statusBayar' => $statusBayar,
                'kodeEt' => $kodeEt
            );
        } else {
            continue;
        }
        $dataTiketArray[] = $resItemTiket;
        $summaryTransaksi++;
    }

    return array('Data tiket masuk ditemukan', $summaryTransaksi, $dataTiketArray);
}
