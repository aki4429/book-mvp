<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Illuminate\Http\Request;

class TimeSlotController extends Controller
{
    public function index()
    {
        $timeSlots = TimeSlot::orderBy('date')->orderBy('start_time')->paginate(20);
        return view('admin.timeslots.index', compact('timeSlots'));
    }

    public function create()
    {
        return view('admin.timeslots.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'       => 'required|date',
            'start_time' => 'required',
            'end_time'   => 'required|after:start_time',
            'capacity'   => 'required|integer|min:1',
            'available'  => 'boolean',
        ]);

        TimeSlot::create($validated);
        return redirect()->route('admin.timeslots.index')->with('success', '枠を作成しました');
    }

    public function edit(TimeSlot $timeslot)
    {
        return view('admin.timeslots.edit', compact('timeslot'));
    }

    public function update(Request $request, TimeSlot $timeslot)
    {
        $validated = $request->validate([
            'date'       => 'required|date',
            'start_time' => 'required',
            'end_time'   => 'required|after:start_time',
            'capacity'   => 'required|integer|min:1',
            'available'  => 'boolean',
        ]);

        $timeslot->update($validated);
        return redirect()->route('admin.timeslots.index')->with('success', '枠を更新しました');
    }

    public function destroy(TimeSlot $timeslot)
    {
        $timeslot->delete();
        return redirect()->route('admin.timeslots.index')->with('success', '削除しました');
    }
}
