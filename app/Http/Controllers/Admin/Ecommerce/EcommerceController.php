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
use App\Shoppingbanner;
use Helpers;
use DB;
use Str;

class EcommerceController extends Controller
{

    function main_category (Request $request){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $data = array(
                'page_title' => 'Main Category',
            );
            $categories = Category::get();
            return view('admin.ecommerce.main_category', compact('categories'))->with($data);
        }else{
            return redirect()->back();
        }
    }

    function save_category (Request $request){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $rules = array(
                'category_name' => 'required|unique:categories',
                'font_icon' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $category_name = $request->category_name;
            $font_icon = $request->font_icon;
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            DB::beginTransaction();
            try{
                Category::insertGetId([
                    'user_id' => Auth::id(),
                    'category_name' => $category_name,
                    'font_icon' => $font_icon,
                    'created_at' => $ctime,
                    'status_id' => 1,
                    'comapny_id' => Auth::User()->company_id,
                ]);
                DB::commit();
                return response()->json(['status' => 'success', 'message' => 'Successful..!']);
            }catch (\Exception $ex) {
                DB::rollback();
                // throw $ex;
                return response()->json(['status' => 'failure', 'message' => 'something went wrong']);
            }
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function view_category (Request $request){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
                $id = $request->id;
                $categories = Category::find($id);
                if ($categories){

                    $details = array(
                        'id' => $categories->id,
                        'category_name' => $categories->category_name,
                        'font_icon' => $categories->font_icon,
                        'status_id' => $categories->status_id,
                    );
                    return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'details' => $details]);
                }else{
                    return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
                }
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_category (Request $request){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $id = $request->id;
            $rules = array(
                'category_name' => 'required|unique:categories,category_name,'.$id,'category_name',
                'status_id' => 'required',
                'font_icon' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }

            $category_name = $request->category_name;
            $font_icon = $request->font_icon;
            $status_id = $request->status_id;
            Category::where('id', $id)->update(['category_name' => $category_name, 'font_icon' => $font_icon,  'status_id' => $status_id]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function sub_category (){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $data = array(
                'page_title' => 'Sub Category',
            );
            $categories = Category::where('status_id', 1)->get();
            $subcategories = Subcategory::get();
            return view('admin.ecommerce.sub_category', compact('categories','subcategories'))->with($data);
        }else{
            return redirect()->back();
        }
    }

    function save_sub_category (Request $request){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $rules = array(
                'category_name' => 'required|unique:subcategories',
                'category_id' => 'required|exists:categories,id',
                'slug' => 'required',
                'meta_title' => 'required',
                'meta_keywords' => 'required',
                'meta_description' => 'required',
                'commission' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            DB::beginTransaction();
            try{
            $slug = Str::slug($request->slug, '-');
            $category_name = $request->category_name;
            $category_id = $request->category_id;
            $meta_title = $request->meta_title;
            $meta_keywords = $request->meta_keywords;
            $meta_description = $request->meta_description;
            $commission = $request->commission;
            $subcategories = Subcategory::where('slug', $slug)->first();
            if ($subcategories){
                return Response()->json(['status' => 'failure', 'message' => 'Sorry slug (URL) already exists']);
            }else{
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                Subcategory::insertGetId([
                    'user_id' => Auth::id(),
                    'category_id' => $category_id,
                    'category_name' => $category_name,
                    'commission' => $commission,
                    'slug' => $slug,
                    'meta_title' => $meta_title,
                    'meta_keywords' => $meta_keywords,
                    'meta_description' => $meta_description,
                    'created_at' => $ctime,
                    'status_id' => 1,
                    'company_id' => Auth::User()->company_id,
                ]);
                DB::commit();
                return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
            }
            }catch (\Exception $ex) {
                DB::rollback();
                // throw $ex;
                return response()->json(['status' => 'failure', 'message' => 'something went wrong']);
            }

        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function view_sub_category (Request $request){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $id = $request->id;
            $subcategories = Subcategory::find($id);
            if ($subcategories){
                $details = array(
                    'id' => $subcategories->id,
                    'category_id' => $subcategories->category_id,
                    'category_name' => $subcategories->category_name,
                    'slug' => $subcategories->slug,
                    'meta_title' => $subcategories->meta_title,
                    'meta_keywords' => $subcategories->meta_keywords,
                    'meta_description' => $subcategories->meta_description,
                    'status_id' => $subcategories->status_id,
                    'commission' => $subcategories->commission,
                );
                return Response()->json(['status' => 'success', 'message' => 'successful..!', 'details' => $details]);
            }else{
                return Response()->json(['status' => 'failure', 'message' => 'Sub category not found']);
            }
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_sub_category (Request $request){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $id = $request->id;
            $rules = array(
                'category_name' => 'required|unique:categories,category_name,'.$id,'category_name',
                'status_id' => 'required',
                'category_id' => 'required',
                'slug' => 'required',
                'meta_title' => 'required',
                'meta_keywords' => 'required',
                'meta_description' => 'required',
                'commission' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            DB::beginTransaction();
            try{
                $slug = Str::slug($request->slug, '-');
                $category_name = $request->category_name;
                $category_id = $request->category_id;
                $status_id = $request->status_id;
                $meta_title = $request->meta_title;
                $meta_keywords = $request->meta_keywords;
                $meta_description = $request->meta_description;
                $commission = $request->commission;
                $subcategories = Subcategory::where('slug', $slug)->whereNotIn('id', [$id])->first();
                if ($subcategories){
                    return Response()->json(['status' => 'failure', 'message' => 'Sorry slug (URL) already exists']);
                }else{
                    Subcategory::where('id', $id)->update([
                        'category_id' => $category_id,
                        'category_name' => $category_name,
                        'slug' => $slug,
                        'status_id' => $status_id,
                        'meta_title' => $meta_title,
                        'meta_keywords' => $meta_keywords,
                        'meta_description' => $meta_description,
                        'commission' => $commission,
                    ]);
                }
                DB::commit();
                return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
            }catch (\Exception $ex) {
            DB::rollback();
            // throw $ex;
            return response()->json(['status' => 'failure', 'message' => 'something went wrong']);
        }

        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function shopping_banners (){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $data = array(
                'page_title' => 'Ecommerce Banners',
            );
            $shoppingbanners = Shoppingbanner::orderBy('id', 'DESC')->get();
            return view('admin.ecommerce.shopping_banners', compact('shoppingbanners'))->with($data);
        }else{
            return redirect()->back();
        }
    }

    function store_shopping_banners (Request $request){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $this->validate($request, [
                'banners' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
            ]);
            $company_name = Auth::User()->company->company_website;
            $banners = $request->banners;
            $photo = base64_encode(file_get_contents($banners));
            $url = "https://cdn.bceres.com/api/file/v1/ecommerce";
            $api_request_parameters = array(
                'image' => $photo,
                'name' => $company_name,
                'type' => 1,
            );
            $method = 'POST';
            $header = ["Accept:application/json"];
            $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
            $res = json_decode($response);
            if ($res->status == 'success'){
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                Shoppingbanner::insertGetId([
                    'user_id' => Auth::id(),
                    'banners' => $res->image_url,
                    'created_at' => $ctime,
                    'company_id' => Auth::User()->company_id,
                    'status_id' => 1,
                ]);
                \Session::flash('success', 'Banner successfully uploaded!' );
                return redirect()->back();
            }else{
                \Session::flash('failure', $res->message);
                return redirect()->back();
            }
        }else{
            return redirect()->back();
        }
    }

    function delete_shopping_banners (Request $request){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            Shoppingbanner::where('id', $request->id)->delete();
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }
}
