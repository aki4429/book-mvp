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
    public $showTimeSlotForm = false;
    public $editingDate = null;

    protected $listeners = ['refreshCalendar'];

    public function refreshCalendar()
    {
        // カレンダーの再レンダリング
        $this->showTimeSlotForm = false;
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
    }

    public function openTimeSlotForm($date = null, $timeslotId = null)
    {
        $this->editingDate = $date;
        $this->showTimeSlotForm = true;
        
        // その日の既存の予約枠を取得（最初の予約枠を編集対象とする）
        $existingSlot = null;
        if ($date) {
            $existingSlot = TimeSlot::whereDate('date', $date)
                ->orderBy('start_time')
                ->first();
        }
        
        $this->dispatch('openTimeSlotForm', $date, $existingSlot ? $existingSlot->id : null);
    }

    public function mount()
    {
        $this->year  = now()->year;
        $this->month = now()->month;
    }

    public function prevMonth()
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = $date->year;
        $this->month = $date->month;
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = $date->year;
        $this->month = $date->month;
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
            'showTimeSlotForm' => $this->showTimeSlotForm,
            'editingDate' => $this->editingDate,
        ]);
    }



}
