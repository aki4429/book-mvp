<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TimeSlot;
use Carbon\Carbon;

class TimeSlotManager extends Component
{
    public $selectedDate = null;
    public $timeSlots = [];
    public $isEditing = [];
    public $editingData = [];
    public $showAddForm = false;
    public $newTimeSlot = [
        'start_time' => '',
        'end_time' => '',
        'capacity' => 1,
        'available' => true,
    ];

    protected $listeners = [
        'manageTimeSlots',
        'manageTimeSlots' => 'manageTimeSlots'
    ];

    // Livewireの更新可能なプロパティを明示的に定義
    protected $fillable = [
        'selectedDate',
        'timeSlots',
        'isEditing',
        'editingData',
        'showAddForm',
        'newTimeSlot'
    ];

    public function mount()
    {
        // 初期化処理
        $this->timeSlots = [];
        $this->isEditing = [];
        $this->editingData = [];
        $this->showAddForm = false;
    }

    protected $rules = [
        'editingData.*.start_time' => 'required',
        'editingData.*.end_time' => 'required|after:editingData.*.start_time',
        'editingData.*.capacity' => 'required|integer|min:1',
        'newTimeSlot.start_time' => 'required',
        'newTimeSlot.end_time' => 'required|after:newTimeSlot.start_time',
        'newTimeSlot.capacity' => 'required|integer|min:1',
    ];

    /**
     * 既存の重複した時間枠を検出
     */
    public function findDuplicateTimeSlots()
    {
        $duplicates = [];
        $timeSlots = TimeSlot::whereDate('date', $this->selectedDate)
            ->orderBy('start_time')
            ->get();

        for ($i = 0; $i < count($timeSlots); $i++) {
            for ($j = $i + 1; $j < count($timeSlots); $j++) {
                $slot1 = $timeSlots[$i];
                $slot2 = $timeSlots[$j];

                $start1 = Carbon::parse($slot1->start_time);
                $end1 = Carbon::parse($slot1->end_time);
                $start2 = Carbon::parse($slot2->start_time);
                $end2 = Carbon::parse($slot2->end_time);

                // 重複チェック
                if (($start1 < $end2) && ($start2 < $end1)) {
                    $duplicates[] = [
                        'slot1' => $slot1,
                        'slot2' => $slot2,
                    ];
                }
            }
        }

        return $duplicates;
    }

    /**
     * 重複した時間枠を削除（より新しいものを残す）
     */
    public function removeDuplicateTimeSlots()
    {
        $duplicates = $this->findDuplicateTimeSlots();
        $deletedCount = 0;

        foreach ($duplicates as $duplicate) {
            $slot1 = $duplicate['slot1'];
            $slot2 = $duplicate['slot2'];

            // より古い時間枠を削除（created_atが古い方）
            if ($slot1->created_at < $slot2->created_at) {
                $slot1->delete();
            } else {
                $slot2->delete();
            }
            $deletedCount++;
        }

        if ($deletedCount > 0) {
            $this->loadTimeSlots();
            $this->dispatch('refreshCalendar');
            session()->flash('message', "{$deletedCount}件の重複した時間枠を削除しました。");
        } else {
            session()->flash('message', '重複した時間枠はありませんでした。');
        }
    }

    /**
     * 時間枠の重複をチェック
     */
    private function checkTimeSlotOverlap($startTime, $endTime, $excludeId = null)
    {
        $start = Carbon::parse($this->selectedDate . ' ' . $startTime);
        $end = Carbon::parse($this->selectedDate . ' ' . $endTime);

        $query = TimeSlot::whereDate('date', $this->selectedDate)
            ->where(function ($q) use ($start, $end) {
                $q->where(function ($query) use ($start, $end) {
                    // 新しい時間枠の開始時間が既存の時間枠内にある
                    $query->whereTime('start_time', '<=', $start->format('H:i:s'))
                          ->whereTime('end_time', '>', $start->format('H:i:s'));
                })->orWhere(function ($query) use ($start, $end) {
                    // 新しい時間枠の終了時間が既存の時間枠内にある
                    $query->whereTime('start_time', '<', $end->format('H:i:s'))
                          ->whereTime('end_time', '>=', $end->format('H:i:s'));
                })->orWhere(function ($query) use ($start, $end) {
                    // 新しい時間枠が既存の時間枠を完全に包含する
                    $query->whereTime('start_time', '>=', $start->format('H:i:s'))
                          ->whereTime('end_time', '<=', $end->format('H:i:s'));
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function manageTimeSlots($date)
    {
        logger('TimeSlotManager: manageTimeSlots called', [
            'date' => $date,
            'component_id' => $this->getId()
        ]);

        $this->selectedDate = $date;
        $this->loadTimeSlots();
        $this->resetAddForm();

        logger('TimeSlotManager: state after manageTimeSlots', [
            'selectedDate' => $this->selectedDate,
            'timeSlots_count' => count($this->timeSlots)
        ]);
    }

    public function loadTimeSlots()
    {
        if (!$this->selectedDate) {
            return;
        }

        $timeSlots = TimeSlot::whereDate('date', $this->selectedDate)
            ->orderBy('start_time')
            ->get();

        // 各時間枠の満席状況をチェックして自動更新
        $updatedCount = 0;
        foreach ($timeSlots as $slot) {
            if ($slot->checkAndUpdateAvailability()) {
                $updatedCount++;
            }
        }

        // 更新があった場合は再度取得
        if ($updatedCount > 0) {
            $timeSlots = TimeSlot::whereDate('date', $this->selectedDate)
                ->orderBy('start_time')
                ->get();
        }

        // 配列を初期化
        $this->timeSlots = [];
        $this->isEditing = [];
        $this->editingData = [];

        foreach ($timeSlots as $index => $slot) {
            // 現在の予約数を取得
            $currentReservations = $slot->getCurrentReservationCount();
            $isFull = $slot->isFull();

            $this->timeSlots[$index] = array_merge($slot->toArray(), [
                'current_reservations' => $currentReservations,
                'is_full' => $isFull,
                'is_bookable' => $slot->isBookable()
            ]);

            $this->isEditing[$index] = false;
            $this->editingData[$index] = [
                'start_time' => Carbon::parse($slot->start_time)->format('H:i'),
                'end_time' => Carbon::parse($slot->end_time)->format('H:i'),
                'capacity' => $slot->capacity,
                'available' => $slot->available,
            ];
        }

        if ($updatedCount > 0) {
            session()->flash('message', "{$updatedCount}件の時間枠が満席のため受付を停止しました。");
        }

        logger('TimeSlotManager: loadTimeSlots completed', [
            'count' => count($this->timeSlots),
            'updated' => $updatedCount,
            'editingData' => $this->editingData
        ]);
    }

    public function startEdit($index)
    {
        logger('TimeSlotManager: startEdit called', ['index' => $index]);
        $this->isEditing[$index] = true;
    }

    public function cancelEdit($index)
    {
        logger('TimeSlotManager: cancelEdit called', ['index' => $index]);
        $this->isEditing[$index] = false;
        // 元のデータに戻す
        $slot = $this->timeSlots[$index];
        $this->editingData[$index] = [
            'start_time' => Carbon::parse($slot['start_time'])->format('H:i'),
            'end_time' => Carbon::parse($slot['end_time'])->format('H:i'),
            'capacity' => $slot['capacity'],
            'available' => $slot['available'],
        ];
    }

    public function saveEdit($index)
    {
        $this->validate([
            "editingData.$index.start_time" => 'required',
            "editingData.$index.end_time" => 'required|after:editingData.' . $index . '.start_time',
            "editingData.$index.capacity" => 'required|integer|min:1',
        ]);

        // 重複チェック
        $startTime = $this->editingData[$index]['start_time'];
        $endTime = $this->editingData[$index]['end_time'];
        $currentSlotId = $this->timeSlots[$index]['id'];

        if ($this->checkTimeSlotOverlap($startTime, $endTime, $currentSlotId)) {
            $this->addError("editingData.$index.start_time", 'この時間帯は他の時間枠と重複しています。');
            return;
        }

        $slot = TimeSlot::find($this->timeSlots[$index]['id']);
        $slot->update([
            'start_time' => $this->selectedDate . ' ' . $this->editingData[$index]['start_time'],
            'end_time' => $this->selectedDate . ' ' . $this->editingData[$index]['end_time'],
            'capacity' => $this->editingData[$index]['capacity'],
            'available' => $this->editingData[$index]['available'],
        ]);

        $this->isEditing[$index] = false;
        $this->loadTimeSlots();
        $this->dispatch('refreshCalendar');

        session()->flash('message', '時間枠を更新しました');
    }

    public function toggleAvailable($index)
    {
        $slot = TimeSlot::find($this->timeSlots[$index]['id']);
        $newAvailable = !$slot->available;

        // 受付を有効にしようとしているが満席の場合は警告
        if ($newAvailable && $slot->isFull()) {
            session()->flash('message', 'この時間枠は満席のため受付を有効にできません。');
            return;
        }

        $slot->update(['available' => $newAvailable]);

        $this->loadTimeSlots();
        $this->dispatch('refreshCalendar');

        $status = $newAvailable ? '有効' : '無効';
        session()->flash('message', "時間枠を{$status}にしました");
    }

    public function deleteTimeSlot($index)
    {
        TimeSlot::find($this->timeSlots[$index]['id'])->delete();
        $this->loadTimeSlots();
        $this->dispatch('refreshCalendar');

        session()->flash('message', '時間枠を削除しました');
    }

    public function toggleAddForm()
    {
        logger('TimeSlotManager: toggleAddForm called');
        $this->showAddForm = !$this->showAddForm;
        if ($this->showAddForm) {
            $this->resetAddForm();
        }
    }

    public function hideAddForm()
    {
        logger('TimeSlotManager: hideAddForm called');
        $this->showAddForm = false;
    }

    public function resetAddForm()
    {
        $this->newTimeSlot = [
            'start_time' => '',
            'end_time' => '',
            'capacity' => 1,
            'available' => true,
        ];
    }

    public function addTimeSlot()
    {
        $this->validate([
            'newTimeSlot.start_time' => 'required',
            'newTimeSlot.end_time' => 'required|after:newTimeSlot.start_time',
            'newTimeSlot.capacity' => 'required|integer|min:1',
        ]);

        // 重複チェック
        if ($this->checkTimeSlotOverlap($this->newTimeSlot['start_time'], $this->newTimeSlot['end_time'])) {
            $this->addError('newTimeSlot.start_time', 'この時間帯は他の時間枠と重複しています。');
            return;
        }

        TimeSlot::create([
            'date' => $this->selectedDate,
            'start_time' => $this->selectedDate . ' ' . $this->newTimeSlot['start_time'],
            'end_time' => $this->selectedDate . ' ' . $this->newTimeSlot['end_time'],
            'capacity' => $this->newTimeSlot['capacity'],
            'available' => $this->newTimeSlot['available'],
        ]);

        $this->hideAddForm();
        $this->loadTimeSlots();
        $this->dispatch('refreshCalendar');

        session()->flash('message', '時間枠を追加しました');
    }

    public function close()
    {
        logger('TimeSlotManager: close called');
        $this->reset(['selectedDate', 'timeSlots', 'isEditing', 'editingData', 'showAddForm']);
        $this->resetAddForm();
    }

    public function render()
    {
        return view('livewire.time-slot-manager');
    }
}
