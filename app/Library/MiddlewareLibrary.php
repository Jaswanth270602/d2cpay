<?php

namespace App\library {
    use App\User;
    use Auth;
    use http\Env\Response;
    use Str;
    use App\Providerlimit;

    class MiddlewareLibrary {




        function kyc_checking (){
            if (auth()->check() && Auth::User()->member->kyc_status == 1){
                User::where('id', Auth::id())->update(['active' => 1, 'reason' => 'Active']);
            }else{
                if (auth()->check() && auth()->user()->created_at) {
                    $banned_days = now()->diffInDays(auth()->user()->created_at);
                    if ($banned_days > 29) {
                        $message = 'your account has been temporarily suspended due to kyc not uploaded';
                        User::where('id', Auth::id())->update(['active' => 0, 'reason' => $message]);
                    } else {
                        $message = 'Your account has been suspended for '.$banned_days.' '.Str::plural('day', $banned_days).'. due to kyc not uploaded';
                        User::where('id', Auth::id())->update(['reason' => $message]);
                    }
                }
            }
        }

        function increase_daily_limit (){
            $providerlimit = Providerlimit::where('status_id', 1)->get();
            foreach ($providerlimit as $value){
                Providerlimit::where('id', $value->id)->update(['amount_limit' => $value->daily_limit]);
            }
            return Response()->json(['status' => 'success', 'message' => 'success']);
        }






    }}