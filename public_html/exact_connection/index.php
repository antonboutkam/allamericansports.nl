<?php

require_once 'exact_main.php';

$Exact_stock = new Exact_stock();
$Exact_token = $Exact_stock->get_stock_exact('Bol');

echo strval($Exact_token[0]);
//echo strval($Exact_token[1]);

$plaintext = $Exact_token[0];
$cipher = "aes-256-cbc";
$ivlen = openssl_cipher_iv_length($cipher);
$iv = "0123456789012345";
$key = "akshayakshayaksh";
$ciphertext = openssl_encrypt($plaintext, $cipher, $key, $options=0, $iv);
echo $ciphertext;

$text = $ciphertext;

file_put_contents("test.txt", $text);


?>