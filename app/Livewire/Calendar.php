<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\TimeSlot;

class Calendar extends Component
{
    public $year;
    public $month;
    public $selectedDate = null; // ← 追加

    public function selectDate($date) // ← 追加
    {
        $this->selectedDate = $date;
    }

    public function mount()
    {
        $this->year  = now()->year;
        $this->month = now()->month;
    }

    public function prevMonth()
    {
        \Log::info('=== prevMonth START ===');
        \Log::info('prevMonth called', ['current' => $this->year . '-' . $this->month]);

        $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = $date->year;
        $this->month = $date->month;

        \Log::info('prevMonth result', ['new' => $this->year . '-' . $this->month]);
        \Log::info('=== prevMonth END ===');

        // 強制的に再レンダリング
        $this->render();
    }

    public function nextMonth()
    {
        \Log::info('=== nextMonth START ===');
        \Log::info('nextMonth called', ['current' => $this->year . '-' . $this->month]);

        $date = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = $date->year;
        $this->month = $date->month;

        \Log::info('nextMonth result', ['new' => $this->year . '-' . $this->month]);
        \Log::info('=== nextMonth END ===');

        // 強制的に再レンダリング
        $this->render();
    }

    public function render()
    {
        $first  = Carbon::create($this->year, $this->month, 1);
        $start  = $first->copy()->startOfWeek(Carbon::SUNDAY);
        $end    = $first->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        // --- CarbonPeriod を使わない -----------------
        $weeks = [];
        $week  = [];
        $day   = $start->copy();

        while ($day <= $end) {
            $week[] = $day->copy();                 // セル追加

            if ($day->dayOfWeek === Carbon::SATURDAY) {
                $weeks[] = $week;                   // 土曜で 1 週確定
                $week = [];
            }
            $day->addDay();                         // 1 日進める
        }
        // --------------------------------------------
        // $slots = TimeSlot::whereBetween('date', [$start, $end])
        //     ->get()
        //     ->keyBy(fn ($slot) => $slot->date->format('Y-m-d'));

        $slots = TimeSlot::whereBetween('date', [$start, $end])
    ->orderBy('date')
    ->orderBy('start_time')
    ->get()
    ->groupBy(function ($slot) {
        return $slot->date->format('Y-m-d');
    });



        return view('livewire.calendar', [
            'weeks'        => $weeks,
            'currentMonth' => $first,
            'year'         => $this->year,
            'month'        => $this->month,
            'slots' => $slots,
            'selectedDate'  => $this->selectedDate, // ← 追加
        ]);
    }



}
