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
use App\Category;
use App\Subcategory;
use App\Brand;
use App\Product;
use Helpers;
use DB;
use App\Status;
use App\Productimage;
use App\Library\MemberLibrary;

class SellerController extends Controller
{


    function product_list (Request $request){
        if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->seller == 1){
            if ($request->status_id){
                $status_id = $request->status_id;
                $urls = url('agent/ecommerce-seller/product-list-api').'?'.'status_id='.$status_id;
            }else{
                $status_id = 0;
                $urls = url('agent/ecommerce-seller/product-list-api').'?'.'status_id='.$status_id;
            }
            $data = array(
                'page_title' => 'Product List',
                'urls' => $urls,
                'status_id' => $status_id,
            );
            return view('agent.ecommerce-seller.product_list')->with($data);
        }else{
            return redirect()->back();
        }
    }

    function product_list_api (Request $request){
        if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->seller == 1){
            $status_id = $request->status_id;
            if ($status_id == 0){
                $status_id = Status::whereIn('id', [1,2,3])->get(['id']);
            }else{
                $status_id = Status::where('id', $status_id)->get(['id']);
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

            $role_id = Auth::User()->role_id;
            $company_id = Auth::User()->company_id;
            $user_id = Auth::id();
            $library = new MemberLibrary();
            $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

            $totalRecords = Product::select('count(*) as allcount')
                ->where('user_id', Auth::id())
                ->whereIn('status_id', $status_id)
                ->count();

            $totalRecordswithFilter = Product::select('count(*) as allcount')
                ->where('user_id', Auth::id())
                ->where('product_name', 'like', '%' .$searchValue . '%')
                ->whereIn('status_id', $status_id)
                ->count();

            // Fetch records

            $records = Product::orderBy($columnName,$columnSortOrder)
                ->where('product_name', 'like', '%' .$searchValue . '%')
                ->where('user_id', Auth::id())
                ->whereIn('status_id', $status_id)
                ->orderBy('id', 'DESC')
                ->skip($start)
                ->take($rowperpage)
                ->get();
            $data_arr = array();
            $i = 0;
            foreach($records as $value){
                $image = '<a href="'.$value->product_image.'" target="_blank"><img src="'. $value->product_image.'" style="width:20%;"></a>';
                if ($value->status_id == 1){
                    $status = '<span class="badge badge-success">Approved</span>';
                }elseif ($value->status_id == 2){
                    $status = '<span class="badge badge-danger">Rejected</span>';
                }else{
                    $status = '<span class="badge badge-warning">Pending</span>';
                }
                $data_arr[] = array(
                    "sr_no" => ++$i,
                    "created_at" => "$value->created_at",
                    "user" => $value->user->name.' '.$value->user->last_name,
                    "category_name" => $value->subcategory->category_name,
                    "product_image" => $image,
                    "product_name" => $value->product_name,
                    "product_price" => number_format($value->product_price, 2),
                    "shipping_charge" => number_format($value->shipping_charge, 2),
                    "status" => $status,
                    "add_image" => '<a href="'.url('agent/ecommerce-seller/add-product-image').'/'. Crypt::encrypt($value->id) .'" target="_blank" class="btn btn-danger btn-sm">Add</a>',
                    "action" => '<a href="'.url('agent/ecommerce-seller/update-product').'/'. Crypt::encrypt($value->id) .'" target="_blank" type="button" class="btn btn-success btn-sm">Update</a>',
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

    function add_products (Request $request){
        if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->seller == 1){
            $data = array(
                'page_title' => 'Add Product',
            );
            $categories = Category::where('status_id', 1)->get();
            $brands = Brand::where('status_id', 1)->get();
            return view('agent.ecommerce-seller.add_products', compact('categories', 'brands'))->with($data);
        }else{
            return redirect()->back();
        }
    }
    function update_product ($encrypt_id){
        DB::beginTransaction();
        try{
            if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->seller == 1){
                 $id = Crypt::decrypt($encrypt_id);
                $products = Product::where('id', $id)->where('user_id', Auth::id())->first();
                if ($products){
                    $data = array(
                        'page_title' => $products->product_name,
                        'encrypt_id' => $encrypt_id,
                        'product_image' => $products->product_image,
                        'category_id' => $products->category_id,
                        'subcategory_id' => $products->subcategory_id,
                        'brand_id' => $products->brand_id,
                        'product_name' => $products->product_name,
                        'product_price' => $products->product_price,
                        'shipping_charge' => $products->shipping_charge,
                        'product_discount' => $products->product_discount,
                        'product_weight' => $products->product_weight,
                        'description' => $products->description,
                        'meta_title' => $products->meta_title,
                        'meta_keywords' => $products->meta_keywords,
                        'meta_description' => $products->meta_description,
                        'status_id' => $products->status_id,
                        'home_page' => $products->home_page,
                    );
                    $categories = Category::where('status_id', 1)->get();
                    $brands = Brand::where('status_id', 1)->get();
                    $subcategories = Subcategory::where('status_id', 1)->get();
                    return view('agent.ecommerce-seller.update_product', compact('categories', 'brands', 'subcategories'))->with($data);
                }else{
                    return redirect()->back();
                }
                DB::commit();
            }else{
                return redirect()->back();
            }
        }catch (\Exception $ex) {
            DB::rollback();
             //throw $ex;
            return redirect()->back();
        }
    }

    function add_product_image ($encrypt_id){
        DB::beginTransaction();
        try{
            if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->seller == 1){
                $id = Crypt::decrypt($encrypt_id);
                $products = Product::where('id', $id)->where('user_id', Auth::id())->first();
                if ($products){
                    $data = array(
                        'page_title' => $products->product_name,
                        'encrypt_id' => $encrypt_id,
                    );
                    $productimages = Productimage::where('product_id', $id)->get();
                    return view('agent.ecommerce-seller.add_product_image', compact('productimages'))->with($data);
                }else{
                    return redirect()->back();
                }
            }else{
                return redirect()->back();
            }
            DB::commit();
        }catch (\Exception $ex) {
            DB::rollback();
            //throw $ex;
            return redirect()->back();
        }
    }

    function my_product (Request $request){
        if (Auth::User()->company->ecommerce == 1 && Auth::User()->profile->seller == 1){
                $data = array(
                    'page_title' => 'My Product',
                );
                $products = Product::where('status_id', 1)->where('user_id', Auth::id())->inRandomOrder()->paginate(20);
                return view('agent.ecommerce.welcome', compact('products'))->with($data);
        }else{
            return redirect()->back();
        }
    }
}
