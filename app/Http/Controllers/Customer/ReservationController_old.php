<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\TimeSlot;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ReservationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:customer');
    }

    /**
     * 予約一覧表示
     */
    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $status = $request->get('status', 'all');
        
        $query = $customer->reservations()->with(['timeSlot']);
        
        // ステータスフィルター
        if ($status === 'upcoming') {
            $query->whereHas('timeSlot', function($q) {
                $q->where('date', '>=', today());
            });
        } elseif ($status === 'past') {
            $query->whereHas('timeSlot', function($q) {
                $q->where('date', '<', today());
            });
        }
        
        // 日付順でソート
        $reservations = $query->join('time_slots', 'reservations.time_slot_id', '=', 'time_slots.id')
            ->orderBy('time_slots.date', 'desc')
            ->orderBy('time_slots.start_time', 'desc')
            ->select('reservations.*')
            ->paginate(10);
            
        return view('customer.reservations.index', compact('reservations', 'status'));
    }

    /**
     * 予約作成フォーム表示
     */
    public function create(Request $request)
    {
        $slotId = $request->get('slot_id');
        $timeSlot = null;

        if ($slotId) {
            $timeSlot = TimeSlot::find($slotId);
            if (!$timeSlot || $timeSlot->capacity <= $timeSlot->reservations->count()) {
                return redirect()->route('calendar.public')
                    ->with('error', '選択された時間枠は予約できません。');
            }
        }

        return view('customer.reservations.create', compact('timeSlot'));
    }

    /**
     * 予約作成処理
     */
    public function store(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'time_slot_id' => 'required|exists:time_slots,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $timeSlot = TimeSlot::find($request->time_slot_id);

        // 空き容量チェック
        if ($timeSlot->capacity <= $timeSlot->reservations->count()) {
            return back()->with('error', 'この時間枠は満席です。');
        }

        // 重複予約チェック
        $existingReservation = Reservation::where('customer_id', $customer->id)
            ->where('time_slot_id', $timeSlot->id)
            ->first();

        if ($existingReservation) {
            return back()->with('error', 'この時間枠は既に予約済みです。');
        }

        Reservation::create([
            'customer_id' => $customer->id,
            'time_slot_id' => $timeSlot->id,
            'status' => 'confirmed',
            'notes' => $request->notes,
        ]);

        return redirect()->route('customer.reservations.index')
            ->with('success', '予約が完了しました。');
    }

    /**
     * 顧客の予約一覧
     */
    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $status = $request->get('status', 'all');
        
        $query = $customer->reservations()->with(['timeSlot']);
        
        // ステータスフィルター
        if ($status === 'upcoming') {
            $query->whereHas('timeSlot', function($q) {
                $q->where('date', '>=', today());
            });
        } elseif ($status === 'past') {
            $query->whereHas('timeSlot', function($q) {
                $q->where('date', '<', today());
            });
        }
        
        // 日付順でソート
        $reservations = $query->join('time_slots', 'reservations.time_slot_id', '=', 'time_slots.id')
            ->orderBy('time_slots.date', 'desc')
            ->orderBy('time_slots.start_time', 'desc')
            ->select('reservations.*')
            ->paginate(10);
            
        return view('customer.reservations.index', compact('reservations', 'status'));
    }

    /**
     * 予約詳細表示
     */
    public function show(Reservation $reservation)
    {
        $customer = Auth::guard('customer')->user();

        // 自分の予約かチェック
        if ($reservation->customer_id !== $customer->id) {
            abort(403, 'この予約にアクセスする権限がありません。');
        }

        return view('customer.reservations.show', compact('reservation'));
    }

    /**
     * 予約キャンセル処理
     */
    public function cancel(Reservation $reservation)
    {
        $customer = Auth::guard('customer')->user();

        // 自分の予約かチェック
        if ($reservation->customer_id !== $customer->id) {
            abort(403, 'この予約をキャンセルする権限がありません。');
        }

        // 既にキャンセル済みかチェック
        if ($reservation->status === 'canceled') {
            return back()->with('error', 'この予約は既にキャンセル済みです。');
        }

        $reservation->update(['status' => 'canceled']);

        return redirect()->route('customer.reservations.index')
            ->with('success', '予約をキャンセルしました。');
    }
}
