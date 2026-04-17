<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use App\Company;
use \Crypt;
use Validator;
use App\Library\MemberLibrary;

class WhitelabelController extends Controller {


    function white_label (){
        if (Auth::User()->role_id <= 3){
           // $company = Company::where('parent_id', Auth::id())->get();
            if (Auth::User()->role_id == 1){
                $company = Company::get();
            }else{
                $company = Company::where('parent_id', Auth::id())->get();
            }

            $data = array('page_title' => 'White Label Setting');
            $role_id = Auth::User()->role_id;
            $company_id = Auth::User()->company_id;
            $user_id = Auth::id();
            $library = new MemberLibrary();
            $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
            $users = User::whereIn('id', $my_down_member)->whereIn('role_id', [3,4])->get();
            return view('admin.white_label',compact('company', 'users'))->with($data);
        }else{
            return Redirect::back();
        }
    }

    function view_white_label_details (Request $request){
        if (Auth::User()->role_id <= 3){
        $id = $request->id;
        if (Auth::User()->role_id <= 2){
            $company = Company::where('id', $id)->first();
        }else{
            $company = Company::where('id', $id)->where('parent_id', Auth::id())->first();
        }
            $details = array(
                'company_id' => $company->id,
                'company_name' => $company->company_name,
                'company_email' => $company->company_email,
                'company_address' => $company->company_address,
                'company_address_two' => $company->company_address_two,
                'support_number' => $company->support_number,
                'whatsapp_number' => $company->whatsapp_number,
                'company_logo' => $company->company_logo,
                'company_website' => $company->company_website,
                'news' => $company->news,
                'update_one' => $company->update_one,
                'update_two' => $company->update_two,
                'update_three' => $company->update_three,
                'sender_id' => $company->sender_id,
                'recharge' => $company->recharge,
                'money' => $company->money,
                'aeps' => $company->aeps,
                'payout' => $company->payout,
                'view_plan' => $company->view_plan,
                'pancard' => $company->pancard,
                'ecommerce' => $company->ecommerce,
                'status_id' => $company->status_id,
                'user_id' => $company->user_id,

            );
            return Response()->json([
                'status' => 'success',
                'details' => $details,
                ]);

        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function create_white_label (Request $request){
        if (Auth::User()->role_id <= 3){
            $rules = array(
                'company_name' => 'required',
                'company_email' => 'required|email|unique:companies',
                'company_address' => 'required',
                'support_number' => 'required|unique:companies|digits:10',
                'whatsapp_number' => 'required|unique:companies|digits:10',
                'company_website' => 'required|unique:companies',
                'user_id' => 'required|unique:companies',
                'sender_id' => 'required|max:6|min:6',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $company_name = $request->company_name;
            $company_email = $request->company_email;
            $company_address = $request->company_address;
            $support_number = $request->support_number;
            $whatsapp_number = $request->whatsapp_number;
            $company_website = $request->company_website;
            $recharge = $request->recharge;
            $money = $request->money;
            $aeps = $request->aeps;
            $payout = $request->payout;
            $view_plan = $request->view_plan;
            $pancard = $request->pancard;
            $ecommerce = $request->ecommerce;
            $user_id = $request->user_id;
            $sender_id = $request->sender_id;
            $now = new \DateTime();
            $datetime = $now->getTimestamp();
            $ctime = $now->format('Y-m-d H:i:s');

            Company::insertGetId([
                'company_name' => $company_name,
                'company_email' => $company_email,
                'company_address' => $company_address,
                'support_number' => $support_number,
                'whatsapp_number' => $whatsapp_number,
                'company_website' => $company_website,
                'sender_id' => $sender_id,
                'user_id' => $user_id,
                'parent_id' => Auth::id(),
                'created_at' => $ctime,
                'recharge' => $recharge,
                'money' => $money,
                'aeps' => $aeps,
                'payout' => $payout,
                'view_plan' => $view_plan,
                'pancard' => $pancard,
                'ecommerce' => $ecommerce,
                'status_id' => 1,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Whiate Label Successfully Created']);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_white_label (Request $request){
        if (Auth::User()->role_id <= 3){
          $company_id = $request->company_id;

            $rules = array(
                'company_name' => 'required',
                'company_email' => 'required|email|unique:companies,company_email,'.$company_id,'company_email',
                'company_address' => 'required',
                'support_number' => 'required|digits:10|unique:companies,support_number,'.$company_id,'support_number',
                'whatsapp_number' => 'required|digits:10|unique:companies,whatsapp_number,'.$company_id,'whatsapp_number',
                'company_website' => 'required|unique:companies,company_website,'.$company_id,'company_website',
                'user_id' => 'required|digits_between:1,5|unique:companies,user_id,'.$company_id,'user_id',
                'sender_id' => 'required|max:6|min:6',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $company_name = $request->company_name;
            $company_email = $request->company_email;
            $company_address = $request->company_address;
            $support_number = $request->support_number;
            $whatsapp_number = $request->whatsapp_number;
            $company_website = $request->company_website;
            $recharge = $request->recharge;
            $money = $request->money;
            $aeps = $request->aeps;
            $payout = $request->payout;
            $view_plan = $request->view_plan;
            $pancard = $request->pancard;
            $ecommerce = $request->ecommerce;
            $user_id = $request->user_id;
            $status_id = $request->status_id;
            $sender_id = $request->sender_id;
            if (Auth::User()->role_id <= 3){
                $companydetails = Company::where('id', $company_id)->first();
            }else{
                $companydetails = Company::where('id', $company_id)->where('parent_id', Auth::id())->first();
            }
            if ($companydetails){

                Company::where('id', $company_id)->update([
                    'company_name' => $company_name,
                    'company_email' => $company_email,
                    'company_address' => $company_address,
                    'support_number' => $support_number,
                    'whatsapp_number' => $whatsapp_number,
                    'company_website' => $company_website,
                    'recharge' => $recharge,
                    'money' => $money,
                    'aeps' => $aeps,
                    'payout' => $payout,
                    'view_plan' => $view_plan,
                    'pancard' => $pancard,
                    'ecommerce' => $ecommerce,
                    'status_id' => $status_id,
                    'user_id' => $user_id,
                    'sender_id' => $sender_id,
                ]);
                return Response()->json(['status' => 'success', 'message' => 'White label details successfully updated']);
            }else{
                return Response()->json(['status' => 'failure', 'message' => 'White details not found']);
            }


        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }
}

