<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Sitesetting;
use Illuminate\Support\Facades\Auth;
use Config;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $sitesettings = Sitesetting::find(1);
        if ($sitesettings){
            $config = array(
                'driver'     => $sitesettings->mail_transport,
                'host'       => $sitesettings->mail_host,
                'port'       => $sitesettings->mail_port,
                'username'   => $sitesettings->mail_username,
                'password'   => $sitesettings->mail_password,
                'encryption' => $sitesettings->mail_encryption,
                'from'       => array('address' => $sitesettings->mail_from, 'name' => $sitesettings->brand_name),
                'sendmail'   => '/usr/sbin/sendmail -bs',
                'pretend'    => false,
            );
            Config::set('mail', $config);
        }
    }
}
