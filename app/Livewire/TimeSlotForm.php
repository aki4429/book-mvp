<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TimeSlot;

class TimeSlotForm extends Component
{
    public $timeslotId;
    public $date;
    public $start_time;
    public $end_time;
    public $capacity;
    public $available = true;

    protected $listeners = ['openTimeSlotForm'];

    public function openTimeSlotForm($timeslotId = null)
    {
        $this->timeslotId = $timeslotId;
        if ($timeslotId) {
            $slot = TimeSlot::find($timeslotId);
            if ($slot) {
                $this->date = $slot->date->format('Y-m-d');
                $this->start_time = $slot->start_time;
                $this->end_time = $slot->end_time;
                $this->capacity = $slot->capacity;
                $this->available = $slot->available;
            }
        }
        $this->dispatch('show-modal');
    }

    public function save()
    {
        $this->validate([
            'date' => 'required|date',
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
        $this->reset();  // フォームリセット
    }

    public function render()
    {
        return view('livewire.time-slot-form');
    }
}
