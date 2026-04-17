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


class ShopController extends Controller {

    function shop_page ($slug){
        if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->ecommerce == 1){
            $subcategories = Subcategory::where('slug', $slug)->where('status_id', 1)->first();
            if ($subcategories){
                $data = array(
                    'page_title' => $subcategories->category_name,
                );
                $products = Product::where('status_id', 1)->where('subcategory_id', $subcategories->id)->inRandomOrder()->paginate(20);
                return view('agent.ecommerce.welcome', compact('products'))->with($data);
            }else{
                return redirect()->back();
            }

        }else{
            return redirect()->back();
        }
    }

    function welcome (Request $request){
        if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->ecommerce == 1){
            $data = array(
                'page_title' => 'Shop',
            );
            $products = Product::where('status_id', 1)->where('home_page', 1)->inRandomOrder()->paginate(20);
            return view('agent.ecommerce.welcome', compact('products'))->with($data);
        }else{
            return redirect()->back();
        }
    }

    function product_details ($id){
        if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->ecommerce == 1){
            $products = Product::where('id', $id)->where('status_id', 1)->first();
            if ($products){

                $product_discount = ($products->product_price *  $products->product_discount) / 100;
                $discount_price = $products->product_price - $product_discount;
                $commission = ($products->product_price * $products->subcategory->commission) / 100;
                $product_price = $discount_price + $commission;

                $data = array(
                    'product_id' => $products->id,
                    'page_title' => $products->product_name,
                    'product_image' => $products->product_image,
                    'product_name' => $products->product_name,
                    'description' => $products->description,
                    'product_price' => $product_price,
                    'product_discount' => $product_discount,
                );
                $relatedproduct = Product::where('subcategory_id', $products->subcategory_id)
                    ->where('status_id', 1)
                    ->where('id','!=', $id)->limit(4)
                    ->inRandomOrder()
                    ->get();
                $productimages = Productimage::where('product_id', $id)->where('status_id', 1)->get();
                return view('agent.ecommerce.product_details', compact('products', 'productimages', 'relatedproduct'))->with($data);
            }else{
                return redirect()->back();
            }
        }else{

        }
    }

    function add_to_cart (Request $request){
        $this->validate($request, [
            'product_id' => 'required',
            'quantity' => 'required',
        ]);
        $product_id  = $request->product_id;
        $quantity = $request->quantity;

        $session_id = Session::get('session_id');
        if (empty($session_id)){
            $session_id = Session::getId();
            Session::put('session_id', $session_id);
        }
        $user_id = Auth::id();
        $checkcart = Cart::where('user_id', $user_id)->where('product_id', $product_id)->first();
        if ($checkcart){
            $quantity = $checkcart->quantity + $quantity;
            if ($quantity > 10){
                Session::flash('error_message', 'You can order more than 10 quantities in 1 order !');
                return redirect()->back();
            }else{
                Cart::where('id', $checkcart->id)->update(['quantity' => $quantity]);
            }

        }else{
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            Cart::insert([
                'session_id' => $session_id,
                'user_id' => Auth::id(),
                'product_id' => $product_id,
                'quantity' => $quantity,
                'created_at' => $ctime,
            ]);
        }
        Session::flash('success_message', 'Product has been added in cart!');
        return redirect()->back();
    }

    function view_cart (){
        if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->ecommerce == 1){
            $data = array(
                'page_title' => 'View Cart',
            );
            $session_id = Session::get('session_id');
            $cart = Cart::where('user_id', Auth::id())->get();
            return view('agent.ecommerce.view_cart', compact('cart'))->with($data);
        }else{
            return redirect()->back();
        }
    }

    function delete_product_from_cart (Request $request){
        $id = $request->id;
        $session_id = Session::get('session_id');
        Cart::where('id', $id)->where('user_id', Auth::id())->delete();
        return Response(['status' => 'success', 'message' => 'Product successfully deleted from cart !']);
    }

    function update_quantity_in_cart (Request $request){
        $id = $request->id;
        $quantity = $request->quantity;
        $session_id = Session::get('session_id');
        Cart::where('id', $id)->where('user_id', Auth::id())->update(['quantity' => $quantity]);
        return Response(['status' => 'success', 'message' => 'Successful.. !']);
    }

    function save_to_wishlist (Request $request){
        $product_id = $request->product_id;
        $session_id = Session::get('session_id');
        if (empty($session_id)){
            $session_id = Session::getId();
            Session::put('session_id', $session_id);
        }
        $wishlists = Wishlist::where('user_id', Auth::id())->where('product_id', $product_id)->first();
        if ($wishlists){
            return Response()->json(['status' => 'failure', 'message' => 'product already added in wishlist !']);
        }else{
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            Wishlist::insert([
                'session_id' => $session_id,
                'user_id' => Auth::id(),
                'product_id' => $product_id,
                'created_at' => $ctime,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'product added to wishlist !']);
        }
    }

    function my_wishlist (Request $request){
        if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->ecommerce == 1){
            $data = array(
                'page_title' => 'My Wishlist',
            );
            $session_id = Session::getId();
            $product_id = Wishlist::where('user_id', Auth::id())->get(['product_id']);
            $products = Product::where('status_id', 1)->whereIn('id', $product_id)->inRandomOrder()->paginate(20);
            return view('agent.ecommerce.welcome', compact('products'))->with($data);
        }else{
            return redirect()->back();
        }
    }

    function searchProductAjax (Request $request){
        $search_value = $request->get('term', '');
        $products = Product::where('product_name', 'LIKE', '%'.$search_value.'%')->where('status_id', 1)->get();
        $data = [];
        foreach ($products as $value){
            $data[] = [
                'value' => $value->product_name,
                'id' => $value->id,
            ];
        }
        if (count($data)){
            return $data;
        }else{
            return ['value' => 'No Result Found', 'id' => ''];
        }
    }

    function search_product (Request $request){
        if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->ecommerce == 1){
            $search_value = $request->search_product;
            $data = array(
                'page_title' => $search_value,
            );
            $products = Product::where('status_id', 1)->where('product_name', 'LIKE', '%'.$search_value.'%')->inRandomOrder()->paginate(20);
            return view('agent.ecommerce.welcome', compact('products'))->with($data);
        }else{
            return redirect()->back();
        }
    }
}
