<?php

$servername = "localhost";
$database = "u625466052_sumbermaron";
$username = "u625466052_sumbermaron";
$password = "Sumbermaron123@";

$conn = @mysqli_connect($servername, $username, $password, $database);
if (mysqli_connect_errno()) {
    $response['textMessage'] = 'Koneksi database gagal, silakan coba lagi nanti atau hubungi dukungan teknis.';
    echo json_encode($response);
    die();
}
date_default_timezone_set('Asia/Jakarta');

function test_admin($conn)
{
    if (!$conn) {
        return 'Koneksi database admin gagal.';
    } else {
        return 'Koneksi database admin berhasil.';
    }
}

function log_admin($idAdmin, $idToken, $keterangan, $statusLog, $conn)
{
    $dateTime = new DateTime();
    $logAt = $dateTime->format('Y-m-d H:i:s');

    if ($idAdmin > 0) {
        $usedAtAdmin = mysqli_query($conn, "UPDATE akun_admin SET admin_used_at = '$logAt' WHERE id_admin = '$idAdmin'");
    }
    if ($idToken > 0) {
        $usedAtApi = mysqli_query($conn, "UPDATE token SET token_used_at = '$logAt' WHERE id_tokenized = '$idToken'");
    }
    $tambahLog = mysqli_query($conn, "INSERT INTO log_activity_admin (id_admin, keterangan_log_admin, status_log_admin, admin_activity_at) VALUES ('$idAdmin', '$keterangan', '$statusLog', '$logAt')");
}

function test_user($conn)
{
    if (!$conn) {
        return 'Koneksi database user gagal.';
    } else {
        return 'Koneksi database user berhasil.';
    }
}

function log_user($idUser, $idToken, $keterangan, $statusLog, $conn)
{
    $dateTime = new DateTime();
    $logAt = $dateTime->format('Y-m-d H:i:s');

    if ($idUser > 0) {
        $usedAtAdmin = mysqli_query($conn, "UPDATE akun_user SET user_used_at = '$logAt' WHERE id_user = '$idUser'");
    }
    if ($idToken > 0) {
        $usedAtApi = mysqli_query($conn, "UPDATE token SET token_used_at = '$logAt' WHERE id_tokenized = '$idToken'");
    }
    $tambahLog = mysqli_query($conn, "INSERT INTO log_activity_user (id_user, keterangan_log_user, status_log_user, user_activity_at) VALUES ('$idUser', '$keterangan', '$statusLog', '$logAt')");
}
