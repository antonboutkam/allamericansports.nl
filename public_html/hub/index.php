<?php

$auth = htmlspecialchars($_GET["auth"]);

//If the correct parameter has been passed
if($auth == 'wM67StABDnaVtfeTUYV2'){
	
	require_once 'exact_main.php';

	$Exact_stock = new Exact_stock();
	$Exact_token = $Exact_stock->get_stock_exact('Bol');

	$plaintext = $Exact_token[1];
	$cipher = "aes-256-cbc";
	$ivlen = openssl_cipher_iv_length($cipher);
	$iv = "0123456789012345";
	$key = "akshayakshayaksh";
	$ciphertext = openssl_encrypt($plaintext, $cipher, $key, $options=0, $iv);

	file_put_contents("tQ7E0nEpC50ETf7svChn.txt", $ciphertext);

	echo "Succes! De voorraad update zal nu uitgevoerd worden. Dit scherm kan gesloten worden.";

}

?>