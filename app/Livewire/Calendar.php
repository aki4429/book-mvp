<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\TimeSlot;
use App\Models\Setting;

class Calendar extends Component
{
    public $year;
    public $month;
    public $showTimeSlotForm = false;
    public $editingDate = null;
    public $hoveredDate = null;
    public $pinnedDate = null; // 固定表示用
    public $isAdmin = false; // 管理者フラグ
    public $isReservationManagement = false; // 予約管理カレンダーフラグ

    protected $listeners = ['refreshCalendar'];

    public function refreshCalendar()
    {
        // カレンダーの再レンダリング
        $this->showTimeSlotForm = false;
    }

    public function hydrate()
    {
        // Livewireコンポーネントが再水和されるたびに実行される
        logger('Calendar: hydrate called', [
            'year' => $this->year,
            'month' => $this->month
        ]);
    }

    public function dehydrate()
    {
        // Livewireコンポーネントが脱水化されるたびに実行される
        logger('Calendar: dehydrate called', [
            'year' => $this->year,
            'month' => $this->month
        ]);
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

    public function openTimeSlotManager($date)
    {
        logger('Calendar: openTimeSlotManager called', ['date' => $date]);
        $this->dispatch('manageTimeSlots', $date);
    }

    public function hoverDate($date)
    {
        // 予約管理カレンダーの場合はホバー表示を無効
        if ($this->isReservationManagement) {
            return;
        }

        // 固定表示されている場合は、固定日付以外のホバーを無視
        if ($this->pinnedDate && $this->pinnedDate !== $date) {
            return;
        }

        $this->hoveredDate = $date;
    }

    public function unhoverDate()
    {
        // 予約管理カレンダーの場合はホバー表示を無効
        if ($this->isReservationManagement) {
            return;
        }

        // 固定表示されている場合は、ホバー解除を無視
        if ($this->pinnedDate) {
            return;
        }

        $this->hoveredDate = null;
    }

    public function pinDate($date)
    {
        logger('Calendar: pinDate called', ['date' => $date, 'currentPinnedDate' => $this->pinnedDate]);

        // 予約可能日をチェック
        $reservationAdvanceDays = Setting::get('reservation_advance_days', 0);
        $reservationStartDate = Carbon::today()->addDays($reservationAdvanceDays);
        $selectedDate = Carbon::parse($date);

        if ($selectedDate->lt($reservationStartDate)) {
            logger('Calendar: pin blocked - reservation not allowed', ['date' => $date]);
            return; // 予約不可日はクリックを無視
        }

        // 同じ日付を再クリックした場合は固定解除
        if ($this->pinnedDate === $date) {
            $this->pinnedDate = null;
            $this->hoveredDate = null;
            logger('Calendar: pin cleared');
        } else {
            $this->pinnedDate = $date;
            $this->hoveredDate = $date;
            logger('Calendar: pin set', ['pinnedDate' => $this->pinnedDate]);
        }
    }

    public function clearPin()
    {
        $this->pinnedDate = null;
        $this->hoveredDate = null;
    }

    public function mount($isAdmin = null, $isReservationManagement = false)
    {
        logger('Calendar: mount called', ['isAdmin' => $isAdmin, 'isReservationManagement' => $isReservationManagement]);
        
        $this->year  = now()->year;
        $this->month = now()->month;

        // 予約管理カレンダーのフラグを設定
        $this->isReservationManagement = $isReservationManagement;

        // isAdminパラメータが明示的に設定されている場合はそれを使用
        // そうでなければ、認証されたユーザーがいるかどうかで判断（簡易的な管理者判定）
        if ($isAdmin !== null) {
            $this->isAdmin = $isAdmin;
        } else {
            // 管理者レイアウトを使用しているページまたは認証済みユーザーを管理者とみなす
            $this->isAdmin = auth()->check();
        }
        
        logger('Calendar: mount completed', ['isAdmin' => $this->isAdmin, 'isReservationManagement' => $this->isReservationManagement, 'year' => $this->year, 'month' => $this->month]);
    }    public function prevMonth()
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = $date->year;
        $this->month = $date->month;

        // 月を移動した際にhover/pin状態をリセット
        $this->reset(['hoveredDate', 'pinnedDate']);

        // コンポーネントの再描画を強制
        $this->skipRender = false;

        logger('Calendar: prevMonth executed', [
            'new_year' => $this->year,
            'new_month' => $this->month
        ]);
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = $date->year;
        $this->month = $date->month;

        // 月を移動した際にhover/pin状態をリセット
        $this->reset(['hoveredDate', 'pinnedDate']);

        // コンポーネントの再描画を強制
        $this->skipRender = false;
    }    public function render()
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

        // 予約可能開始日を取得
        $reservationAdvanceDays = Setting::get('reservation_advance_days', 0);
        $reservationStartDate = Carbon::today()->addDays($reservationAdvanceDays);

        return view('livewire.calendar', [
            'weeks'        => $weeks,
            'currentMonth' => $first,
            'year'         => $this->year,
            'month'        => $this->month,
            'slots' => $slots,
            'showTimeSlotForm' => $this->showTimeSlotForm,
            'editingDate' => $this->editingDate,
            'hoveredDate' => $this->hoveredDate,
            'pinnedDate' => $this->pinnedDate,
            'isAdmin' => $this->isAdmin,
            'isReservationManagement' => $this->isReservationManagement,
            'reservationStartDate' => $reservationStartDate,
        ]);
    }



}
