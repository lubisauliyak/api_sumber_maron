<?php
$servername = "localhost";
$database = "u625466052_airmaroon";
$username = "u625466052_airmaroon";
$password = "Airmaroon123@";

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
