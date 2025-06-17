<?php
// Connect to DB
$conn = new mysqli("sql313.infinityfree.com", "if0_38969326", "oj12202003", "if0_38969326_hotspot_voucherslin");

$data = json_decode(file_get_contents('php://input'), true);
$mpesa_code = $data['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'];
$phone = $data['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value'];
$amount = $data['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'];

// Find unused voucher
$sql = "SELECT * FROM vouchers WHERE phone IS NULL AND amount = '$amount' LIMIT 1";
$res = $conn->query($sql);
if ($res->num_rows > 0) {
    $voucher = $res->fetch_assoc();
    $code = $voucher['voucher_code'];
    $conn->query("UPDATE vouchers SET phone='$phone', mpesa_code='$mpesa_code' WHERE voucher_code='$code'");
    file_put_contents("voucher_log.txt", "Paid: $phone -> $code\n", FILE_APPEND);
}
?>
