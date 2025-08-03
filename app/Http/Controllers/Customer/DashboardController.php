<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:customer');
    }

    /**
     * 顧客ダッシュボード表示
     */
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        
        // 今後の予約（日付順）
        $upcomingReservations = $customer->reservations()
            ->with(['timeSlot'])
            ->whereHas('timeSlot', function($query) {
                $query->where('date', '>=', today());
            })
            ->join('time_slots', 'reservations.time_slot_id', '=', 'time_slots.id')
            ->orderBy('time_slots.date', 'asc')
            ->orderBy('time_slots.start_time', 'asc')
            ->select('reservations.*')
            ->limit(5)
            ->get();

        // 過去の予約（最新5件）
        $pastReservations = $customer->reservations()
            ->with(['timeSlot'])
            ->whereHas('timeSlot', function($query) {
                $query->where('date', '<', today());
            })
            ->join('time_slots', 'reservations.time_slot_id', '=', 'time_slots.id')
            ->orderBy('time_slots.date', 'desc')
            ->orderBy('time_slots.start_time', 'desc')
            ->select('reservations.*')
            ->limit(5)
            ->get();

        return view('customer.dashboard', compact('customer', 'upcomingReservations', 'pastReservations'));
    }
}
