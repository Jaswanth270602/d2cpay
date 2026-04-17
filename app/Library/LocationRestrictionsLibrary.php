<?php

namespace App\library {

    use App\Models\User;
    use App\Services\DistanceCalculator;


    class LocationRestrictionsLibrary
    {


        function loginRestrictions($user_id, $latitude, $longitude)
        {
            $userDetails = User::find($user_id);
            $loginRestrictionsKM = $userDetails->company->login_restrictions_km;
            if ($userDetails->login_restrictions == 1 && $userDetails->company->login_restrictions == 1) {
                $distance = DistanceCalculator::haversineDistance($userDetails->latitude, $userDetails->longitude, $latitude, $longitude);
                return ($distance <= $loginRestrictionsKM) ? 1 : 0;
            } else {
                return 1;
            }
        }


    }
}