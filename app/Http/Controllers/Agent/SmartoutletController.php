<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\User;
use App\Masterbank;
use Helpers;
use App\Api;
use App\Apiresponse;
use App\Provider;
use App\Report;
use App\Aepsreport;
use App\Balance;
use Validator;
use App\State;
use App\District;
use App\Agentonboarding;
use App\Service;
use App\Library\Commission_increment;
use App\Library\GetcommissionLibrary;
use App\Library\BasicLibrary;

class SmartoutletController extends Controller
{

    public function __construct()
    {
        $this->api_id = 1;
    }


    function smart_outlet(Request $request)
    {
        Apiresponse::insertGetId(['message' => $request, 'api_type' => 1, 'request_message' => 'merchant_aeps_response', 'ip_address' => request()->ip()]);
        $status_id = $request->status_id;
        $amount = $request->amount;
        $utr = $request->utr;
        $report_id = $request->report_id;
        $client_id = $request->client_id;
        $mobile_number = $request->mobile_number;
        $payment_id = $request->payment_id;
        $provider_id = $request->provider_id;
        $message = $request->message;
        $card_number = $request->card_number;
        $txnid = $request->txnid;
        $service_id = $request->service_id;
        if ($status_id == 1 || $status_id == 0) {
            if ($payment_id == 1) {
                return $this->getrepoprt($mobile_number);
            } elseif ($payment_id == 2) {
                return $this->userdetails($mobile_number);
            } elseif ($payment_id == 6) {
                if ($service_id == 13 && $provider_id == 158) {
                    return $this->update_microatm_balance($status_id, $utr, $report_id, $client_id, $mobile_number, $amount, $message, $card_number, $txnid);
                } elseif ($provider_id == 158 || $provider_id == 154) {
                    return $this->update_aeps_balance($status_id, $utr, $report_id, $client_id, $mobile_number, $amount, $message, $card_number, $txnid);
                } elseif ($provider_id == 175) {
                    return $this->aadhar_pay($status_id, $utr, $report_id, $client_id, $mobile_number, $amount, $message, $card_number, $txnid);
                } else {
                    return Response()->json(['status' => 2, 'message' => 'Provider Id Not Mention']);
                }
            }
        } else {
            return Response()->json(['status' => 2, 'message' => 'Wrong Status']);
        }
    }


    function getrepoprt($number)
    {
        $provider_id = 319;
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1) {
            $agentonboardings = Agentonboarding::where('mobile_number', $number)->first();
            if ($agentonboardings) {
                $userdetails = User::find($agentonboardings->user_id);
                if ($userdetails) {
                    if ($userdetails->role_id == 10) {
                        $url = $userdetails->member->aeps_call_back . "?type=1&number=$number";
                        return $this->sendapi($url);
                    }
                }
            }
            $users = User::where('mobile', $number)->first();
            if ($users) {
                $user_id = $users->id;
                $report = Report::where('user_id', $user_id)->orderBy('id', 'DESC')->paginate(20);
                $accounts = array();
                foreach ($report->all() as $account) {
                    $accounts[] = [
                        'id' => $account->id,
                        'date' => $account->created_at->format('Y-m-d h:m:s'),
                        'provider' => $account->provider->provider_name,
                        'number' => $account->number,
                        'txnid' => $account->txnid,
                        'amount' => number_format($account->amount, 2),
                        'commisson' => number_format($account->profit, 2),
                        'total_balance' => number_format($account->total_balance, 2),
                        'status' => $account->status->status,
                    ];
                }
                return response()->json([
                    'total' => $report->total(),
                    'pageNumber' => $report->currentPage(),
                    'nextPageUrl' => $report->nextPageUrl(),
                    'page' => $report->currentPage(),
                    'pages' => $report->lastPage(),
                    'perpage' => $report->perPage(),
                    'reports' => $accounts,
                    'status' => 0,
                ]);
            } else {
                return Response()->json(['status' => 2, 'message' => 'User Not Found']);
            }
        } else {
            return Response()->json(['status' => 2, 'message' => 'Service not activate kindly contact customer care']);
        }

    }

    function userdetails($number)
    {
        $provider_id = 319;
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1) {
            $agentonboardings = Agentonboarding::where('mobile_number', $number)->first();
            if ($agentonboardings) {
                $userdetails = User::find($agentonboardings->user_id);
                if ($userdetails) {
                    if ($userdetails->role_id == 10) {
                        $url = $userdetails->member->aeps_call_back . "?type=2&number=$number";
                        return $this->sendapi($url);
                    }
                }
            }
            $users = User::where('mobile', $number)->first();
            if ($users) {
                $user_id = $users->id;
                $balance = Balance::where('user_id', $user_id)->first();
                $user_balance = $balance->user_balance;
                $aeps_balance = $balance->aeps_balance;
                $data = array(
                    'user_id' => $user_id,
                    'user_balance' => number_format($user_balance, 2),
                    'aeps_balance' => number_format($aeps_balance, 2),
                    'name' => $users->name . ' ' . $users->last_name,
                    'email' => $users->email,
                    'mobile' => $users->mobile,
                );
                return Response()->json(['status' => 0, 'userdetails' => $data]);
            } else {
                return Response()->json(['status' => 2, 'message' => 'User Not Found']);
            }
        } else {
            return Response()->json(['status' => 2, 'message' => 'Service not activate kindly contact customer care']);
        }

    }

    function update_microatm_balance($status_id, $utr, $report_id, $client_id, $number, $amount, $message, $card_number, $txnid)
    {
        if ($status_id == 1) {
            $agentonboardings = Agentonboarding::where('mobile_number', $number)->first();
            if ($agentonboardings) {
                $userdetails = User::find($agentonboardings->user_id);
            } else {
                $userdetails = User::where('mobile', $number)->first();
            }

            if ($userdetails) {
                $user_id = $userdetails->id;
                $scheme_id = $userdetails->scheme_id;
                $company_id = $userdetails->company_id;
                $opening_balance = $userdetails->balance->aeps_balance;
                $checktxnid = Report::where('txnid', $utr)->first();
                if ($checktxnid) {
                    return Response()->json(['status' => 2, 'message' => 'dupplicate txnid']);
                } else {

                    $reports = Report::where('id', $client_id)->first();
                    if ($reports) {
                        return Response()->json(['status' => 2, 'message' => 'Record already updated']);
                    } else {
                        $request_ip = request()->ip();
                        $provider_id = 322;
                        $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
                        $now = new \DateTime();
                        $ctime = $now->format('Y-m-d H:i:s');
                        $description = "$providers->provider_name  $number";
                        $library = new GetcommissionLibrary();
                        $commission = $library->get_commission($scheme_id, $provider_id, $amount);
                        $retailer = $commission['retailer'];
                        $d = $commission['distributor'];
                        $sd = $commission['sdistributor'];
                        $st = $commission['sales_team'];
                        $rf = $commission['referral'];
                        $sum_amount = $amount + $retailer;
                        Balance::where('user_id', $user_id)->increment('aeps_balance', $sum_amount);
                        $balance = Balance::where('user_id', $user_id)->first();
                        $total_balance = $balance->aeps_balance;
                        $api_id = $this->api_id;
                        $insert_id = Report::insertGetId([
                            'number' => $number,
                            'provider_id' => $provider_id,
                            'amount' => $amount,
                            'api_id' => $api_id,
                            'status_id' => 6,
                            'client_id' => $client_id,
                            'created_at' => $ctime,
                            'user_id' => $user_id,
                            'profit' => $retailer,
                            'mode' => "APP",
                            'txnid' => $utr,
                            'ip_address' => $request_ip,
                            'description' => $description,
                            'opening_balance' => $opening_balance,
                            'total_balance' => $total_balance,
                            'wallet_type' => 2,
                        ]);

                        Aepsreport::insertGetId([
                            'aadhar_number' => $card_number,
                            'bank_name' => '',
                            'created_at' => $ctime,
                            'report_id' => $insert_id,
                        ]);
                        $library = new Commission_increment();
                        $library->parent_recharge_commission($user_id, $number, $insert_id, $provider_id, $amount, $this->api_id, $retailer, $d, $sd, $st, $rf);
                        if ($userdetails->role_id == 10) {
                            $url = $userdetails->member->aeps_call_back . "?type=3&number=$number&status_id=1&amount=$amount&utr=$utr";
                            return $this->sendapi($url);
                        }
                        // get wise commission
                        $library = new GetcommissionLibrary();
                        $apiComms = $library->getApiCommission($api_id, $provider_id, $amount);
                        $apiCommission = $apiComms['apiCommission'];
                        $commissionType = $apiComms['commissionType'];
                        $library = new Commission_increment();
                        $library->updateApiComm($user_id, $provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);
                        return Response()->json(['status' => 0, 'message' => 'Success']);

                    }

                }
            } else {
                return Response()->json(['status' => 2, 'message' => 'User not found']);
            }
        } else {
            return Response()->json(['status' => 2, 'message' => 'Wrong Status']);
        }
    }

    function update_aeps_balance($status_id, $utr, $report_id, $client_id, $number, $amount, $message, $card_number, $txnid)
    {
        if ($status_id == 1) {
            $agentonboardings = Agentonboarding::where('mobile_number', $number)->first();
            if ($agentonboardings) {
                $userdetails = User::find($agentonboardings->user_id);
            } else {
                $userdetails = User::where('mobile', $number)->first();
            }
            if ($userdetails) {
                $user_id = $userdetails->id;
                $scheme_id = $userdetails->scheme_id;
                $company_id = $userdetails->company_id;
                $opening_balance = $userdetails->balance->aeps_balance;
                $checktxnid = Report::where('txnid', $utr)->first();
                if ($checktxnid) {
                    return Response()->json(['status' => 2, 'message' => 'dupplicate txnid']);
                } else {
                    $reports = Report::where('id', $client_id)->first();
                    if ($reports) {
                        return Response()->json(['status' => 2, 'message' => 'Record already updated']);
                    } else {
                        $request_ip = request()->ip();
                        $provider_id = 319;
                        $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
                        $now = new \DateTime();
                        $ctime = $now->format('Y-m-d H:i:s');
                        $description = "$providers->provider_name  $number";
                        $library = new GetcommissionLibrary();
                        $commission = $library->get_commission($scheme_id, $provider_id, $amount);
                        $retailer = $commission['retailer'];
                        $d = $commission['distributor'];
                        $sd = $commission['sdistributor'];
                        $st = $commission['sales_team'];
                        $rf = $commission['referral'];
                        $sum_amount = $amount + $retailer;
                        Balance::where('user_id', $user_id)->increment('aeps_balance', $sum_amount);
                        $balance = Balance::where('user_id', $user_id)->first();
                        $total_balance = $balance->aeps_balance;
                        $api_id = $this->api_id;
                        $insert_id = Report::insertGetId([
                            'number' => $number,
                            'provider_id' => $provider_id,
                            'amount' => $amount,
                            'api_id' => $api_id,
                            'status_id' => 6,
                            'client_id' => $client_id,
                            'created_at' => $ctime,
                            'user_id' => $user_id,
                            'profit' => $retailer,
                            'mode' => "APP",
                            'txnid' => $utr,
                            'ip_address' => $request_ip,
                            'description' => $description,
                            'opening_balance' => $opening_balance,
                            'total_balance' => $total_balance,
                            'wallet_type' => 2,
                        ]);
                        Aepsreport::insertGetId([
                            'aadhar_number' => $card_number,
                            'bank_name' => '',
                            'created_at' => $ctime,
                            'report_id' => $insert_id,
                        ]);
                        $library = new Commission_increment();
                        $library->parent_recharge_commission($user_id, $number, $insert_id, $provider_id, $amount, $api_id, $retailer, $d, $sd, $st, $rf);
                        if ($userdetails->role_id == 10) {
                            $url = $userdetails->member->aeps_call_back . "?type=3&number=$number&status_id=1&amount=$amount&utr=$utr";
                            return $this->sendapi($url);
                        }
                        // get wise commission
                        $library = new GetcommissionLibrary();
                        $apiComms = $library->getApiCommission($api_id, $provider_id, $amount);
                        $apiCommission = $apiComms['apiCommission'];
                        $commissionType = $apiComms['commissionType'];
                        $library = new Commission_increment();
                        $library->updateApiComm($user_id, $provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);
                        return Response()->json(['status' => 0, 'message' => 'Success']);
                    }
                }
            } else {
                return Response()->json(['status' => 2, 'message' => 'User not found']);
            }
        } else {
            return Response()->json(['status' => 2, 'message' => 'Wrong Status']);
        }
    }

    function aadhar_pay($status_id, $utr, $report_id, $client_id, $number, $amount, $message, $card_number, $txnid)
    {
        if ($status_id == 1) {
            $userdetails = User::where('mobile', $number)->first();
            if ($userdetails) {
                $user_id = $userdetails->id;
                $scheme_id = $userdetails->scheme_id;
                $company_id = $userdetails->company_id;
                $opening_balance = $userdetails->balance->aeps_balance;
                $checktxnid = Report::where('txnid', $utr)->first();
                if ($checktxnid) {
                    return Response()->json(['status' => 2, 'message' => 'dupplicate txnid']);
                } else {
                    $reports = Report::where('id', $client_id)->first();
                    if ($reports) {
                        return Response()->json(['status' => 2, 'message' => 'Record already updated']);
                    } else {
                        $request_ip = request()->ip();
                        $provider_id = 321;
                        $providers = Provider::where('id', $provider_id)->where('status_id', 1)->first();
                        $now = new \DateTime();
                        $ctime = $now->format('Y-m-d H:i:s');
                        $description = "$providers->provider_name  $number";
                        $library = new GetcommissionLibrary();
                        $commission = $library->get_commission($scheme_id, $provider_id, $amount);
                        $retailer = $commission['retailer'];
                        $distributor = $commission['distributor'];
                        $sdistributor = $commission['sdistributor'];
                        $sales_team = $commission['sales_team'];
                        $referral = $commission['referral'];
                        $sum_amount = $amount - $retailer;
                        Balance::where('user_id', $user_id)->increment('aeps_balance', $sum_amount);
                        $balance = Balance::where('user_id', $user_id)->first();
                        $total_balance = $balance->aeps_balance;
                        $api_id = $this->api_id;
                        $insert_id = Report::insertGetId([
                            'number' => $number,
                            'provider_id' => $provider_id,
                            'amount' => $amount,
                            'api_id' => $api_id,
                            'status_id' => 6,
                            'client_id' => $client_id,
                            'created_at' => $ctime,
                            'user_id' => $user_id,
                            'profit' => '-' . $retailer,
                            'mode' => "APP",
                            'txnid' => $utr,
                            'ip_address' => $request_ip,
                            'description' => $description,
                            'opening_balance' => $opening_balance,
                            'total_balance' => $total_balance,
                            'wallet_type' => 2,
                        ]);
                        Aepsreport::insertGetId([
                            'aadhar_number' => $card_number,
                            'bank_name' => '',
                            'created_at' => $ctime,
                            'report_id' => $insert_id,
                        ]);
                        $library = new Commission_increment();
                        $library->parent_recharge_commission($user_id, $number, $insert_id, $provider_id, $amount, $this->api_id, $retailer, $distributor, $sdistributor, $sales_team, $referral);
                        // get wise commission
                        $library = new GetcommissionLibrary();
                        $apiComms = $library->getApiCommission($api_id, $provider_id, $amount);
                        $apiCommission = $apiComms['apiCommission'];
                        $commissionType = $apiComms['commissionType'];
                        $library = new Commission_increment();
                        $library->updateApiComm($user_id, $provider_id, $api_id, $amount, $retailer, $d, $sd, $st, $rf, $apiCommission, $insert_id, $commissionType);
                        return Response()->json(['status' => 0, 'message' => 'Success']);
                    }

                }
            } else {
                return Response()->json(['status' => 2, 'message' => 'User not found']);
            }
        } else {
            return Response()->json(['status' => 2, 'message' => 'Wrong Status']);
        }
    }

    function sendapi($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

}
