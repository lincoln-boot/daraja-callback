<?php
// callback.php (on Render)

// Step 1: Get raw POST data
$data = file_get_contents("php://input");

// Log raw data (optional but useful)
file_put_contents("mpesa_callback_log.txt", $data . PHP_EOL, FILE_APPEND);

// Step 2: Decode the JSON
$response = json_decode($data);

if (!$response || !isset($response->Body->stkCallback->ResultCode)) {
    http_response_code(400); // Tell Daraja something went wrong
    echo json_encode([
        "ResultCode" => 1,
        "ResultDesc" => "Invalid callback data"
    ]);
    exit();
}

// Step 3: Extract values safely
$callback = $response->Body->stkCallback;
$resultCode = $callback->ResultCode;

if ($resultCode == 0) {
    $metadata = $callback->CallbackMetadata->Item;

    $amount = $metadata[0]->Value ?? 0;
    $mpesaCode = $metadata[1]->Value ?? '';
    $phone = $metadata[4]->Value ?? '';

    $voucher = strtoupper(substr(md5(time()), 0, 10));

    // Step 4: Connect to InfinityFree MySQL DB
    $conn = new mysqli("sql313.infinityfree.com", "if0_38969326", "oj12202003", "if0_38969326_epiz_12345678_daylight");

    if ($conn->connect_error) {
        file_put_contents("db_error_log.txt", $conn->connect_error . PHP_EOL, FILE_APPEND);
        echo json_encode([
            "ResultCode" => 1,
            "ResultDesc" => "DB connection failed"
        ]);
        exit();
    }

    // Step 5: Save voucher to DB
    $stmt = $conn->prepare("INSERT INTO vouchers (voucher_code, phone, amount, mpesa_code) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $voucher, $phone, $amount, $mpesaCode);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    // Step 6: Respond to Daraja
    echo json_encode([
        "ResultCode" => 0,
        "ResultDesc" => "Callback processed successfully"
    ]);
} else {
    echo json_encode([
        "ResultCode" => 0,
        "ResultDesc" => "Payment failed or cancelled"
    ]);
}
?>
