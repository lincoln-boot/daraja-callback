<?php
// callback.php on Render

// Step 1: Get raw POST data from Daraja
$data = file_get_contents("php://input");
file_put_contents("/tmp/mpesa_callback_log.txt", $data . PHP_EOL, FILE_APPEND); // Safe log

$response = json_decode($data);

if (!$response || !isset($response->Body->stkCallback->ResultCode)) {
    http_response_code(400);
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data']);
    exit();
}

$resultCode = $response->Body->stkCallback->ResultCode;

if ($resultCode == 0) {
    $items = $response->Body->stkCallback->CallbackMetadata->Item;

    // Extract values
    $amount = $items[0]->Value ?? 0;
    $mpesaCode = $items[1]->Value ?? 'UNKNOWN';
    $phone = $items[4]->Value ?? 'UNKNOWN';

    // Generate voucher
    $voucher = strtoupper(substr(md5(time()), 0, 10));

    // Send this to InfinityFree server
    $payload = http_build_query([
        'voucher' => $voucher,
        'phone' => $phone,
        'amount' => $amount,
        'mpesa_code' => $mpesaCode,
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded",
            'content' => $payload
        ]
    ]);

    // 🔐 Use secret key for security (insert.php should require this)
    $infinityfree_url = 'https://daylightwifi.great-site.net/insert.php?secret=daylight123';

    $response = file_get_contents($infinityfree_url, false, $context);

    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Callback forwarded to InfinityFree']);
    exit();
} else {
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Payment not successful']);
    exit();
}
