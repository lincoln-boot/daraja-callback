// callback.php logic here – will be filled after confirmation
<?php
// callback.php

// Step 1: Get raw POST data from Daraja
$data = file_get_contents("php://input");
$logFile = "mpesa_callback_log.txt"; // Optional: for logging
file_put_contents($logFile, $data, FILE_APPEND); // Log for debugging

// Step 2: Decode JSON data
$response = json_decode($data);

// Step 3: Check if payment is successful
$resultCode = $response->Body->stkCallback->ResultCode;

if ($resultCode == 0) {
    // Success
    $amount = $response->Body->stkCallback->CallbackMetadata->Item[0]->Value;
    $mpesaCode = $response->Body->stkCallback->CallbackMetadata->Item[1]->Value;
    $phone = $response->Body->stkCallback->CallbackMetadata->Item[4]->Value;

    // Step 4: Generate random voucher
    $voucher = strtoupper(substr(md5(time()), 0, 10)); // Example voucher

    // Step 5: Connect to InfinityFree MySQL database
    $conn = new mysqli("sql313.infinityfree.com", "if0_38969326", "oj12202003", "if0_38969326_epiz_12345678_daylight");
    if ($conn->connect_error) {
        die("DB Connection failed: " . $conn->connect_error);
    }

    // Step 6: Save voucher to DB
    $stmt = $conn->prepare("INSERT INTO vouchers (voucher_code, phone, amount, mpesa_code) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $voucher, $phone, $amount, $mpesaCode);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    // Step 7: Redirect to thank you page with voucher
    header("Location: thankyou.php?voucher=" . urlencode($voucher));
    exit();
} else {
    // Failed payment
    header("Location: thankyou.php?voucher=FAILED");
    exit();
}
?>
