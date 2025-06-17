<?php
$host = 'localhost';
$dbname = 'voucher_db';
$username = 'root';
$password = '';

function generateCode($length = 6) {
    return strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'), 0, $length));
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $count = 100; // Number of vouchers to generate

    for ($i = 0; $i < $count; $i++) {
        $code = generateCode(); // Generate random code
        $stmt = $conn->prepare("INSERT INTO vouchers (code, used) VALUES (?, 0)");
        $stmt->execute([$code]);
    }

    echo "$count vouchers generated successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
