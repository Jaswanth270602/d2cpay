<?php

// Usage:
// php enc_login.php <username> <password> [device_id] [latitude] [longitude] [encryption_key]
//
// Example:
// php enc_login.php 7076373992 Password@1234 WIN-TEST-01 0 0 cipherprojectkey

$username = $argv[1] ?? '';
$password = $argv[2] ?? '';
$deviceId = $argv[3] ?? 'WIN-TEST-01';
$latitude = $argv[4] ?? '0';
$longitude = $argv[5] ?? '0';
$key = $argv[6] ?? 'cipherprojectkey';

if ($username === '' || $password === '') {
    fwrite(STDERR, "Missing required arguments.\n");
    fwrite(STDERR, "Usage: php enc_login.php <username> <password> [device_id] [latitude] [longitude] [encryption_key]\n");
    exit(1);
}

$payload = json_encode([
    'username' => (string)$username,
    'password' => (string)$password,
    'device_id' => (string)$deviceId,
    'latitude' => (string)$latitude,
    'longitude' => (string)$longitude,
], JSON_UNESCAPED_SLASHES);

if ($payload === false) {
    fwrite(STDERR, "Failed to build payload.\n");
    exit(1);
}

$encrypted = openssl_encrypt($payload, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
if ($encrypted === false) {
    fwrite(STDERR, "Encryption failed. Check key and OpenSSL availability.\n");
    exit(1);
}

echo base64_encode($encrypted);
