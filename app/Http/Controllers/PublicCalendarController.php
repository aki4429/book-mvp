<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TimeSlot;
use App\Models\Setting;

class PublicCalendarController extends Controller
{
    /**
     * 顧客用カレンダーを表示
     */
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        
        return view('calendar.public-simple', [
            'calendarData' => $this->getCalendarData($year, $month),
            'currentYear' => $year,
            'currentMonth' => $month,
        ]);
    }

    /**
     * Ajax用のカレンダーデータを取得
     */
    public function getCalendarData($year, $month)
    {
        $first = Carbon::create($year, $month, 1);
        $start = $first->copy()->startOfWeek(Carbon::SUNDAY);
        $end = $first->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        // 週の配列を生成
        $weeks = [];
        $week = [];
        $day = $start->copy();

        while ($day <= $end) {
            $week[] = $day->copy();
            if ($day->dayOfWeek === Carbon::SATURDAY) {
                $weeks[] = $week;
                $week = [];
            }
            $day->addDay();
        }

        // タイムスロットを取得
        $slots = TimeSlot::whereBetween('date', [$start, $end])
            ->with(['reservations' => function($query) {
                $query->where('status', 'confirmed');
            }])
            ->select('id', 'date', 'start_time', 'end_time', 'available', 'capacity', 'service_id')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(function ($slot) {
                // 実際の空席状況を計算
                $confirmedReservations = $slot->reservations->count();
                $slot->available = $slot->available && ($confirmedReservations < $slot->capacity);
                return $slot;
            })
            ->groupBy(function ($slot) {
                return $slot->date->format('Y-m-d');
            });

        // 予約可能開始日を取得
        $reservationAdvanceDays = Setting::get('reservation_advance_days', 0);
        $reservationStartDate = Carbon::today()->addDays($reservationAdvanceDays);

        return [
            'weeks' => $weeks,
            'currentMonth' => $first,
            'slots' => $slots,
            'reservationStartDate' => $reservationStartDate,
            'year' => $year,
            'month' => $month,
        ];
    }

    /**
     * Ajax用のカレンダー月変更
     */
    public function changeMonth(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        
        $calendarData = $this->getCalendarData($year, $month);
        
        return response()->json([
            'success' => true,
            'html' => view('calendar.partials.calendar-grid', $calendarData)->render(),
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * 特定日のタイムスロット取得
     */
    public function getDaySlots(Request $request)
    {
        $date = $request->get('date');
        $slots = TimeSlot::where('date', $date)
            ->with(['reservations' => function($query) {
                $query->where('status', 'confirmed');
            }])
            ->orderBy('start_time')
            ->get()
            ->map(function ($slot) {
                // 実際の空席状況を計算
                $confirmedReservations = $slot->reservations->count();
                $slot->available = $slot->available && ($confirmedReservations < $slot->capacity);
                return $slot;
            });

        return response()->json([
            'success' => true,
            'slots' => $slots,
            'html' => view('calendar.partials.day-slots', compact('slots', 'date'))->render(),
        ]);
    }
}
