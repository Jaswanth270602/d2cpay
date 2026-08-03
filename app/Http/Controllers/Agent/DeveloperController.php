<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Library\SmsLibrary;
use App\Library\BasicLibrary;
use App\Models\User;
use App\Models\Member;
use App\Models\Provider;
use App\Models\Banktransferswitching;
use App\Models\Traceurl;
use Validator;
use App\Models\Sitesetting;
use Helpers;
use Hash;
use Str;
use App\Models\Agentonboarding;
use App\Library\AurexaPayLibrary;
use App\Library\RojgaarPeLibrary;
use App\Library\FrapPayLibrary;

class DeveloperController extends Controller
{

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;
    }

    function settings()
    {
        if (Auth::User()->role_id == 10) {
            $data = array('page_title' => 'Developer Settings');
            return view('agent.developer.settings')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function generate_token_otp(Request $request)
    {
        $user_id = Auth::id();
        $userdetails = User::find($user_id);
        $otp = mt_rand(100000, 999999);
        $message = "Dear $userdetails->name your new api token generate OTP Is : $otp $this->brand_name";
        $template_id = 10;
        $library = new SmsLibrary();
        $library->send_sms($userdetails->mobile, $message, $template_id);
        User::where('id', $user_id)->update(['login_otp' => $otp]);
        return Response()->json(['status' => 'success', 'message' => 'OTP successfully sent to your register mobile number']);
    }

    function generate_token_save(Request $request)
    {
        if (Auth::User()->role_id == 10) {
            $rules = array(
                'otp' => 'required|digits:6',
                'password' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $otp = $request->otp;
            $password = $request->password;
            $user_id = Auth::id();
            $userdetail = User::find($user_id);
            $current_password = $userdetail->password;
            if (Hash::check($password, $current_password)) {
                if ($userdetail->login_otp == $otp) {
                    $api_token = Str::random(60);
                    User::where('id', $user_id)->update(['api_token' => $api_token]);
                    return Response()->json(['status' => 'success', 'message' => 'Api token successfully generated kindly contact your technical team for change new api token']);
                } else {
                    return Response()->json(['status' => 'failure', 'message' => 'OTP not match']);
                }

            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Password not match']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function add_ipaddress_otp(Request $request)
    {
        if (Auth::User()->role_id == 10) {
            $rules = array(
                'ip_address' => 'required|ip',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $user_id = Auth::id();
            $userdetails = User::find($user_id);
            $otp = mt_rand(100000, 999999);
            $message = "Dear $userdetails->name your new ip address OTP Is: $otp $this->brand_name";
            $template_id = 11;
            $library = new SmsLibrary();
            $library->send_sms($userdetails->mobile, $message, $template_id);
            User::where('id', $user_id)->update(['login_otp' => $otp]);
            return Response()->json(['status' => 'success', 'message' => 'OTP successfully sent to your register mobile number']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function ip_address_save(Request $request)
    {
        if (Auth::User()->role_id == 10) {
            $rules = array(
                'ip_address' => 'required|ip',
                'password' => 'required',
                'otp' => 'nullable|digits:6',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $user_id = Auth::id();
            $ip_address = $request->ip_address;
            $otp = $request->input('otp');
            $password = $request->password;
            $userdetail = User::find($user_id);
            $current_password = $userdetail->password;
            if (!Hash::check($password, $current_password)) {
                return Response()->json(['status' => 'failure', 'message' => 'Password not match']);
            }
            if ($request->filled('otp')) {
                if ((string)$userdetail->login_otp !== (string)$otp) {
                    return Response()->json(['status' => 'failure', 'message' => 'OTP not match']);
                }
            }
            Member::where('user_id', $user_id)->update(['ip_address' => $ip_address]);
            return Response()->json(['status' => 'success', 'message' => 'IP Address successfully updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_call_back_url(Request $request)
    {
        if (Auth::User()->role_id == 10) {
            $rules = array(
                'call_back_url' => 'required',
                'payout_call_back_url' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $call_back_url = $request->call_back_url;
            $payout_call_back_url = $request->payout_call_back_url;
            Member::where('user_id', Auth::id())->update([
                'call_back_url' => $call_back_url,
                'payoutcallbackurl' => $payout_call_back_url,
            ]);
            User::where('id', Auth::id())->update([
                'payoutcallbackurl' => $payout_call_back_url,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Call Back Url Successfully Updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function provider_list()
    {
        if (Auth::User()->role_id == 10) {
            $providers = Provider::whereIn('service_id', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15])->get();
            $data = array('page_title' => 'Provider List');
            return view('agent.developer.provider_list', compact('providers'))->with($data);
        } else {
            return redirect()->back();
        }


    }

    function call_back_logs(Request $request)
    {
        if ($request->fromdate && $request->todate) {
            $fromdate = $request->fromdate;
            $todate = $request->todate;
        } else {
            $fromdate = date('Y-m-d', time());
            $todate = date('Y-m-d', time());
        }
        $data = array(
            'page_title' => 'Call Back Logs',
            'fromdate' => $fromdate,
            'todate' => $todate,
        );
        $reports = Traceurl::where('user_id', Auth::id())
            ->whereDate('created_at', '>=', $fromdate)
            ->whereDate('created_at', '<=', $todate)
            ->get();
        return view('agent.developer.call_back_logs', compact('reports'))->with($data);
    }

    function view_callback_logs(Request $request)
    {
        $id = $request->id;
        $user_id = Auth::id();
        $trace = Traceurl::where('id', $id)->where('user_id', $user_id)->first();
        if ($trace) {
            return Response()->json([
                'status' => 'success',
                'id' => $id,
                'request_url' => $trace->url,
                'response_message' => $trace->response_message,
            ]);

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
        }

    }

    function resend_callback_url(Request $request)
    {
        $id = $request->callback_id;
        $user_id = Auth::id();
        $trace = Traceurl::where('id', $id)->where('user_id', $user_id)->first();
        if ($trace) {
            $url = $trace->url;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            Traceurl::where('id', $id)->update(['response_message' => $response]);
            return Response()->json(['status' => 'success', 'message' => 'Call back successfully resend']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
        }
    }

    function prepaid_and_dth(Request $request)
    {
        if (Auth::User()->role_id == 10) {
            $data = array('page_title' => 'Prepaid And DTH');
            return view('agent.developer.prepaid_and_dth')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function bill_payment(Request $request)
    {
        if (Auth::User()->role_id == 10) {
            $data = array('page_title' => 'Bill Payment');
            return view('agent.developer.bill_payment')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function money_transfer_docs(Request $request)
    {
        if (Auth::User()->role_id == 10) {
            $data = array('page_title' => 'Money Transfer Document');
            return view('agent.developer.money_transfer_docs')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function bank_transfer_docs(Request $request)
    {
        if (Auth::User()->role_id == 10) {
            $data = array('page_title' => 'Bank Transfer Document');
            return view('agent.developer.bank_transfer_docs')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function outlet_list(Request $request)
    {
        if (Auth::User()->company->money == 1 && Auth::User()->profile->money == 1 && Auth::User()->role_id == 10) {
            $data = array(
                'page_title' => 'Outlet List',
                'urls' => url('agent/developer/outlet-list-api')
            );
            return view('agent.developer.outlet_list')->with($data);
        } else {
            return redirect()->back();
        }
    }

    function outlet_list_api(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value


        $totalRecords = Agentonboarding::select('count(*) as allcount')
            ->where('user_id', Auth::id())
            ->count();

        $totalRecordswithFilter = Agentonboarding::select('count(*) as allcount')
            ->where('user_id', Auth::id())
            ->where(function ($query) use ($searchValue) {
                $query->where('first_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('last_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile_number', 'like', '%' . $searchValue . '%')
                    ->orWhere('aadhar_number', 'like', '%' . $searchValue . '%')
                    ->orWhere('pan_number', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%');
            })->count();

        // Fetch records

        $records = Agentonboarding::orderBy($columnName, $columnSortOrder)
            ->where('user_id', Auth::id())
            ->where(function ($query) use ($searchValue) {
                $query->where('first_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('last_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile_number', 'like', '%' . $searchValue . '%')
                    ->orWhere('aadhar_number', 'like', '%' . $searchValue . '%')
                    ->orWhere('pan_number', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%');
            })->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            $data_arr[] = array(
                "id" => $value->id,
                "created_at" => "$value->created_at",
                "user" => $value->user->name . ' ' . $value->user->last_name,
                "first_name" => $value->first_name,
                "last_name" => $value->last_name,
                "mobile_number" => $value->mobile_number,
                "email" => $value->email,
                "aadhar_number" => $value->aadhar_number,
                "pan_number" => $value->pan_number,
                "company" => $value->company,
                "pin_code" => $value->pin_code,
                "address" => $value->address,
                "bank_account_number" => $value->bank_account_number,
                "ifsc" => $value->ifsc,
                "state_name" => $value->state->name,
                "district_name" => $value->district->district_name,
                "city" => $value->city,
                "status" => '<span class="' . $value->status->class . '">' . $value->status->status . '</span>',
            );
        }
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );
        echo json_encode($response);
        exit;
    }

    function remove_ip_address_otp(Request $request)
    {
        if (Auth::User()->role_id == 10) {
            $user_id = Auth::id();
            $userdetails = User::find($user_id);
            $otp = mt_rand(100000, 999999);
            $message = "Dear $userdetails->name your OTP Is : $otp for remove ip address $this->brand_name";
            $template_id = 20;
            $library = new SmsLibrary();
            $library->send_sms($userdetails->mobile, $message, $template_id);
            User::where('id', $user_id)->update(['login_otp' => $otp]);
            return Response()->json(['status' => 'success', 'message' => 'OTP successfully sent to your register mobile number']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function remove_ip_address_save(Request $request)
    {
        if (Auth::User()->role_id == 10) {
            $rules = array(
                'password' => 'required',
                'otp' => 'nullable|digits:6',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $otp = $request->input('otp');
            $password = $request->password;
            $user_id = Auth::id();
            $userdetail = User::find($user_id);
            $current_password = $userdetail->password;
            if (!Hash::check($password, $current_password)) {
                return Response()->json(['status' => 'failure', 'message' => 'Password not match']);
            }
            if ($request->filled('otp')) {
                if ((string)$userdetail->login_otp !== (string)$otp) {
                    return Response()->json(['status' => 'failure', 'message' => 'OTP not match']);
                }
            }
            Member::where('user_id', $user_id)->update(['ip_address' => '']);
            return Response()->json(['status' => 'success', 'message' => 'IP address successfully removed']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function payoutDocs(Request $request)
    {
        if (Auth::User()->role_id == 10) {
            $gateway = $this->resolvePayoutDocsGateway((int)Auth::id());
            $data = array_merge(['page_title' => 'Payout Document'], $gateway);
            return view('agent.developer.payoutDocs')->with($data);
        } else {
            return redirect()->back();
        }
    }

    /**
     * Resolve merchant-facing payout gateway docs from company route + bank switching override.
     */
    private function resolvePayoutDocsGateway(int $userId): array
    {
        $user = User::with('company')->find($userId);
        $apiId = (int)($user->company->payout_route ?? 0);

        $switching = Banktransferswitching::where('user_id', $userId)
            ->orderByDesc('id')
            ->first();

        if (!$switching) {
            $switching = Banktransferswitching::where(function ($query) {
                $query->where('user_id', 0)->orWhereNull('user_id');
            })
                ->orderByDesc('id')
                ->first();
        }

        if ($switching && !empty($switching->api_id)) {
            $apiId = (int)$switching->api_id;
        }

        $catalog = [
            16 => [
                'gateway_short' => 'Payin 9',
                'min_amount' => 1,
                'max_amount' => 100000,
                'provider_callback_path' => 'api/call-back/qpc-payout',
            ],
            17 => [
                'gateway_short' => 'Payin 10',
                'min_amount' => RojgaarPeLibrary::PAYOUT_MIN,
                'max_amount' => RojgaarPeLibrary::PAYOUT_MAX,
                'provider_callback_path' => 'api/call-back/rojgaarpe-payout',
            ],
            18 => [
                'gateway_short' => 'Payin 11',
                'min_amount' => FrapPayLibrary::PAYOUT_MIN,
                'max_amount' => FrapPayLibrary::PAYOUT_MAX,
                'provider_callback_path' => 'api/call-back/frappay-payout',
            ],
            19 => [
                'gateway_short' => 'Payin 12',
                'min_amount' => AurexaPayLibrary::PAYOUT_MIN,
                'max_amount' => AurexaPayLibrary::PAYOUT_MAX,
                'provider_callback_path' => 'api/call-back/aurexapay-payout',
            ],
        ];

        $meta = $catalog[$apiId] ?? [
            'gateway_short' => 'Payout',
            'min_amount' => 1,
            'max_amount' => 10000000,
            'provider_callback_path' => '',
        ];

        $provider = Provider::where('api_id', $apiId)->where('status_id', 1)->orderByDesc('id')->first()
            ?: Provider::where('api_id', $apiId)->orderByDesc('id')->first();
        if ($provider) {
            if (isset($provider->min_amount) && (float)$provider->min_amount > 0) {
                $meta['min_amount'] = (float)$provider->min_amount;
            }
            if (isset($provider->max_amount) && (float)$provider->max_amount > 0) {
                $meta['max_amount'] = (float)$provider->max_amount;
            }
        }

        // Prefer library payout limits for known gateways (provider min/max is often payin-oriented).
        if ($apiId === 19) {
            $meta['min_amount'] = AurexaPayLibrary::PAYOUT_MIN;
            $meta['max_amount'] = AurexaPayLibrary::PAYOUT_MAX;
        } elseif ($apiId === 17) {
            $meta['min_amount'] = RojgaarPeLibrary::PAYOUT_MIN;
            $meta['max_amount'] = RojgaarPeLibrary::PAYOUT_MAX;
        } elseif ($apiId === 18) {
            $meta['min_amount'] = FrapPayLibrary::PAYOUT_MIN;
            $meta['max_amount'] = FrapPayLibrary::PAYOUT_MAX;
        }

        return [
            'payout_api_id' => $apiId,
            'payout_gateway_label' => 'Payout',
            'payout_gateway_short' => $meta['gateway_short'],
            'payout_min_amount' => (int)$meta['min_amount'],
            'payout_max_amount' => (int)$meta['max_amount'],
            'payout_provider_callback' => $meta['provider_callback_path'] !== ''
                ? url($meta['provider_callback_path'])
                : '',
        ];
    }

    function collectPayment()
    {
        $data = array('page_title' => 'Collect Payment Docs');
        return view('agent.developer.collectPayment')->with($data);
    }

    function payinDocs()
    {
        $data = array('page_title' => 'Payin Docs');
        return view('agent.developer.payinDocs')->with($data);
    }

    function payinTwoDocs()
    {
        $data = array('page_title' => 'Payin 4 Docs');
        return view('agent.developer.payinTwoDocs')->with($data);
    }

    function payinfiveDocs()
    {
        $data = array('page_title' => 'Payin 5 Docs');
        return view('agent.developer.payinfiveDocs')->with($data);
    }
    
    function payinSixDocs()
    {
        $data = array('page_title' => 'Payin 6 Docs');
        return view('agent.developer.payinsixDocs')->with($data);
    }
    
    function payinSevenDocs()
    {
        $data = array('page_title' => 'Payin 7 Docs');
        return view('agent.developer.payinSevenDocs')->with($data);
    }

    function payinEightDocs()
    {
        $data = array('page_title' => 'Payin 8 Docs');
        return view('agent.developer.payinEightDocs')->with($data);
    }

    function payinNineDocs()
    {
        $library = new BasicLibrary();
        $activeService = $library->getActiveService(340, Auth::id());
        if (($activeService['status_id'] ?? 0) != 1) {
            return redirect()->back();
        }

        $data = array('page_title' => 'Payin 9 Docs');
        return view('agent.developer.payinNineDocs')->with($data);
    }

    function payinTenDocs()
    {
        $library = new BasicLibrary();
        $activeService = $library->getActiveService(341, Auth::id());
        if (($activeService['status_id'] ?? 0) != 1) {
            return redirect()->back();
        }

        $data = array('page_title' => 'Payin 10 Docs');
        return view('agent.developer.payinTenDocs')->with($data);
    }

    function payinElevenDocs()
    {
        $library = new BasicLibrary();
        $activeService = $library->getActiveService(342, Auth::id());
        if (($activeService['status_id'] ?? 0) != 1) {
            return redirect()->back();
        }

        $data = array('page_title' => 'Payin 11 Docs');
        return view('agent.developer.payinElevenDocs')->with($data);
    }

    function payinTwelveDocs()
    {
        $library = new BasicLibrary();
        $activeService = $library->getActiveService(343, Auth::id());
        if (($activeService['status_id'] ?? 0) != 1) {
            return redirect()->back();
        }

        $data = array('page_title' => 'Payin 12 Docs');
        return view('agent.developer.payinTwelveDocs')->with($data);
    }

}
