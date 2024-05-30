<?php
function encrypt($data, $key) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

$credentials = [
    'servername' => 'localhost',
    'username' => 'root',
    'password' => 'mysqladmin',
    'database' => 'buscavet'
];

$poema = "Poema de Carlos Drummond de Andrade";
$key = substr(hash('sha256', $poema, true), 0, 32);

$encryptedData = [];
foreach ($credentials as $credKey => $value) {
    $encryptedData[$credKey] = encrypt($value, $key);
}

$dataToHide = json_encode($encryptedData);

$imagePath = 'C:/xampp/htdocs/img/lagarto.png';
$outputPath = 'C:/xampp/htdocs/img/lagarto_escondido.png';

$imageData = file_get_contents($imagePath);

$hiddenData = $imageData . '###' . base64_encode($dataToHide);

file_put_contents($outputPath, $hiddenData);

echo "Credenciais escondidas com sucesso na imagem!";
?>
