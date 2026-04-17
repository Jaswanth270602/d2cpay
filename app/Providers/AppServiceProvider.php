<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Sitesetting;
use View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $host = !empty($_SERVER['HTTP_HOST']) ? trim($_SERVER['HTTP_HOST']) : '';
        $hostOnly = preg_replace('/:\d+$/', '', $host);

        $candidates = array_values(array_filter(array_unique([
            $host,
            $hostOnly,
            'localhost:8000',
            '127.0.0.1:8000',
            'localhost',
            '127.0.0.1',
            'localhost:8888',
        ])));

        $company = null;
        if (!empty($candidates)) {
            $company = Company::whereIn('company_website', $candidates)
                ->where('status_id', 1)
                ->first();
        }

        if (empty($company)) {
            $company = Company::where('status_id', 1)->first();
        }

        if (!empty($company)) {
            $sitesettings = Sitesetting::where('company_id', $company->id)->first();
            View::share('company_id', $company->id);
            View::share('company_name', $company->company_name);
            View::share('company_email', $company->company_email);
            View::share('company_address', $company->company_address);
            View::share('company_address_two', $company->company_address_two);
            View::share('support_number', $company->support_number);
            View::share('whatsapp_number', $company->whatsapp_number);
            View::share('company_website', $company->company_website);
            View::share('company_logo', $company->company_logo);
            View::share('news', $company->news);
            View::share('sender_id', $company->sender_id);
            View::share('color_start', $company->color_start);
            View::share('color_end', $company->color_end);
            View::share('chat_script', $company->chat_script);
            View::share('cdnLink', $company->cdn_link);
            View::share('registration_status', (empty($sitesettings) ? 0 : $sitesettings->registration_status));
            View::share('facebook_link', $company->facebook_link);
            View::share('instagram_link', $company->instagram_link);
            View::share('twitter_link', $company->twitter_link);
            View::share('youtube_link', $company->youtube_link);
        } else {
            View::share('company_id', null);
            View::share('company_name', '');
            View::share('company_email', '');
            View::share('company_address', '');
            View::share('company_address_two', '');
            View::share('support_number', '');
            View::share('whatsapp_number', '');
            View::share('company_website', '');
            View::share('company_logo', '');
            View::share('news', '');
            View::share('sender_id', '');
            View::share('color_start', '');
            View::share('color_end', '');
            View::share('chat_script', '');
            View::share('cdnLink', '');
            View::share('registration_status', 0);
            View::share('facebook_link', '');
            View::share('instagram_link', '');
            View::share('twitter_link', '');
            View::share('youtube_link', '');
        }
    }
}
