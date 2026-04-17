<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Brand;
use DB;
use Str;

class BrandController extends Controller
{

    function brands (){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $data = array(
                'page_title' => 'Brands',
            );
            $brands = Brand::get();
            return view('admin.ecommerce.brands', compact('brands'))->with($data);
        }else{
            return redirect()->back();
        }
    }

    function save_brands (Request $request){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $rules = array(
                'brand_name' => 'required|unique:brands',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $brand_name = $request->brand_name;
            $slug = Str::slug($brand_name, '-');
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            DB::beginTransaction();
            try{
                Brand::insertGetId([
                    'user_id' => Auth::id(),
                    'brand_name' => $brand_name,
                    'slug' => $slug,
                    'created_at' => $ctime,
                    'company_id' => Auth::User()->company_id,
                    'status_id' => 1,
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

    function view_brand (Request $request){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $id = $request->id;
            $brands = Brand::find($id);
            if ($brands){
                $details = array(
                    'id' => $brands->id,
                    'brand_name' => $brands->brand_name,
                    'status_id' => $brands->status_id,
                );
                return Response()->json(['status' => 'success', 'message' => 'Successful..', 'details' => $details]);
            }else{
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_brands (Request $request){
        if (Auth::User()->role_id == 1 && Auth::User()->company->ecommerce == 1){
            $id = $request->id;
            $rules = array(
                'brand_name' => 'required|unique:brands,brand_name,'.$id,'brand_name',
                'status_id' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $brand_name = $request->brand_name;
            $status_id = $request->status_id;
            Brand::where('id', $id)->update(['brand_name' => $brand_name, 'status_id' => $status_id]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        }else{
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }
}
