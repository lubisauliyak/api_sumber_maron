<?php
require_once 'koneksi.php';
require_once 'functionSecurity.php';

function login_user($bodyRequest, $conn)
{
    $email = test_input($bodyRequest['email']);
    if ($email == '') {
        return array('Masukkan e-mail');
    }
    $password = test_input($bodyRequest['password']);
    if ($password == '') {
        return array('Masukkan password');
    }

    $cekEmail = mysqli_query($conn, "SELECT * FROM akun_user WHERE email = '$email'");
    if (mysqli_num_rows($cekEmail) == 0) {
        return array('Login gagal, e-mail belum terdaftar');
    }
    $dataUser = mysqli_fetch_assoc($cekEmail);
    $passwordUser = $dataUser['password'];
    if ($passwordUser != $password) {
        return array('Login gagal, pastikan kata sandi Anda benar');
    }

    $idUser = $dataUser['id_user'];
    $idApiKey = $dataUser['id_tokenized'];
    $namaUser = $dataUser['nama_lengkap_user'];
    $emailUser = $dataUser['email'];
    $teleponUser = $dataUser['telepon'];

    $dateTime = new DateTime();
    $usedAt = $dateTime->format('Y-m-d H:i:s');

    $newApiKey = digest($teleponUser . $usedAt);
    $updateApiKey = mysqli_query($conn, "UPDATE token SET tokenized = '$newApiKey' WHERE id_tokenized = '$idApiKey'");

    $responseItem['idUser'] = $idUser;
    $responseItem['nama'] = $namaUser;
    $responseItem['email'] = $emailUser;
    $responseItem['telepon'] = $teleponUser;
    $responseItem['apiKey'] = $newApiKey;

    $dataBanner = banner($idUser, $conn);
    $dataHariLibur = hari_libur($conn);
    $dataLokasiParkir = lokasi_parkir($conn);

    return array('Login berhasil', $responseItem, $dataBanner, $dataHariLibur, $dataLokasiParkir);
}

function banner($idUser, $conn)
{
    if ($idUser == '') {
        return array('Masukkan id user');
    }

    $cekBanner = mysqli_query($conn, "SELECT * FROM banner WHERE is_activated = 'Aktif'");
    $dataImageBanner = array();

    while ($dataBanner = mysqli_fetch_assoc($cekBanner)) {
        $tipeBanner = $dataBanner['tipe_banner'];
        $imageBanner = $dataBanner['image_banner'];
        $namaPromo = $dataBanner['nama_promo'];
        $promoTiket = $dataBanner['promo_tiket'];
        $tiketMinimal = $dataBanner['tiket_minimal'];

        if ($namaPromo == 'Pengguna Baru') {
            $usePromo = mysqli_query($conn, "SELECT * FROM tiket_digital td, record_promo rp WHERE td.id_tiket = rp.id_tiket AND td.id_user = '$idUser' AND rp.keterangan_promo = 'Pengguna Baru'");
            if (mysqli_num_rows($usePromo) > 0) {
                continue;
            }
        }

        $itemBanner['imageBanner'] = $imageBanner;
        $itemBanner['tipeBanner'] = $tipeBanner;
        $itemBanner['namaPromo'] = $namaPromo;
        $itemBanner['promoTiket'] = $promoTiket;
        $itemBanner['tiketMinimal'] = $tiketMinimal;

        $dataImageBanner[] = $itemBanner;
    }

    return $dataImageBanner;
}

function lokasi_parkir($conn)
{
    $cekParkir = mysqli_query($conn, "SELECT * FROM parkir");
    $dataLokasiParkir = array();

    while ($lokasiParkir = mysqli_fetch_assoc($cekParkir)) {
        $tipeKendaraan = $lokasiParkir['tipe_kendaraan'];
        $linkMaps = $lokasiParkir['link_maps'];
        $imageMaps = $lokasiParkir['image_maps'];

        $resItemParkir['linkMaps'] = $linkMaps;
        $resItemParkir['imageMaps'] = $imageMaps;

        $dataLokasiParkir['parkir' . $tipeKendaraan][] = $resItemParkir;
    }

    return $dataLokasiParkir;
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

function registrasi_user($bodyRequest, $conn)
{
    $nama = test_input($bodyRequest['nama']);
    if ($nama == '') {
        return array('Masukkan nama lengkap');
    }

    $telepon = test_input($bodyRequest['telepon']);
    if ($telepon == '') {
        return array('Masukkan no telepon');
    }
    $cekTelepon = mysqli_query($conn, "SELECT * FROM akun_user WHERE telepon = '$telepon'");
    if (mysqli_num_rows($cekTelepon) > 0) {
        return array('No telepon ini sudah terdaftar');
    }

    $email = test_input($bodyRequest['email']);
    if ($email == '') {
        return array('Masukkan e-mail');
    }
    $cekEmail = mysqli_query($conn, "SELECT * FROM akun_user WHERE email = '$email'");
    if (mysqli_num_rows($cekEmail) > 0) {
        return array('E-mail ini sudah terdaftar');
    }

    $password = test_input($bodyRequest['password']);
    if ($password == '') {
        return array('Masukkan kata sandi');
    }

    $dateTime = new DateTime();
    $createdAt = $dateTime->format('Y-m-d H:i:s');

    $newApiKey = digest($telepon . $createdAt);

    $tambahApiKey = mysqli_query($conn, "INSERT INTO token (tokenized, token_used_at, token_created_at) VALUES ('$newApiKey', '$createdAt', '$createdAt')");
    $idApiKey = mysqli_insert_id($conn);
    $tambahAkunUser = mysqli_query($conn, "INSERT INTO akun_user (id_tokenized, nama_lengkap_user, email, password, telepon, user_used_at, user_created_at, is_activated) VALUES ('$idApiKey', '$nama', '$email', '$password', '$telepon', '$createdAt', '$createdAt', 'Belum')");
    $idUser = mysqli_insert_id($conn);
    $tambahAktifasiUser = mysqli_query($conn, "INSERT INTO aktifasi_user (id_user, kode_aktifasi, status_aktifasi, aktifasi_created_at) VALUES ('$idUser', '$newApiKey', 'Belum', '$createdAt')");

    return array('Pendaftaran berhasil');
}

function edit_user($bodyRequest, $conn)
{
    $idUser = test_input($bodyRequest['idUser']);
    if ($idUser == '') {
        return array('Masukkan id user');
    }

    $nama = test_input($bodyRequest['nama']);
    if ($nama == '') {
        return array('Masukkan nama lengkap');
    }

    $telepon = test_input($bodyRequest['telepon']);
    if ($telepon == '') {
        return array('Masukkan no telepon');
    }
    $cekTelepon = mysqli_query($conn, "SELECT * FROM akun_user WHERE telepon = '$telepon' AND NOT id_user = '$idUser'");
    if (mysqli_num_rows($cekTelepon) > 0) {
        return array('No telepon ini sudah digunakan');
    }

    $email = test_input($bodyRequest['email']);
    if ($email == '') {
        return array('Masukkan e-mail');
    }
    $cekEmail = mysqli_query($conn, "SELECT * FROM akun_user WHERE email = '$email' AND NOT id_user = '$idUser'");
    if (mysqli_num_rows($cekEmail) > 0) {
        return array('E-mail ini sudah digunakan');
    }

    $updateUser = mysqli_query($conn, "UPDATE akun_user SET nama_lengkap_user = '$nama', telepon = '$telepon', email = '$email' WHERE id_user = '$idUser'");

    return array('Edit akun user berhasil');
}

function pesan_tiket($bodyRequest, $conn)
{
    $idUser = test_input($bodyRequest['idUser']);
    if ($idUser == '') {
        return array('Masukkan id user');
    }
    $tanggalBooking = test_input($bodyRequest['tanggalBooking']);
    if ($tanggalBooking == '') {
        return array('Masukkan tanggal pembelian');
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
    $statusBayar = 'Belum Lunas';

    $dateTime = new DateTime();
    $tanggalPesan = $dateTime->format('Y-m-d');
    $waktuPesan = $dateTime->format('H:i:s');

    $tambahTiket = mysqli_query($conn, "INSERT INTO tiket (jumlah_pengunjung, diskon_tiket, total_bayar, harga_tiket, week_tiket, status_tiket, kategori_tiket) VALUES ('$jumlahPengunjung', '$diskonTiket', '$totalBayar', '$hargaTiket', '$weekTiket', 'Booking', 'Tiket Digital')");
    $idTiket = mysqli_insert_id($conn);

    $namaPromo = test_input($bodyRequest['namaPromo']);
    if ($namaPromo != '') {
        $tambahPromo = mysqli_query($conn, "INSERT INTO record_promo (id_tiket, keterangan_promo) VALUES ('$idTiket', '$namaPromo')");
    }

    $kodeEt = order_et($tanggalBooking, $jumlahTiket, $totalBayar, $idTiket);
    $kodeQr = encrypted_qr($kodeEt);

    $tambahTiketDigital = mysqli_query($conn, "INSERT INTO tiket_digital (id_tiket, id_user, tanggal_pesan, waktu_pesan, tanggal_booking, status_bayar, order_id) VALUES ('$idTiket', '$idUser', '$tanggalPesan', '$waktuPesan', '$tanggalBooking', '$statusBayar', '$kodeEt')");
    $idTiketDigital = mysqli_insert_id($conn);

    $resItemTiket['idTiket'] = $idTiket;
    $resItemTiket['jumlahPengunjung'] = $jumlahPengunjung;
    $resItemTiket['jumlahTiket'] = $jumlahTiket;
    $resItemTiket['diskonTiket'] = $diskonTiket;
    $resItemTiket['totalBayar'] = $totalBayar;
    $resItemTiket['tanggalBooking'] = $tanggalBooking;
    $resItemTiket['statusBayar'] = $statusBayar;
    $resItemTiket['kodeEt'] = $kodeEt;
    $resItemTiket['kodeQr'] = $kodeQr;

    return array('Pesan tiket berhasil', $resItemTiket);
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

    $cekTiketDigital = mysqli_query($conn, "SELECT * FROM tiket tk, tiket_digital td WHERE tk.id_tiket = td.id_tiket AND tk.id_tiket = '$idTiket'");
    if (mysqli_num_rows($cekTiketDigital) == 0) {
        return array('Tiket tidak ditemukan');
    }
    $dataTiket = mysqli_fetch_assoc($cekTiketDigital);
    $tanggalBookingOld = $dataTiket['tanggal_booking'];

    $dateTime = new DateTime();
    $tanggalSekarang = $dateTime->format('Y-m-d');
    $waktuSekarang = $dateTime->format('H:i:s');
    $tanggalSekarangAdd30 = date('Y-m-d', strtotime('+30 days', strtotime($tanggalSekarang)));

    if ($tanggalBookingOld <= $tanggalSekarang && (strtotime('16:00:00')) <= (strtotime($waktuSekarang))) {
        return array('Reschedule tiket tidak diterima, batas maksimal sebelum jam operasional berakhir');
    }

    $jumlahPengunjung = $dataTiket['jumlah_pengunjung'];
    $diskonTiket = $dataTiket['diskon_tiket'];
    $jumlahTiket = strval($jumlahPengunjung - $diskonTiket);
    $statusBayar = $dataTiket['status_bayar'];
    $kodeEtOld = $dataTiket['order_id'];

    $kodeEtNew = order_et($tanggalBookingNew, $jumlahTiket, $totalBayar, $idTiket);
    $kodeQr = encrypted_qr($kodeEtNew);

    $tambahReschedule = mysqli_query($conn, "INSERT INTO record_reschedule (id_tiket, tanggal_booking_old, tanggal_booking_new, order_id_old, order_id_new) VALUES ('$idTiket', '$tanggalBookingOld', '$tanggalBookingNew', '$kodeEtOld', '$kodeEtNew')");
    $idReschedule = mysqli_insert_id($conn);
    $updateTiket = mysqli_query($conn, "UPDATE tiket SET harga_tiket = '$hargaTiket', week_tiket = '$weekTiket', total_bayar = '$totalBayar' WHERE id_tiket = '$idTiket'");
    $updateTiketDigital = mysqli_query($conn, "UPDATE tiket_digital SET tanggal_booking = '$tanggalBookingNew', order_id = '$kodeEtNew' WHERE id_tiket = '$idTiket'");

    $resItemTiket['idTiket'] = $idTiket;
    $resItemTiket['jumlahPengunjung'] = $jumlahPengunjung;
    $resItemTiket['jumlahTiket'] = $jumlahTiket;
    $resItemTiket['diskonTiket'] = $diskonTiket;
    $resItemTiket['totalBayar'] = $totalBayar;
    $resItemTiket['tanggalBooking'] = $tanggalBookingNew;
    $resItemTiket['statusBayar'] = $statusBayar;
    $resItemTiket['kodeEt'] = $kodeEtNew;
    $resItemTiket['kodeQr'] = $kodeQr;

    return array('Reschedule tiket berhasil', $resItemTiket);
}

function hapus_tiket($bodyRequest, $conn)
{
    $idTiket = test_input($bodyRequest['idTiket']);
    if ($idTiket == '') {
        return array('Masukkan id tiket');
    }

    $deleteTiket = mysqli_query($conn, "DELETE FROM tiket WHERE id_tiket = '$idTiket'");
    $deleteTiketDigital = mysqli_query($conn, "DELETE FROM tiket_digital WHERE id_tiket = '$idTiket'");

    return array('Hapus tiket berhasil');
}

function data_tiket_aktif($idUser, $conn)
{
    if ($idUser == '') {
        return array('Masukkan id user');
    }

    $cekTiket = mysqli_query($conn, "SELECT * FROM tiket tk, tiket_digital td WHERE td.id_tiket = tk.id_tiket AND tk.status_tiket = 'Booking' AND td.id_user = '$idUser' ORDER BY td.tanggal_booking ASC");
    if (mysqli_num_rows($cekTiket) == 0) {
        return array('Data tiket aktif belum ada');
    }

    $dateTime = new DateTime();
    $tanggalSekarang = $dateTime->format('Y-m-d');

    $summaryTransaksi = 0;
    while ($dataTiket = mysqli_fetch_assoc($cekTiket)) {
        $idTiket = $dataTiket['id_tiket'];
        $tanggalBooking = $dataTiket['tanggal_booking'];
        $jumlahPengunjung = $dataTiket['jumlah_pengunjung'];
        $diskonTiket = $dataTiket['diskon_tiket'];
        $jumlahTiket = $jumlahPengunjung - $diskonTiket;
        $totalBayar = $dataTiket['total_bayar'];
        $statusBayar = $dataTiket['status_bayar'];
        if ($statusBayar == 'Belum Lunas' && $tanggalBooking < $tanggalSekarang) {
            $updateTiket = mysqli_query($conn, "UPDATE tiket SET status_tiket = 'Dibatalkan Sistem' WHERE id_tiket = '$idTiket'");
            $updateTiketDigital = mysqli_query($conn, "UPDATE tiket_digital SET status_bayar = 'Dibatalkan Sistem' WHERE id_tiket = '$idTiket'");
            continue;
        }
        if ($tanggalBooking >= $tanggalSekarang) {
            if ($statusBayar == 'Belum Lunas') {
                $kodeEt = $dataTiket['order_id'];
                $kodeQr = encrypted_qr($kodeEt);
                $resItemTiket = array(
                    'idTiket' => $idTiket,
                    'jumlahPengunjung' => $jumlahPengunjung,
                    'jumlahTiket' => $jumlahTiket,
                    'diskonTiket' => $diskonTiket,
                    'totalBayar' => $totalBayar,
                    'tanggalBooking' => $tanggalBooking,
                    'statusBayar' => $statusBayar,
                    'kodeEt' => $kodeEt,
                    'kodeQr' => $kodeQr
                );
                $dataTiketArray[] = $resItemTiket;
                $summaryTransaksi++;
            }
        }
    }

    return array('Data tiket aktif ditemukan', $summaryTransaksi, $dataTiketArray);
}

function data_riwayat_tiket($idUser, $conn)
{
    if ($idUser == '') {
        return array('Masukkan id user');
    }

    $cekTiket = mysqli_query($conn, "SELECT * FROM tiket tk, tiket_digital td WHERE td.id_tiket = tk.id_tiket AND td.id_user = '$idUser' AND tk.status_tiket IN ('Masuk', 'Dibatalkan Sistem') AND td.status_bayar IN ('Lunas', 'Dibatalkan Sistem') ORDER BY td.tanggal_booking DESC");
    if (mysqli_num_rows($cekTiket) == 0) {
        return array('Data riwayat tiket belum ada');
    }

    while ($dataTiket = mysqli_fetch_assoc($cekTiket)) {
        $jumlahPengunjung = $dataTiket['jumlah_pengunjung'];
        $diskonTiket = $dataTiket['diskon_tiket'];
        $jumlahTiket = $jumlahPengunjung - $diskonTiket;
        $totalBayar = $dataTiket['total_bayar'];
        $tanggalBooking = $dataTiket['tanggal_booking'];
        $statusBayar = $dataTiket['status_bayar'];
        $kodeEt = $dataTiket['order_id'];

        if ($statusBayar != 'Lunas') {
            $kodeEt = $dataTiket['order_id'];
            $kodeQr = encrypted_qr($kodeEt);
            $resItemTiket = array(
                'jumlahPengunjung' => $jumlahPengunjung,
                'jumlahTiket' => $jumlahTiket,
                'totalBayar' => $totalBayar,
                'tanggalBooking' => $tanggalBooking,
                'statusBayar' => $statusBayar,
                'kodeEt' => $kodeEt
            );
        } else {
            $waktuTiket = $dataTiket['waktu_tiket'];
            $metodeBayar = $dataTiket['metode_bayar'];
            $resItemTiket = array(
                'jumlahPengunjung' => $jumlahPengunjung,
                'jumlahTiket' => $jumlahTiket,
                'totalBayar' => $totalBayar,
                'tanggalBooking' => $tanggalBooking,
                'waktuTiket' => $waktuTiket,
                'metodeBayar' => $metodeBayar,
                'statusBayar' => $statusBayar,
                'kodeEt' => $kodeEt
            );
        }
        $dataTiketArray[] = $resItemTiket;
    }
    $summaryTransaksi = mysqli_num_rows($cekTiket);

    return array('Data riwayat tiket ditemukan', $summaryTransaksi, $dataTiketArray);
}
