<?php

$plaintext = "Dit_moet_encrypt_worden";
$cipher = "aes-256-cbc";
$ivlen = openssl_cipher_iv_length($cipher);
$iv = "0123456789012345";
$key = "akshayakshayaksh";
$ciphertext = openssl_encrypt($plaintext, $cipher, $key, $options=0, $iv);
echo $ciphertext;

?>