<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ログインしているかチェック
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        // 管理者権限があるかチェック
        $user = Auth::user();
        if (!$user->is_admin) {
            abort(403, 'このページにアクセスする権限がありません。');
        }

        return $next($request);
    }
}
