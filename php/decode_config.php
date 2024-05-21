<?php
include '../../TotalmenteSeguro/config.php';
// Convertendo de hexadecimal para texto original
$servername = hex2bin(constant("servername"));
$username = hex2bin(constant("username"));
$password = hex2bin(constant("password"));
$database = hex2bin(constant("database"));
?>
