<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\Provider;
use App\Models\Service;
use App\Models\Api;
use App\Models\Apiprovider;
use App\Models\Numberdata;
use App\Models\State;
use App\Models\Circleprovider;
use App\Models\Status;
use App\Models\Backupapi;
use App\Models\Role;
use App\Models\User;
use App\Models\Providerlimit;
use App\Models\Callbackurl;
use App\Models\Responsesetting;
use App\Models\Denomination;
use App\Models\Apiresponse;
use App\Models\Apicheckbalance;
use App\Models\Company;
use App\Models\Sitesetting;
use App\Models\Banktransferswitching;
use Helpers;
use Str;
use DB;
use App\Library\ApibalanceLibrary;
use App\Library\PermissionLibrary;
use App\Library\BasicLibrary;

class ApimasterController extends Controller
{

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $companies = Company::find($this->company_id);
        $this->cdnLink = (empty($companies)) ? '' : $companies->cdn_link;

        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        if ($sitesettings) {
            $this->brand_name = $sitesettings->brand_name;
            $this->backend_template_id = $sitesettings->backend_template_id;
        } else {
            $this->brand_name = "";
            $this->backend_template_id = 1;
        }
    }

    function provider_master()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['provider_master_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $service_id = Service::where('status_id', 1)->get(['id']);
            $providers = Provider::whereIn('service_id', $service_id)->get();
            $services = Service::whereIn('id', $service_id)->get();
            $data = array(
                'page_title' => 'Provider Master',
                'urls' => url('admin/provider-master-api'),
            );
            if ($this->backend_template_id == 1) {
                return view('admin.api-master.provider_master', compact('providers', 'services'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.api-master.provider_master', compact('providers', 'services'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.api-master.provider_master', compact('providers', 'services'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.api-master.provider_master', compact('providers', 'services'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function provider_master_api(Request $request)
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

        $totalRecords = Provider::select('count(*) as allcount')
            ->count();

        $totalRecordswithFilter = Provider::select('count(*) as allcount')
            ->where(function ($query) use ($searchValue) {
                $query->where('id', 'like', '%' . $searchValue . '%')
                    ->orWhere('provider_name', 'like', '%' . $searchValue . '%');
            })->count();

        // Fetch records

        $records = Provider::orderBy($columnName, $columnSortOrder)
            ->where(function ($query) use ($searchValue) {
                $query->where('id', 'like', '%' . $searchValue . '%')
                    ->orWhere('provider_name', 'like', '%' . $searchValue . '%');
            })->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            $data_arr[] = array(
                "id" => $value->id,
                "provider_name" => Str::limit($value->provider_name, 25) . ' <small id="slabHelp" class="form-text text-muted mt-0" style="font-size:80%"><u><a href="#" class="text-info" onclick="viewProviderForLogo(' . $value->id . ')">Add Logo</a></u></small>',
                "service_name" => $value->service->service_name,
                "provider_image" => '<a href="' . $this->cdnLink . $value->provider_image . '" target="_blank"><img src="' . $this->cdnLink . $value->provider_image . '" style="width: 50%;"></a>',
                "status" => ($value->status_id == 1) ? '<span class="badge badge-success">Enabled</span>' : '<span class="badge badge-danger">Disabled</span> ',
                "min_length" => $value->min_length,
                "max_length" => $value->max_length,
                "start_with" => $value->start_with,
                "gst_type" => ($value->gst_type == 1) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>',
                "min_amount" => $value->min_amount,
                "max_amount" => $value->max_amount,
                "help_line" => $value->help_line,
                "action" => '<button class="btn btn-danger btn-sm" onclick="view_provider(' . $value->id . ')">Update</button>',
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


    function view_provider(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['provider_master_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $providers = Provider::where('id', $id)->first();
            if ($providers) {
                $details = array(
                    'provider_id' => $providers->id,
                    'provider_name' => $providers->provider_name,
                    'service_id' => $providers->service_id,
                    'gst_type' => $providers->gst_type,
                    'status_id' => $providers->status_id,
                    'min_length' => $providers->min_length,
                    'max_length' => $providers->max_length,
                    'start_with' => $providers->start_with,
                    'min_amount' => $providers->min_amount,
                    'max_amount' => $providers->max_amount,
                    'help_line' => $providers->help_line,
                    'block_amount' => $providers->block_amount,
                );
                return Response()->json([
                    'status' => 'success',
                    'details' => $details
                ]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'provider not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
        }

    }

    function update_provider(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['provider_master_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'provider_id' => 'required',
                'provider_name' => 'required',
                'service_id' => 'required',
                'status_id' => 'required',
                'gst_type' => 'required',
                'min_length' => 'required',
                'max_length' => 'required',
                'min_amount' => 'required',
                'max_amount' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }

            $provider_id = $request->provider_id;
            $provider_name = $request->provider_name;
            $service_id = $request->service_id;
            $status_id = $request->status_id;
            $gst_type = $request->gst_type;
            $min_length = $request->min_length;
            $max_length = $request->max_length;
            $start_with = $request->start_with;
            $min_amount = $request->min_amount;
            $max_amount = $request->max_amount;
            $help_line = $request->help_line;
            $block_amount = preg_replace('/\s+/', '', $request->block_amount);
            Provider::where('id', $provider_id)->update([
                'provider_name' => $provider_name,
                'service_id' => $service_id,
                'status_id' => $status_id,
                'gst_type' => $gst_type,
                'min_length' => $min_length,
                'max_length' => $max_length,
                'start_with' => $start_with,
                'min_amount' => $min_amount,
                'max_amount' => $max_amount,
                'help_line' => $help_line,
                'block_amount' => $block_amount,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Provider successfully updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
        }
    }

    function add_provider(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['provider_master_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'service_id' => 'required',
                'provider_name' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }

            $service_id = $request->service_id;
            $provider_name = $request->provider_name;
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            Provider::insertGetId([
                'provider_name' => $provider_name,
                'service_id' => $service_id,
                'api_id' => 1,
                'created_at' => $ctime,
                'status_id' => 1,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Provider successfully added']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
        }
    }

    function store_provider_logo(Request $request)
    {
        if (Auth::User()->role_id <= 2) {
            $this->validate($request, [
                'logo_provider_id' => 'required|exists:providers,id',
                'provider_logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
            ]);
            $providers = Provider::find($request->logo_provider_id);
            $photo = base64_encode(file_get_contents($request->provider_logo));
            $url = $this->cdnLink . "api/file/v1/save-provider-icon";
            $api_request_parameters = array(
                'image' => $photo,
                'name' => $providers->id,
            );
            $method = 'POST';
            $header = ["Accept:application/json"];
            $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
            $res = json_decode($response);
            $status = $res->status;
            if ($status == 'success') {
                $image_url = $res->image_url;
                Provider::where('id', $request->logo_provider_id)->update(['provider_image' => $image_url]);
                \Session::flash('success', 'Successful..!');
                return redirect()->back();
            } else {
                \Session::flash('failure', $res->message);
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }

    function api_master()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['api_master_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $api = Api::get();
            $data = array('page_title' => 'Api Master');
            if ($this->backend_template_id == 1) {
                return view('admin.api-master.api_master', compact('api'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.api-master.api_master', compact('api'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.api-master.api_master', compact('api'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.api-master.api_master', compact('api'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function viewApiCredentials(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'id' => 'required|exists:apis,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $id = $request->id;
            $apis = Api::find($id);
            if ($apis) {
                return Response()->json([
                    'status' => 'success',
                    'message' => 'Successful..!',
                    'api_id' => $id,
                    'api_name' => $apis->api_name,
                    'credentials' => json_decode($apis->credentials),
                ]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found!']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permision!']);
        }
    }


    function updateApiCredentials(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'api_id' => 'required|exists:apis,id',
                'jsonData' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $api_id = $request->api_id;
            $credentials = $request->jsonData;
            Api::where('id', $api_id)->update(['credentials' => $credentials]);
            return Response()->json(['status' => 'success', 'message' => 'Credentials successfully updated!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permision!']);
        }

    }

    function view_api_provider($id)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['add_api_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $library = new BasicLibrary();
            $serviceId = $library->getTelecomServiceId();
            $providers = Provider::whereIn('service_id', $serviceId)->get();
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            foreach ($providers as $value) {
                $apiproviders = Apiprovider::where('provider_id', $value->id)->where('api_id', $id)->first();
                if (empty($apiproviders)) {
                    Apiprovider::insertGetId([
                        'provider_id' => $value->id,
                        'service_id' => $value->service_id,
                        'api_id' => $id,
                        'created_at' => $ctime,
                        'user_id' => Auth::id(),
                    ]);
                }
            }
            $apiprovider = Apiprovider::where('api_id', $id)->whereIn('service_id', $serviceId)->get();
            $data = array('page_title' => 'Api Provider Settings');
            if ($this->backend_template_id == 1) {
                return view('admin.api-master.view_api_provider', compact('apiprovider'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.api-master.view_api_provider', compact('apiprovider'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.api-master.view_api_provider', compact('apiprovider'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.api-master.view_api_provider', compact('apiprovider'))->with($data);
            } else {
                return redirect()->back();
            }

        } else {
            return Redirect::back();
        }
    }

    function view_api_master_provider(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $id = $request->id;
            $apiproviders = Apiprovider::where('id', $id)->first();
            if ($apiproviders) {
                $details = array(
                    'id' => $apiproviders->id,
                    'provider_name' => $apiproviders->provider->provider_name,
                    'service_name' => $apiproviders->provider->service->service_name,
                    'api_name' => $apiproviders->api->api_name,
                    'operator_code' => $apiproviders->operator_code,
                    'api_commission' => $apiproviders->api_commission,
                );
                return Response()->json([
                    'status' => 'success',
                    'details' => $details,
                ]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_api_provider(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['add_api_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $operator_code = $request->operator_code;
            $api_commission = $request->api_commission;
            $type = $request->type;
            Apiprovider::where('id', $id)->update([
                'type' => $type,
                'api_commission' => $api_commission,
                'operator_code' => $operator_code,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'update success']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function number_series_master()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['number_series_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $state = State::where('status_id', 1)->get();
            $numberdata = Numberdata::where('company_id', Auth::User()->company_id)->get();
            $data = array('page_title' => 'Number Series Master');
            if ($this->backend_template_id == 1) {
                return view('admin.api-master.number_series_master', compact('state', 'numberdata'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.api-master.number_series_master', compact('state', 'numberdata'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.api-master.number_series_master', compact('state', 'numberdata'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.api-master.number_series_master', compact('state', 'numberdata'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function view_number_series(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['number_series_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $numberdatas = Numberdata::where('id', $id)->first();
            if ($numberdatas) {

                $details = array(
                    'id' => $numberdatas->id,
                    'number' => $numberdatas->number,
                    'state_id' => $numberdatas->state_id,
                );
                return Response()->json([
                    'status' => 'success',
                    'details' => $details,
                ]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
        }
    }

    function update_number_series(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['number_series_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $rules = array(
                'id' => 'required',
                'number' => 'required',
                'state_id' => 'required|unique:numberdatas,state_id,' . $id, 'state_id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }

            $id = $request->id;
            $state_id = $request->state_id;
            $number = str_replace(' ', '', $request->number);
            Numberdata::where('id', $id)->update(['number' => $number, 'state_id' => $state_id]);
            return Response()->json(['status' => 'success', 'message' => 'Number series successfully updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
        }
    }

    function add_number_series(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['number_series_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'number' => 'required',
                'state_id' => 'required|unique:numberdatas',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $number = str_replace(' ', '', $request->number);
            $state_id = $request->state_id;
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            Numberdata::insertGetId([
                'user_id' => Auth::id(),
                'number' => $number,
                'state_id' => $state_id,
                'created_at' => $ctime,
                'company_id' => Auth::User()->company_id,
                'status_id' => 1,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Number series successfully added']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
        }
    }

    function state_wise_api()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['state_wise_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $state = State::where('status_id', 1)->get();
            $data = array('page_title' => 'State Wise Api');
            if ($this->backend_template_id == 1) {
                return view('admin.api-master.state_wise_api', compact('state'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.api-master.state_wise_api', compact('state'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.api-master.state_wise_api', compact('state'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.api-master.state_wise_api', compact('state'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function state_provider_setting($id)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['state_wise_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $providers = Provider::whereIn('service_id', [1])->where('status_id', 1)->get();
            foreach ($providers as $value) {
                $circleproviders = Circleprovider::where('provider_id', $value->id)->where('state_id', $id)->first();
                if (empty($circleprovider)) {
                    Circleprovider::insertGetId([
                        'user_id' => Auth::id(),
                        'state_id' => $id,
                        'provider_id' => $value->id,
                        'company_id' => Auth::User()->company_id,
                        'created_at' => $ctime,
                        'status_id' => 0,
                        'api_id' => 0,
                    ]);
                }
            }
            $data = array('page_title' => 'State Wise Provider Settings');
            $circleprovider = Circleprovider::where('state_id', $id)->get();
            $statuses = Status::whereIn('id', [0, 1])->get();
            $apis = Api::where('status_id', 1)->get();
            if ($this->backend_template_id == 1) {
                return view('admin.api-master.state_provider_setting', compact('circleprovider', 'statuses', 'apis'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.api-master.state_provider_setting', compact('circleprovider', 'statuses', 'apis'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.api-master.state_provider_setting', compact('circleprovider', 'statuses', 'apis'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.api-master.state_provider_setting', compact('circleprovider', 'statuses', 'apis'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function update_state_wise_api_status(Request $request)
    {
        $id = $request->id;
        $status_id = $request->status_id;
        Circleprovider::where('id', $id)->update(['status_id' => $status_id]);
        return Response()->json(['status' => 'success', 'message' => 'status successfully updated']);
    }

    function update_state_wise_api_id(Request $request)
    {
        if ($request->api_id) {
            $id = $request->id;
            $api_id = $request->api_id;
            Circleprovider::where('id', $id)->update(['api_id' => $api_id]);
            return Response()->json(['status' => 'success', 'message' => 'Api Successfully updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Kindly select api']);
        }

    }

    function backup_api_master()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['backup_api_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $service_id = Service::where('status_id', 1)->get(['id']);
            $providers = Provider::whereIn('service_id', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 15])->whereIn('service_id', $service_id)->get();
            $apis = Api::where('company_id', Auth::User()->company_id)->get();
            $backupapi = Backupapi::where('created_by', Auth::id())->where('company_id', Auth::User()->company_id)->orderBy('id', 'DESC')->get();
            $data = array('page_title' => 'Backup Api Master');
            if ($this->backend_template_id == 1) {
                return view('admin.api-master.backup_api_master', compact('providers', 'backupapi', 'apis'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.api-master.backup_api_master', compact('providers', 'backupapi', 'apis'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.api-master.backup_api_master', compact('providers', 'backupapi', 'apis'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.api-master.backup_api_master', compact('providers', 'backupapi', 'apis'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function save_backup_api(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['backup_api_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'provider_id' => 'required',
                'api_id' => 'required',

            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $provider_id = $request->provider_id;
            $api_id = $request->api_id;
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $checkdetails = Backupapi::where('provider_id', $provider_id)->where('api_id', $api_id)->first();
            if ($checkdetails) {
                return Response()->json(['status' => 'failure', 'message' => 'Duplicate Entry']);
            } else {
                $checkentry = Backupapi::where('provider_id', $provider_id)->orderBy('id', 'DESC')->first();
                if ($checkentry) {
                    $api_type = $checkentry->api_type + 1;
                    Backupapi::insertGetId([
                        'user_id' => 0,
                        'created_by' => Auth::id(),
                        'provider_id' => $provider_id,
                        'api_id' => $api_id,
                        'api_type' => $api_type,
                        'company_id' => Auth::User()->company_id,
                        'created_at' => $ctime,
                        'status_id' => 1,
                    ]);
                } else {
                    $api_type = 1;
                    Backupapi::insertGetId([
                        'user_id' => 0,
                        'created_by' => Auth::id(),
                        'provider_id' => $provider_id,
                        'api_id' => $api_id,
                        'api_type' => $api_type,
                        'company_id' => Auth::User()->company_id,
                        'created_at' => $ctime,
                        'status_id' => 1,
                    ]);

                }
                return Response()->json(['status' => 'success', 'message' => 'Data successfully added']);
            }

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }

    }

    function delete_backup_api(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['backup_api_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $backupapis = Backupapi::where('id', $id)->first();
            if ($backupapis) {
                Backupapi::where('id', $id)->delete();
                return Response()->json(['status' => 'success', 'message' => 'record successfully deleted']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function view_backup_api(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['backup_api_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $backupapi = Backupapi::where('id', $id)->first();
            if ($backupapi) {
                $details = array(
                    'id' => $backupapi->id,
                    'provider_id' => $backupapi->provider_id,
                    'api_id' => $backupapi->api_id,
                    'api_type' => $backupapi->api_type,
                    'status_id' => $backupapi->status_id
                );
                return Response()->json([
                    'status' => 'success',
                    'details' => $details,
                ]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_backup_api(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['backup_api_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'id' => 'required',
                'provider_id' => 'required',
                'api_id' => 'required',
                'api_type' => 'required',
                'status_id' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }

            $id = $request->id;
            $provider_id = $request->provider_id;
            $api_id = $request->api_id;
            $api_type = $request->api_type;
            $status_id = $request->status_id;
            $checkdetails = Backupapi::where('provider_id', $provider_id)->where('api_id', $api_id)->where('api_type', $api_type)->whereNotIn('id', [$id])->first();
            if ($checkdetails) {
                return Response()->json(['status' => 'failure', 'message' => 'duplicate entry']);
            } else {
                $chektype = Backupapi::where('provider_id', $provider_id)->where('api_id', $api_id)->where('api_type', $api_type)->where('id', $id)->first();
                if ($chektype) {
                    Backupapi::where('id', $id)->update(['provider_id' => $provider_id, 'api_id' => $api_id, 'status_id' => $status_id]);
                } else {
                    Backupapi::where('id', $id)->update(['provider_id' => $provider_id, 'api_id' => $api_id, 'api_type' => $api_type, 'status_id' => $status_id]);
                }
            }
            return Response()->json(['status' => 'success', 'message' => 'data successfully updated']);

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }


    function api_switching()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['api_switching_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $service_id = Service::where('status_id', 1)->get(['id']);
            $providers = Provider::whereIn('service_id', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 15])->whereIn('service_id', $service_id)->get();
            $apis = Api::where('company_id', Auth::User()->company_id)->get();
            $data = array('page_title' => 'Api Switching');
            if ($this->backend_template_id == 1) {
                return view('admin.api-master.api_switching', compact('providers', 'apis'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.api-master.api_switching', compact('providers', 'apis'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.api-master.api_switching', compact('providers', 'apis'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.api-master.api_switching', compact('providers', 'apis'))->with($data);
            } else {
                return redirect()->back();
            }

        } else {
            return Redirect::back();
        }
    }

    function update_api_switching(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['api_switching_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            if ($request->api_id) {
                $id = $request->id;
                $api_id = $request->api_id;
                Provider::where('id', $id)->update(['api_id' => $api_id]);
                return Response()->json(['status' => 'success', 'message' => 'Api switching successfully updated']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Kindly select api']);
            }

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function user_operator_limit(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['user_operator_limit_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }

        if (Auth::User()->role_id <= 2) {
            if ($request->user_id || $request->role_id) {
                $role_id = $request->role_id;
                $user_id = $request->user_id;
                $urls = url('admin/user-operator-limit-api') . '?' . 'role_id=' . $role_id . '&user_id=' . $user_id;
            } else {
                $role_id = 0;
                $user_id = 0;
                $urls = url('admin/user-operator-limit-api') . '?' . 'role_id=' . $role_id . '&user_id=' . $user_id;
            }
            $data = array(
                'page_title' => 'User Operator Limit',
                'role_id' => $role_id,
                'user_id' => $user_id,
                'urls' => $urls
            );
            $roles = Role::whereIn('id', [8, 9, 10])->get();
            $users = User::where('status_id', 1)->whereIn('role_id', [8, 9, 10])->get();
            if ($this->backend_template_id == 1) {
                return view('admin.api-master.user_operator_limit', compact('roles', 'users'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.api-master.user_operator_limit', compact('roles', 'users'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.api-master.user_operator_limit', compact('roles', 'users'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.api-master.user_operator_limit', compact('roles', 'users'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function user_operator_limit_api(Request $request)
    {
        $role_id = $request->get('role_id');
        $user_id = $request->get('amp;user_id');

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
        if ($role_id == 0) {
            $roles_id = Role::whereIn('id', [8, 9, 10])->get(['id']);
        } else {
            $roles_id = Role::where('id', $role_id)->get(['id']);
        }

        if ($user_id == 0) {
            $users_id = User::whereIn('role_id', [8, 9, 10])->where('status_id', 1)->get(['id']);
        } else {
            $users_id = User::where('id', $user_id)->where('status_id', 1)->get(['id']);
        }

        $totalRecords = User::select('count(*) as allcount')
            ->whereIn('id', $users_id)
            ->whereIn('role_id', $roles_id)
            ->count();

        $totalRecordswithFilter = User::select('count(*) as allcount')
            ->whereIn('id', $users_id)
            ->whereIn('role_id', $roles_id)
            ->where('mobile', 'like', '%' . $searchValue . '%')
            ->count();

        $records = User::orderBy($columnName, $columnSortOrder)
            ->where('mobile', 'like', '%' . $searchValue . '%')
            ->whereIn('id', $users_id)
            ->whereIn('role_id', $roles_id)
            ->orderBy('id', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data_arr = array();
        foreach ($records as $value) {
            $urls = url('admin/view-operator-limit') . '/' . $value->id;
            $data_arr[] = array(
                "id" => $value->id,
                "name" => $value->name . ' ' . $value->last_name,
                "email" => $value->email,
                "mobile" => $value->mobile,
                "role_type" => $value->role->role_title,
                "action" => '<a class="btn btn-danger btn-sm" href="' . $urls . '"><i class="fas fa-pen-square"></i> Update</a>',
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

    function get_user_by_role(Request $request)
    {
        $role_id = $request->role_id;
        if ($role_id == 0) {
            $users = User::where('status_id', 1)->whereIn('role_id', [8, 9, 10])->get();
        } else {
            $users = User::where('status_id', 1)->where('role_id', $role_id)->get();
        }
        $response = array();
        foreach ($users as $value) {
            $product = array();
            $product["id"] = $value->id;
            $product["name"] = $value->name . ' ' . $value->last_name . ' - ' . $value->mobile . ' (' . $value->role->role_title . ')';
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'users' => $response]);
    }

    function view_operator_limit($user_id)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['user_operator_limit_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $userdetails = User::find($user_id);
            if ($userdetails) {
                $service_id = Service::where('status_id', 1)->get(['id']);
                $providers = Provider::whereIn('service_id', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 15])->whereIn('service_id', $service_id)->get();
                foreach ($providers as $value) {
                    $providerlimitcheck = Providerlimit::where('user_id', $user_id)->where('provider_id', $value->id)->first();
                    if (empty($providerlimitcheck)) {
                        $now = new \DateTime();
                        $ctime = $now->format('Y-m-d H:i:s');
                        Providerlimit::insertGetId([
                            'user_id' => $user_id,
                            'provider_id' => $value->id,
                            'service_id' => $value->service_id,
                            'amount_limit' => 0,
                            'provider_status' => 1,
                            'created_at' => $ctime,
                            'status_id' => 0,
                            'daily_limit' => 0,
                        ]);
                    }
                }
                $providerlimit = Providerlimit::where('user_id', $user_id)->whereIn('service_id', $service_id)->get();
                $data = array('page_title' => "$userdetails->name  Operator Limit");
                if ($this->backend_template_id == 1) {
                    return view('admin.api-master.view_operator_limit', compact('providerlimit'))->with($data);
                } elseif ($this->backend_template_id == 2) {
                    return view('themes2.admin.api-master.view_operator_limit', compact('providerlimit'))->with($data);
                } elseif ($this->backend_template_id == 3) {
                    return view('themes3.admin.api-master.view_operator_limit', compact('providerlimit'))->with($data);
                } elseif ($this->backend_template_id == 4) {
                    return view('themes4.admin.api-master.view_operator_limit', compact('providerlimit'))->with($data);
                } else {
                    return redirect()->back();
                }
            } else {
                return Redirect::back();
            }
        } else {
            return Redirect::back();
        }
    }

    function update_operator_limit(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['user_operator_limit_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $amount_limit = $request->amount_limit;
            $provider_status = $request->provider_status;
            $status_id = $request->status_id;
            $limit_timing = $request->limit_timing;
            $daily_limit = $request->daily_limit;
            Providerlimit::where('id', $id)->update([
                'amount_limit' => $amount_limit,
                'provider_status' => $provider_status,
                'status_id' => $status_id,
                'limit_timing' => $limit_timing,
                'daily_limit' => $daily_limit,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'limit update successfully']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function create_new_api(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['add_api_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'api_name' => 'required|unique:apis',
                'method' => 'required',
                'response_type' => 'required',
                'base_url' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            DB::beginTransaction();
            try {
                $api_name = $request->api_name;
                $method = $request->method;
                $response_type = $request->response_type;
                $base_url = str_replace(' ', '', $request->base_url);
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                Api::insertGetId([
                    'api_name' => $api_name,
                    'base_url' => $base_url,
                    'method' => $method,
                    'response_type' => $response_type,
                    'user_id' => Auth::id(),
                    'created_at' => $ctime,
                    'company_id' => Auth::User()->company_id,
                    'vender_id' => 0,
                    'status_id' => 1,
                ]);
                DB::commit();
                return response()->json(['status' => 'success', 'message' => 'Api successfully created !']);
            } catch (\Exception $ex) {
                DB::rollback();
                // throw $ex;
                return response()->json(['status' => 'failure', 'message' => $ex->getMessage()]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function view_api_details(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['update_api_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $apis = Api::find($id);
            if ($apis) {
                $details = array(
                    'id' => $apis->id,
                    'api_name' => $apis->api_name,
                    'support_number' => $apis->support_number,
                    'base_url' => $apis->base_url,
                    'method' => $apis->method,
                    'response_type' => $apis->response_type,
                    'speed_status' => $apis->speed_status,
                    'speed_limit' => $apis->speed_limit,
                );
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'details' => $details]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_api_details(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['update_api_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'id' => 'required',
                'api_name' => 'required',
                'method' => 'required',
                'response_type' => 'required',
                'base_url' => 'required',
                'support_number' => 'required|digits:10',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            DB::beginTransaction();
            try {
                $id = $request->id;
                $api_name = $request->api_name;
                $method = $request->method;
                $response_type = $request->response_type;
                $base_url = str_replace(' ', '', $request->base_url);
                $support_number = $request->support_number;
                $speed_status = $request->speed_status;
                $speed_limit = $request->speed_limit;
                Api::where('id', $id)->update([
                    'api_name' => $api_name,
                    'method' => $method,
                    'response_type' => $response_type,
                    'base_url' => $base_url,
                    'support_number' => $support_number,
                    'speed_status' => $speed_status,
                    'speed_limit' => $speed_limit,
                ]);
                DB::commit();
                return response()->json(['status' => 'success', 'message' => 'Api details successfully updated !']);
            } catch (\Exception $ex) {
                DB::rollback();
                // throw $ex;
                return response()->json(['status' => 'failure', 'message' => $ex->getMessage()]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function webhook_setting($api_id)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['add_api_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $apis = Api::find($api_id);
            if ($apis) {
                $callbackurls = Callbackurl::where('api_id', $api_id)->first();
                if ($callbackurls) {

                } else {
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    Callbackurl::insertGetId([
                        'user_id' => Auth::id(),
                        'api_id' => $api_id,
                        'created_at' => $ctime,
                    ]);
                }
                $callbackurls = Callbackurl::where('api_id', $api_id)->first();
                $data = array(
                    'page_title' => $apis->api_name . ' Webhook URL',
                    'api_name' => $apis->api_name,
                    'api_id' => $callbackurls->api_id,
                    'status_parameter' => $callbackurls->status_parameter,
                    'success_value' => $callbackurls->success_value,
                    'failure_value' => $callbackurls->failure_value,
                    'failure_value_two' => $callbackurls->failure_value_two,
                    'failure_value_three' => $callbackurls->failure_value_three,
                    'uniq_id' => $callbackurls->uniq_id,
                    'operator_ref' => $callbackurls->operator_ref,
                    'ip_address' => $callbackurls->ip_address,
                    'webhook_url' => url('api/call-back/recharge-response') . '/' . $api_id,
                );
                if ($this->backend_template_id == 1) {
                    return view('admin.api-master.webhook_setting')->with($data);
                } elseif ($this->backend_template_id == 2) {
                    return view('themes2.admin.api-master.webhook_setting')->with($data);
                } elseif ($this->backend_template_id == 3) {
                    return view('themes3.admin.api-master.webhook_setting')->with($data);
                } elseif ($this->backend_template_id == 4) {
                    return view('themes4.admin.api-master.webhook_setting')->with($data);
                } else {
                    return redirect()->back();
                }
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }

    function update_webhook_url(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['add_api_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'status_parameter' => 'required',
                'success_value' => 'required',
                'failure_value' => 'required',
                'operator_ref' => 'required',
                'uniq_id' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $status_parameter = $request->status_parameter;
            $success_value = $request->success_value;
            $failure_value = $request->failure_value;
            $operator_ref = $request->operator_ref;
            $uniq_id = $request->uniq_id;
            $api_id = $request->api_id;
            $ip_address = $request->ip_address;
            Callbackurl::where('api_id', $api_id)->update([
                'status_parameter' => $status_parameter,
                'success_value' => $success_value,
                'failure_value' => $failure_value,
                'operator_ref' => $operator_ref,
                'uniq_id' => $uniq_id,
                'ip_address' => $ip_address,
                'failure_value_two' => $request->failure_value_two,
                'failure_value_three' => $request->failure_value_three,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function response_setting($api_id)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['add_api_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $apis = Api::find($api_id);
            if ($apis) {
                $data = array(
                    'page_title' => $apis->api_name . ' Response Settings',
                    'api_id' => $api_id,
                );
                $statuses = Status::whereIn('id', [1, 2])->get();
                $responsesettings = Responsesetting::where('api_id', $api_id)->get();
                if ($this->backend_template_id == 1) {
                    return view('admin.api-master.response_setting', compact('responsesettings', 'statuses'))->with($data);
                } elseif ($this->backend_template_id == 2) {
                    return view('themes2.admin.api-master.response_setting', compact('responsesettings', 'statuses'))->with($data);
                } elseif ($this->backend_template_id == 3) {
                    return view('themes3.admin.api-master.response_setting', compact('responsesettings', 'statuses'))->with($data);
                } elseif ($this->backend_template_id == 4) {
                    return view('themes4.admin.api-master.response_setting', compact('responsesettings', 'statuses'))->with($data);
                } else {
                    return redirect()->back();
                }
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }

    function add_new_responses(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['add_api_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'api_id' => 'required',
                'status_id' => 'required',
                'status_parameter' => 'required',
                'status_value' => 'required',
                'operator_ref_parameter' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $api_id = $request->api_id;
            $status_id = $request->status_id;
            $status_parameter = $request->status_parameter;
            $status_value = $request->status_value;
            $operator_ref_parameter = $request->operator_ref_parameter;
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $under_value = $request->under_value;
            Responsesetting::insertGetId([
                'api_id' => $api_id,
                'status_id' => $status_id,
                'status_parameter' => $status_parameter,
                'status_value' => $status_value,
                'operator_ref_parameter' => $operator_ref_parameter,
                'created_at' => $ctime,
                'under_value' => $under_value,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function view_api_responses(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['add_api_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $responsesettings = Responsesetting::find($id);
            if ($responsesettings) {
                $details = array(
                    'id' => $responsesettings->id,
                    'api_id' => $responsesettings->api_id,
                    'status_id' => $responsesettings->status_id,
                    'status_parameter' => $responsesettings->status_parameter,
                    'status_value' => $responsesettings->status_value,
                    'operator_ref_parameter' => $responsesettings->operator_ref_parameter,
                    'under_value' => $responsesettings->under_value,
                );
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'details' => $details]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_api_responses(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['add_api_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'id' => 'required',
                'status_id' => 'required',
                'status_parameter' => 'required',
                'status_value' => 'required',
                'operator_ref_parameter' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $id = $request->id;
            $status_id = $request->status_id;
            $status_parameter = $request->status_parameter;
            $status_value = $request->status_value;
            $operator_ref_parameter = $request->operator_ref_parameter;
            $under_value = $request->under_value;
            Responsesetting::where('id', $id)->update([
                'status_id' => $status_id,
                'status_parameter' => $status_parameter,
                'status_value' => $status_value,
                'operator_ref_parameter' => $operator_ref_parameter,
                'under_value' => $under_value,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function delete_api_responses(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['add_api_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            Responsesetting::where('id', $id)->delete();
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }


    function denomination_wise_api()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['denomination_wise_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $data = array('page_title' => 'Denomination Wise Api');
            $service_id = Service::where('status_id', 1)->get(['id']);
            $providers = Provider::whereIn('service_id', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 15])->whereIn('service_id', $service_id)->get();
            $apis = Api::where('company_id', Auth::User()->company_id)->get();
            $denominations = Denomination::get();
            if ($this->backend_template_id == 1) {
                return view('admin.api-master.denomination_wise_api', compact('providers', 'apis', 'denominations'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.api-master.denomination_wise_api', compact('providers', 'apis', 'denominations'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.api-master.denomination_wise_api', compact('providers', 'apis', 'denominations'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.api-master.denomination_wise_api', compact('providers', 'apis', 'denominations'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function save_denomination_wise_api(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['denomination_wise_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'provider_id' => 'required||exists:providers,id',
                'api_id' => 'required||exists:apis,id',
                'amount' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $provider_id = $request->provider_id;
            $api_id = $request->api_id;
            $amount = $request->amount;

            $denominations = Denomination::where('provider_id', $provider_id)->where('amount', $amount)->first();
            if ($denominations) {
                return Response()->json(['status' => 'failure', 'message' => 'this denomination already exists']);
            } else {
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                Denomination::insertGetId([
                    'user_id' => Auth::id(),
                    'provider_id' => $provider_id,
                    'api_id' => $api_id,
                    'amount' => $amount,
                    'created_at' => $ctime,
                    'status_id' => 1,
                ]);
                return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function view_denomination_wise_api(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['denomination_wise_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }

        if (Auth::User()->role_id <= 2) {
            $denominations = Denomination::find($request->id);
            if ($denominations) {
                $details = array(
                    'id' => $denominations->id,
                    'provider_id' => $denominations->provider_id,
                    'api_id' => $denominations->api_id,
                    'amount' => $denominations->amount,
                    'status_id' => $denominations->status_id,
                );
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'details' => $details]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_denomination_wise_api(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['denomination_wise_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'provider_id' => 'required||exists:providers,id',
                'api_id' => 'required||exists:apis,id',
                'amount' => 'required',
                'id' => 'required',
                'status_id' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $provider_id = $request->provider_id;
            $api_id = $request->api_id;
            $amount = $request->amount;
            $id = $request->id;
            $status_id = $request->status_id;
            $denominations = Denomination::where('provider_id', $provider_id)->where('amount', $amount)->whereNotIn('id', [$id])->first();
            if ($denominations) {
                return Response()->json(['status' => 'failure', 'message' => 'this denomination already exists']);
            } else {
                Denomination::where('id', $id)->update([
                    'provider_id' => $provider_id,
                    'api_id' => $api_id,
                    'amount' => $amount,
                    'status_id' => $status_id,
                ]);
                return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function delete_denomination_wise_api(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['denomination_wise_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            Denomination::where('id', $id)->delete();
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function webhooks_logs($api_id)
    {
        if (Auth::User()->role_id == 1) {
            $apis = Api::find($api_id);
            if ($apis) {
                $data = array(
                    'page_title' => $apis->api_name . ' Logs',
                );
                $apiresponses = Apiresponse::where('api_type', $api_id)->where('response_type', 'call_back')->orderBy('id', 'DESC')->paginate(100);
                if ($this->backend_template_id == 1) {
                    return view('admin.api-master.webhooks_logs', compact('apiresponses'))->with($data);
                } elseif ($this->backend_template_id == 2) {
                    return view('themes2.admin.api-master.webhooks_logs', compact('apiresponses'))->with($data);
                } elseif ($this->backend_template_id == 3) {
                    return view('themes3.admin.api-master.webhooks_logs', compact('apiresponses'))->with($data);
                } elseif ($this->backend_template_id == 4) {
                    return view('themes4.admin.api-master.webhooks_logs', compact('apiresponses'))->with($data);
                } else {
                    return redirect()->back();
                }
            } else {
                return redirect()->back();
            }

        } else {
            return redirect()->back();
        }
    }

    // check balance api
    function view_check_balance_api(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'api_id' => 'required|exists:apis,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $api_id = $request->api_id;
            $apicheckbalances = Apicheckbalance::where('api_id', $api_id)->first();
            if (empty($apicheckbalances)) {
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                Apicheckbalance::insert([
                    'user_id' => Auth::id(),
                    'api_id' => $api_id,
                    'method' => 1,
                    'response_type' => 1,
                    'created_at' => $ctime,
                    'status_id' => 1,
                ]);
            }
            $apicheckbalances = Apicheckbalance::where('api_id', $api_id)->first();
            if ($apicheckbalances) {
                $details = array(
                    'id' => $apicheckbalances->id,
                    'base_url' => $apicheckbalances->base_url,
                    'method' => $apicheckbalances->method,
                    'response_type' => $apicheckbalances->response_type,
                    'status_type' => $apicheckbalances->status_type,
                    'status_parameter' => $apicheckbalances->status_parameter,
                    'status_value' => $apicheckbalances->status_value,
                    'balance_parameter' => $apicheckbalances->balance_parameter,
                    'status_id' => $apicheckbalances->status_id,
                    'under_value' => $apicheckbalances->under_value,
                );
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'details' => $details]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_check_balance_api(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            if ($request->status_type == 1) {
                $rules = array(
                    'id' => 'required||exists:apicheckbalances,id',
                    'base_url' => 'required',
                    'method' => 'required',
                    'response_type' => 'required',
                    'status_parameter' => 'required',
                    'status_value' => 'required',
                    'balance_parameter' => 'required',
                    'status_id' => 'required',
                );
            } else {
                $rules = array(
                    'id' => 'required||exists:apicheckbalances,id',
                    'base_url' => 'required',
                    'method' => 'required',
                    'response_type' => 'required',
                    'balance_parameter' => 'required',
                    'status_id' => 'required',
                );
            }

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $id = $request->id;
            $base_url = str_replace(' ', '', $request->base_url);
            $method = $request->method;
            $response_type = $request->response_type;
            $status_parameter = $request->status_parameter;
            $status_value = $request->status_value;
            $balance_parameter = $request->balance_parameter;
            $status_id = $request->status_id;
            $status_type = $request->status_type;
            $under_value = $request->under_value;
            Apicheckbalance::where('id', $id)->update([
                'base_url' => $base_url,
                'method' => $method,
                'response_type' => $response_type,
                'status_type' => $status_type,
                'status_parameter' => $status_parameter,
                'status_value' => $status_value,
                'balance_parameter' => $balance_parameter,
                'under_value' => $under_value,
                'status_id' => $status_id,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Check balance api successfully updated !']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }


    function get_api_balance(Request $request)
    {
        $rules = array(
            'api_id' => 'required||exists:apis,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $api_id = $request->api_id;
        $apicheckbalances = Apicheckbalance::where('api_id', $api_id)->where('status_id', 1)->first();
        if ($apicheckbalances) {
            $library = new ApibalanceLibrary();
            $apiresponse = $library->api_balance($api_id);
            $balance = $apiresponse['balance'];
            $details = array(
                'balance' => $balance,
                'api_id' => $api_id,
            );
            return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'balance' => $details]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Api not found']);
        }
    }

    function get_api_balance_old(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $apis = Api::get();
            $response = array();
            foreach ($apis as $value) {
                $apicheckbalances = Apicheckbalance::where('api_id', $value->id)->where('status_id', 1)->first();
                if ($apicheckbalances) {
                    $library = new ApibalanceLibrary();
                    $apiresponse = $library->api_balance($value->id);
                    $balance = $apiresponse['balance'];
                } else {
                    $balance = 0;
                }
                $product = array();
                $product["id"] = $value->id;
                $product["balance"] = $balance;
                $product["response"] = '';
                array_push($response, $product);
            }
            return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'balance' => $response]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function bankTransferSwitching()
    {
        if (Auth::User()->role_id == 1) {
            $apis = Api::whereIn('id', [2, 4, 5, 6, 7, 10, 11, 12, 14, 15])->get();
            $data = array('page_title' => 'Money Transfer Switching');
            $users = User::where('status_id', 1)->whereIn('role_id', [8, 9, 10])->get();
            $moneytransferswitchings = Banktransferswitching::get();
            return view('admin.api-master.bankTransferSwitching', compact('apis', 'moneytransferswitchings', 'users'))->with($data);
        } else {
            return redirect()->back();
        }
    }

    function bankTransferSwitchingStore(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'user_id' => 'required',
                'minimum_amount' => 'required',
                'maximum_amount' => 'required',
                'status_id' => 'required',
                'api_id' => 'required|exists:apis,id',

            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $user_id = $request->user_id;
            $minimum_amount = $request->minimum_amount;
            $maximum_amount = $request->maximum_amount;
            $status_id = $request->status_id;
            $api_id = $request->api_id;
            $moneytransferswitchings = Banktransferswitching::where('user_id', $user_id)->where('minimum_amount', '<=', $minimum_amount)->where('maximum_amount', '>=', $maximum_amount)->first();
            if ($moneytransferswitchings) {
                return Response()->json(['status' => 'failure', 'message' => 'duplicate entry!']);
            }
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            Banktransferswitching::insert([
                'user_id' => $user_id,
                'minimum_amount' => $minimum_amount,
                'maximum_amount' => $maximum_amount,
                'api_id' => $api_id,
                'created_at' => $ctime,
                'status_id' => $status_id,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission!']);
        }
    }

    function bankTransferSwitchingDelete(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'id' => 'required|exists:banktransferswitchings,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $id = $request->id;
            Banktransferswitching::where('id', $id)->delete();
            return Response()->json(['status' => 'success', 'message' => 'Successfully deleted!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission!']);
        }
    }

}
