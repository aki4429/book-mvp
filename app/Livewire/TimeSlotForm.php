<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TimeSlot;
use App\Models\Setting;
use Carbon\Carbon;

class TimeSlotForm extends Component
{
    public $timeslotId;
    public $date;
    public $start_time;
    public $end_time;
    public $capacity;
    public $available = true;
    public $existingSlots = [];

    protected $listeners = ['openTimeSlotForm'];

    public function openTimeSlotForm($date = null, $timeslotId = null)
    {
        \Log::info('TimeSlotForm::openTimeSlotForm called', ['date' => $date, 'timeslotId' => $timeslotId]);

        // フォームをリセット
        $this->reset(['timeslotId', 'date', 'start_time', 'end_time', 'capacity', 'available']);
        $this->available = true; // デフォルト値を設定

        $this->timeslotId = $timeslotId;

        if ($timeslotId) {
            // 既存の予約枠を編集 - 予約枠のデータを読み込む
            $slot = TimeSlot::find($timeslotId);
            if ($slot) {
                $this->date = $slot->date->format('Y-m-d');
                $this->start_time = $slot->start_time;
                $this->end_time = $slot->end_time;
                $this->capacity = $slot->capacity;
                $this->available = $slot->available;
            }
        } elseif ($date) {
            // 新規予約枠作成の場合、選択された日付をセット
            $this->date = $date;
            $this->start_time = '09:00'; // デフォルト開始時間
            $this->end_time = '10:00';   // デフォルト終了時間
            $this->capacity = 1;         // デフォルト定員
            $this->available = true;     // デフォルト利用可能
        }

        // その日の既存の予約枠一覧を取得（フォームに表示するため）
        if ($this->date) {
            $this->existingSlots = TimeSlot::whereDate('date', $this->date)
                ->orderBy('start_time')
                ->get()
                ->toArray();
        }

        $this->dispatch('show-modal');
    }

    public function editSlot($slotId)
    {
        $slot = TimeSlot::find($slotId);
        if ($slot) {
            $this->timeslotId = $slot->id;
            $this->date = $slot->date->format('Y-m-d');
            $this->start_time = $slot->start_time;
            $this->end_time = $slot->end_time;
            $this->capacity = $slot->capacity;
            $this->available = $slot->available;
        }
    }

    public function save()
    {
        // 予約可能日のチェック
        $reservationAdvanceDays = Setting::get('reservation_advance_days', 0);
        $reservationStartDate = Carbon::today()->addDays($reservationAdvanceDays);
        $selectedDate = Carbon::parse($this->date);

        $this->validate([
            'date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($reservationStartDate) {
                    if (Carbon::parse($value)->lt($reservationStartDate)) {
                        $fail('選択された日付は予約可能日より前です。');
                    }
                },
            ],
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'capacity' => 'required|integer|min:1',
        ]);

        TimeSlot::updateOrCreate(
            ['id' => $this->timeslotId],
            [
                'date' => $this->date,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'capacity' => $this->capacity,
                'available' => $this->available,
            ]
        );

        $this->dispatch('close-modal');
        $this->dispatch('refreshCalendar'); // カレンダーの更新をトリガー
        $this->reset();  // フォームリセット
    }

    public function render()
    {
        return view('livewire.time-slot-form');
    }
}
