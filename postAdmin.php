<?php
require_once '../../../functions_app/functionAdmin.php';

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
        if ($action == 'login-admin') {
            $hasil = login_admin($bodyRequest, $conn);
            if ($hasil[0] == 'Login berhasil') {
                $response['textMessage'] = $hasil[0];
                $response['dataAdmin'] = $hasil[1];
                $response['dataHariLibur'] = $hasil[2];
                $response['dataSummaryTiket'] = $hasil[3];
            } else {
                $response['textMessage'] = $hasil[0];
            }
        }

        /* - FUNGSI DATA HARI LIBUR */ else if ($action == 'data-hari-libur') {
            $hasil = hari_libur($conn);
            $response['textMessage'] = 'Data hari libur didapatkan';
            $response['dataHariLibur'] = $hasil;
        }

        /* - FUNGSI DATA SUMMARY TIKET */ else if ($action == 'data-summary-tiket') {
            $hasil = summary_tiket($conn);
            $response['textMessage'] = 'Data summary tiket didapatkan';
            $response['dataSummaryTiket'] = $hasil;
        }

        /* - FUNGSI SCAN TIKET */ else if ($action == 'scan-tiket') {
            $hasil = scan_tiket($bodyRequest, $conn);
            if ($hasil[0] == 'Tiket valid') {
                $response['textMessage'] = $hasil[0];
                $response['dataTiket'] = $hasil[1];
            } else {
                $response['textMessage'] = $hasil[0];
            }
        }

        /* - FUNGSI EDIT TIKET */ else if ($action == 'edit-tiket') {
            $hasil = edit_tiket($bodyRequest, $conn);
            if ($hasil[0] == 'Edit tiket berhasil') {
                $response['textMessage'] = $hasil[0];
                $response['dataTiket'] = $hasil[1];
            } else {
                $response['textMessage'] = $hasil[0];
            }
        }

        /* - FUNGSI TUKAR TIKET */ else if ($action == 'tukar-tiket') {
            $hasil = tukar_tiket($bodyRequest, $conn);
            $response['textMessage'] = $hasil[0];
        }

        /* - FUNGSI RESCHEDULE TIKET */ else if ($action == 'reschedule-tiket') {
            $hasil = reschedule_tiket($bodyRequest, $conn);
            if ($hasil[0] == 'Reschedule tiket khusus berhasil') {
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

        /* - FUNGSI BELI TIKET KONVENSIONAL */ else if ($action == 'tiket-konvensional') {
            $hasil = tiket_konvensional($bodyRequest, $conn);
            if ($hasil[0] == 'Beli tiket konvensional berhasil') {
                $response['textMessage'] = $hasil[0];
                $response['dataTiket'] = $hasil[1];
            } else {
                $response['textMessage'] = $hasil[0];
            }
        }

        /* - FUNGSI DAFTAR TIKET KHUSUS */ else if ($action == 'data-tiket-khusus') {
            $hasil = data_tiket_khusus($conn);
            if ($hasil[0] == 'Data tiket khusus ditemukan') {
                $response['textMessage'] = $hasil[0];
                $response['summaryTiket'] = $hasil[1];
                $response['dataTiket'] = $hasil[2];
            } else {
                $response['textMessage'] = $hasil[0];
            }
        }

        /* - FUNGSI DAFTAR TIKET */ else if ($action == 'data-tiket-masuk') {
            $hasil = data_tiket_masuk($conn);
            if ($hasil[0] == 'Data tiket masuk ditemukan') {
                $response['textMessage'] = $hasil[0];
                $response['summaryTiket'] = $hasil[1];
                $response['dataTiket'] = $hasil[2];
            } else {
                $response['textMessage'] = $hasil[0];
            }
        }

        /* - FUNGSI ACTION TIDAK VALID */ else {
            $response['textMessage'] = 'Permintaan action tidak valid';
        }
    } else {
        $response['textMessage'] = 'Permintaan action tidak ditemukan';
    }
} else {
    $response['textMessage'] = 'Isi permintaan tidak ditemukan';
}

echo json_encode($response, JSON_UNESCAPED_SLASHES);
