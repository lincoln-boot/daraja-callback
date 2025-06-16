<?php
// callback.php on Render

// Step 1: Get raw POST data from Daraja
$data = file_get_contents("php://input");

// Step 2: Log the incoming request for debugging
file_put_contents("/tmp/mpesa_callback_log.txt", $data . PHP_EOL, FILE_APPEND);

// Step 3: Decode JSON from M-PESA callback
$response = json_decode($data);

if (!$response || !isset($response->Body->stkCallback->ResultCode)) {
    http_response_code(400);
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data']);
    exit();
}

$resultCode = $response->Body->stkCallback->ResultCode;

if ($resultCode == 0) {
    // Extract transaction details
    $items = $response->Body->stkCallback->CallbackMetadata->Item;

    $amount    = $items[0]->Value ?? 0;
    $mpesaCode = $items[1]->Value ?? 'UNKNOWN';
    $phone     = $items[4]->Value ?? 'UNKNOWN';

    // Generate unique voucher
    $voucher = strtoupper(substr(md5(time()), 0, 10));

    // Step 4: Send data to InfinityFree insert.php
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
            'content' => $payload,
        ]
    ]);

    // ✅ Secure insert endpoint with a secret key
    $infinityfree_url = 'https://daylightwifi.great-site.net/insert.php?secret=daylight123';

    // Step 5: Forward to InfinityFree
    $response = file_get_contents($infinityfree_url, false, $context);

    // Ste
