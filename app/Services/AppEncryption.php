<?php

namespace App\Services;

use App\Company;
use Helpers;

class AppEncryption
{
    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $companies = Company::find($this->company_id);
        $this->key = $companies->encryptionKey;
    }

    public static function decryptText($data, $key)
    {
        if (!empty($data) && !empty($key)) {
            $method = "AES-128-ECB";
            $dataDecoded = base64_decode($data);
            $decrypted = openssl_decrypt($dataDecoded, $method, $key, OPENSSL_RAW_DATA);
            return [
                'status_id' => true,
                'data' => $decrypted,
                'message' => "Success"
            ];
        } else {
            return [
                'status_id' => false,
                'message' => "String to encrypt, Key is required.",
            ];
        }
    }

    public static function encryptText($data, $key)
    {
        if (!empty($data) && !empty($key)) {
            $method = "AES-128-ECB";
            $encrypted = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA);
            $result = base64_encode($encrypted);
            return [
                'status_id' => true,
                'data' => $result,
                'message' => "Success"
            ];
        } else {
            return [
                'status_id' => false,
                'message' => "String to encrypt, Key is required.",
            ];
        }
    }
}
