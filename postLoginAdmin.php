<?php
require_once '../../koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $action = test_input($_POST['action']);
    if ($action == 'loginAdmin') {
        if (isset($_POST['email']) && isset($_POST['password'])) {
            $email = test_input($_POST['email']);
            $password = test_input($_POST['password']);
            $password = md5($password);

            $sql = "SELECT * FROM akun_admin WHERE email = '$email' AND password = '$password'";

            $result = mysqli_query($conn, $sql);
            $count = mysqli_num_rows($result);

            if ($count == 1) {
                $data = mysqli_fetch_array($result);

                $response['text_message'] = 'Login berhasil.';
                $response['id_admin'] = $data['id_admin'];
                $response['rule_admin'] = $data['rule'];
                $response['name_admin'] = $data['nama_lengkap'];
            } else {
                $response['text_message'] = 'Login gagal.';
            }
        } else {
            $response['text_message'] = 'Data masukan kurang lengkap.';
        }
    } else {
        $response['text_message'] = 'Data masukan kurang lengkap.';
    }
    echo json_encode($response);
} else {
    $response['text_message'] = 'Kesalahan sistem HTTP.';
    echo json_encode($response);
}

$conn->close();
