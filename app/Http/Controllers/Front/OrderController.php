<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\EcommerceOrder;
use App\EcommerceProductorder;


class OrderController extends Controller
{
    function my_order (Request $request){
        $data = array(
            'page_title' => 'My Order',
            'meta_title' => '',
            'meta_keywords' => '',
            'meta_description' => '',
        );
        $ecommerce_orders = EcommerceOrder::where('user_id', Auth::id())->orderBy('id', 'DESC')->paginate(10);
        return View('front.ecommerce.my_order', compact('ecommerce_orders'))->with($data);
    }

    function order_details ($id){
        $user_id = Auth::id();
        $ecommerce_orders = EcommerceOrder::where('id', $id)->where('user_id', $user_id)->first();
        if ($ecommerce_orders){
            $data = array(
                'page_title' => 'Order Details',
                'meta_title' => '',
                'meta_keywords' => '',
                'meta_description' => '',
                'name' => $ecommerce_orders->name,
                'address' => $ecommerce_orders->address,
                'city' => $ecommerce_orders->city,
                'state_name' => $ecommerce_orders->state->name,
                'district_name' => $ecommerce_orders->district->district_name,
                'pin_code' => $ecommerce_orders->pin_code,
                'mobile_number' => $ecommerce_orders->mobile_number,
                'email' => $ecommerce_orders->email,
            );
            $ecommerce_productorders = EcommerceProductorder::where('ecommerceorder_id', $ecommerce_orders->id)->get();
            return View('front.ecommerce.order_details',compact('ecommerce_productorders'))->with($data);
        }else{
            return redirect()->back();
        }
    }
}
