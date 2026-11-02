<?php

function encryptMessage($message)
{
    $key = "GovSecureSecretKey1234567890123456";

    $iv = random_bytes(16);

    $encrypted = openssl_encrypt(
        $message,
        "AES-256-CBC",
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );

    return base64_encode($iv . $encrypted);
}

function decryptMessage($encryptedMessage)
{
    $key = "GovSecureSecretKey1234567890123456";

    $data = base64_decode($encryptedMessage);

    if ($data === false || strlen($data) < 17) {
        return "[Unable to decrypt message]";
    }

    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);

    $decrypted = openssl_decrypt(
        $encrypted,
        "AES-256-CBC",
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );

    return $decrypted !== false
        ? $decrypted
        : "[Unable to decrypt message]";
}