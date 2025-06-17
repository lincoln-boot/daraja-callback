<?php
// callback.php hosted on Render

// 1. Receive raw M-PESA callback data
$data = file_get_contents("php://input");

// Optional: Log it to check later
file_put_contents("/tmp/mpesa_callback_log.txt", $data . PHP_EOL, FILE_APPEND);

// 2. Decode JSON
$response = json_decode($data);

// Validate structure
if (!$response || !isset($response->Body->stkCallback->ResultCode)) {
    http_response_code(400);
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data']);
    exit();
}

// 3. Check if transaction was successful
$resultCode = $response->Body->stkCallback->ResultCode;

if ($resultCode == 0) {
    $items = $response->Body->stkCallback->CallbackMetadata->Item;

    // 4. Extract data safely
    $amount     = $items[0]->Value ?? 0;
    $mpesaCode  = $items[1]->Value ?? 'UNKNOWN';
    $phone      = $items[4]->Value ?? 'UNKNOWN';

    // 5. Generate unique voucher
    $voucher = strtoupper(substr(md5(time()), 0, 10));

    // 6. Prepare payload for insert.php (hosted on another server)
    $payload = http_build_query([
        'voucher'    => $voucher,
        'phone'      => $phone,
        'amount'     => $amount,
        'mpesa_code' => $mpesaCode,
    ]);

    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded",
            'content' => $payload
        ]
    ]);

    // ✅ Replace this URL with where insert.php is hosted (on Replit, 000Webhost, etc.)
    $render_url = 'https://your-render-service-name.onrender.com/insert.php?secret=daylight123';

    // 7. Send to InfinityFree or alternative insert.php
    $insert_response = file_get_contents($insert_url, false, $context);

    // 8. Respond to M-PESA (always)
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Callback forwarded to InfinityFree']);
    exit(); } 
    else {
    // Payment failed or cancelled
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Payment not successful']);
    exit();
}
?>
