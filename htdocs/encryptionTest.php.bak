<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 28/09/2018
 * Time: 10:16
 */


$publicKey = file_get_contents('c:\\keys\\privkey.pub');

openssl_public_encrypt(
    "some data",
    $cryptedData,
    $publicKey,
    OPENSSL_PKCS1_OAEP_PADDING
);

echo "Encrypted data: " . base64_encode($cryptedData);