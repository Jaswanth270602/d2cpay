<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
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

class EcommerceController extends Controller {


    function product_details ($id){
        $products = Product::where('id', $id)->where('status_id', 1)->first();
        if ($products){

            $product_discount = ($products->product_price * $products->product_discount) / 100;
            $discount_price = $products->product_price - $product_discount;
            $commission = ($products->product_price * $products->subcategory->commission) / 100;
            $product_price = $discount_price + $commission;
            $product_show_price = $products->product_price + $commission;

            $data = array(
                'product_id' => $products->id,
                'page_title' => $products->product_name,
                'product_image' => $products->product_image,
                'product_name' => $products->product_name,
                'description' => $products->description,
                'category_name' => $products->subcategory->category_name,
                'product_price' => $product_price,
                'product_discount' => $products->product_discount,
                'product_show_price' => $product_show_price,
                'meta_title' => '',
                'meta_keywords' => '',
                'meta_description' => '',
            );
            $relatedproduct = Product::where('subcategory_id', $products->subcategory_id)
                ->where('status_id', 1)
                ->where('id','!=', $id)->limit(4)
                ->inRandomOrder()
                ->get();
            $productimages = Productimage::where('product_id', $id)->where('status_id', 1)->get();
            return View('front.ecommerce.product_details', compact('relatedproduct','productimages'))->with($data);
        }else{
            return redirect()->back();
        }
    }

    function view_cart (Request $request){
        $data = array(
            'page_title' => 'View Cart',
            'meta_title' => '',
            'meta_keywords' => '',
            'meta_description' => '',
        );
        $cart = Cart::where('user_id', Auth::id())->get();
        return View('front.ecommerce.view_cart', compact('cart'))->with($data);
    }

    function checkout (Request $request){
        $data = array(
            'page_title' => 'View Cart',
            'meta_title' => '',
            'meta_keywords' => '',
            'meta_description' => '',
        );
        $cart = Cart::where('user_id', Auth::id())->get();
        $deliveryaddresses = Deliveryaddress::where('user_id', Auth::id())->get();
        $states = State::where('status_id', 1)->orderBy('name', 'ASC')->get();
        $districts = District::where('status_id', 1)->orderBy('district_name', 'ASC')->get();
        $deliverymethods = Deliverymethod::where('status_id', 1)->get();
        return View('front.ecommerce.checkout', compact('cart','deliveryaddresses','states', 'districts','deliverymethods'))->with($data);
    }


    function my_wishlist (){
        $data = array(
            'page_title' => 'Wishlist',
            'meta_title' => '',
            'meta_keywords' => '',
            'meta_description' => '',
        );
        $product_id = Wishlist::where('user_id', Auth::id())->get(['product_id']);
        $products = Product::whereIn('id', $product_id)->paginate(9);
        return View('front.ecommerce.my_wishlist', compact('products'))->with($data);
    }
}

