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
use App\Status;
use App\DeliveryStatus;

class OrderController extends Controller {
    //


    function my_orders (Request $request){
        if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->ecommerce == 1){
            if ($request->status_id){
                $status_id = $request->status_id;
                $urls = url('agent/ecommerce/my-orders-api').'?'.'status_id='.$status_id;
            }else{
                $status_id = 0;
                $urls = url('agent/ecommerce/my-orders-api').'?'.'status_id='.$status_id;
            }
            $data = array(
                'page_title' => 'My Order',
                'urls' => $urls,
                'status_id' => $status_id,
            );
            $delivery_statuses = DeliveryStatus::get();
            return view('agent.ecommerce.my_orders', compact('delivery_statuses'))->with($data);
        }else{
            return redirect()->back();
        }
    }

    function my_orders_api (Request $request){
        if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->ecommerce == 1){
            $status_id = $request->status_id;
            if ($status_id == 0){
                $status_id = DeliveryStatus::whereIn('id', [1,2,3,4])->get(['id']);
            }else{
                $status_id = DeliveryStatus::where('id', $status_id)->get(['id']);
            }
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


            $totalRecords = EcommerceOrder::select('count(*) as allcount')
                ->where('user_id', Auth::id())
                ->whereIn('status_id', $status_id)
                ->count();

            $totalRecordswithFilter = EcommerceOrder::select('count(*) as allcount')
                ->where('user_id', Auth::id())
                ->where('mobile_number', 'like', '%' .$searchValue . '%')
                ->whereIn('status_id', $status_id)
                ->count();

            // Fetch records

            $records = EcommerceOrder::orderBy($columnName,$columnSortOrder)
                ->where('mobile_number', 'like', '%' .$searchValue . '%')
                ->where('user_id', Auth::id())
                ->whereIn('status_id', $status_id)
                ->orderBy('id', 'DESC')
                ->skip($start)
                ->take($rowperpage)
                ->get();
            $data_arr = array();
            $i = 0;
            foreach($records as $value){
                if ($value->status_id == 1){
                    $status = '<span class="badge badge-success">Delivered</span>';
                }elseif ($value->status_id == 2){
                    $status = '<span class="badge badge-primary">Shipped</span>';
                }elseif ($value->status_id == 3){
                    $status = '<span class="badge badge-warning">Packed</span>';
                }else{
                    $status = '<span class="badge badge-warning">Ordered</span>';
                }

                $data_arr[] = array(
                    "sr_no" => $value->id,
                    "created_at" => "$value->created_at",
                    "mobile_number" => $value->mobile_number,
                    "product" => 'Ecommerce Product',
                    "amount" => number_format($value->total_amount + $value->total_commission, 2),
                    "total_discount" => number_format($value->total_discount, 2),
                    "shipping_charges" => number_format($value->shipping_charges, 2),
                    "total_commission" => number_format($value->total_commission, 2),
                    "grand_total" => number_format($value->grand_total, 2),
                    "status" => $status,
                    "action" => '<button onclick="view_product('. $value->id.')" type="button" class="btn btn-danger btn-sm">Details</button>',
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
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function view_order_product (Request $request){
        $rules = array(
            'id' => 'required|exists:ecommerce_orders,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $ecommerceorder_id = $request->id;
        $ecommerce_productorders = EcommerceProductorder::where('ecommerceorder_id', $ecommerceorder_id)->get();
        $productdetails = Self::get_product_details($ecommerce_productorders);

        //addresss details
        $ecommerce_orders = EcommerceOrder::find($ecommerceorder_id);
        $addressdetails = array(
            'name' => $ecommerce_orders->name,
            'address' => $ecommerce_orders->address,
            'city' => $ecommerce_orders->city,
            'state_name' => $ecommerce_orders->state->name,
            'district_name' => $ecommerce_orders->district->district_name,
            'pin_code' => $ecommerce_orders->pin_code,
            'mobile_number' => $ecommerce_orders->mobile_number,
            'email' => $ecommerce_orders->email,
        );
        return Response()->json([
            'status' => 'success',
            'addressdetails' => $addressdetails,
            'productdetails' => $productdetails,
            ]);
    }

    function get_product_details ($ecommerce_productorders){
        $response = array();
        foreach ($ecommerce_productorders as $value) {
            if ($value->status_id == 1){
                $status = '<span class="badge badge-success">Delivered</span>';
            }elseif ($value->status_id == 2){
                $status = '<span class="badge badge-primary">Shipped</span>';
            }elseif ($value->status_id == 3){
                $status = '<span class="badge badge-warning">Packed</span>';
            }else{
                $status = '<span class="badge badge-warning">Ordered</span>';
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

    function view_track_order (Request $request){
        $rules = array(
            'order_id' => 'required|exists:ecommerce_productorders,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $order_id = $request->order_id;
        $ecommerce_productorders = EcommerceProductorder::where('id', $order_id)->where('user_id', Auth::id())->first();
        if ($ecommerce_productorders){
            if ($ecommerce_productorders->status_id == 4){
                $orderdetails = '<ul id="progressbar">
                            <li class="step0 active " id="step1">Ordered</li>
                            <li class="step0  text-center" id="step2">Packed</li>
                            <li class="step0  text-right" id="step3">Shipped</li>
                            <li class="step0  text-right" id="step4">Delivered</li>
                        </ul>';
            }elseif ($ecommerce_productorders->status_id == 3){
                $orderdetails = '<ul id="progressbar">
                            <li class="step0 active " id="step1">Ordered</li>
                            <li class="step0 active text-center" id="step2">Packed</li>
                            <li class="step0  text-right" id="step3">Shipped</li>
                            <li class="step0  text-right" id="step4">Delivered</li>
                        </ul>';
            }elseif ($ecommerce_productorders->status_id == 2){
                $orderdetails = '<ul id="progressbar">
                            <li class="step0 active " id="step1">Ordered</li>
                            <li class="step0 active text-center" id="step2">Packed</li>
                            <li class="step0 active text-right" id="step3">Shipped</li>
                            <li class="step0  text-right" id="step4">Delivered</li>
                        </ul>';
            }elseif ($ecommerce_productorders->status_id == 1){
                $orderdetails = '<ul id="progressbar">
                            <li class="step0 active " id="step1">Ordered</li>
                            <li class="step0 active text-center" id="step2">Packed</li>
                            <li class="step0 active text-right" id="step3">Shipped</li>
                            <li class="step0 active text-right" id="step4">Delivered</li>
                        </ul>';
            }

           return Response(['status' => 'success', 'orderdetails' => $orderdetails]);
        }else{
            return Response(['status' => 'failure', 'message' => 'Record not found']);
        }
    }

    function track_orders (Request $request){

        if ($request->order_id){
            $order_id = $request->order_id;
        }else{
            $order_id = '';
        }
        $data = array(
            'page_title' => 'Track Order',
            'order_id' => $order_id,
        );
        $ecommerce_productorders = EcommerceProductorder::where('ecommerceorder_id', $order_id)->where('user_id', Auth::id())->get();
        return view('agent.ecommerce.track_order', compact('ecommerce_productorders'))->with($data);
    }
}
