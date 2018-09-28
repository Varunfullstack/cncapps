<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 28/09/2018
 * Time: 10:24
 */


if ($_POST) {


    if (isset($_REQUEST['Encrypt'])) {

        $publicKey = file_get_contents('c:\\keys\\privkey.pub');

        openssl_public_encrypt(
            $_POST['toEncryptData'],
            $cryptedData,
            $publicKey,
            OPENSSL_PKCS1_OAEP_PADDING
        );
        ?>
        The encrypted data is:
        <div style="width: 250px">
            <?= base64_encode($cryptedData) ?>
        </div>
        <a href="?decrypt=true">Decrypt</a>
        <a href="?enctyp=true">Encrypt</a>
        <?php
    } else {
        $keyData = file_get_contents('c:\\keys\\privkey.pem');

        $key = openssl_pkey_get_private(
            $keyData,
            $_POST['passphrase']
        );
        openssl_private_decrypt(
            base64_decode($_POST['encryptedData']),
            $decryptedData,
            $key,
            OPENSSL_PKCS1_OAEP_PADDING
        );

        ?>
        The encrypted data was <?= $decryptedData ?>
        <a href="?decrypt=true">Decrypt</a>
        <a href="?enctyp=true">Encrypt</a>
        <?php
    }


} else {

    if (!isset($_GET['decrypt'])) {
        ?>

        <form method="post"
              action="?Encrypt=true"
        >
            <div>
                <label for="toEncryptData">Give me your data to encrypt</label>
                <textarea name="toEncryptData"
                          id="toEncryptData"
                ></textarea>
            </div>
            <input type="submit"
                   value="Encrypt"
            >
        </form>

        <?php
    } else {
        ?>
        <form method="post">
            <div>
                <label for="password">Give me your passphrase</label>
                <input type="password"
                       id="password"
                       name="passphrase"
                >
            </div>
            <div>
                <label for="encryptedData">Give me your encrypted data</label>
                <textarea name="encryptedData"
                          id="encryptedData"
                ></textarea>
            </div>
            <input type="submit"
                   value="Submit"
            >
        </form>
        <?php
    }


}
?>



