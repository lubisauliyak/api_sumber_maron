<?php
require_once '../../../functions_app/functionUser.php';

// Mendapatkan status code
$statusCode = http_response_code();
$response = array();
$response['statusCode'] = strval($statusCode);

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $jsonString = file_get_contents("php://input");
    $bodyRequest = json_decode($jsonString, true);
    if (isset($bodyRequest['action'])) {
        $action = test_input($bodyRequest['action']);

        /* - FUNGSI LOGIN */
        if ($action == 'login-user') {
            $hasil = login_user($bodyRequest, $conn);
            if ($hasil[0] == 'Login berhasil') {
                $response['textMessage'] = $hasil[0];
                $response['dataUser'] = $hasil[1];
                $response['dataBanner'] = $hasil[2];
                $response['dataHariLibur'] = $hasil[3];
                $response['dataLokasiParkir'] = $hasil[4];
            } else {
                $response['textMessage'] = $hasil[0];
            }
        }

        /* - FUNGSI DAFTAR */ else if ($action == 'registrasi-user') {
            $hasil = registrasi_user($bodyRequest, $conn);
            if ($hasil[0] == 'Pendaftaran berhasil') {
                $response['textMessage'] = $hasil[0];
            } else {
                $response['textMessage'] = $hasil[0];
            }
        }

        /* - FUNGSI EDIT USER */ else if ($action == 'edit-user') {
            $hasil = edit_user($bodyRequest, $conn);
            if ($hasil[0] == 'Edit akun user berhasil') {
                $response['textMessage'] = $hasil[0];
            } else {
                $response['textMessage'] = $hasil[0];
            }
        }

        /* - FUNGSI DATA BANNER */ else if ($action == 'data-banner') {
            $idUser = test_input($bodyRequest['idUser']);
            $hasil = banner($idUser, $conn);
            $response['textMessage'] = 'Data banner didapatkan';
            $response['dataBanner'] = $hasil;
        }

        /* - FUNGSI DATA LOKASI PARKIR */ else if ($action == 'data-lokasi-parkir') {
            $hasil = lokasi_parkir($conn);
            $response['textMessage'] = 'Data lokasi parkir didapatkan';
            $response['dataLokasiParkir'] = $hasil;
        }

        /* - FUNGSI DATA HARI LIBUR */ else if ($action == 'data-hari-libur') {
            $hasil = hari_libur($conn);
            $response['textMessage'] = 'Data hari libur didapatkan';
            $response['dataHariLibur'] = $hasil;
        }

        /* - FUNGSI PESAN TIKET */ else if ($action == 'pesan-tiket') {
            $hasil = pesan_tiket($bodyRequest, $conn);
            if ($hasil[0] == 'Pesan tiket berhasil') {
                $response['textMessage'] = $hasil[0];
                $response['dataTiket'] = $hasil[1];
            } else {
                $response['textMessage'] = $hasil[0];
            }
        }

        /* - FUNGSI RESCHEDULE TIKET */ else if ($action == 'reschedule-tiket') {
            $hasil = reschedule_tiket($bodyRequest, $conn);
            if ($hasil[0] == 'Reschedule tiket berhasil') {
                $response['textMessage'] = $hasil[0];
                $response['dataTiket'] = $hasil[1];
            } else {
                $response['textMessage'] = $hasil[0];
            }
        }

        /* - FUNGSI HAPUS TIKET */ else if ($action == 'hapus-tiket') {
            $hasil = hapus_tiket($bodyRequest, $conn);
            $response['textMessage'] = $hasil[0];
        }

        /* - FUNGSI DAFTAR TIKET AKTIF */ else if ($action == 'data-tiket-aktif') {
            $idUser = test_input($bodyRequest['idUser']);
            $hasil = data_tiket_aktif($idUser, $conn);
            if ($hasil[0] == 'Data tiket aktif ditemukan') {
                $response['textMessage'] = $hasil[0];
                $response['summaryTiket'] = $hasil[1];
                $response['dataTiket'] = $hasil[2];
            } else {
                $response['textMessage'] = $hasil[0];
            }
        }

        /* - FUNGSI DAFTAR RIWAYAT TIKET */ else if ($action == 'data-riwayat-tiket') {
            $idUser = test_input($bodyRequest['idUser']);
            $hasil = data_riwayat_tiket($idUser, $conn);
            if ($hasil[0] == 'Data riwayat tiket ditemukan') {
                $response['textMessage'] = $hasil[0];
                $response['summaryTiket'] = $hasil[1];
                $response['dataTiket'] = $hasil[2];
            } else {
                $response['textMessage'] = $hasil[0];
            }
        }
    } else {
        $response['textMessage'] = 'Permintaan action tidak ditemukan';
    }
} else {
    $response['textMessage'] = 'Isi permintaan tidak ditemukan';
}

echo json_encode($response, JSON_UNESCAPED_SLASHES);
