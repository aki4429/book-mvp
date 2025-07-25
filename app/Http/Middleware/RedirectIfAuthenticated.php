<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // 顧客ガードの場合は顧客ダッシュボードへリダイレクト
                if ($guard === 'customer') {
                    return redirect()->route('customer.dashboard');
                }
                // 管理者ガードの場合は管理者ダッシュボードへリダイレクト
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
