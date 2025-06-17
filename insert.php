<?php
// insert.php – now hosted on Render

// Secret key check
if (!isset($_GET['secret']) || $_GET['secret'] !== 'daylight123') {
    http_response_code(403);
    echo "Forbidden: Invalid secret key.";
    exit();
}

// Collect POST or GET data (Daraja uses POST, but fallback for testing)
$voucher    = $_POST['voucher']    ?? $_GET['voucher']    ?? '';
$phone      = $_POST['phone']      ?? $_GET['phone']      ?? '';
$amount     = $_POST['amount']     ?? $_GET['amount']     ?? '';
$mpesa_code = $_POST['mpesa_code'] ?? $_GET['mpesa_code'] ?? '';

// Validate basic data
if (!$voucher || !$phone || !$amount || !$mpesa_code) {
    http_response_code(400);
    echo "Missing required data.";
    exit();
}

// DB credentials for InfinityFree
$dbHost = 'sql313.infinityfree.com';
$dbUser = 'if0_38969326';
$dbPass = 'oj12202003';
$dbName = 'if0_38969326_hotspot_voucherslin';

// Connect to InfinityFree database
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo "DB connection failed: " . $conn->connect_error;
    exit();
}

// Insert voucher data
$stmt = $conn->prepare("INSERT INTO vouchers (voucher_code, phone, amount, mpesa_code) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $voucher, $phone, $amount, $mpesa_code);
$success = $stmt->execute();

if ($success) {
    echo "Voucher saved successfully.";
} else {
    http_response_code(500);
    echo "Insert failed: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
