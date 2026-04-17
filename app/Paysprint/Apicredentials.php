<?php

namespace App\Paysprint {

    use Helpers;
    use App\Models\Api;
    use http\Env\Response;

    class Apicredentials
    {
        private $alg;
        private $hash;
        private $data;

        public function __construct()
        {
            $apis = Api::find(3);
            $credentials = $apis->credentials;
            if (empty($credentials)) {
                $this->base_url = '';
                $this->partner_id = '';
                $this->api_key = '';
                $this->jwt_header = '';
                $this->authorised_key = '';
                $this->key = '';
                $this->iv = '';
            } else {
                $credentials = json_decode($credentials);
                $this->base_url = (empty($credentials->base_url)) ? '' : $credentials->base_url;
                $this->partner_id = (empty($credentials->partner_id)) ? '' : $credentials->partner_id;
                $this->api_key = (empty($credentials->api_key)) ? '' : $credentials->api_key;
                $this->jwt_header = '{"typ":"JWT","alg":"HS256"}';
                $this->authorised_key = (empty($credentials->authorised_key)) ? '' : $credentials->authorised_key;
                $this->key = (empty($credentials->key)) ? '' : $credentials->key;
                $this->iv = (empty($credentials->iv)) ? '' : $credentials->iv;
            }
            $this->api_id = $apis->id;
        }


        function getCredentials($mode)
        {
            if ($mode == 'LIVE') {
                return [
                    'base_url' => $this->base_url,
                    'partner_id' => $this->partner_id,
                    'api_key' => $this->api_key,
                    'jwt_header' => $this->jwt_header,
                    'authorised_key' => $this->authorised_key,
                    'key' => $this->key,
                    'iv' => $this->iv,
                    'api_id' => $this->api_id,
                ];
            } else {
                return [
                    'base_url' => 'https://sit.paysprint.in/service-api/',
                    'partner_id' => 'PS001',
                    'api_key' => 'UFMwMDEyNGQ2NTliODUzYmViM2I1OWRjMDc2YWNhMTE2M2I1NQ==',
                    'jwt_header' => $this->jwt_header,
                    'authorised_key' => 'MzNkYzllOGJmZGVhNWRkZTc1YTgzM2Y5ZDFlY2EyZTQ=',
                    'key' => '060e37d1f82cde00',
                    'iv' => '788a4b959058271e',
                    'api_id' => $this->api_id,
                ];
            }

        }

        public function encode($header, $payload, $key)
        {
            $this->data = $this->base64url_encode($header) . '.' . $this->base64url_encode($payload);
            return $this->data . '.' . $this->JWS($header, $key);
        }

        private function base64url_encode($data)
        {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        }

        private function base64url_decode($data)
        {
            return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
        }

        private function JWS($header, $key)
        {
            $json = json_decode($header);
            $this->setAlgorithm($json->alg);
            if ($this->alg == 'plaintext') {
                return '';
            }
            return $this->base64url_encode(hash_hmac($this->hash, $this->data, $key, true));
        }

        private function setAlgorithm($algorithm)
        {
            $hash = 'sha256';
            if (in_array($hash, hash_algos())) $this->hash = $hash;
        }


    }
}