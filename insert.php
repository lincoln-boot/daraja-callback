<?php
if ($_GET['secret'] !== 'daylight123') {
    http_response_code(403);
    exit('Access denied');
}

$voucher    = $_POST['voucher'] ?? '';
$phone      = $_POST['phone'] ?? '';
$amount     = $_POST['amount'] ?? '';
$mpesa_code = $_POST['mpesa_code'] ?? '';

// ✅ Connect to InfinityFree MySQL database using IP address of sql313.infinityfree.com
$dbHost = '185.27.134.10'; // IP address for sql313.infinityfree.com
$dbUser = 'if0_38969326';
$dbPass = 'oj12202003';
$dbName = 'if0_38969326_hotspot_voucherslin';

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    http_response_code(500);
    echo 'Database connection error';
    exit();
}

// Save to DB
$stmt = $conn->prepare("INSERT INTO vouchers (voucher_code, phone, amount, mpesa_code) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $voucher, $phone, $amount, $mpesa_code);
$stmt->execute();
$stmt->close();
$conn->close();

echo "Saved successfully!";
?>
