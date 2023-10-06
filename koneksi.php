<?php
$servername = "localhost";
$database = "sumber_maron";
$username = "sumber-maron";
$password = "Tiketmaron123@";

$conn = mysqli_connect($servername, $username, $password, $database);
$response = array('code_response' => http_response_code());
if (!$conn) {
    $response['text_message'] = 'Tidak dapat mengakses Database';
    echo json_encode($response);
    return;
}

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function hashPassword($password)
{
    // Hash password dengan menggunakan SHA-256
    $hashedPassword = hash('sha256', $password . '-SM');

    // Kembalikan hasil dalam format Base64
    return base64_encode($hashedPassword);
}
