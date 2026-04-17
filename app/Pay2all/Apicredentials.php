<?php

namespace App\Pay2all {

    use Helpers;
    use App\Models\Api;
    use http\Env\Response;

    class Apicredentials
    {

        public function __construct()
        {
            $apis = Api::find(1);
            $credentials = $apis->credentials;
            if (empty($credentials)) {
                $this->base_url = '';
                $this->authorizationKey = '';
            } else {
                $credentials = json_decode($credentials);
                $this->base_url = (empty($credentials->base_url)) ? '' : $credentials->base_url;
                $this->authorizationKey = (empty($credentials->authorization)) ? '' : $credentials->authorization;
            }
            $this->api_id = $apis->id;
        }


        function getCredentials()
        {
           return [
               'base_url' => $this->base_url,
               'authorizationKey' => $this->authorizationKey,
               'api_id' => $this->api_id,
           ];
        }



    }
}