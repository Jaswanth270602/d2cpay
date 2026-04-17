<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Category;
use App\Subcategory;
use App\Brand;
use App\Product;
use Helpers;
use DB;
use Str;
use \Crypt;
use App\Status;
use App\Productimage;
use App\Library\MemberLibrary;

class ProductController extends Controller
{

    function product_list (Request $request){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            if ($request->status_id){
                $status_id = $request->status_id;
                $urls = url('admin/ecommerce/product-list-api').'?'.'status_id='.$status_id;
            }else{
                $status_id = 0;
                $urls = url('admin/ecommerce/product-list-api').'?'.'status_id='.$status_id;
            }

            $data = array(
                'page_title' => 'Product List',
                'urls' => $urls,
                'status_id' => $status_id,
            );
            return view('admin.ecommerce.product_list')->with($data);
        }else{
            return redirect()->back();
        }
    }

    function product_list_api (Request $request){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
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
                ->whereIn('user_id', $my_down_member)
                ->whereIn('status_id', $status_id)
                ->count();

            $totalRecordswithFilter = Product::select('count(*) as allcount')
                ->whereIn('user_id', $my_down_member)
                ->where('product_name', 'like', '%' .$searchValue . '%')
                ->whereIn('status_id', $status_id)
                ->count();

            // Fetch records

            $records = Product::orderBy($columnName,$columnSortOrder)
                ->where('product_name', 'like', '%' .$searchValue . '%')
                ->whereIn('user_id', $my_down_member)
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
                    "add_image" => '<a href="'.url('admin/ecommerce/add-product-image').'/'. Crypt::encrypt($value->id) .'" target="_blank" class="btn btn-danger btn-sm">Add</a>',
                    "action" => '<a href="'.url('admin/ecommerce/update-product').'/'. Crypt::encrypt($value->id) .'" target="_blank" type="button" class="btn btn-success btn-sm">Update</a>',
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
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $data = array(
                'page_title' => 'Add Product',
            );
            $categories = Category::where('status_id', 1)->get();
            $brands = Brand::where('status_id', 1)->get();
            return view('admin.ecommerce.add_products', compact('categories', 'brands'))->with($data);
        }else{
            return redirect()->back();
        }
    }

    function get_sub_category (Request $request){
        $rules = array(
            'category_id' => 'required|exists:categories,id',

        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $category_id = $request->category_id;
        $subcategories = Subcategory::where('category_id', $category_id)->where('status_id', 1)->get();
        $response = array();
        foreach ($subcategories as $value) {
            $product = array();
            $product["id"] = $value->id;
            $product["category_name"] = $value->category_name;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'list' => $response]);
    }

    function save_products (Request $request){
        $this->validate($request, [
            'product_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480|dimensions:max_width=300,max_height=300',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'product_name' => 'required',
            'product_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'product_discount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'product_weight' => 'required|numeric',
            'description' => 'required',
            'meta_title' => 'required',
            'meta_keywords' => 'required',
            'meta_description' => 'required',
            'shipping_charge' => 'required|regex:/^\d+(\.\d{1,2})?$/',
        ]);
        $category_id = $request->category_id;
        $subcategory_id = $request->subcategory_id;
        if ($request->brand_id){
            $brand_id = $request->brand_id;
        }else{
            $brand_id = 0;
        }

        $product_name = $request->product_name;
        $product_price = $request->product_price;
        $product_discount = $request->product_discount;
        $product_weight = $request->product_weight;
        $description = $request->description;
        $meta_title = $request->meta_title;
        $meta_keywords = $request->meta_keywords;
        $meta_description = $request->meta_description;
        $shipping_charge = $request->shipping_charge;
        DB::beginTransaction();
        try{
            $company_name = Auth::User()->company->company_website;
            $product_image = $request->product_image;
            $photo = base64_encode(file_get_contents($product_image));
            $url = "https://cdn.bceres.com/api/file/v1/ecommerce";
            $api_request_parameters = array(
                'image' => $photo,
                'name' => $company_name,
                'type' => 2,
            );
            $method = 'POST';
            $header = ["Accept:application/json"];
            $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
            $res = json_decode($response);
            if ($res->status == 'success'){
                $image_url = $res->image_url;
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                Product::insertGetId([
                    'user_id' => Auth::id(),
                    'product_image' => $image_url,
                    'category_id' => $category_id,
                    'subcategory_id' => $subcategory_id,
                    'brand_id' => $brand_id,
                    'product_name' => $product_name,
                    'product_price' => $product_price,
                    'shipping_charge' => $shipping_charge,
                    'product_discount' => $product_discount,
                    'product_weight' => $product_weight,
                    'description' => $description,
                    'meta_title' => $meta_title,
                    'meta_keywords'  => $meta_keywords,
                    'meta_description' => $meta_description,
                    'created_at' => $ctime,
                    'company_id' => Auth::User()->company_id,
                    'status_id' => 3,
                ]);
                DB::commit();
                \Session::flash('success', 'Product successfully uploaded!');
                return redirect()->back();
            }else{
                \Session::flash('failure', $res->message);
                return redirect()->back();
            }

        }catch (\Exception $ex) {
            DB::rollback();
            // throw $ex;
            \Session::flash('failure', 'something went wrong');
            return redirect()->back();
        }
    }


    function update_product ($encrypt_id){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $id = Crypt::decrypt($encrypt_id);
            $products = Product::find($id);
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
                return view('admin.ecommerce.update_product', compact('categories', 'brands', 'subcategories'))->with($data);
            }else{
                return redirect()->back();
            }
        }else{
            return redirect()->back();
        }
    }

    function products_update_now (Request $request){
            $this->validate($request, [
                'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:20480|dimensions:max_width=300,max_height=300',
                'category_id' => 'required|exists:categories,id',
                'subcategory_id' => 'required|exists:subcategories,id',
                'brand_id' => 'nullable|exists:brands,id',
                'product_name' => 'required',
                'product_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
                'product_discount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
                'product_weight' => 'required|numeric',
                'description' => 'required',
                'meta_title' => 'required',
                'meta_keywords' => 'required',
                'meta_description' => 'required',
                'status_id' => 'required',
                'home_page' => 'required',
                'shipping_charge' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            ]);
            $id = Crypt::decrypt($request->encrypt_id);
            $category_id = $request->category_id;
            $subcategory_id = $request->subcategory_id;
            if ($request->brand_id){
                $brand_id = $request->brand_id;
            }else{
                $brand_id = 0;
            }

            $product_name = $request->product_name;
            $product_price = $request->product_price;
            $product_discount = $request->product_discount;
            $product_weight = $request->product_weight;
            $description = $request->description;
            $meta_title = $request->meta_title;
            $meta_keywords = $request->meta_keywords;
            $meta_description = $request->meta_description;
            if(Auth::User()->role_id == 1){
                $status_id = $request->status_id;    
                $home_page = $request->home_page;
            }else{
                $status_id = 3;
                $home_page = 0;
            }
            
            
            $shipping_charge = $request->shipping_charge;
            $products = Product::find($id);
            if ($products){
                DB::beginTransaction();
                try{
                    if ($request->product_image){
                        $company_name = Auth::User()->company->company_website;
                        $product_image = $request->product_image;
                        $photo = base64_encode(file_get_contents($product_image));
                        $url = "https://cdn.bceres.com/api/file/v1/ecommerce";
                        $api_request_parameters = array(
                            'image' => $photo,
                            'name' => $company_name,
                            'type' => 2,
                        );
                        $method = 'POST';
                        $header = ["Accept:application/json"];
                        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
                        $res = json_decode($response);
                        if ($res->status == 'success'){
                            $image_url = $res->image_url;
                        }else{
                            $image_url = $products->product_image;
                        }
                    }else{
                        $image_url = $products->product_image;
                    }
                    Product::where('id', $id)->update([
                        'category_id' => $category_id,
                        'subcategory_id' => $subcategory_id,
                        'brand_id' => $brand_id,
                        'product_name' => $product_name,
                        'product_image' => $image_url,
                        'product_price' => $product_price,
                        'product_discount' => $product_discount,
                        'product_weight' => $product_weight,
                        'description' => $description,
                        'meta_title' => $meta_title,
                        'meta_keywords' => $meta_keywords,
                        'meta_description' => $meta_description,
                        'status_id' => $status_id,
                        'home_page' => $home_page,
                        'shipping_charge' => $shipping_charge,
                    ]);
                    DB::commit();
                    \Session::flash('success', 'Product successfully updated !');
                    return redirect()->back();

                }catch (\Exception $ex) {
                    DB::rollback();
                    // throw $ex;
                    \Session::flash('failure', 'something went wrong');
                    return redirect()->back();
                }


            }else{
                \Session::flash('failure', 'Record not found');
                return redirect()->back();
            }
       
    }

    function add_product_image ($encrypt_id){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $id = Crypt::decrypt($encrypt_id);
            $products = Product::find($id);
            if ($products){
                $data = array(
                    'page_title' => $products->product_name,
                    'encrypt_id' => $encrypt_id,
                );
                $productimages = Productimage::where('product_id', $id)->get();
                return view('admin.ecommerce.add_product_image', compact('productimages'))->with($data);
            }else{
                return redirect()->back();
            }
        }else{
            return redirect()->back();
        }
    }

    function save_product_image (Request $request){
        $this->validate($request, [
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480|max:20480|dimensions:max_width=400,max_height=300',
            'encrypt_id' => 'required',
        ]);
        DB::beginTransaction();
        try{
            $id = Crypt::decrypt($request->encrypt_id);
            $company_name = Auth::User()->company->company_website;
            $product_image = $request->photo;
            $photo = base64_encode(file_get_contents($product_image));
            $url = "https://cdn.bceres.com/api/file/v1/ecommerce";
            $api_request_parameters = array(
                'image' => $photo,
                'name' => $company_name,
                'type' => 2,
            );
            $method = 'POST';
            $header = ["Accept:application/json"];
            $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
            $res = json_decode($response);
            if ($res->status == 'success'){
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                Productimage::insertGetId([
                    'user_id' => Auth::id(),
                    'product_id' => $id,
                    'photo' => $res->image_url,
                    'created_at' => $ctime,
                    'status_id' => 3,
                ]);
                DB::commit();
                \Session::flash('success', 'Product image successfully uploaded!');
                return redirect()->back();
            }else{
                \Session::flash('failure', $res->message);
                return redirect()->back();
            }

        }catch (\Exception $ex) {
            DB::rollback();
            // throw $ex;
            \Session::flash('failure', 'something went wrong');
            return redirect()->back();
        }
    }

    function delete_product_image (Request $request){
            $rules = array(
                'id' => 'required|exists:productimages,id',

            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            Productimage::where('id', $request->id)->delete();
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        
    }

    function view_product_image (Request $request){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $rules = array(
                'id' => 'required|exists:productimages,id',

            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $id = $request->id;
            $productimages = Productimage::find($id);
            if ($productimages){
                $details = array(
                    'id' => $productimages->id,
                    'status_id' => $productimages->status_id,
                );
                return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'details' => $details]);
            }else{
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_product_image (Request $request){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $rules = array(
                'id' => 'required|exists:productimages,id',
                'status_id' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $id = $request->id;
            $status_id = $request->status_id;
            Productimage::where('id', $id)->update(['status_id' => $status_id]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }
}
