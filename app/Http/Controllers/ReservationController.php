<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Reservation;
use App\Models\Customer;

class ReservationController extends Controller
{
    public function create(Request $request)
    {
        $slotId = $request->input('slot_id');

        $slot = \App\Models\TimeSlot::findOrFail($slotId);

        return view('reservations.create', [
            'slot' => $slot,
                   'reservation' => new \App\Models\Reservation(),
        'customers'   => \App\Models\Customer::orderBy('name')->get(),
        'timeSlots'   => \App\Models\TimeSlot::orderBy('date')->orderBy('start_time')->get(),
        'statuses'    => ['pending','confirmed','canceled','completed'],
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

        // 既存の顧客がいれば再利用、いなければ作成
        $customer = Customer::firstOrCreate(
            ['email' => $data['email']],
            ['name' => $data['name'], 'phone' => $data['phone']]
        );

        Reservation::create([
            'customer_id'  => $customer->id,
            'time_slot_id' => $data['time_slot_id'],
            'status'       => 'pending',
            'notes'        => $data['notes'] ?? null,
        ]);

        return redirect()->route('reservations.index')->with('success', '予約が完了しました！');
    }

}
