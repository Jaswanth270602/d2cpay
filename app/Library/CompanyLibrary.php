<?php

namespace App\library {
    use App\Models\User;
    use App\Models\Company;


    class CompanyLibrary {



        public function get_company_detail(){
            $host = !empty($_SERVER['HTTP_HOST']) ? trim($_SERVER['HTTP_HOST']) : '';
            $hostOnly = preg_replace('/:\d+$/', '', $host);

            $candidates = array_values(array_filter(array_unique([
                $host,
                $hostOnly,
                'localhost:8000',
                '127.0.0.1:8000',
                'localhost',
                '127.0.0.1',
                'localhost:8888',
            ])));

            if (!empty($candidates)) {
                $company = Company::whereIn('company_website', $candidates)
                    ->where('status_id', 1)
                    ->first();
                if ($company) {
                    return $company;
                }
            }

            return Company::where('status_id', 1)->first();
        }

        public static function company_details()
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

            return Company::where('status_id', 1)->firstOrFail();
        }

    }

}