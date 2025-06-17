<?php
$data = json_decode(file_get_contents('php://input'), true);

file_put_contents("mpesa_log.txt", json_encode($data) . "\n", FILE_APPEND);

$items = $data['Body']['stkCallback']['CallbackMetadata']['Item'] ?? [];
$mpesa_code = null;
$amount = null;
$phone = null;

foreach ($items as $item) {
    if ($item['Name'] == 'MpesaReceiptNumber') {
        $mpesa_code = $item['Value'];
    } elseif ($item['Name'] == 'Amount') {
        $amount = $item['Value'];
    } elseif ($item['Name'] == 'PhoneNumber') {
        $phone = preg_replace('/[^0-9]/', '', $item['Value']);
    }
}

if ($mpesa_code && $amount && $phone) {
    $url = "https://daylightwifi.great-site.net/insert.php?phone=$phone&mpesa=$mpesa_code&amount=$amount";
    $response = file_get_contents($url);

    file_put_contents("insert_log.txt", "$url\n$response\n", FILE_APPEND);
    echo json_encode(["ResultCode" => 0, "ResultDesc" => "Accepted"]);
} else {
    echo json_encode(["ResultCode" => 1, "ResultDesc" => "Missing data"]);
}
?>
