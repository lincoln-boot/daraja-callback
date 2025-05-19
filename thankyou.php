<?php
$voucher = $_GET['voucher'] ?? 'NO_VOUCHER';
echo "Your voucher is: " . htmlspecialchars($voucher);
?>
