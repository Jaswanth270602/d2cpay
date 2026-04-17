<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use Str;
use \Crypt;
use App\Shoppingbanner;
use App\Category;
use App\Subcategory;
use App\Product;
use App\Wishlist;
use App\Cart;
use App\Deliveryaddress;
use App\Deliverymethod;
use App\Provider;
use App\Balance;
use App\Report;
use App\EcommerceOrder;
use App\EcommerceProductorder;
use DB;
use App\User;


class EcommerceController extends Controller
{
    function banners (){
        $shoppingbanners = Shoppingbanner::where('status_id', 1)->get();
        $response = array();
        foreach ($shoppingbanners as $value) {
            $product = array();
            $product["image"] = $value->banners;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'banners' => $response]);
    }

    function get_category (Request $request){
        $categories = Category::where('status_id', 1)->get();
        $response = array();
        foreach ($categories as $value) {
            $subcategories = Self::get_subcategory($value->id);
            $product = array();
            $product["category_id"] = $value->id;
            $product["category_name"] = $value->category_name;
            $product["subcategories"] = $subcategories;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'categories' => $response]);
    }

    function get_subcategory ($category_id){
        $subcategories = Subcategory::where('category_id', $category_id)->where('status_id', 1)->get();
        $response = array();
        foreach ($subcategories as $value) {
            $product = array();
            $product["subcategory_id"] = $value->id;
            $product["subcategory_name"] = $value->category_name;
            array_push($response, $product);
        }
        return $response;
    }

    function home_page_product (Request $request){
        $products = Product::where('status_id', 1)->where('home_page', 1)->inRandomOrder()->paginate(20);
        return Self::getProduct($products);
    }

    function product_by_category (Request $request){
        $rules = array(
            'subcategory_id' => 'required|exists:subcategories,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $subcategory_id = $request->subcategory_id;
        $products = Product::where('status_id', 1)->where('subcategory_id', $subcategory_id)->inRandomOrder()->paginate(20);
        return Self::getProduct($products);
    }

    function search_product (Request $request){
        $product_name = $request->product_name;
        $products = Product::where('status_id', 1)->where('product_name', 'LIKE', '%'.$product_name.'%')->inRandomOrder()->paginate(20);
        return Self::getProduct($products);
    }


    function getProduct ($products){
        $response = array();
        foreach ($products as $value) {
            // product details
            $productDetails = Self::product_details($value->id);
            // product price and discount
            $product_discount = ($value->product_price * $value->product_discount) / 100;
            $discount_price = $value->product_price - $product_discount;
            $commission = ($value->product_price * $value->subcategory->commission) / 100;
            $product_price = $discount_price + $commission;
            $product = array();
            $product["product_id"] = $value->id;
            $product["product_name"] = $value->product_name;
            $product["product_image"] = $value->product_image;
            $product["category_name"] = $value->subcategory->category_name;
            $product["product_price"] = number_format($product_price,2);
            $product["productDetails"] = $productDetails;
            array_push($response, $product);
        }
        return response()->json([
            'total' => $products->total(),
            'pageNumber' => $products->currentPage(),
            'nextPageUrl' => $products->nextPageUrl(),
            'page' => $products->currentPage(),
            'pages' => $products->lastPage(),
            'perpage' => $products->perPage(),
            'products' => $response,
            'status' => 'success',
        ]);

    }

    function product_details ($id){
        $products = Product::where('id', $id)->where('status_id', 1)->first();
        if ($products){
            // related product
            $relatedproduct = Product::where('subcategory_id', $products->subcategory_id)
                ->where('status_id', 1)
                ->where('id','!=', $id)->limit(4)
                ->inRandomOrder()
                ->get();
            $relatedproduct = Self::relatedProduct($relatedproduct);

            // price calculation
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
                'relatedProduct' => $relatedproduct,
            );
        }else{
            $data = array(
                'product_id' => '',
                'page_title' => '',
                'product_image' => '',
                'product_name' => '',
                'description' => '',
                'product_price' => '',
                'product_discount' => '',
                'relatedProduct' => [],
                );
        }
        return $data;
    }

    function relatedProduct ($relatedproduct){
        $response = array();
        foreach ($relatedproduct as $value) {
            $product_discount = ($value->product_price * $value->product_discount) / 100;
            $discount_price = $value->product_price - $product_discount;
            $commission = ($value->product_price * $value->subcategory->commission) / 100;
            $product_price = $discount_price + $commission;
            $product = array();
            $product["product_id"] = $value->id;
            $product["product_name"] = $value->product_name;
            $product["product_image"] = $value->product_image;
            $product["category_name"] = $value->subcategory->category_name;
            $product["product_price"] = number_format($product_price,2);
            array_push($response, $product);
        }
        return $response;
    }

    function add_to_cart (Request $request){
        $rules = array(
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $product_id = $request->product_id;
        $quantity = $request->quantity;
        $user_id = Auth::id();
        $checkcart = Cart::where('user_id', $user_id)->where('product_id', $product_id)->first();
        if ($checkcart){
            $quantity = $checkcart->quantity + $quantity;
            if ($quantity > 10){
                return Response()->json(['status' => 'failure', 'message' => 'You can order more than 10 quantities in 1 order !']);
            }else{
                Cart::where('id', $checkcart->id)->update(['quantity' => $quantity]);
            }
        }else{
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            Cart::insert([
                'user_id' => Auth::id(),
                'product_id' => $product_id,
                'quantity' => $quantity,
                'created_at' => $ctime,
            ]);
        }
        return Response()->json(['status' => 'success', 'message' => 'Product has been added in cart !']);
    }

    function view_cart_item (Request $request){
        $user_id = Auth::id();
        $cartItem = Cart::where('user_id', $user_id)->get();
        $response = array();
        $total_amount = 0;
        $total_discount = 0;
        $commission = 0;
        $shipping_charge = 0;
        $grand_amount = 0;
        foreach ($cartItem as $value) {
            $product_price = $value->product->product_price;
            $service_Charge = ($value->product->product_price * $value->product->subcategory->commission) / 100;
            $unit_price = $product_price + $service_Charge;
            // discount
            $actutal_discount = ($value->product->product_price * $value->product->product_discount) / 100;
            // sumtotal
            $sub_total = $unit_price * $value->quantity;
            $product = array();
            $product["cart_id"] = $value->id;
            $product["image"] = $value->product->product_image;
            $product["category_name"] = $value->product->subcategory->category_name;
            $product["product_name"] = $value->product->product_name;
            $product["product_weight"] = $value->product->product_weight.' Grams';
            $product["unit_price"] = $unit_price;
            $product["quantity"] = $value->quantity;
            $product["product_discount"] = $actutal_discount * $value->quantity;
            $product["sub_total"] = $sub_total;
            array_push($response, $product);
            // total
            $quantity = $value->quantity;
            $actutal_product_price = $value->product->product_price * $quantity;
            $actual_commission = ($actutal_product_price * $value->product->subcategory->commission) / 100;
            $shipping_charge += $value->product->shipping_charge;
            $actutal_discount = ($value->product->product_price * $value->product->product_discount) / 100;
            $total_discount += $actutal_discount * $quantity;

           // $commission += $actual_commission;
            $total_amount += $actutal_product_price + $actual_commission;

            $grand_amount = $total_amount +  $shipping_charge - $total_discount;
        }
        $total = array(
            'total_amount' => $total_amount,
            'shipping_charge' => $shipping_charge,
            'total_discount' => $total_discount,
            'grand_total' => $grand_amount,
        );
        return Response()->json(['status' => 'success', 'message' => 'successful..!', 'cartItem' => $response, 'total' => $total]);
    }

    function delete_cart_item (Request $request){
        $rules = array(
            'cart_id' => 'required|exists:carts,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $cart_id = $request->cart_id;
        $user_id = Auth::id();
        $checkCartItem = Cart::where('id', $cart_id)->where('user_id', $user_id)->first();
        if ($checkCartItem){
            Cart::where('id', $cart_id)->where('user_id', Auth::id())->delete();
            return Response(['status' => 'success', 'message' => 'Product successfully deleted from cart !']);
        }else{
            return Response(['status' => 'failure', 'message' => 'Item not found !']);
        }
    }

    function update_cart_item (Request $request){
        $rules = array(
            'cart_id' => 'required|exists:carts,id',
            'quantity' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $cart_id = $request->cart_id;
        $quantity = $request->quantity;
        Cart::where('id', $cart_id)->where('user_id', Auth::id())->update(['quantity' => $quantity]);
        return Response(['status' => 'success', 'message' => 'Successful.. !']);
    }

    function add_to_wishlist (Request $request){
        $rules = array(
            'product_id' => 'required|exists:products,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $product_id = $request->product_id;
        $user_id = Auth::id();
        $wishlists = Wishlist::where('user_id', $user_id)->where('product_id', $product_id)->first();
        if ($wishlists){
            return Response()->json(['status' => 'failure', 'message' => 'product already added in wishlist !']);
        }else{
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            Wishlist::insert([
                'user_id' => $user_id,
                'product_id' => $product_id,
                'created_at' => $ctime,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'product added to wishlist !']);
        }
    }

    function my_wishlist (Request $request){
        $product_id = Wishlist::where('user_id', Auth::id())->get(['product_id']);
        $products = Product::where('status_id', 1)->whereIn('id', $product_id)->inRandomOrder()->paginate(20);
        return Self::getProduct($products);
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

    function my_delivery_addresses (Request $request){
        $deliveryaddresses = Deliveryaddress::where('user_id', Auth::id())->get();
        $response = array();
        foreach ($deliveryaddresses as $value) {
            $product = array();
            $product["address_id"] = $value->id;
            $product["name"] = $value->name;
            $product["state_id"] = $value->state_id;
            $product["state_name"] = $value->state->name;
            $product["district_id"] = $value->district_id;
            $product["district_name"] = $value->district->district_name;
            $product["city"] = $value->city;
            $product["pin_code"] = $value->pin_code;
            $product["mobile_number"] = $value->mobile_number;
            $product["email"] = $value->email;
            $product["address"] = $value->address;
            array_push($response, $product);
        }
       return Response()->json(['status' => 'success', 'message' => 'successful..!', 'addressList' => $response]);
    }

    function update_delivery_addresses (Request $request){
        $rules = array(
            'address_id' => 'required|exists:deliveryaddresses,id',
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
        $id = $request->address_id;
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

    function payment_method (Request $request){
        $deliverymethods = Deliverymethod::where('status_id', 1)->get();
        $response = array();
        foreach ($deliverymethods as $value) {
            $product = array();
            $product["paymentMethod_id"] = $value->id;
            $product["paymentMethod_name"] = $value->name;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'message' => 'successful..!', 'paymentMethods' => $response]);
    }

    function confirm_order (Request $request){
        $rules = array(
            'paymentMethod_id' => 'required|exists:deliverymethods,id',
            'address_id' => 'required|exists:deliveryaddresses,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        DB::beginTransaction();
        try{
            $paymentMethod_id = $request->paymentMethod_id;
            $address_id = $request->address_id;
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
            if ($paymentMethod_id == 1){
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

                    $deliveryaddresses = Deliveryaddress::find($address_id);
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
                    return Response()->json(['status' => 'success', 'message' => 'Your product has been successfully purchased, you will get the product delivered soon']);
                }else{
                    return Response()->json(['status' => 'failure', 'message' => 'Your wallet balance is low kinldy refill your wallet']);
                }

            }

        }catch (\Exception $ex) {
            DB::rollback();
            // throw $ex;
          return Response()->json(['status' => 'failure', 'message' => 'Something went wrong']);
        }
    }

    function order_report (Request $request){
        $reports = EcommerceOrder::where('user_id', Auth::id())->paginate(20);
        $response = array();
        foreach ($reports as $value) {
            if ($value->status_id == 1){
                $status = 'Delivered';
            }elseif ($value->status_id == 2){
                $status = 'Shipped';
            }elseif ($value->status_id == 3){
                $status = 'Packed';
            }else{
                $status = 'Ordered';
            }

            $deliveryAddresses = array(
                'name' => $value->name,
                'address' => $value->address,
                'city' => $value->city,
                'state_name' => $value->state->name,
                'district_name' => $value->district->district_name,
                'pin_code' => $value->pin_code,
                'mobile_number' => $value->mobile_number,
                'email' => $value->email,
            );
            $productDetails = Self::getProductDetails($value->id);
            $product = array();
            $product["tracking_id"] = $value->id;
            $product["created_at"] = $value->created_at->format('Y-m-d h:m:s');
            $product["mobile_number"] = $value->mobile_number;
            $product["product"] = 'Ecommerce Product';
            $product["amount"] = number_format($value->total_amount + $value->total_commission, 2);
            $product["total_discount"] = number_format($value->total_discount, 2);
            $product["shipping_charges"] = number_format($value->shipping_charges, 2);
            $product["grand_total"] = number_format($value->grand_total, 2);
            $product["status"] = $status;
            $product["deliveryAddresses"] = $deliveryAddresses;
            $product["productDetails"] = $productDetails;
            array_push($response, $product);
        }
        return response()->json([
            'total' => $reports->total(),
            'pageNumber' => $reports->currentPage(),
            'nextPageUrl' => $reports->nextPageUrl(),
            'page' => $reports->currentPage(),
            'pages' => $reports->lastPage(),
            'perpage' => $reports->perPage(),
            'reports' => $response,
            'status' => 'success',
        ]);
    }

    function getProductDetails ($id){
        $ecommerce_productorders = EcommerceProductorder::where('ecommerceorder_id', $id)->get();
        $response = array();
        foreach ($ecommerce_productorders as $value) {
            if ($value->status_id == 1){
                $status = 'Delivered';
            }elseif ($value->status_id == 2){
                $status = 'Shipped';
            }elseif ($value->status_id == 3){
                $status = 'Packed';
            }else{
                $status = 'Ordered';
            }
            $product = array();
            $product["order_id"] = $value->id;
            $product["image"] = $value->product->product_image;
            $product["product_weight"] = $value->product->product_weight.' Grams';
            $product["product_name"] = $value->product->product_name;
            $product["category_name"] = $value->subcategory->category_name;
            $product["product_price"] = number_format($value->product_price + $value->commission, 2);
            $product["product_discount"] = number_format($value->product_discount, 2);
            $product["shipping_charge"] = number_format($value->shipping_charge, 2);
            $product["quantity"] = $value->quantity;
            $product["commission"] = number_format($value->commission, 2);
            $product["total_amount"] = number_format($value->total_amount, 2);
            $product["status"] = $status;
            array_push($response, $product);
        }
        return $response;
    }



}
