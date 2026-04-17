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

class OrderrequestController extends Controller {


    function order_request (Request $request){
        if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->seller == 1){
            if ($request->fromdate && $request->todate) {
                $fromdate = $request->fromdate;
                $todate = $request->todate;
                $status_id = $request->status_id;
                $urls = url('agent/ecommerce-seller/order-request-api').'?'.'fromdate='.$fromdate.'&todate='.$todate.'&status_id='.$status_id;
            } else {
                $status_id = 0;
                $fromdate = date('Y-m-d', time());
                $todate = date('Y-m-d', time());
                $urls = url('agent/ecommerce-seller/order-request-api').'?'.'fromdate='.$fromdate.'&todate='.$todate.'&status_id='.$status_id;
            }
            $data = array(
                'page_title' => 'Order Request',
                'urls' => $urls,
                'status_id' => $status_id,
                'fromdate' => $fromdate,
                'todate' => $todate,
            );
            $delivery_statuses = DeliveryStatus::get();
            return view('agent.ecommerce-seller.order_request', compact('delivery_statuses'))->with($data);
        }else{
            return redirect()->back();
        }
    }


    function order_request_api (Request $request){
        if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->seller == 1){
            $fromdate = $request->get('fromdate');
            $todate =  $request->get('amp;todate');
            $status_id =  $request->get('amp;status_id');
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

             $product_id = Product::where('user_id', Auth::id())->get(['id']);


            $totalRecords = EcommerceProductorder::select('count(*) as allcount')
                ->whereIn('product_id', $product_id)
                ->whereIn('status_id', $status_id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->count();

            $totalRecordswithFilter = EcommerceProductorder::select('count(*) as allcount')
                ->whereIn('product_id', $product_id)
                ->where('product_name', 'like', '%' .$searchValue . '%')
                ->whereIn('status_id', $status_id)
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->count();

            // Fetch records

            $records = EcommerceProductorder::orderBy($columnName,$columnSortOrder)
                ->where('product_name', 'like', '%' .$searchValue . '%')
                ->whereDate('created_at', '>=', $fromdate)
                ->whereDate('created_at', '<=', $todate)
                ->whereIn('product_id', $product_id)
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
                    "username" => $value->user->name.' '.$value->user->last_name,
                    "created_at" => "$value->created_at",
                    "product" => '<div class="media"><div class="card-aside-img"><img src="'. $value->product->product_image.'" alt="img" class="h-60 w-60"></div><div class="media-body" style="margin-left: 5%;"> <div class="card-item-desc mt-0"> <h6 class="font-weight-semibold mt-0 text-uppercase">'.$value->subcategory->category_name.'</h6> <dl class="card-item-desc-1"> <dt>Name: </dt> <dd>'. $value->product->product_name.'</dd> </dl> <dl class="card-item-desc-1"> <dt>Weight: </dt> <dd>'. $value->product->product_weight.'</dd> </dl> </div> </div> </div>',
                    "quantity" => $value->quantity,
                    "product_price" => number_format($value->product_price, 2),
                    "product_discount" => number_format($value->product_discount, 2),
                    "shipping_charge" => number_format($value->shipping_charge, 2),
                    "commission" => number_format($value->commission, 2),
                    "total_amount" => number_format($value->total_amount, 2),
                    "status" => $status,
                    "details" => '<button onclick="view_product('. $value->id.')" type="button" class="btn btn-success btn-sm">Details</button>',
                    "action" => '<button onclick="view_update_product('. $value->id.')" type="button" class="btn btn-danger btn-sm">Update</button>',
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

    function view_order_product_details (Request $request){
        $rules = array(
            'id' => 'required|exists:ecommerce_productorders,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $id = $request->id;
        $product_id = Product::where('user_id', Auth::id())->get(['id']);
        $ecommerce_productorders = EcommerceProductorder::where('id', $id)->whereIn('product_id', $product_id)->get();
        $productdetails = Self::get_product_details($ecommerce_productorders);

        //addresss details
        $ecommerce_productorders_ecommerceorder_id = EcommerceProductorder::where('id', $id)->whereIn('product_id', $product_id)->first();
        if ($ecommerce_productorders_ecommerceorder_id){
            $ecommerce_orders = EcommerceOrder::find($ecommerce_productorders_ecommerceorder_id->ecommerceorder_id);

            // track order details
            $trackOrderDetails = Self::trackOrderDetails($id);

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
                'trackOrderDetails' => $trackOrderDetails,
            ]);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
        }

    }

    function trackOrderDetails ($id){
        $ecommerce_productorders = EcommerceProductorder::where('id', $id)->first();
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
        }else{
            $orderdetails = '<ul id="progressbar">
                            <li class="step0 " id="step1">Ordered</li>
                            <li class="step0 text-center" id="step2">Packed</li>
                            <li class="step0 text-right" id="step3">Shipped</li>
                            <li class="step0 text-right" id="step4">Delivered</li>
                        </ul>';
        }

        return $orderdetails;
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
            $product["product_price"] = number_format($value->product_price, 2);
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

    function view_update_product (Request $request){
            $id = $request->id;
            $product_id = Product::where('user_id', Auth::id())->get(['id']);
            $ecommerce_productorders = EcommerceProductorder::where('id', $id)->whereIn('product_id', $product_id)->first();
            if ($ecommerce_productorders){
                $details = array(
                    'id' => $ecommerce_productorders->id,
                    'username' => $ecommerce_productorders->user->name.' '.$ecommerce_productorders->user->last_name,
                    'product_name' => $ecommerce_productorders->product->product_name,
                    'status_id' => $ecommerce_productorders->status_id,
                );
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'details' => $details]);
            }else{
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }
    }

    function update_product_delivery_status (Request $request){
            $rules = array(
                'id' => 'required',
                'status_id' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $id = $request->id;
            $status_id = $request->status_id;
            $product_id = Product::where('user_id', Auth::id())->get(['id']);
            $ecommerce_productorders = EcommerceProductorder::where('id', $id)->whereIn('product_id', $product_id)->first();
            if ($ecommerce_productorders){
                EcommerceProductorder::where('id', $id)->update(['status_id' => $status_id]);
                return Response()->json(['status' => 'success', 'message' => 'Successful...!']);
            }else{
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }

    }
}
