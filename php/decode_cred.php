<?php
function decrypt($data, $key) {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

$poema = "Poema de Carlos Drummond de Andrade";
$key = substr(hash('sha256', $poema, true), 0, 32);

$imagePath = 'C:/xampp/htdocs/img/lagarto_escondido.png';

$imageData = file_get_contents($imagePath);
$pos = strrpos($imageData, '###');
if ($pos === false) {
    die("Delimitador não encontrado na imagem.");
}

$encodedData = substr($imageData, $pos + 3);
$hiddenData = base64_decode($encodedData);

$decodedData = json_decode($hiddenData, true);

if (!$decodedData) {
    die("Falha ao decodificar os dados.");
}

$credentials = [];
foreach ($decodedData as $credKey => $value) {
    $credentials[$credKey] = decrypt($value, $key);
}
?>
