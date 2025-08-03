<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TimeSlot;
use App\Models\Setting;

class AdminCalendarController extends Controller
{
    /**
     * 管理者用カレンダーを表示
     */
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        
        return view('admin.calendar.index', [
            'calendarData' => $this->getCalendarData($year, $month),
            'currentYear' => $year,
            'currentMonth' => $month,
        ]);
    }

    /**
     * カレンダーデータを取得
     */
    private function getCalendarData($year, $month)
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
            ->with('reservations')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(function ($slot) {
                return $slot->date->format('Y-m-d');
            });

        return [
            'weeks' => $weeks,
            'currentMonth' => $first,
            'slots' => $slots,
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
            'html' => view('admin.calendar.partials.calendar-grid', $calendarData)->render(),
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * 特定日のタイムスロット管理
     */
    public function manageDaySlots(Request $request)
    {
        $date = $request->get('date');
        $slots = TimeSlot::where('date', $date)
            ->with('reservations')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'slots' => $slots,
            'html' => view('admin.calendar.partials.day-slots-admin', compact('slots', 'date'))->render(),
        ]);
    }
}
