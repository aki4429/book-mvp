<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

    public function bulkCreate()
    {
        $presets = \App\Models\TimeSlotPreset::active()->ordered()->get();
        return view('admin.timeslots.bulk-create', compact('presets'));
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'days'        => 'required|array',
            'time_slots'  => 'required|array|min:1',
            'time_slots.*.start_time' => 'required',
            'time_slots.*.end_time'   => 'required|after:time_slots.*.start_time',
            'time_slots.*.capacity'   => 'required|integer|min:1',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
        ]);

        $daysMap = [
            'sun' => 0,
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6,
        ];

        $start = Carbon::parse($request->start_date);
        $end   = Carbon::parse($request->end_date);

        $dates = [];

        while ($start->lte($end)) {
            if (in_array($start->dayOfWeek, collect($request->days)->map(fn ($d) => $daysMap[$d])->toArray())) {
                $dates[] = $start->copy();
            }
            $start->addDay();
        }

        $createdCount = 0;

        foreach ($dates as $date) {
            foreach ($request->time_slots as $timeSlot) {
                $created = TimeSlot::firstOrCreate(
                    [
                        'date'       => $date->format('Y-m-d'),
                        'start_time' => $timeSlot['start_time'],
                        'end_time'   => $timeSlot['end_time'],
                    ],
                    [
                        'capacity'  => $timeSlot['capacity'],
                        'available' => true,
                    ]
                );

                if ($created->wasRecentlyCreated) {
                    $createdCount++;
                }
            }
        }

        return redirect()->route('admin.timeslots.index')->with('success', "{$createdCount}件の予約枠を一括登録しました");
    }


}
