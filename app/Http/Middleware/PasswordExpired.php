<?php

namespace App\Http\Middleware;

use App\Library\CompanyLibrary;
use Carbon\Carbon;
use Closure;
use App\Models\Sitesetting;

class PasswordExpired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function __construct()
    {
        $company = new CompanyLibrary();
        $this->company_id = $company->company_details()->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->password_expires_days = (empty($sitesettings)) ? 30 : $sitesettings->password_expires_days;
    }

    public function handle($request, Closure $next)
    {
        $user = $request->user();
        $password_changed_at = new Carbon(($user->password_changed_at) ? $user->password_changed_at : $user->created_at);
        if (Carbon::now()->diffInDays($password_changed_at) >= $this->password_expires_days) {
            return redirect()->route('password.expired');
        }
        return $next($request);
    }
}
