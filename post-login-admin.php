<?php
require_once '../../koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    // Ambil nilai parameter post[aksi yg dilakukan] pada body
    $action = test_input($_POST['action']);

    // Menyesuaikan aksi dan mengambil nilai parameter post lainnya
    if ($action == 'loginAdmin') {
        // Cek apakah ada data yg masih kosong
        if (isset($_POST['email']) && isset($_POST['password'])) {
            $email = test_input($_POST['email']);
            $password = test_input($_POST['password']);
            // Hash password menggunakan fungsi SHA256 > Base64
            $password = hashPassword($password);

            // Mengambil data akun_admin
            $sql = "SELECT * FROM akun_admin WHERE email = '$email' AND password = '$password'";
            $result = mysqli_query($conn, $sql);
            $count = mysqli_num_rows($result);

            // Cek apakah ada data admin
            if ($count == 1) {
                $data = mysqli_fetch_array($result);

                // Tambahkan data akun_admin ke response json
                $response['text_message'] = 'Login berhasil.';
                $response['id_admin'] = $data['id_admin'];
                $response['rule_admin'] = $data['rule'];
                $response['name_admin'] = $data['nama_lengkap'];
            } else {
                $response['text_message'] = 'Login gagal.';
            }
        }
        // Terdapat nilai parameter post yg masih kosong
        else {
            $response['text_message'] = 'Data masukan kurang lengkap.';
        }
    }
    // Terdapat nilai parameter post yg masih kosong
    else {
        $response['text_message'] = 'Data masukan kurang lengkap.';
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