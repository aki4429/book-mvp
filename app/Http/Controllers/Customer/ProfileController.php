<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:customer');
    }

    /**
     * プロフィール表示
     */
    public function show()
    {
        $customer = Auth::guard('customer')->user();
        return view('customer.profile.show', compact('customer'));
    }

    /**
     * プロフィール編集フォーム表示
     */
    public function edit()
    {
        $customer = Auth::guard('customer')->user();
        return view('customer.profile.edit', compact('customer'));
    }

    /**
     * プロフィール更新処理
     */
    public function update(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($customer->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // パスワード変更が要求された場合の現在のパスワード確認
        if ($request->filled('password')) {
            if (!$request->filled('current_password')) {
                return back()->withErrors(['current_password' => '新しいパスワードを設定するには現在のパスワードが必要です。']);
            }
            
            if (!Hash::check($request->current_password, $customer->password)) {
                return back()->withErrors(['current_password' => '現在のパスワードが正しくありません。']);
            }
        }

        // プロフィール情報の更新
        $customer->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        // パスワードの更新（指定された場合のみ）
        if ($request->filled('password')) {
            $customer->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('customer.profile.show')
            ->with('success', 'プロフィールを更新しました。');
    }
}
