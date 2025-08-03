<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * 顧客登録・ログインフォーム表示
     */
    public function showLoginForm()
    {
        return view('customer.auth.login');
    }

    /**
     * 顧客登録処理
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $customer = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_admin' => false,
        ]);

        Auth::guard('customer')->login($customer);

        return redirect()->intended(route('customer.dashboard'))
            ->with('success', 'アカウントが作成されました。');
    }

    /**
     * 顧客ログイン処理
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // 顧客のみログイン可能（is_admin = false）
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'is_admin' => false,
        ];

        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('customer.dashboard'))
                ->with('success', 'ログインしました。');
        }

        throw ValidationException::withMessages([
            'email' => __('メールアドレスまたはパスワードが間違っています。'),
        ]);
    }

    /**
     * 顧客ログアウト処理
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login')
            ->with('success', 'ログアウトしました。');
    }
}
