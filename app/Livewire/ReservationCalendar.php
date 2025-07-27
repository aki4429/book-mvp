<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\TimeSlot;
use App\Models\Reservation;

class ReservationCalendar extends Component
{
    public $year;
    public $month;
    public $hoveredDate = null;
    public $pinnedDate = null;
    public $showReservationDetails = false;
    public $selectedReservation = null;
    public $showCreateReservation = false;
    public $selectedTimeSlot = null;

    public function mount()
    {
        $this->year = now()->year;
        $this->month = now()->month;
    }

    public function prevMonth()
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = $date->year;
        $this->month = $date->month;

        // 月を移動した際にhover/pin状態をリセット
        $this->reset(['hoveredDate', 'pinnedDate', 'selectedReservation', 'showReservationDetails', 'showCreateReservation', 'selectedTimeSlot']);
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = $date->year;
        $this->month = $date->month;

        // 月を移動した際にhover/pin状態をリセット
        $this->reset(['hoveredDate', 'pinnedDate', 'selectedReservation', 'showReservationDetails', 'showCreateReservation', 'selectedTimeSlot']);
    }

    public function hoverDate($date)
    {
        // 予約管理カレンダーではホバー表示を無効にする
        return;
        
        // 固定表示されている場合は、固定日付以外のホバーを無視
        if ($this->pinnedDate && $this->pinnedDate !== $date) {
            return;
        }

        $this->hoveredDate = $date;
        $this->showReservationDetails = false;
    }

    public function unhoverDate()
    {
        // 予約管理カレンダーではホバー表示を無効にする
        return;
        
        // 固定表示されている場合はホバー解除を無視
        if ($this->pinnedDate) {
            return;
        }

        $this->hoveredDate = null;
        $this->showReservationDetails = false;
    }

    public function pinDate($date)
    {
        if ($this->pinnedDate === $date) {
            $this->pinnedDate = null;
            $this->hoveredDate = null;
        } else {
            $this->pinnedDate = $date;
            $this->hoveredDate = $date;
        }
        $this->showReservationDetails = false;
    }

    public function clearPin()
    {
        $this->pinnedDate = null;
        $this->hoveredDate = null;
        $this->showReservationDetails = false;
    }

    public function createReservationForSlot($timeSlotId)
    {
        // 管理者の場合は直接予約作成ページにリダイレクト
        return redirect()->route('admin.reservations.create', ['slot_id' => $timeSlotId]);
    }

    public function closeCreateReservation()
    {
        $this->showCreateReservation = false;
        $this->selectedTimeSlot = null;
    }

    public function showReservationDetail($reservationId)
    {
        $this->selectedReservation = Reservation::with(['customer', 'timeSlot'])->find($reservationId);
        $this->showReservationDetails = true;
    }

    public function editReservation($reservationId)
    {
        return redirect()->route('admin.reservations.edit', $reservationId);
    }

    public function deleteReservation($reservationId)
    {
        $reservation = Reservation::find($reservationId);
        if ($reservation) {
            $reservation->delete();
            $this->dispatch('reservation-deleted');
            session()->flash('message', '予約を削除しました。');
        }
    }

    public function render()
    {
        $first = Carbon::create($this->year, $this->month, 1);
        $start = $first->copy()->startOfWeek(Carbon::SUNDAY);
        $end = $first->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        // カレンダー用の週データを作成
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

        // 期間内の時間枠データを取得
        $timeSlots = TimeSlot::whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->orderBy('start_time')
            ->with(['reservations.customer'])
            ->get()
            ->groupBy(function ($slot) {
                return $slot->date->format('Y-m-d');
            });

        // 期間内の予約データを取得（時間枠情報も含む）
        $reservations = Reservation::with(['customer', 'timeSlot'])
            ->whereHas('timeSlot', function ($query) use ($start, $end) {
                $query->whereBetween('date', [$start, $end]);
            })
            ->get()
            ->groupBy(function ($reservation) {
                return $reservation->timeSlot->date->format('Y-m-d');
            });

        // ホバー中の日付の時間枠と予約詳細を取得
        $hoveredSlots = collect();
        $hoveredReservations = collect();
        if ($this->hoveredDate) {
            $hoveredSlots = $timeSlots->get($this->hoveredDate, collect());
            $hoveredReservations = $reservations->get($this->hoveredDate, collect());
        }

        return view('livewire.reservation-calendar', [
            'weeks' => $weeks,
            'currentMonth' => $first,
            'year' => $this->year,
            'month' => $this->month,
            'timeSlots' => $timeSlots,
            'reservations' => $reservations,
            'hoveredSlots' => $hoveredSlots,
            'hoveredReservations' => $hoveredReservations,
            'hoveredDate' => $this->hoveredDate,
            'pinnedDate' => $this->pinnedDate,
            'showReservationDetails' => $this->showReservationDetails,
            'selectedReservation' => $this->selectedReservation,
            'showCreateReservation' => $this->showCreateReservation,
            'selectedTimeSlot' => $this->selectedTimeSlot,
        ]);
    }
}
