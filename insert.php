<?php
// insert.php on Render

// ✅ Require secret key
$secretKey = $_GET['secret'] ?? '';
if ($secretKey !== 'daylight123') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// ✅ Collect data
$voucher    = $_POST['voucher'] ?? '';
$phone      = $_POST['phone'] ?? '';
$amount     = $_POST['amount'] ?? '';
$mpesa_code = $_POST['mpesa_code'] ?? '';

// ✅ Validate
if (empty($voucher) || empty($phone) || empty($amount) || empty($mpesa_code)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

// ✅ Connect to InfinityFree MySQL
$dbHost = 'sql313.infinityfree.com';
$dbUser = 'if0_38969326';
$dbPass = 'oj12202003';
$dbName = 'if0_38969326_hotspot_voucherslin';

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// ✅ Insert into vouchers table
$stmt = $conn->prepare("INSERT INTO vouchers (voucher_code, phone, amount, mpesa_code) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $voucher, $phone, $amount, $mpesa_code);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'voucher' => $voucher]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Insert failed']);
}

$stmt->close();
$conn->close();
?>
