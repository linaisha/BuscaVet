<?php
$privateKeyPath = '../chaves/private_key.pem';
$publicKeyPath = '../chaves/public_key.pem';

$privateKey = openssl_pkey_new(array(
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
));

openssl_pkey_export_to_file($privateKey, $privateKeyPath);

$publicKey = openssl_pkey_get_details($privateKey)["key"];
file_put_contents($publicKeyPath, $publicKey);

echo "Chaves geradas com sucesso!";
?>
