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
    use Illuminate\Support\Facades\Notification;
    use App\Models\Denomination;
    use App\Models\State;
    use App\Models\District;
    use App\Models\Agentonboarding;
    use App\Models\Api;
    use Helpers;


    class BasicLibrary
    {

        public function __construct()
        {
            $this->company_id = Helpers::company_id()->id;
            $dt = Helpers::company_id();
            $this->company_id = $dt->id;
            $api = Api::where('id', 1)->first();
            if ($api) {
                $this->key = 'Bearer ' . $api->api_key;
                $this->api_id = $api->id;
            } else {
                $this->key = "";
                $this->api_id = "";
            }
        }

        var $recharge_minute = 30;

        function get_api($provider_id, $number, $amount, $user_id)
        {

            $Check4digitnumber = mb_substr($number, 0, 4, "UTF-8");
            $checknumberdata = Numberdata::where('number', 'like', '%' . $Check4digitnumber . '%')->first();
            if ($checknumberdata) {
                $state_id = $checknumberdata->state_id;
            } else {
                $state_id = 0;
            }

            // denominations wise api
            $denominations = Denomination::where('provider_id', $provider_id)->where('amount', $amount)->where('status_id', 1)->first();
            if ($denominations) {
                $api_id = $denominations->api_id;
                return ['api_id' => $api_id, 'state_id' => $state_id];
            }

            // circle wise api
            $Check4digitnumber = mb_substr($number, 0, 4, "UTF-8");
            $checknumberdata = Numberdata::where('number', 'like', '%' . $Check4digitnumber . '%')->where('status_id', 1)->first();
            if ($checknumberdata) {
                $state_id = $checknumberdata->state_id;
                $checkcircleprovider = Circleprovider::where('state_id', $state_id)->where('provider_id', $provider_id)->where('status_id', 1)->first();
                if ($checkcircleprovider) {
                    $api_id = $checkcircleprovider->api_id;
                    return ['api_id' => $api_id, 'state_id' => $state_id];
                }
            }

            // backup api
            $backupapi = Backupapi::where('provider_id', $provider_id)->where('api_type', 1)->first();
            if ($backupapi) {
                $api_id = $backupapi->api_id;
                return ['api_id' => $api_id, 'state_id' => $state_id];
            }

            // swtching api
            $providers = Provider::where('id', $provider_id)->first();
            if ($providers) {
                $api_id = $providers->api_id;
                return ['api_id' => $api_id, 'state_id' => $state_id];
            } else {
                $api_id = 1;
                return ['api_id' => $api_id, 'state_id' => $state_id];
            }

        }

        function recharge_validation($user_id, $provider_id, $amount, $number)
        {
            $userdetails = User::find($user_id);
            $recharge_minute = $userdetails->company->same_amount;
            if ($userdetails->profile->recharge == 1 && $userdetails->company->recharge == 1) {
                $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
                if ($providers) {
                    $services = Service::where('id', $providers->service_id)->where('status_id', 1)->first();
                    if ($services) {
                        if ($userdetails->active == 1) {
                            $checkproviders = Providerlimit::where('provider_id', $provider_id)->where('user_id', $user_id)->first();
                            if ($checkproviders) {
                                $provider_status = $checkproviders->provider_status;
                            } else {
                                $provider_status = 1;
                            }
                            if ($provider_status == 1) {
                                $checkprovider_limit = Providerlimit::where('user_id', $user_id)->where('provider_id', $provider_id)->where('status_id', 1)->first();
                                if ($checkprovider_limit) {
                                    $amount_limit = $checkprovider_limit->amount_limit;
                                } else {
                                    $amount_limit = 25000;
                                }

                                if ($amount_limit >= $amount) {
                                    // same number validation
                                    $report = Report::where('number', $number)
                                        ->where('amount', $amount)
                                        ->where('provider_id', $provider_id)
                                        ->where('user_id', $user_id)
                                        ->whereIn('status_id', [1, 2, 3, 5])
                                        ->whereDate('created_at', '>=', date('Y-m-d', time()))->first();
                                    if ($report) {
                                        $minute = now()->diffInMinutes($report->created_at);
                                        if ($minute > $recharge_minute) {
                                            return ['status' => 'success', 'message' => 'success'];
                                        } else {
                                            return ['status' => 'failure', 'message' => "Same Number Same Amount, try after $recharge_minute minutes"];
                                        }
                                    } else {
                                        return ['status' => 'success', 'message' => 'success'];
                                    }
                                    // same number validation close
                                } else {
                                    return ['status' => 'failure', 'message' => 'Failed! daily limit exceeds for this provider'];
                                }


                            } else {
                                return ['status' => 'failure', 'message' => 'Provider not activated kindly contact customer care'];
                            }

                        } else {
                            return ['status' => 'failure', 'message' => $userdetails->reason];
                        }
                    } else {
                        return ['status' => 'failure', 'message' => 'Service not activate kindly contact customer care'];
                    }


                } else {
                    return ['status' => 'failure', 'message' => 'Provider not activated kindly contact customer care'];
                }
            } else {
                return ['status' => 'failure', 'message' => 'Service not activate kindly contact customer care'];
            }
        }


        function money_transfer_validation($user_id)
        {
            $userdetails = User::find($user_id);
            if ($userdetails->profile->money == 1 && $userdetails->company->money == 1) {
                if ($userdetails->active == 1) {
                    return ['status' => 'success', 'message' => 'success'];
                } else {
                    return ['status' => 'failure', 'message' => $userdetails->reason];
                }
            } else {
                return ['status' => 'failure', 'message' => 'Service not activate kindly contact customer care'];
            }
        }

        function money_transfer_two_validation($user_id)
        {
            $userdetails = User::find($user_id);
            if ($userdetails->profile->money_two == 1 && $userdetails->company->money_two == 1) {
                if ($userdetails->active == 1) {
                    return ['status' => 'success', 'message' => 'success'];
                } else {
                    return ['status' => 'failure', 'message' => $userdetails->reason];
                }
            } else {
                return ['status' => 'failure', 'message' => 'Service not activate kindly contact customer care'];
            }
        }


        function block_string()
        {
            return $vowels = array("php", "script", "query", "DB", "foreach", "database");
        }

        function split_number($number)
        {
            $field1expload = explode(',', $number);
            $optional1 = $field1expload[0];
            if (!empty($field1expload[1])) {
                $optional2 = $field1expload[1];
            } else {
                $optional2 = "";
            }
            if (!empty($field1expload[2])) {
                $optional3 = $field1expload[2];
            } else {
                $optional3 = "";
            }
            if (!empty($field1expload[3])) {
                $optional4 = $field1expload[3];
            } else {
                $optional4 = "";
            }
            return [
                'optional1' => $optional1,
                'optional2' => $optional2,
                'optional3' => $optional3,
                'optional4' => $optional4,
            ];
        }



        function send_notification($user_id, $body)
        {
            $users = User::whereIn('id', $user_id)->get();
            Notification::send($users, new DatabseNotification($body));
        }

        function update_number_series($number)
        {
            $number_digit = mb_substr($number, 0, 4, "UTF-8");
            $checknumberdata = Numberdata::where('number', 'like', '%' . $number_digit . '%')->first();
            if (empty($checknumberdata)) {
                $endurl = "http://planapi.in/api/Mobile/OperatorFetchNew?ApiUserID=3397&ApiPassword=N123456&Mobileno=$number";
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $endurl);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($curl);
                $res = json_decode($response);
                if ($res->STATUS == 1) {
                    $CircleCode = $res->CircleCode;
                    if ($CircleCode == 0) {

                    } else {
                        $states = State::where('planapi', $CircleCode)->first();
                        if ($states) {
                            $numberstate = Numberdata::where('state_id', $states->id)->first();
                            if ($numberstate->number) {
                                $data_number = $numberstate->number . ',' . $number_digit;
                            } else {
                                $data_number = $number_digit;
                            }

                            Numberdata::where('id', $numberstate->id)->update(['number' => $data_number]);
                        }
                    }

                }
            }
        }

        function agent_onboarding($first_name, $last_name, $mobile_number, $email, $aadhar_number, $pan_number, $company, $pin_code, $address, $bank_account_number, $ifsc, $state_id, $district_id, $city, $user_id, $company_id, $insert_id)
        {
            $states = State::find($state_id);
            $district = District::find($district_id);
            $url = "https://api.pay2all.in/v1/outlet/store";
            $api_request_parameters = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'mobile_number' => $mobile_number,
                'email' => $email,
                'aadhar_number' => $aadhar_number,
                'pan_number' => $pan_number,
                'company' => $company,
                'pin_code' => $pin_code,
                'address' => $address,
                'bank_account_number' => $bank_account_number,
                'ifsc' => $ifsc,
                'circle_id' => $states->pay2all_state,
                'city' => $city,
                'district' => $district->district_name,
                'kyc_front' => "",
                'kyc_back' => "",
                'self_photo' => "",
                'office_photo' => "",
                'pan_photo' => "",
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->key];
            $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
            $res = json_decode($response);
            $message = $res->message;
            if ($message == 'Successfully Saved') {
                return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
            } elseif ($message == 'The given data was invalid.') {
                Agentonboarding::where('id', $insert_id)->delete();
                return Response()->json(['status' => 'validation_error', 'errors' => $res->errors]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'something went wrong']);
            }
        }

        function reportDataInsert($number, $provider_id, $amount, $api_id, $status_id, $client_id, $user_id, $profit, $mode, $ip_address, $description, $opening_balance, $total_balance, $wallet_type, $state_id)
        {
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $insert_id = Report::insertGetId([
                'number' => $number,
                'provider_id' => $provider_id,
                'amount' => $amount,
                'api_id' => $api_id,
                'status_id' => $status_id,
                'client_id' => $client_id,
                'created_at' => $ctime,
                'user_id' => $user_id,
                'profit' => $profit,
                'mode' => $mode,
                'ip_address' => $ip_address,
                'description' => $description,
                'opening_balance' => $opening_balance,
                'total_balance' => $total_balance,
                'wallet_type' => $wallet_type,
                'state_id' => $state_id,
            ]);
            return $insert_id;
        }

        function getTelecomServiceId()
        {
            return $serviceId = Service::whereIn('servicegroup_id', [1, 2])->get(['id']);
        }

        function getActiveService($provider_id, $user_id)
        {
            $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
            if ($providers) {
                $companyActiveService = Self::getCompanyActiveService($user_id);
                $userActiveService = Self::getUserActiveService($user_id);
                $services = Service::whereIn('id', $companyActiveService)->whereIn('id', $userActiveService)->where('id', $providers->service_id)->first();
                $status_id = (empty($services)) ? 2 : 1;
                return ['status_id' => $status_id];
            } else {
                return ['status_id' => 2];
            }
        }

        function getCompanyActiveService($user_id)
        {
            $userDetails = User::find($user_id);
            $export = explode(',', $userDetails->company->active_services);
            $activeService = array();
            foreach ($export as $value) {
                $product = array();
                $product["id"] = $value;
                array_push($activeService, $product);
            }
            return $activeService;
        }

        function getUserActiveService($user_id)
        {
            $userDetails = User::find($user_id);
            if ($userDetails->role_id == 1){
                $export = explode(',', $userDetails->company->active_services);
                $activeService = array();
                foreach ($export as $value) {
                    $product = array();
                    $product["id"] = $value;
                    array_push($activeService, $product);
                }
                return $activeService;
            }
            $export = explode(',', $userDetails->profile->active_services);
            $activeService = array();
            foreach ($export as $value) {
                $product = array();
                $product["id"] = $value;
                array_push($activeService, $product);
            }
            return $activeService;
        }




    }

}