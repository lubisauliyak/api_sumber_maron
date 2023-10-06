<?php
require_once '../../koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    // Ambil nilai parameter post[aksi yg dilakukan] pada body
    $action = test_input($_POST['action']);

    // Menyesuaikan aksi dan mengambil nilai parameter post lainnya
    if ($action == 'registrasiAdmin' && isset($_POST['nama']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['telepon']) && isset($_POST['username'])) {
        $nama = test_input($_POST['nama']);
        $email = test_input($_POST['email']);
        $password = test_input($_POST['password']);
        $telepon = test_input($_POST['telepon']);
        $username = test_input($_POST['username']);
        // Cek apakah data nama kosong
        if (empty($nama)) {
            $response['text_message'] = 'Nama tidak boleh kosong.';
        }
        // Cek apakah data email kosong
        else if (empty($email)) {
            $response['text_message'] = 'Email tidak boleh kosong.';
        }
        // Cek apakah data password kosong
        else if (empty($password)) {
            $response['text_message'] = 'Password tidak boleh kosong.';
        }
        // Cek apakah data telepon kosong
        else if (empty($telepon)) {
            $response['text_message'] = 'Telepon tidak boleh kosong.';
        }
        // Cek apakah data username kosong
        else if (empty($username)) {
            $response['text_message'] = 'Username tidak boleh kosong.';
        }
        // Tidak ada data yg kosong
        else {
            // Hash password menggunakan fungsi SHA256 > Base64
            $password = hashPassword($password);

            // Menambah data akun_admin yg baru
            $sql = "SELECT * FROM akun_admin WHERE nama_lengkap = '$nama' AND email = '$email' AND telepon = '$telepon' AND username ='$username'";
            $result = mysqli_query($conn, $sql);
            $count = mysqli_num_rows($result);

            // Cek apakah data admin sudah terdaftar
            if ($count == 1) {
                $response['text_message'] = 'Akun sudah terdaftar, gunakan lainnya.';
            }
            // Data user belum terdaftar
            else {
                // Menambah akun_admin yg baru
                $insert = "INSERT INTO akun_admin (rule, nama_lengkap, email, password, telepon, username, created_at) VALUES ('Petugas', '$nama', '$email', '$password', '$telepon', '$username', current_timestamp())";
                $query = mysqli_query($conn, $insert);

                // Cek apakah query berhasil
                if ($query) {
                    $response['text_message'] = 'Registrasi berhasil.';
                } else {
                    $response['text_message'] = 'Registrasi gagal.';
                }
            }
        }
    }
    // Terdapat nilai parameter post yg masih kosong
    else {
        $response['text_message'] = 'Data yang dimasukkan belum lengkap.';
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
