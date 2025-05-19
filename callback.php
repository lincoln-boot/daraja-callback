<?php
// callback.php

// Step 1: Get raw POST data from Daraja
$data = file_get_contents("php://input");
file_put_contents("/tmp/mpesa_callback_log.txt", $data . PHP_EOL, FILE_APPEND); // Safe log

// Step 2: Decode JSON data
$response = json_decode($data);

if (!$response || !isset($response->Body->stkCallback->ResultCode)) {
    http_response_code(400);
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data']);
    exit();
}

$resultCode = $response->Body->stkCallback->ResultCode;

if ($resultCode == 0) {
    // Successful payment
    $amount    = $response->Body->stkCallback->CallbackMetadata->Item[0]->Value ?? 0;
    $mpesaCode = $response->Body->stkCallback->CallbackMetadata->Item[1]->Value ?? '';
    $phone     = $response->Body->stkCallback->CallbackMetadata->Item[4]->Value ?? '';

    // Generate voucher
    $voucher = strtoupper(substr(md5(time()), 0, 10));

    // Step 3: Connect to your InfinityFree MySQL database (IP is more reliable than hostname)
    $dbHost = 'IP_ADDRESS_HERE'; // Replace this with resolved IP of sql313.infinityfree.com
    $dbUser = 'if0_38969326';
    $dbPass = 'oj12202003';
    $dbName = 'if0_38969326_epiz_12345678_daylight';

    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        file_put_contents("/tmp/db_error_log.txt", $conn->connect_error . PHP_EOL, FILE_APPEND);
        echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'DB connection error']);
        exit();
    }

    // Step 4: Save voucher to database
    $stmt = $conn->prepare("INSERT INTO vouchers (voucher_code, phone, amount, mpesa_code) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $voucher, $phone, $amount, $mpesaCode);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Callback received successfully']);
    exit();
} else {
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Payment failed or cancelled']);
    exit();
}
?>
