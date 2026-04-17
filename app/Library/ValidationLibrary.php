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
    use http\Env\Request;
    use Notification;
    use App\Models\Denomination;
    use App\Models\State;
    use App\Models\Apicheckbalance;
    use App\Models\Profile;
    use Mail;
    use Helpers;
    use App\Models\Sitesetting;
    use Carbon\Carbon;
    use Validator;
    use Maatwebsite\Excel\Facades\Excel;
    use App\Exports\ChildstatementExport;

    class ValidationLibrary {


        function rechargeValidation ($request){
            $provider_id = $request->provider_id;
            $providers = Provider::find($provider_id);
            if ($providers->min_length == 0 && $providers->max_length == 0){
                $min = 0;
                $max = 50;
            }else{
                $min = $providers->min_length;
                $max = $providers->max_length;
            }

            if ($providers->start_with){
                $number_validation = 'required|regex:/^[\w-]*$/|between:'.$min.','.$max.'|starts_with:'.$providers->start_with.'';
            }else{
                $number_validation = 'required|regex:/^[\w-]*$/|between:'.$min.','.$max.'';
            }
            if ($providers->min_amount == 0 && $providers->max_amount == 0){
                $amount_validation = 'required|regex:/^\d+(\.\d{1,2})?$/';
            }else{
                $amount_validation = 'required|numeric|between:'.$providers->min_amount.','.$providers->max_amount.'';
            }
            if (empty($providers->block_amount)){
                $block_amount = "";
            }else{
                $block_amount = 'not_in:'.$providers->block_amount.'';
            }
           return  $rules = array(
                'mobile_number' => $number_validation,
                'amount' => "$amount_validation|$block_amount",
                'provider_id' => 'required|exists:providers,id',
            );
        }



    }
}