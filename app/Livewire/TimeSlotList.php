<?php

namespace App\Livewire;

use Livewire\Component;
// class TimeSlotList extends Component
// {
//     public function render()
//     {
//         return view('livewire.time-slot-list');
//     }
// }

use App\Models\TimeSlot;

class TimeSlotList extends Component
{
    public $date;
    public $slots = [];

    public function mount($date)
    {
        $this->date = $date;
        $this->slots = TimeSlot::where('date', $date)
            ->where('available', '>', 0)
            ->orderBy('start_time')
            ->get();
    }

    public function render()
    {
        return view('livewire.time-slot-list');
    }
}
