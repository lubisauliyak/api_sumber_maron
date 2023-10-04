<?php
require_once '../../koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $action = test_input($_POST['action']);
    if ($action == 'registrasiAdmin' && isset($_POST['nama']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['telepon']) && isset($_POST['username'])) {
        $nama = test_input($_POST['nama']);
        $email = test_input($_POST['email']);
        $password = test_input($_POST['password']);
        $telepon = test_input($_POST['telepon']);
        $username = test_input($_POST['username']);
        if (empty($nama)) {
            $response['text_message'] = 'Nama tidak boleh kosong.';
        } else if (empty($email)) {
            $response['text_message'] = 'Email tidak boleh kosong.';
        } else if (empty($password)) {
            $response['text_message'] = 'Password tidak boleh kosong.';
        } else if (empty($telepon)) {
            $response['text_message'] = 'Telepon tidak boleh kosong.';
        } else if (empty($username)) {
            $response['text_message'] = 'Username tidak boleh kosong.';
        } else {
            $password = md5($password);

            $sql = "SELECT * FROM akun_admin WHERE nama_lengkap = '$nama' AND email = '$email' AND telepon = '$telepon' AND username ='$username'";

            $result = mysqli_query($conn, $sql);
            $count = mysqli_num_rows($result);

            if ($count == 1) {
                $response['text_message'] = 'Akun sudah terdaftar, gunakan lainnya.';
            } else {
                $insert = "INSERT INTO akun_admin (rule, nama_lengkap, email, password, telepon, username, created_at) VALUES ('Petugas', '$nama', '$email', '$password', '$telepon', '$username', current_timestamp())";
                $query = mysqli_query($conn, $insert);
                if ($query) {
                    $response['text_message'] = 'Registrasi berhasil.';
                } else {
                    $response['text_message'] = 'Registrasi gagal.';
                }
            }
        }
    } else {
        $response['text_message'] = 'Data yang dimasukkan belum lengkap.';
    }
    echo json_encode($response);
} else {
    $response['text_message'] = 'Kesalahan Sistem HTTP.';
    echo json_encode($response);
}

$conn->close();
