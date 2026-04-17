<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\Frontbanner;
use Helpers;
use App\Models\Navigation;
use App\Models\Company;
use App\Models\Websitecontent;
use App\Models\Sitesetting;
use App\Library\BasicLibrary;
use Str;
use App\Library\PermissionLibrary;

class WebsiteMasterController extends Controller
{

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $companies = Company::find($this->company_id);
        $this->cdnLink = (empty($companies)) ? '' : $companies->cdn_link;

        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        if ($sitesettings) {
            $this->brand_name = $sitesettings->brand_name;
            $this->backend_template_id = $sitesettings->backend_template_id;
        } else {
            $this->brand_name = "";
            $this->backend_template_id = 1;
        }
    }

    function home_page_content(Request $request)
    {
        if (Auth::User()->role_id <= 4) {
            $data = array('page_title' => 'Home Page Content');
            return view('admin.website-master.home_page_content')->with($data);
        } else {
            return Redirect::back();
        }
    }

    function dynamic_page(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['dynamic_page_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $navigation = Navigation::where('company_id', Auth::User()->company_id)->get();
            $data = array('page_title' => 'Dynamic Page');
            if ($this->backend_template_id == 1) {
                return view('admin.website-master.dynamic_page', compact('navigation'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.website-master.dynamic_page', compact('navigation'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.website-master.dynamic_page', compact('navigation'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.website-master.dynamic_page', compact('navigation'))->with($data);
            } else {
                return redirect()->back();
            }

        } else {
            return Redirect::back();
        }
    }

    function create_navigation(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['dynamic_page_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $data = array('page_title' => 'Create Navigation');
            if ($this->backend_template_id == 1) {
                return view('admin.website-master.create_navigation')->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.website-master.create_navigation')->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.website-master.create_navigation')->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.website-master.create_navigation')->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function store_navigation(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['dynamic_page_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'title' => 'required',
                'keyword' => 'required',
                'description' => 'required',
                'navigation_name' => 'required',
                'navigation_slug' => 'required',
                'type' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $title = $request->title;
            $keyword = $request->keyword;
            $description = $request->description;
            $navigation_name = $request->navigation_name;
            $navigation_slug = Str::slug($request->navigation_slug, '-');
            $type = $request->type;
            $company_id = Auth::User()->company_id;
            $user_id = Auth::id();
            $checking_slug = Navigation::where('company_id', $company_id)->where('navigation_slug', $navigation_slug)->first();
            if ($checking_slug) {
                return Response()->json(['status' => 'failure', 'message' => 'Slug (Navigation URL) already exists!']);
            } else {
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');
                Navigation::insertGetId([
                    'user_id' => $user_id,
                    'title' => $title,
                    'keyword' => $keyword,
                    'description' => $description,
                    'navigation_name' => $navigation_name,
                    'navigation_slug' => $navigation_slug,
                    'type' => $type,
                    'created_at' => $ctime,
                    'company_id' => $company_id,
                    'status_id' => 1,
                ]);
                return Response()->json(['status' => 'success', 'message' => 'Navigation Successfully Added!']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry!']);
        }
    }

    function edit_navigation($id)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['dynamic_page_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $navigation = Navigation::where('id', $id)->where('company_id', Auth::User()->company_id)->first();
            if ($navigation) {
                $data = array(
                    'page_title' => $navigation->navigation_name . ' Edit',
                    'navigation_id' => $navigation->id,
                    'title' => $navigation->title,
                    'keyword' => $navigation->keyword,
                    'description' => $navigation->description,
                    'navigation_name' => $navigation->navigation_name,
                    'navigation_slug' => $navigation->navigation_slug,
                    'type' => $navigation->type,
                );
                if ($this->backend_template_id == 1) {
                    return view('admin.website-master.edit_navigation')->with($data);
                } elseif ($this->backend_template_id == 2) {
                    return view('themes2.admin.website-master.edit_navigation')->with($data);
                } elseif ($this->backend_template_id == 3) {
                    return view('themes3.admin.website-master.edit_navigation')->with($data);
                } elseif ($this->backend_template_id == 4) {
                    return view('themes4.admin.website-master.edit_navigation')->with($data);
                } else {
                    return redirect()->back();
                }
            } else {
                return Redirect::back();
            }
        } else {
            return Redirect::back();
        }
    }

    function update_navigation(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['dynamic_page_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry!']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'navigation_id' => 'required',
                'title' => 'required',
                'keyword' => 'required',
                'description' => 'required',
                'navigation_name' => 'required',
                'navigation_slug' => 'required',
                'type' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $navigation_id = $request->navigation_id;
            $title = $request->title;
            $keyword = $request->keyword;
            $description = $request->description;
            $navigation_name = $request->navigation_name;
            $navigation_slug = Str::slug($request->navigation_slug, '-');
            $type = $request->type;
            $company_id = Auth::User()->company_id;
            $check_slug = Navigation::where('navigation_slug', $navigation_slug)->where('company_id', $company_id)->whereNotIn('id', [$navigation_id])->first();
            if ($check_slug) {
                return Response()->json(['status' => 'failure', 'message' => 'Slug (Navigation URL) already exists!']);
            } else {
                Navigation::where('id', $navigation_id)->update([
                    'title' => $title,
                    'keyword' => $keyword,
                    'description' => $description,
                    'navigation_name' => $navigation_name,
                    'navigation_slug' => $navigation_slug,
                    'type' => $type,
                ]);
                return Response()->json(['status' => 'success', 'message' => 'Navigation update successfully!']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry!']);
        }
    }

    function delete_navigation(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['dynamic_page_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry!']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            $navigation = Navigation::where('id', $id)->where('company_id', Auth::User()->company_id)->first();
            if ($navigation) {
                Navigation::where('id', $id)->delete();
                return Response()->json(['status' => 'success', 'message' => 'Navigation successfully deleted!']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found!']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry!']);
        }
    }

    function front_banners(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['front_banners_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }

        if (Auth::User()->role_id <= 2) {
            $frontbanner = Frontbanner::where('company_id', Auth::User()->company_id)->get();
            $data = array('page_title' => 'Front Banner');
            if ($this->backend_template_id == 1) {
                return view('admin.website-master.front_banners', compact('frontbanner'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.website-master.front_banners', compact('frontbanner'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.website-master.front_banners', compact('frontbanner'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.website-master.front_banners', compact('frontbanner'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function store_front_banner(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['front_banners_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $company_id = Auth::User()->company_id;
            $company_name = Auth::User()->company->company_website;
            if ($request->banners) {
                $this->validate($request, [
                    'banners' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
                    'type' => 'required',
                ]);

                $type = $request->type;
                $photo = $request->banners;
                $photo = base64_encode(file_get_contents($photo));
                $url = $this->cdnLink . "api/file/v1/company-logo";
                $api_request_parameters = array(
                    'image' => $photo,
                    'name' => $company_name,
                    'type' => 2,
                );
                $method = 'POST';
                $header = ["Accept:application/json"];
                $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
                $res = json_decode($response);
                $status = $res->status;
                if ($status == 'success') {
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    Frontbanner::insert([
                        'user_id' => Auth::id(),
                        'banners' => $res->image_url,
                        'type' => $type,
                        'company_id' => $company_id,
                        'created_at' => $ctime,
                        'status_id' => 1
                    ]);
                    \Session::flash('msg', 'Your Logo Successfully Updated');
                    return redirect()->back();
                } else {
                    \Session::flash('failure', $res->message);
                    return redirect()->back();
                }

            } else {
                \Session::flash('failure', 'Please Select Banner');
                return redirect()->back();
            }

        } else {
            return Redirect::back();
        }
    }

    function delete_front_banner(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['front_banners_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry!']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $id = $request->id;
            Frontbanner::where('id', $id)->delete();
            return Response()->json(['status' => 'success', 'message' => 'Banner successfully deleted!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry!']);
        }
    }

    function add_content($navigation_id)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['dynamic_page_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 2) {
            $navigation = Navigation::where('id', $navigation_id)->where('company_id', Auth::User()->company_id)->first();
            if ($navigation) {
                $content = Websitecontent::where('navigation_id', $navigation_id)->first();
                if (empty($content)) {
                    $now = new \DateTime();
                    $ctime = $now->format('Y-m-d H:i:s');
                    Websitecontent::insertGetId([
                        'user_id' => Auth::id(),
                        'navigation_id' => $navigation_id,
                        'company_id' => Auth::User()->company_id,
                        'created_at' => $ctime,
                        'status_id' => 1,
                    ]);
                }
                $websitecontent = Websitecontent::where('navigation_id', $navigation_id)->first();
                $data = array(
                    'page_title' => $navigation->navigation_name . ' Content',
                    'navigation_id' => $websitecontent->navigation_id,
                    'navigation_name' => $navigation->navigation_name,
                    'navigation_slug' => $navigation->navigation_slug,
                    'content' => $websitecontent->content,
                );
                if ($this->backend_template_id == 1) {
                    return view('admin.website-master.add_content')->with($data);
                } elseif ($this->backend_template_id == 2) {
                    return view('themes2.admin.website-master.add_content')->with($data);
                } elseif ($this->backend_template_id == 3) {
                    return view('themes3.admin.website-master.add_content')->with($data);
                } elseif ($this->backend_template_id == 4) {
                    return view('themes4.admin.website-master.add_content')->with($data);
                } else {
                    return redirect()->back();
                }

            } else {
                return Redirect::back();
            }
        } else {
            return Redirect::back();
        }
    }

    function update_content(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['dynamic_page_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }

        if (Auth::User()->role_id <= 2) {
            $navigation_id = $request->navigation_id;
            $content = $request->nav_content;
            $navigation = Navigation::where('id', $navigation_id)->where('company_id', Auth::User()->company_id)->first();
            if ($navigation) {
                $library = new BasicLibrary();
                $block_string = $library->block_string();
                $onlyconsonants = str_replace($block_string, "", $content);
                Websitecontent::where('navigation_id', $navigation_id)->update([
                    'content' => $onlyconsonants,
                ]);
                \Session::flash('msg', 'Success');
                return redirect()->back();
            } else {
                \Session::flash('failure', 'Sorry');
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }


}
