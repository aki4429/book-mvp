<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeSlotPreset;
use Illuminate\Http\Request;

class TimeSlotPresetController extends Controller
{
    public function index()
    {
        $presets = TimeSlotPreset::ordered()->get();
        return view('admin.presets.index', compact('presets'));
    }

    public function create()
    {
        return view('admin.presets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_slots' => 'required|array|min:1',
            'time_slots.*.start_time' => 'required',
            'time_slots.*.end_time' => 'required|after:time_slots.*.start_time',
            'time_slots.*.capacity' => 'required|integer|min:1',
        ]);

        TimeSlotPreset::create([
            'name' => $request->name,
            'description' => $request->description,
            'time_slots' => $request->time_slots,
            'is_active' => true,
            'sort_order' => TimeSlotPreset::max('sort_order') + 1,
        ]);

        return redirect()->route('admin.presets.index')->with('success', 'プリセットを作成しました');
    }

    public function edit(TimeSlotPreset $preset)
    {
        return view('admin.presets.edit', compact('preset'));
    }

    public function update(Request $request, TimeSlotPreset $preset)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_slots' => 'required|array|min:1',
            'time_slots.*.start_time' => 'required',
            'time_slots.*.end_time' => 'required|after:time_slots.*.start_time',
            'time_slots.*.capacity' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $preset->update([
            'name' => $request->name,
            'description' => $request->description,
            'time_slots' => $request->time_slots,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.presets.index')->with('success', 'プリセットを更新しました');
    }

    public function destroy(TimeSlotPreset $preset)
    {
        $preset->delete();
        return redirect()->route('admin.presets.index')->with('success', 'プリセットを削除しました');
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'required|integer|exists:time_slot_presets,id'
        ]);

        foreach ($request->orders as $order => $id) {
            TimeSlotPreset::where('id', $id)->update(['sort_order' => $order]);
        }

        return response()->json(['success' => true]);
    }
}
