<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 30/11/2018
 * Time: 9:42
 */

namespace CNCLTD;


use Exception;

class Encryption
{

    public static function decrypt($privateKeyPath,
                                   $passphrase,
                                   $encryptedData
    )
    {
        $keyData = file_get_contents($privateKeyPath);

        $key = openssl_pkey_get_private(
            $keyData,
            $passphrase
        );

        if (!$key) {
            throw new Exception('Passphrase not valid');
        }

        openssl_private_decrypt(
            base64_decode($encryptedData),
            $decryptedData,
            $key,
            OPENSSL_PKCS1_OAEP_PADDING
        );
        return $decryptedData;

    }

    public static function encrypt($publicKeyPath,
                                   $data
    )
    {
        $publicKey = file_get_contents($publicKeyPath);
        openssl_public_encrypt(
            $data,
            $cryptedData,
            $publicKey,
            OPENSSL_PKCS1_OAEP_PADDING
        );
        return base64_encode($cryptedData);
    }
}