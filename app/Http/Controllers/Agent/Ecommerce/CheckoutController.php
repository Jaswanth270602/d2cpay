<?php

namespace App\Http\Controllers\Agent\Ecommerce;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use Str;
use \Crypt;
use App\Productimage;
use App\Product;
use Helpers;
use DB;
use Session;
use App\Cart;
use App\Wishlist;
use App\Subcategory;
use App\Deliveryaddress;
use App\State;
use App\District;
use App\Deliverymethod;
use App\User;
use App\EcommerceOrder;
use App\EcommerceProductorder;
use App\Balance;
use App\Provider;
use App\Report;
use App\DeliveryStatus;

class CheckoutController extends Controller
{

    function checkout (Request $request){
        if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->ecommerce == 1){
            $data = array(
                'page_title' => 'Checkout',
            );
            $cart = Cart::where('user_id', Auth::id())->get();
            $states = State::where('status_id', 1)->orderBy('name', 'ASC')->get();
            $districts = District::where('status_id', 1)->orderBy('district_name', 'ASC')->get();
            $deliveryaddresses = Deliveryaddress::where('user_id', Auth::id())->get();
            $deliverymethods = Deliverymethod::where('status_id', 1)->get();
            return view('agent.ecommerce.checkout', compact('cart','states','deliveryaddresses', 'districts','deliverymethods'))->with($data);
        }else{
            return redirect()->back();
        }
    }

    function save_delivery_addresses (Request $request){
        $rules = array(
            'name' => 'required',
            'mobile_number' => 'required|digits:10',
            'email' => 'required|email',
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
        $name = $request->name;
        $mobile_number = $request->mobile_number;
        $email = $request->email;
        $state_id = $request->state_id;
        $district_id = $request->district_id;
        $city = $request->city;
        $pin_code = $request->pin_code;
        $address = $request->address;
        $user_id = Auth::id();
        $countaddress = Deliveryaddress::where('user_id', $user_id)->count();
        if ($countaddress <= 2) {
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            Deliveryaddress::insert([
                'user_id' => $user_id,
                'name' => $name,
                'address' => $address,
                'city' => $city,
                'state_id' => $state_id,
                'district_id' => $district_id,
                'pin_code' => $pin_code,
                'mobile_number' => $mobile_number,
                'email' => $email,
                'created_at' => $ctime,
                'status_id' => 1,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Delivery address successfully added !']);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'You can add up to 3 addresses']);
        }
    }

    function view_delivery_addresses (Request $request){
        $rules = array(
            'id' => 'required|exists:deliveryaddresses,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $id = $request->id;
        $user_id = Auth::id();
        $deliveryaddresses = Deliveryaddress::where('id', $id)->where('user_id', $user_id)->first();
        if ($deliveryaddresses){
            $details = array(
                'id' => $deliveryaddresses->id,
                'name' => $deliveryaddresses->name,
                'address' => $deliveryaddresses->address,
                'city' => $deliveryaddresses->city,
                'state_id' => $deliveryaddresses->state_id,
                'district_id' => $deliveryaddresses->district_id,
                'pin_code' => $deliveryaddresses->pin_code,
                'mobile_number' => $deliveryaddresses->mobile_number,
                'email' => $deliveryaddresses->email,
            );
            return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'details' => $details]);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
        }
    }

    function update_delivery_addresses (Request $request){
        $rules = array(
            'id' => 'required|exists:deliveryaddresses,id',
            'name' => 'required',
            'mobile_number' => 'required|digits:10',
            'email' => 'required|email',
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
        $id = $request->id;
        $name = $request->name;
        $mobile_number = $request->mobile_number;
        $email = $request->email;
        $state_id = $request->state_id;
        $district_id = $request->district_id;
        $city = $request->city;
        $pin_code = $request->pin_code;
        $address = $request->address;
        $deliveryaddresses = Deliveryaddress::where('id', $id)->where('user_id', Auth::id())->first();
        if ($deliveryaddresses){
            Deliveryaddress::where('id', $id)->update([
                'name' => $name,
                'address' => $address,
                'city' => $city,
                'state_id' => $state_id,
                'district_id' => $district_id,
                'pin_code' => $pin_code,
                'mobile_number' => $mobile_number,
                'email' => $email,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Delivery address successfully updated !']);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
        }
    }

    function place_order (Request $request){
        if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->ecommerce == 1){
            $this->validate($request, [
                'deliveryaddress' => 'required|exists:deliveryaddresses,id',
                'payment_method' => 'required|exists:deliverymethods,id',
            ]);
            DB::beginTransaction();
            try{
                $deliveryaddress_id = $request->deliveryaddress;
                $deliverymethod_id = $request->payment_method;
                $user_id = Auth::id();
                $provider_id = 338;
                $request_ip =  request()->ip();
                $cartItems = Cart::where('user_id', $user_id)->get();
                $userdetails = User::find($user_id);
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                $providers = Provider::where('id', $provider_id)->first();
                $description = "$providers->provider_name $userdetails->mobile";

                $total_amount = 0;
                $total_discount = 0;
                $commission = 0;
                $shipping_charge = 0;
                foreach ($cartItems as $value){
                    $quantity = $value->quantity;
                    $actutal_product_price = $value->product->product_price * $quantity;
                   // $actual_commission = $value->product->subcategory->commission;
                    $actual_commission = ($actutal_product_price * $value->product->subcategory->commission) / 100;
                    $shipping_charge += $value->product->shipping_charge;
                    // sum total value
                    $actutal_discount = ($value->product->product_price * $value->product->product_discount) / 100;
                    $total_discount += $actutal_discount * $quantity;
                    $total_amount += $actutal_product_price;
                    $commission += $actual_commission;
                    $grand_amount = $total_amount + $commission + $shipping_charge - $total_discount;
                }

                $opening_balance = $userdetails->balance->user_balance;
                $sumamount = $grand_amount + $userdetails->lock_amount + $userdetails->balance->lien_amount;
               // if payment method wallet
                if ($deliverymethod_id == 1){
                    if ($opening_balance >= $sumamount && $sumamount >= 10) {

                        Balance::where('user_id', $user_id)->decrement('user_balance', $grand_amount);
                        $balance = Balance::where('user_id', $user_id)->first();
                        $user_balance = $balance->user_balance;

                        $report_id = Report::insertGetId([
                            'number' => $userdetails->mobile,
                            'provider_id' => $provider_id,
                            'amount' => $grand_amount,
                            'api_id' => 0,
                            'status_id' => 3,
                            'created_at' => $ctime,
                            'user_id' => $user_id,
                            'profit' => 0,
                            'mode' => "WEB",
                            'ip_address' => $request_ip,
                            'description' => $description,
                            'opening_balance' => $opening_balance,
                            'total_balance' => $user_balance,
                            'wallet_type' => 1,
                        ]);

                        $deliveryaddresses = Deliveryaddress::find($deliveryaddress_id);
                        // details insert in EcommerceOrder table
                        $ecommerceorder_id = EcommerceOrder::insertGetId([
                            'user_id' => $user_id,
                            'name' => $deliveryaddresses->name,
                            'address' => $deliveryaddresses->address,
                            'city' => $deliveryaddresses->city,
                            'state_id' => $deliveryaddresses->state_id,
                            'district_id' => $deliveryaddresses->district_id,
                            'pin_code' => $deliveryaddresses->pin_code,
                            'mobile_number' => $deliveryaddresses->mobile_number,
                            'email' => $deliveryaddresses->email,
                            'total_amount' => $total_amount,
                            'total_discount' => $total_discount,
                            'shipping_charges' => $shipping_charge,
                            'total_commission' => $commission,
                            'grand_total' => $grand_amount,
                            'report_id' => $report_id,
                            'created_at' => $ctime,
                            'status_id' => 4,
                        ]);
                        // product insert in EcommerceProductorder table
                        foreach ($cartItems as $item){
                            // product price
                            $quantity = $item->quantity;
                            $product_price = $item->product->product_price * $quantity;
                            // product discount
                            $actutal_discount = ($item->product->product_price * $item->product->product_discount) / 100;
                            $product_discount = $actutal_discount * $quantity;

                            // product commission
                            $service_Charge = ($item->product->product_price * $item->product->subcategory->commission) / 100;
                            $total_commision = $service_Charge * $quantity;
                            // total amount
                            $total_amount = $product_price + $total_commision + $item->product->shipping_charge - $product_discount;
                            EcommerceProductorder::insert([
                                'user_id' => $user_id,
                                'product_id' => $item->product_id,
                                'product_name' => $item->product->product_name,
                                'subcategory_id' => $item->product->subcategory_id,
                                'brand_id' => $item->product->brand_id,
                                'product_price' => $product_price,
                                'product_discount' => $product_discount,
                                'shipping_charge' => $item->product->shipping_charge,
                                'quantity' => $quantity,
                                'commission' => $total_commision,
                                'total_amount' => $total_amount,
                                'report_id' => $report_id,
                                'ecommerceorder_id' => $ecommerceorder_id,
                                'created_at' => $ctime,
                                'status_id' => 4,
                            ]);
                        }

                         Cart::where('user_id', $user_id)->delete();
                         DB::commit();
                        \Session::flash('success_message', 'Your product has been successfully purchased, you will get the product delivered soon');
                        return redirect()->back();
                    }else{
                        \Session::flash('error_message', 'Your wallet balance is low kinldy refill your wallet');
                        return redirect()->back();
                    }

                }


            }catch (\Exception $ex) {
                DB::rollback();
                // throw $ex;
                \Session::flash('error_message', 'something went wrong');
                return redirect()->back();
            }
        }else{
            return redirect()->back();
        }
    }
}
