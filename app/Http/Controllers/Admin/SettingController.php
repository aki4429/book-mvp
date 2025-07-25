<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * 設定画面を表示
     */
    public function index()
    {
        $settings = [
            'reservation_advance_days' => Setting::get('reservation_advance_days', 0),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * 設定を更新
     */
    public function update(Request $request)
    {
        $request->validate([
            'reservation_advance_days' => 'required|integer|min:0|max:365',
        ]);

        Setting::set(
            'reservation_advance_days',
            $request->reservation_advance_days,
            'integer',
            '予約可能開始日（今日から何日後から予約可能にするか）'
        );

        return redirect()
            ->route('admin.settings.index')
            ->with('success', '設定を更新しました。');
    }
}
