<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Library\CompanyLibrary;
use App\Models\Frontbanner;
use App\Models\Navigation;
use App\Models\Websitecontent;
use App\Models\Contactenquiry;
use Validator;
use App\Models\State;
use App\Models\Numberdata;
use App\Models\Report;
use App\Models\Provider;
use App\Models\Company;
use App\Models\User;
use App\Models\Product;
use App\Models\Sitesetting;
use Mail;
use Carbon\Carbon;
use Helpers;
use Illuminate\Support\Facades\Redirect;

class FrontController extends Controller
{
    //
    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        if ($sitesettings){
            $this->brand_name = $sitesettings->brand_name;
            $this->sms = $sitesettings->sms;
            $this->sms_key = $sitesettings->sms_key;
            $this->whatsapp = $sitesettings->whatsapp;
            $this->whatsapp_key = $sitesettings->whatsapp_key;
            $this->send_mail = $sitesettings->send_mail;
            $this->mail_from = $sitesettings->mail_from;
        }else{
            $this->brand_name = "";
            $this->sms = "";
            $this->sms_key = "";
            $this->whatsapp = "";
            $this->whatsapp_key = "";
            $this->send_mail = "";
            $this->mail_from = "";
        }
    }

    function welcome (){
        $library = new CompanyLibrary();
        $companydetails = $library->get_company_detail();
        $company_id = $companydetails->id;
        $frontbanner = Frontbanner::where('company_id', $company_id)->where('type', 'WEB')->get();
        return View('front.template1.welcome', compact('frontbanner'));
    }

    function contact_us (){
        return View('front.template1.contact_us');
    }

    function dynamic_page ($company_id, $navigation_slug){
       $navigation = Navigation::where('company_id', $company_id)->where('navigation_slug', $navigation_slug)->first();
       if ($navigation){
           $navigation_id = $navigation->id;
           $websitecontent = Websitecontent::where('navigation_id', $navigation_id)->first();
           if ($websitecontent){
               $data = array(
                   'navigation_name' => $navigation->navigation_name,
                    'content' => $websitecontent->content,
               );
               return View('front.template1.dynamic_page')->with($data);
           }else{
               return Redirect::back();
           }

       }else{
           return Redirect::back();
       }
    }

    function save_contact_enquiry (Request $request){
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'mobile_number' => 'required|digits:10',
            'message' => 'required',
        ]);
        $name = $request->name;
        $email = $request->email;
        $mobile_number = $request->mobile_number;
        $message = $request->message;
        $now = new \DateTime();
        $ctime = $now->format('Y-m-d H:i:s');
        Contactenquiry::insertGetId([
            'name' => $name,
            'email' => $email,
            'mobile_number' => $mobile_number,
            'message' => $message,
            'created_at' => $ctime,
            'status_id' => 3,
        ]);
   /*     $data = [
            'name' => $name,
            'email' => $email,
            'mobile_number' => $mobile_number,
            'message' => $message,
            'Date' => $ctime,
            'website' => url(),
        ];
        Mail::send('mail.contact_enquiry', $data, function ($m) use ($email) {
            $m->to('neeraj@pay2all.in', 'Contact Enquiry')->subject('Contact Enquiry');
            $m->from($this->mail_from, 'Contact Enquiry');
        });*/
        \Session::flash('success', 'Thank you, your enquiry has been submitted successfully');
        return redirect()->back();
    }


}
