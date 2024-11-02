<?php

namespace App\Http\Middleware;

use App\UserLogin;
use Brian2694\Toastr\Facades\Toastr;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class CheckUserActiveStatus
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && !Session::has('impersonated')) {
            if (Auth::user()->status == 0) {
                Auth::logout();
                Toastr::error(trans('frontend.Your account has been disabled'), trans('common.Failed'));
                return redirect('/');
            }

            $time = (int)Settings('device_limit_time');
            if (Auth::check() && Auth::user()->role_id == 3 && $time != 0) {
                $total = UserLogin::where([
                    'user_id' => Auth::id()])->count();
                if ($total > 1) {
                    $login = UserLogin::where([
                        'user_id' => Auth::id(),
                        'token' => session('login_token'),
                        'status' => 1
                    ])->count();
                    if (!$login) {
                        Auth::logout();
                        Toastr::error(trans('frontend.Logout from other device'), trans('common.Failed'));
                        return redirect('/');
                    }
                }
            }

            if (isModuleActive('TwoFA') && Settings('enable_two_fa')) {
                $currentRoute = Route::currentRouteName();
                $allowRoutes = [
                    '2fa',
//                    'users.settings',
//                    'users.2Fa.update',
                    'logout'
                ];
                $user = Auth::user();
                if (!in_array($currentRoute, $allowRoutes) && $user->two_step_verification == 2) {

                    $authenticator = app(Authenticator::class)->boot($request);
//
                    if (!$authenticator->isAuthenticated()) {
                        return $authenticator->makeRequestOneTimePasswordResponse();
                    }
                }
            }
        }

        return $next($request);
    }
}
