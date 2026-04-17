<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\Masterbank;
use App\Models\Role;
use App\Models\Status;
use App\Models\Service;
use App\Models\Paymentmethod;
use App\Models\Payoutbeneficiary;
use App\Models\Contactenquiry;
use App\Models\Agentonboarding;
use App\Models\Userbroadcast;
use App\Library\MemberLibrary;
use \Crypt;
use App\Models\User;
use App\Models\State;
use App\Models\District;
use App\Models\Cashfreegateway;
use App\Models\Company;
use App\Models\Sitesetting;
use App\Models\Gatewayslab;
use App\Models\Gatewaycharge;
use App\Models\Wallet;
use App\Library\BasicLibrary;
use App\Library\PermissionLibrary;
use Helpers;

class MasterController extends Controller
{

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $companies = Company::find($this->company_id);
        $this->cdnLink = (empty($companies)) ? '' : $companies->cdn_link;

        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->brand_name = (empty($sitesettings)) ? '' :  $sitesettings->brand_name;
        $this->backend_template_id = (empty($sitesettings)) ? 1 :  $sitesettings->backend_template_id;
    }

    function bank_master()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['bank_master_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }

        if (Auth::User()->role_id <= 2) {
            $bank = Masterbank::get();
            $data = array('page_title' => 'Bank Master');
            if ($this->backend_template_id == 1) {
                return view('admin.master.bank_master', compact('bank'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.master.bank_master', compact('bank'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.master.bank_master', compact('bank'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.master.bank_master', compact('bank'))->with($data);
            } else {
                return redirect()->back();
            }

        } else {
            return Redirect::back();
        }
    }

    function view_bank_master(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['bank_master_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }

        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $banks = Masterbank::where('id', $id)->first();
            if ($banks) {
                $details = array(
                    'id' => $banks->id,
                    'bank_name' => $banks->bank_name,
                    'ifsc' => $banks->ifsc,
                    'bank_id' => $banks->bank_id,
                    'status_id' => $banks->status_id
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

    function update_bank_master(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['bank_master_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }

        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'id' => 'required',
                'bank_name' => 'required',
                'ifsc' => 'required',
                'bank_id' => 'required',
                'status_id' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $id = $request->id;
            $bank_name = $request->bank_name;
            $ifsc = $request->ifsc;
            $bank_id = $request->bank_id;
            $status_id = $request->status_id;
            $banks = Masterbank::where('id', $id)->first();
            if ($banks) {
                Masterbank::where('id', $id)->update([
                    'bank_name' => $bank_name,
                    'ifsc' => $ifsc,
                    'bank_id' => $bank_id,
                    'status_id' => $status_id,
                ]);
                return Response()->json(['status' => 'success', 'message' => 'bank updated successfully']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
        }
    }

    function add_banks(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['bank_master_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }

        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'bank_name' => 'required',
                'ifsc' => 'required',
                'bank_id' => 'required|unique:masterbanks',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $bank_name = $request->bank_name;
            $ifsc = $request->ifsc;
            $bank_id = $request->bank_id;
            Masterbank::insertGetId([
                'bank_name' => $bank_name,
                'ifsc' => $ifsc,
                'bank_id' => $bank_id,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Bank successfully added']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
        }
    }

    function role_master()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['role_master_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $roles = Role::where('status_id', 1)->get();
            $data = array('page_title' => 'Role Master');
            if ($this->backend_template_id == 1) {
                return view('admin.master.role_master', compact('roles'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.master.role_master', compact('roles'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.master.role_master', compact('roles'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.master.role_master', compact('roles'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function view_role_master(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['role_master_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $roles = Role::where('id', $id)->first();
            if ($roles) {
                $details = array(
                    'id' => $roles->id,
                    'role_title' => $roles->role_title,
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

    function update_role_master(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['role_master_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }

        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'id' => 'required',
                'role_title' => 'required|unique:roles,role_title,' . $request->id, 'role_title',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $id = $request->id;
            $role_title = $request->role_title;
            Role::where('id', $id)->update(['role_title' => $role_title]);
            return Response()->json(['status' => 'success', 'message' => 'Role title updated successfully']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
        }
    }

    function status_master(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['status_master_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $status = Status::get();
            $data = array('page_title' => 'Status Master');
            if ($this->backend_template_id == 1) {
                return view('admin.master.status_master', compact('status'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.master.status_master', compact('status'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.master.status_master', compact('status'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.master.status_master', compact('status'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function view_status_master(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['status_master_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }

        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $status = Status::where('id', $id)->first();
            if ($status) {
                $details = array(
                    'id' => $status->id,
                    'status' => $status->status,
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

    function update_status_master(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['status_master_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'id' => 'required',
                'status' => 'required|unique:statuses,status,' . $request->id, 'status',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $id = $request->id;
            $status = $request->status;
            Status::where('id', $id)->update(['status' => $status]);
            return Response()->json(['status' => 'success', 'message' => 'Status title update successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }


    function service_master()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['service_master_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $service = Service::whereIn('status_id', [1])->get();
            $wallets = Wallet::where('status_id', 1)->get();
            $data = array('page_title' => 'Service Master');
            if ($this->backend_template_id == 1) {
                return view('admin.master.service_master', compact('service','wallets'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.master.service_master', compact('service'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.master.service_master', compact('service'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.master.service_master', compact('service'))->with($data);
            } else {
                return redirect()->back();
            }

        } else {
            return Redirect::back();
        }
    }

    function view_serivce_master(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['service_master_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $service = Service::where('id', $id)->first();
            if ($service) {
                $details = array(
                    'id' => $service->id,
                    'service_name' => $service->service_name,
                    'wallet_id' => $service->wallet_id,
                    'status_id' => $service->status_id,
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

    function update_service_master(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['service_master_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'id' => 'required',
                'service_name' => 'required|unique:services,service_name,' . $request->id, 'service_name',
                'wallet_id' => 'required|exists:wallets,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $id = $request->id;
            $service_name = $request->service_name;
            $status_id = $request->status_id;
            Service::where('id', $id)->update([
                'service_name' => $service_name,
                'status_id' => $status_id,
                'wallet_id' => $request->wallet_id,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Service name updated successfully']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
        }
    }


    function upload_service_master_icon(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $this->validate($request, [
                'service_id' => 'required|exists:services,id',
                'service_logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
            ]);
            $services = Service::find($request->service_id);
            $photo = base64_encode(file_get_contents($request->service_logo));
            $url = $this->cdnLink . "api/file/v1/save-provider-icon";
            $name = Auth::User()->company->company_website . '-' . $services->service_name;
            $api_request_parameters = array(
                'image' => $photo,
                'name' => $name,
            );
            $method = 'POST';
            $header = ["Accept:application/json"];
            $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
            $res = json_decode($response);
            $status = $res->status;
            if ($status == 'success') {
                $image_url = $res->image_url;
                Service::where('id', $request->service_id)->update(['service_image' => $image_url]);
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


    function payment_method()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['payment_method_master_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $paymentmethod = Paymentmethod::get();
            $data = array('page_title' => 'Payment Method');
            if ($this->backend_template_id == 1) {
                return view('admin.master.payment_method', compact('paymentmethod'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.master.payment_method', compact('paymentmethod'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.master.payment_method', compact('paymentmethod'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.master.payment_method', compact('paymentmethod'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function view_payment_method(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['payment_method_master_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $method = Paymentmethod::where('id', $id)->first();
            if ($method) {

                $details = array(
                    'id' => $method->id,
                    'payment_type' => $method->payment_type,
                    'status_id' => $method->status_id,
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

    function update_payment_method(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['payment_method_master_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'id' => 'required',
                'payment_type' => 'required|unique:paymentmethods,payment_type,' . $request->id, 'payment_type',
                'status_id' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $id = $request->id;
            $payment_type = $request->payment_type;
            $status_id = $request->status_id;
            Paymentmethod::where('id', $id)->update(['payment_type' => $payment_type, 'status_id' => $status_id]);
            return Response()->json(['status' => 'success', 'message' => 'Payment Method Successfully Updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function add_payment_method(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['payment_method_master_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'payment_type' => 'required|unique:paymentmethods',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $payment_type = $request->payment_type;
            Paymentmethod::insertGetId([
                'payment_type' => $payment_type,
                'created_at' => $ctime,
                'status_id' => 1,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Payment method successfully added!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function payout_beneficiary_master(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['payout_beneficiary_master_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2 && Auth::User()->company->payout == 1 && Auth::User()->profile->payout == 1) {
            if ($request->status_id) {
                $status_id = $request->status_id;
            } else {
                $status_id = 3;
            }
            $data = array(
                'page_title' => 'Payout Beneficiary Master',
                'status_id' => $status_id
            );
            $payoutbeneficiary = Payoutbeneficiary::where('status_id', $status_id)->get();
            if ($this->backend_template_id == 1) {
                return view('admin.master.payout_beneficiary_master', compact('payoutbeneficiary'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.master.payout_beneficiary_master', compact('payoutbeneficiary'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.master.payout_beneficiary_master', compact('payoutbeneficiary'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.master.payout_beneficiary_master', compact('payoutbeneficiary'))->with($data);
            } else {
                return redirect()->back();
            }

        } else {
            return Redirect::back();
        }
    }

    function update_payout_beneficiary(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['payout_beneficiary_master_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $status_id = $request->status_id;
            Payoutbeneficiary::where('id', $id)->update(['status_id' => $status_id]);
            return Response()->json(['status' => 'success', 'message' => 'Status update successfully']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function contact_enquiry(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['contact_enquiry_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $contactenquiries = Contactenquiry::orderBy('id', 'DESC')->get();
            $data = array(
                'page_title' => 'Contact Enquiry',
            );
            if ($this->backend_template_id == 1) {
                return view('admin.master.contact_enquiry', compact('contactenquiries'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.master.contact_enquiry', compact('contactenquiries'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.master.contact_enquiry', compact('contactenquiries'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.master.contact_enquiry', compact('contactenquiries'))->with($data);
            } else {
                return redirect()->back();
            }

        } else {

        }
    }

    function delete_contact_enquiry(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['contact_enquiry_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permissions']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            Contactenquiry::where('id', $id)->delete();
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permissions']);
        }
    }

    function agent_onboarding_list(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['agent_onboarding_list_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->company->aeps == 1 && Auth::User()->role_id <= 2) {
            $data = array(
                'page_title' => 'Agent Onboarding List',
                'urls' => url('admin/agent-onboarding-list-api')
            );
            $users = User::whereIn('role_id', [8, 9])->where('status_id', 1)->get();
            $states = State::where('status_id', 1)->get();
            $districts = District::where('status_id', 1)->get();
            if ($this->backend_template_id == 1) {
                return view('admin.master.agent_onboarding_list', compact('users', 'states', 'districts'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.master.agent_onboarding_list', compact('users', 'states', 'districts'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.master.agent_onboarding_list', compact('users', 'states', 'districts'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.master.agent_onboarding_list', compact('users', 'states', 'districts'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }

    function agent_onboarding_list_api(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['agent_onboarding_list_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permissions']);
            }
        }
        if (Auth::User()->company->aeps == 1 && Auth::User()->role_id <= 2) {
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


            $role_id = Auth::User()->role_id;
            $company_id = Auth::User()->company_id;
            $user_id = Auth::id();
            $library = new MemberLibrary();
            $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

            $totalRecords = Agentonboarding::select('count(*) as allcount')
                ->whereIn('user_id', $my_down_member)
                ->count();

            $totalRecordswithFilter = Agentonboarding::select('count(*) as allcount')
                ->whereIn('user_id', $my_down_member)
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
                ->whereIn('user_id', $my_down_member)
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
                $statement_url = url('admin/user-ledger-report') . '/' . Crypt::encrypt($value->user_id);
                $data_arr[] = array(
                    "id" => $value->id,
                    "created_at" => "$value->created_at",
                    "user" => '<a href="' . $statement_url . '">' . $value->user->name . ' ' . $value->user->last_name . '</a>',
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
                    "view" => '<button class="btn btn-danger btn-sm" onclick="view_details(' . $value->id . ')">Update</button>',
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

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permissions']);
        }
    }

    function agent_onboarding_user_details(Request $request)
    {
        $rules = array(
            'user_id' => 'required|exists:users,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }

        $user_id = $request->user_id;
        $userDetails = User::find($user_id);
        if ($userDetails) {
            $details = array(
                'first_name' => $userDetails->name,
                'last_name' => $userDetails->last_name,
                'mobile_number' => $userDetails->mobile,
                'email' => $userDetails->email,
                'aadhar_number' => '',
                'pan_number' => $userDetails->member->pan_number,
                'company' => $userDetails->member->shop_name,
                'pin_code' => $userDetails->member->permanent_pin_code,
                'address' => $userDetails->member->office_address,
                'bank_account_number' => '',
                'ifsc' => '',
                'state_id' => $userDetails->member->permanent_state,
                'district_id' => $userDetails->member->permanent_district,
                'city' => $userDetails->member->permanent_city,
            );
            return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'details' => $details]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'User not found']);
        }
    }

    function save_agent_onboarding(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['agent_onboarding_list_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permissions']);
            }
        }
        if (Auth::User()->company->aeps == 1 && Auth::User()->role_id <= 2) {
            $rules = array(
                'first_name' => 'required',
                'last_name' => 'required',
                'mobile_number' => 'required|digits:10|unique:agentonboardings',
                'email' => 'required|email|unique:agentonboardings',
                'aadhar_number' => 'required|digits:12|unique:agentonboardings',
                'pan_number' => 'required|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/|unique:agentonboardings',
                'company' => 'required',
                'pin_code' => 'required|digits:6|integer',
                'address' => 'required',
                'bank_account_number' => 'required',
                'ifsc' => 'required',
                'state_id' => 'required',
                'district_id' => 'required',
                'city' => 'required',
                'user_id' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $first_name = $request->first_name;
            $last_name = $request->last_name;
            $mobile_number = $request->mobile_number;
            $email = $request->email;
            $aadhar_number = $request->aadhar_number;
            $pan_number = $request->pan_number;
            $company = $request->company;
            $pin_code = $request->pin_code;
            $address = $request->address;
            $bank_account_number = $request->bank_account_number;
            $ifsc = $request->ifsc;
            $state_id = $request->state_id;
            $district_id = $request->district_id;
            $city = $request->city;
            $user_id = $request->user_id;
            $company_id = Auth::User()->company_id;
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $insert_id = Agentonboarding::insertGetId([
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
                'state_id' => $state_id,
                'district_id' => $district_id,
                'city' => $city,
                'created_at' => $ctime,
                'user_id' => $user_id,
                'company_id' => $company_id,
                'status_id' => 1,
            ]);
            $onboarding = new BasicLibrary();
            return $onboarding->agent_onboarding($first_name, $last_name, $mobile_number, $email, $aadhar_number, $pan_number, $company, $pin_code, $address, $bank_account_number, $ifsc, $state_id, $district_id, $city, $user_id, $company_id, $insert_id);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
        }
    }

    function view_agent_onboarding(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['agent_onboarding_list_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permissions']);
            }
        }
        if (Auth::User()->company->aeps == 1 && Auth::User()->role_id <= 2) {
            $rules = array(
                'id' => 'required|exists:agentonboardings,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $id = $request->id;
            $agentonboardings = Agentonboarding::find($id);
            if ($agentonboardings) {
                $details = array(
                    'id' => $agentonboardings->id,
                    'first_name' => $agentonboardings->first_name,
                    'last_name' => $agentonboardings->last_name,
                    'mobile_number' => $agentonboardings->mobile_number,
                    'email' => $agentonboardings->email,
                    'aadhar_number' => $agentonboardings->aadhar_number,
                    'pan_number' => $agentonboardings->pan_number,
                    'company' => $agentonboardings->company,
                    'pin_code' => $agentonboardings->pin_code,
                    'address' => $agentonboardings->address,
                    'bank_account_number' => $agentonboardings->bank_account_number,
                    'ifsc' => $agentonboardings->ifsc,
                    'state_id' => $agentonboardings->state_id,
                    'district_id' => $agentonboardings->district_id,
                    'city' => $agentonboardings->city,
                    'user_id' => $agentonboardings->user_id,
                    'company_id' => $agentonboardings->company_id,
                    'status_id' => $agentonboardings->status_id,
                );
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'details' => $details]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_agent_onboarding(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['agent_onboarding_list_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permissions']);
            }
        }
        if (Auth::User()->company->aeps == 1 && Auth::User()->role_id <= 2) {
            $rules = array(
                'id' => 'required|exists:agentonboardings,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $id = $request->id;
            $rules = array(
                'first_name' => 'required',
                'last_name' => 'required',
                'mobile_number' => 'required|digits:10|unique:agentonboardings,mobile_number,' . $id, 'mobile_number',
                'email' => 'required|email|unique:agentonboardings,email,' . $id, 'email',
                'aadhar_number' => 'required|digits:12|unique:agentonboardings,aadhar_number,' . $id, 'aadhar_number',
                'pan_number' => 'required|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/|unique:agentonboardings,pan_number,' . $id, 'pan_number',
                'company' => 'required',
                'pin_code' => 'required|digits:6|integer',
                'address' => 'required',
                'bank_account_number' => 'required',
                'ifsc' => 'required',
                'state_id' => 'required',
                'district_id' => 'required',
                'city' => 'required',
                'user_id' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $first_name = $request->first_name;
            $last_name = $request->last_name;
            $mobile_number = $request->mobile_number;
            $email = $request->email;
            $aadhar_number = $request->aadhar_number;
            $pan_number = $request->pan_number;
            $company = $request->company;
            $pin_code = $request->pin_code;
            $address = $request->address;
            $bank_account_number = $request->bank_account_number;
            $ifsc = $request->ifsc;
            $state_id = $request->state_id;
            $district_id = $request->district_id;
            $city = $request->city;
            $user_id = $request->user_id;
            Agentonboarding::where('id', $id)->update([
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
                'state_id' => $state_id,
                'district_id' => $district_id,
                'city' => $city,
                'user_id' => $user_id,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function cashfree_gateway_master(Request $request)
    {
        if (Auth::User()->role_id <= 2 && Auth::User()->company->cashfree == 1) {
            $data = array('page_title' => 'Cashfree Gateway Master');
            $cashfreegateways = Cashfreegateway::get();
            return view('admin.master.cashfree_gateway_master', compact('cashfreegateways'))->with($data);
        } else {
            return redirect()->back();
        }
    }

    function view_cashfree_gateway_master(Request $request)
    {
        if (Auth::User()->role_id <= 2 && Auth::User()->company->cashfree == 1) {
            $rules = array(
                'id' => 'required|exists:cashfreegateways,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $id = $request->id;
            $cashfreegateways = Cashfreegateway::find($id);
            if ($cashfreegateways) {
                $details = array(
                    'id' => $cashfreegateways->id,
                    'app_id' => $cashfreegateways->app_id,
                    'secret_key' => $cashfreegateways->secret_key,
                    'base_url' => $cashfreegateways->base_url,
                    'min_amount' => $cashfreegateways->min_amount,
                    'max_amount' => $cashfreegateways->max_amount,
                    'status_id' => $cashfreegateways->status_id,
                );
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'details' => $details]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_cashfree_gateway_master(Request $request)
    {
        if (Auth::User()->role_id <= 2 && Auth::User()->company->cashfree == 1) {
            $rules = array(
                'id' => 'required|exists:cashfreegateways,id',
                'app_id' => 'required',
                'secret_key' => 'required',
                'base_url' => 'required',
                'min_amount' => 'required',
                'max_amount' => 'required',
                'status_id' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $id = $request->id;
            $app_id = $request->app_id;
            $secret_key = $request->secret_key;
            $base_url = $request->base_url;
            $status_id = $request->status_id;
            Cashfreegateway::where('id', $id)->update([
                'app_id' => $app_id,
                'secret_key' => $secret_key,
                'base_url' => $base_url,
                'status_id' => $status_id,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function broadcast()
    {
        if (Auth::User()->role_id <= 2) {
            $userbroadcasts = Userbroadcast::where('company_id', Auth::User()->company_id)->first();
            $data = array(
                'page_title' => 'Broadcast',
                'heading' => (empty($userbroadcasts) ? '' : $userbroadcasts->heading),
                'message' => (empty($userbroadcasts) ? '' : $userbroadcasts->message),
                'status_id' => (empty($userbroadcasts) ? 2 : $userbroadcasts->status_id),
                'img_status' => (empty($userbroadcasts) ? 2 : $userbroadcasts->img_status),
            );
            if ($this->backend_template_id == 1) {
                return view('admin.master.broadcast')->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.master.broadcast')->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.master.broadcast')->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.master.broadcast')->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }

    function save_broadcast(Request $request)
    {
        if (Auth::User()->role_id <= 2) {
            $this->validate($request, [
                'heading' => 'required',
                'message' => 'required',
                'status_id' => 'required',
                'img_status' => 'required',
            ]);
            $heading = $request->heading;
            $message = $request->message;
            $status_id = $request->status_id;
            $img_status = $request->img_status;
            $company_id = Auth::User()->company_id;
            $user_id = Auth::id();
            $userbroadcasts = Userbroadcast::where('company_id', $company_id)->first();
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');

            if (!empty($request->photo)) {
                $company_name = Auth::User()->company->company_name;
                $photo = $request->photo;
                $photo = base64_encode(file_get_contents($photo));
                $url = $this->cdnLink . "api/file/v1/company-logo";
                $api_request_parameters = array(
                    'image' => $photo,
                    'name' => $company_name,
                    'type' => 1,
                );
                $method = 'POST';
                $header = ["Accept:application/json"];
                $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
                $res = json_decode($response);
                $status = $res->status;
                if ($status == 'success') {
                    $image_url = $res->image_url;
                } else {
                    \Session::flash('failure', $res->message);
                    return redirect()->back();
                }
            } else {
                $image_url = (empty($userbroadcasts) ? '' : $userbroadcasts->image_url);
            }
            if (empty($userbroadcasts)) {
                Userbroadcast::insert([
                    'user_id' => $user_id,
                    'heading' => $heading,
                    'message' => $message,
                    'image_url' => $image_url,
                    'img_status' => $img_status,
                    'created_at' => $ctime,
                    'company_id' => $company_id,
                    'status_id' => $status_id,
                ]);
            } else {
                Userbroadcast::where('company_id', $company_id)->update([
                    'heading' => $heading,
                    'message' => $message,
                    'image_url' => $image_url,
                    'img_status' => $img_status,
                    'status_id' => $status_id,
                ]);
            }
            \Session::flash('success', 'Successful..!');
            return redirect()->back();
        } else {
            return redirect()->back();
        }
    }

    function gateway_charges()
    {
        if (Auth::User()->role_id == 1) {
            $data = array(
                'page_title' => 'Set Gateway Charges',
            );
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $gatewayslabs = Gatewayslab::where('status_id', 1)->get();
            foreach ($gatewayslabs as $value) {
                $gatewaycharges = Gatewaycharge::where('gatewayslab_id', $value->id)->first();
                if (empty($gatewaycharges)) {
                    Gatewaycharge::insert([
                        'gatewayslab_id' => $value->id,
                        'created_at' => $ctime,
                        'status_id' => 1,
                    ]);
                }
            }
            $gatewaycharges = Gatewaycharge::where('status_id', 1)->get();
            if ($this->backend_template_id == 1) {
                return view('admin.master.gateway_charges', compact('gatewaycharges'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.master.gateway_charges', compact('gatewaycharges'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.master.gateway_charges', compact('gatewaycharges'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.master.gateway_charges', compact('gatewaycharges'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }

    function view_gateway_charges(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'id' => 'required|exists:gatewaycharges,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $id = $request->id;
            $gatewaycharges = Gatewaycharge::find($id);
            if ($gatewaycharges) {
                $details = array(
                    'id' => $gatewaycharges->id,
                    'gateway_method' => $gatewaycharges->gatewayslab->slab_name,
                    'method_code' => $gatewaycharges->method_code,
                    'commission' => $gatewaycharges->commission,
                    'type' => $gatewaycharges->type,
                );
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'details' => $details]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found!']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission!']);
        }
    }

    function update_gateway_charges_details(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'id' => 'required|exists:gatewaycharges,id',
                'method_code' => 'required',
                'commission' => 'required',
                'type' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $id = $request->id;
            $method_code = $request->method_code;
            $commission = $request->commission;
            $type = $request->type;
            Gatewaycharge::where('id', $id)->update([
                'method_code' => $method_code,
                'commission' => $commission,
                'type' => $type,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission!']);
        }
    }
}
