<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Mail\ReservationConfirmed;
use App\Mail\ReservationNotification;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // カレンダー表示用のビューを返す
        return view('admin.reservations.calendar');
    }

    /**
     * Display a listing of the resource in list format.
     */
    public function list()
    {
        $reservations = Reservation::with(['customer', 'timeSlot'])
            ->latest('created_at')
            ->paginate(20);   // 20件ずつページネーション

        return view('admin.reservations.index', compact('reservations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $selectedTimeSlot = null;
        if ($request->has('slot_id')) {
            $selectedTimeSlot = \App\Models\TimeSlot::find($request->get('slot_id'));
        }

        return view('admin.reservations.create', [
            'customers'   => \App\Models\Customer::orderBy('name')->get(),
            'timeSlots'   => \App\Models\TimeSlot::orderBy('date')->orderBy('start_time')->get(),
            'statuses'    => ['pending','confirmed','canceled','completed'],
            'selectedTimeSlot' => $selectedTimeSlot,
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 新規顧客作成か既存顧客選択かを判定
        if ($request->filled('new_customer')) {
            // 新規顧客の場合
            $customerData = $request->validate([
                'customer_name' => ['required', 'string', 'max:255'],
                'customer_email' => ['required', 'email', 'max:255', 'unique:customers,email'],
                'customer_phone' => ['nullable', 'string', 'max:255'],
            ]);

            $customer = \App\Models\Customer::create([
                'name' => $customerData['customer_name'],
                'email' => $customerData['customer_email'],
                'phone' => $customerData['customer_phone'],
            ]);

            $customerId = $customer->id;
        } else {
            // 既存顧客の場合
            $request->validate([
                'customer_id' => ['required', 'exists:customers,id'],
            ]);
            $customerId = $request->customer_id;
        }

        $data = $request->validate([
            'time_slot_id' => ['required','exists:time_slots,id'],
            'status'       => ['required','in:pending,confirmed,canceled,completed'],
            'notes'        => ['nullable','string'],
        ]);

        $data['customer_id'] = $customerId;
        $data['created_by'] = auth()->id();

        $reservation = \App\Models\Reservation::create($data);

        // 顧客へのサンクスメール
        if ($reservation->customer->email) {
            Mail::to($reservation->customer->email)->send(new ReservationConfirmed($reservation));
        }

        // 管理者への通知メール
        if (config('mail.admin_address')) {
            Mail::to(config('mail.admin_address'))->send(new ReservationNotification($reservation));
        }

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', '予約を登録しました');
    }


    /**
     * Display the specified resource.
     */
    // public function show(string $id)
    // {
    //
    // }
    public function show(Reservation $reservation)
    {
        $reservation->load(['customer','timeSlot']);

        return view('admin.reservations.show', compact('reservation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(\App\Models\Reservation $reservation)
    {
        return view('admin.reservations.edit', [
            'reservation' => $reservation->load(['customer','timeSlot']),
            'customers'   => \App\Models\Customer::orderBy('name')->get(),
            'timeSlots'   => \App\Models\TimeSlot::orderBy('date')->orderBy('start_time')->get(),
            'statuses'    => ['pending','confirmed','canceled','completed'],
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, \App\Models\Reservation $reservation)
    {
        $data = $request->validate([
            'customer_id'  => ['required','exists:customers,id'],
            'time_slot_id' => ['required','exists:time_slots,id'],
            'status'       => ['required','in:pending,confirmed,canceled,completed'],
            'notes'        => ['nullable','string'],
        ]);

        $reservation->update($data);

        return redirect()
            ->route('admin.reservations.show', $reservation)
            ->with('success', '予約を更新しました');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\Reservation $reservation)
    {
        $reservation->delete();

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', '予約を削除しました');
    }

}
