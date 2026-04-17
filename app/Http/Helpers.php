<?php

use App\Models\Company;

class Helpers
{
    public static function company_id()
    {
        $website = !empty($_SERVER['HTTP_HOST']) ? trim($_SERVER['HTTP_HOST']) : '';
        $hostOnly = preg_replace('/:\d+$/', '', $website);

        $candidates = array_values(array_filter(array_unique([
            $website,
            $hostOnly,
            'localhost:8000',
            '127.0.0.1:8000',
            'localhost',
            '127.0.0.1',
        ])));

        if (!empty($candidates)) {
            $company = Company::whereIn('company_website', $candidates)->first();
            if ($company) {
                return $company;
            }
        }

        // Local fallback prevents hard 404 when host mapping is not ready yet.
        return Company::where('status_id', 1)->firstOrFail();
    }

    public static function pay_curl_xml($url, $xml)
    {
        $headers = array(
            "Content-type: text/xml",
            "Content-length: " . strlen($xml),
            "Connection: close",
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $data = curl_exec($ch);
        return $data;
    }

    public static function pay_curl_get($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }


    public static function pay_curl_post($url, $header, $parameters, $method)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($method == 'POST' || $method == 'PUT') {
            if (is_array($parameters)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters)); // send as JSON
            } elseif (is_string($parameters)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters); // already JSON string
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, ''); // fallback
            }
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $response = curl_exec($ch);
        return $response;
    }

    public static function send_sms_msg($number, $message)
    {
        $message = urlencode($message);
        $url = "https://control.msg91.com/api/sendhttp.php?authkey=43466ADvfq19mpb52b33cd2&mobiles=$number&message=$message&sender=PAYTWO&route=4&country=91";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }
}
