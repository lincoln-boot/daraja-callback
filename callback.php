<?php
// callback.php

// Step 1: Read raw JSON data from M-PESA
$data = file_get_contents("php://input");

// Optional: Log raw callback data for debugging
file_put_contents("mpesa_callback_log.txt", $data . PHP_EOL, FILE_APPEND);

// Step 2: Decode JSON data
$response = json_decode($data);

// Step 3: Validate response structure
if (!$response || !isset($response->Body->stkCallback->ResultCode)) {
    http_response_code(400);
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data']);
    exit();
}

$resultCode = $response->Body->stkCallback->ResultCode;

if ($resultCode == 0) {
    // Step 4: Extract metadata safely
    $callbackItems = $response->Body->stkCallback->CallbackMetadata->Item;

    $amount = $callbackItems[0]->Value ?? 0;
    $mpesaCode = $callbackItems[1]->Value ?? '';
    $phone = $callbackItems[4]->Value ?? '';

    // Step 5: Generate a random voucher
    $voucher = strtoupper(substr(md5(time()), 0, 10));

    // Step 6: Connect to MySQL using IP instead of hostname
    $conn = new mysqli("185.27.134.10", "if0_38969326", "oj12202003", "if0_38969326_epiz_12345678_daylight");

    if ($conn->connect_error) {
        // Log and respond if DB fails
        file_put_contents("db_error_log.txt", $conn->connect_error . PHP_EOL, FILE_APPEND);
        echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'DB connection error']);
        exit();
    }

    // Step 7: Save voucher to database
    $stmt = $conn->prepare("INSERT INTO vouchers (voucher_code, phone, amount, mpesa_code) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $voucher, $phone, $amount, $mpesaCode);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    // Step 8: Acknowledge Safaricom
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Callback received successfully']);
    exit();

} else {
    // Payment failed or cancelled
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Payment failed or cancelled']);
    exit();
}
?>
