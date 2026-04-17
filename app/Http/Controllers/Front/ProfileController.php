<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\State;
use Str;
use \Crypt;
use App\District;
use DB;
use App\User;
use App\Member;
use App\Deliveryaddress;


class ProfileController extends Controller {



    function my_account (){
        $data = array(
            'page_title' => 'My Account',
            'meta_title' => '',
            'meta_keywords' => '',
            'meta_description' => '',
        );
        $states = State::where('status_id', 1)->get();
        $districts = District::where('status_id', 1)->get();
        return View('front.ecommerce.my_account', compact('states','districts'))->with($data);
    }

    function update_profile (Request $request){
        $rules = array(
            'first_name' => 'required',
            'last_name' => 'required',
            'state_id' => 'required|exists:states,id',
            'district_id' => 'required|exists:districts,id',
            'city' => 'required',
            'pin_code' => 'required|digits:6|integer',
            'address' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        DB::beginTransaction();
        try{
            $first_name = $request->first_name;
            $last_name = $request->last_name;
            $state_id = $request->state_id;
            $district_id = $request->district_id;
            $city = $request->city;
            $pin_code = $request->pin_code;
            $address = $request->address;
            $user_id = Auth::id();

            User::where('id', $user_id)->update([
                'name' => $first_name,
                'last_name' => $last_name,
            ]);
            Member::where('user_id', $user_id)->update([
                'permanent_address' => $address,
                'permanent_city' => $city,
                'permanent_state' => $state_id,
                'permanent_district' => $district_id,
                'permanent_pin_code' => $pin_code,
                'present_address' => $address,
                'present_city' => $city,
                'present_state' => $state_id,
                'present_district' => $district_id,
                'present_pin_code' => $pin_code,
                'office_address' => $address,
            ]);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Profile successfully updated !']);
        }catch (\Exception $ex) {
            DB::rollback();
            // throw $ex;
            return response()->json(['status' => 'failure', 'message' => 'something went wrong']);
        }
    }

    function my_addresses (){
        $data = array(
            'page_title' => 'My Addresses',
            'meta_title' => '',
            'meta_keywords' => '',
            'meta_description' => '',
        );
        $deliveryaddresses = Deliveryaddress::where('user_id', Auth::id())->get();
        $states = State::where('status_id', 1)->orderBy('name', 'ASC')->get();
        $districts = District::where('status_id', 1)->orderBy('district_name', 'ASC')->get();
        return View('front.ecommerce.my_addresses', compact('deliveryaddresses','states','districts'))->with($data);
    }
}

