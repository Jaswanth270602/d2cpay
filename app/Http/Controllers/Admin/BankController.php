<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\Bankdetail;
use App\Models\Sitesetting;
use App\Models\User;
use App\Library\PermissionLibrary;
use Helpers;

class BankController extends Controller
{

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        if ($sitesettings) {
            $this->brand_name = $sitesettings->brand_name;
            $this->backend_template_id = $sitesettings->backend_template_id;
        } else {
            $this->brand_name = "";
            $this->backend_template_id = 1;
        }
    }

    function bank_settings (){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['bank_settings_permission'];
            if (!$myPermission == 1){
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2){
            $data = array('page_title' => 'Bank Setting');
            $users = User::whereIn('role_id', [8, 9,10])->get();
            $banks = Bankdetail::where('company_id', Auth::User()->company_id)->get();
            if ($this->backend_template_id == 1) {
                return view('admin.bank_settings',compact('banks','users'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.bank_settings',compact('banks'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.bank_settings',compact('banks'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.bank_settings',compact('banks'))->with($data);
            } else {
                return redirect()->back();
            }
        }else{
            return Redirect::back();
        }
    }

    function view_bank_details (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['bank_settings_permission'];
            if (!$myPermission == 1){
                return Response()->json(['status' => 'failure', 'message' => 'Sorry Not Permission']);
            }
        }
        if (Auth::User()->role_id <= 2){
            $id = $request->id;
            $banks = Bankdetail::where('id', $id)->where('company_id', Auth::User()->company_id)->first();
            if ($banks){
                $details = array(
                    'bank_id' => $banks->id,
                    'child_id' => $banks->child_id,
                    'bank_name' => $banks->bank_name,
                    'bank_account_number' => $banks->bank_account_number,
                    'bank_ifsc' => $banks->bank_ifsc,
                    'bank_account_name' => $banks->bank_account_name,
                    'bank_branch' => $banks->bank_branch,
                    'status_id' => $banks->status_id,
                );
                return Response()->json([
                    'status' => 'success',
                    'details' => $details,
                    ]);
            }else{
                return Response()->json(['status' => 'failure', 'message' => 'Bank not found']);
            }

        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry Not Permission']);
        }
    }

    function update_bank (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['bank_settings_permission'];
            if (!$myPermission == 1){
                return Response()->json(['status' => 'failure', 'message' => 'Sorry Not Permission']);
            }
        }
        if (Auth::User()->role_id <= 2){
            $rules = array(
                'bank_id' => 'required',
                'bank_name' => 'required',
                'bank_account_number' => 'required',
                'bank_ifsc' => 'required',
                'bank_account_name' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }

            $bank_id = $request->bank_id;
            $bank_name = $request->bank_name;
            $bank_account_number = $request->bank_account_number;
            $bank_ifsc = $request->bank_ifsc;
            $bank_account_name = $request->bank_account_name;
            $bank_branch = $request->bank_branch;
            $status_id = $request->status_id;

            $banks = Bankdetail::where('id', $bank_id)->where('company_id', Auth::User()->company_id)->first();
            if ($banks){
                Bankdetail::where('id', $bank_id)->update([
                    'bank_name' => $bank_name,
                    'bank_account_number' => $bank_account_number,
                    'bank_ifsc' => $bank_ifsc,
                    'bank_account_name' => $bank_account_name,
                    'bank_branch' => $bank_branch,
                    'status_id' => $status_id,
                    'child_id' => $request->child_id,
                ]);
                return Response()->json(['status' => 'success', 'message' => 'bank details successfully updated']);
            }else{
                return Response()->json(['status' => 'failure', 'message' => 'Bank not found']);
            }
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry Not Permission']);
        }
    }

    function add_bank (Request $request){
        // get staff permission
        if (Auth::User()->role_id == 2){
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['bank_settings_permission'];
            if (!$myPermission == 1){
                return Response()->json(['status' => 'failure', 'message' => 'Sorry Not Permission']);
            }
        }
        if (Auth::User()->role_id <= 2){
            $rules = array(
                'bank_name' => 'required',
                'bank_account_number' => 'required',
                'bank_ifsc' => 'required',
                'bank_account_name' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $bank_name = $request->bank_name;
            $bank_account_number = $request->bank_account_number;
            $bank_ifsc = $request->bank_ifsc;
            $bank_account_name = $request->bank_account_name;
            $bank_branch = $request->bank_branch;
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            Bankdetail::insertGetId([
                'user_id' => Auth::id(),
                'bank_name' => $bank_name,
                'bank_account_number' => $bank_account_number,
                'bank_ifsc' => $bank_ifsc,
                'bank_account_name' => $bank_account_name,
                'bank_branch' => $bank_branch,
                'created_at' => $ctime,
                'company_id' => Auth::User()->company_id,
                'status_id' => 1,
                'child_id' => $request->child_id,
            ]);

            return Response()->json(['status' => 'success', 'message' => 'Bank details successfully added']);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry Not Permission']);
        }
    }
}
