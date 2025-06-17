<?php
// callback.php – hosted on Render

// Connect to InfinityFree DB
$conn = new mysqli(
    "sql313.infinityfree.com",
    "if0_38969326",
    "oj12202003",
    "if0_38969326_hotspot_voucherslin"
);

// Get and decode M-PESA callback data
$data = json_decode(file_get_contents('php://input'), true);
file_put_contents("mpesa_log.txt", json_encode($data) . "\n", FILE_APPEND); // log all callbacks

if (!isset($data['Body']['stkCallback'])) {
    echo json_encode(["ResultCode" => 1, "ResultDesc" => "Invalid callback structure"]);
    exit;
}

// Extract values
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

if (!$mpesa_code || !$amount || !$phone) {
    echo json_encode(["ResultCode" => 1, "ResultDesc" => "Missing values"]);
    exit;
}

// Find unused voucher
$sql = "SELECT * FROM vouchers WHERE phone IS NULL AND amount = '$amount' LIMIT 1";
$res = $conn->query($sql);

if ($res->num_rows > 0) {
    $voucher = $res->fetch_assoc();
    $code = $voucher['voucher_code'];

    $conn->query("UPDATE vouchers SET phone='$phone', mpesa_code='$mpesa_code' WHERE voucher_code='$code'");

    // Log for debugging
    file_put_contents("voucher_log.txt", "Paid: $phone -> $code\n", FILE_APPEND);

    echo json_encode(["ResultCode" => 0, "ResultDesc" => "Voucher assigned"]); }
    else {
    echo json_encode(["ResultCode" => 1, "ResultDesc" => "No available voucher for amount $amount"]);
}
?>
