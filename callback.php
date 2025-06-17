<?php
// callback.php

// Set headers
header("Content-Type: application/json");

// Read the incoming callback
$data = file_get_contents("php://input");
$callback = json_decode($data, true);

// Log if decoding fails
if (!isset($callback['Body']['stkCallback'])) {
    echo json_encode(["ResultCode" => 1, "ResultDesc" => "Invalid callback data"]);
    exit;
}

// Get the transaction details
$stkCallback = $callback['Body']['stkCallback'];
$resultCode = $stkCallback['ResultCode'];
$resultDesc = $stkCallback['ResultDesc'];

// If transaction failed, exit
if ($resultCode !== 0) {
    echo json_encode(["ResultCode" => 0, "ResultDesc" => "Transaction failed"]);
    exit;
}

// Get phone number from callback
$phoneNumber = null;
foreach ($stkCallback['CallbackMetadata']['Item'] as $item) {
    if ($item['Name'] === 'PhoneNumber') {
        $phoneNumber = $item['Value'];
        break;
    }
}

// Format phone number (remove + or spaces)
$phone = preg_replace('/[^0-9]/', '', $phoneNumber);

// === CONNECT TO DATABASE AND PICK VOUCHER ===
$host = 'localhost';
$dbname = 'voucher_db';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Select one unused voucher
    $stmt = $conn->prepare("SELECT code FROM vouchers WHERE used = 0 LIMIT 1");
    $stmt->execute();
    $voucher = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($voucher) {
        // Mark as used
        $update = $conn->prepare("UPDATE vouchers SET used = 1, assigned_to = ? WHERE code = ?");
        $update->execute([$phone, $voucher['code']]);

        // Send voucher to InfinityFree
        $insert_url = 'https://daylightwifi.great-site.net/insert.php?code=' . urlencode($voucher['code']) . '&phone=' . urlencode($phone);
        file_get_contents($insert_url);

        echo json_encode(["ResultCode" => 0, "ResultDesc" => "Success"]);
    } else {
        echo json_encode(["ResultCode" => 1, "ResultDesc" => "No voucher available"]);
    }

} catch (PDOException $e) {
    file_put_contents("db_error_log.txt", $e->getMessage());
    echo json_encode(["ResultCode" => 1, "ResultDesc" => "Database error"]);
}
?>
