<?php

namespace App\library {

    use App\Models\Numberdata;
    use App\Models\Circleprovider;
    use App\Models\Backupapi;
    use App\Models\Provider;
    use App\Models\User;
    use App\Models\Report;
    use App\Models\Providerlimit;
    use App\Models\Service;
    use DB;
    use Auth;
    use App\Library\GetcommissionLibrary;
    use App\Notifications\DatabseNotification;
    use Notification;
    use App\Models\Denomination;
    use App\Models\State;
    use App\Models\District;
    use App\Models\Agentonboarding;
    use App\Models\Api;
    use Helpers;


    class DmtLibrary
    {



        function splitAmount($amount, $provider_id)
        {
            $providers = Provider::find($provider_id);
            $splitAmountBy = ($providers->splitAmountBy == 0) ? 5000 : $providers->splitAmountBy;
            $partsAmount = [];
            while ($amount > $splitAmountBy) {
                $partsAmount[] = $splitAmountBy;
                $amount -= $splitAmountBy;
            }
            if ($amount > 0) {
                $partsAmount[] = $amount;
            }
            return $partsAmount;
        }


        function getTransactionCharges($user_id, $amount, $provider_id)
        {
            $id = sprintf("%06d", mt_rand(1, 999999));
            $partsAmount = Self::splitAmount($amount, $provider_id);
            foreach ($partsAmount as $amounts) {
                Self::getMySlab($amounts, $id, $user_id, $provider_id);
            }
            $list = $this->getslablist($id);
            DB::table('view_charges')->where('myid', $id)->delete();
            return Response()->json([
                'status' => 'success',
                'list' => $list,
            ]);
        }



        function getMySlab($amount, $myid, $user_id, $provider_id)
        {
            $userdetails = User::find($user_id);
            $scheme_id = $userdetails->scheme_id;
            $library = new GetcommissionLibrary();
            $commission = $library->get_commission($scheme_id, $provider_id, $amount);
            $retailer = $commission['retailer'];
            $final_amount = $amount + $retailer;
            $data = array(
                'amount' => $amount,
                'charges' => $retailer,
                'total_amount' => $final_amount,
                'myid' => $myid
            );
            DB::table('view_charges')->insert($data);
            return true;
        }

        function getslablist($myid)
        {
            $report = DB::table('view_charges')->where('myid', $myid)->orderBy('id', 'ASC')->get();
            $response = array();
            foreach ($report as $value) {
                $product = array();
                $product["amount"] = $value->amount;
                $product["charges"] = $value->charges;
                $product["total_amount"] = $value->total_amount;
                array_push($response, $product);
            }
            return $response;
        }
    }

}