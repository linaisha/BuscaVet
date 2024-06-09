<?php
header('Content-Type: application/json');

$certPath = '../chaves/certificate.pem';
$certificate = file_get_contents($certPath);

echo json_encode(['certificate' => $certificate]);
?>