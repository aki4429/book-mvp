<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Reservation;
use App\Models\User;
use App\Models\TimeSlot;

class ReservationController extends Controller
{
    public function create(Request $request)
    {
        // ログインチェック - 未ログインの場合はログイン画面にリダイレクト
        if (!auth()->check()) {
            $slotId = $request->input('slot_id');
            // slot_idをセッションに保存してログイン後に復元
            if ($slotId) {
                session(['intended_slot_id' => $slotId]);
            }
            return redirect()->route('auth.choice')->with('info', '予約をするにはログインまたは新規登録が必要です。');
        }

        $slotId = $request->input('slot_id') ?: session('intended_slot_id');
        
        if (!$slotId) {
            return redirect()->route('calendar.public')->with('error', '時間枠が選択されていません。');
        }

        // セッションから削除
        session()->forget('intended_slot_id');

        $slot = \App\Models\TimeSlot::findOrFail($slotId);
        
        // 時間枠が利用可能かチェック
        if (!$slot->available) {
            return redirect()->route('calendar.public')->with('error', 'この時間枠は既に予約されています。');
        }

        return view('reservations.create', [
            'slot' => $slot,
            'reservation' => new \App\Models\Reservation(),
        ]);
    }
    public function store(Request $request)
    {
        //     $data = $request->validate([
        //         'customer_id'  => ['required', 'exists:customers,id'],
        //         'time_slot_id' => ['required', 'exists:time_slots,id'],
        //         'status'       => ['required', 'in:pending,confirmed,canceled,completed'],
        //         'notes'        => ['nullable', 'string'],
        //     ]);

        //     Reservation::create($data);

        //     return redirect()
        //         ->route('reservations.index')
        //         ->with('success', '予約を作成しました');
        // }
        $data = $request->validate([
        'name'         => ['required', 'string', 'max:255'],
        'email'        => ['required', 'email', 'max:255'],
         'phone'        => ['required', 'string', 'max:20'],  // ← 追加！
        'time_slot_id' => ['required', 'exists:time_slots,id'],
        'notes'        => ['nullable', 'string'],
    ]);

        // 既存のユーザーがいれば再利用、いなければ作成
        $user = User::firstOrCreate(
            ['email' => $data['email']],
            ['name' => $data['name'], 'phone' => $data['phone'], 'is_admin' => false]
        );

        // TimeSlotを取得
        $slot = TimeSlot::findOrFail($data['time_slot_id']);

        $reservation = Reservation::create([
            'customer_id'  => $user->id,
            'time_slot_id' => $data['time_slot_id'],
            'status'       => 'confirmed',
            'notes'        => $data['notes'] ?? null,
        ]);

        // 時間枠の空席状況を更新
        $slot->checkAndUpdateAvailability();

        return redirect()->route('calendar.public')->with('success', '予約が完了しました！確認メールをお送りしました。');
    }

}
