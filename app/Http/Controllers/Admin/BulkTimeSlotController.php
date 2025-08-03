<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use App\Models\TimeSlotPreset;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BulkTimeSlotController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display the bulk time slot creation page (JS version)
     */
    public function index()
    {
        return view('admin.bulk-timeslots.index');
    }

    /**
     * Get all active presets for dropdown
     */
    public function getPresets(Request $request)
    {
        try {
            $presets = TimeSlotPreset::active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->select('id', 'name', 'description', 'time_slots')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $presets
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'プリセットの取得に失敗しました。'
            ], 500);
        }
    }

    /**
     * Get single preset details
     */
    public function getPreset(TimeSlotPreset $preset)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $preset
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'プリセットの取得に失敗しました。'
            ], 500);
        }
    }

    /**
     * Preview time slots before creation
     */
    public function preview(Request $request)
    {
        $request->validate([
            'days' => 'required|array|min:1',
            'days.*' => 'in:sun,mon,tue,wed,thu,fri,sat',
            'time_slots' => 'required|array|min:1',
            'time_slots.*.start_time' => 'required|date_format:H:i',
            'time_slots.*.end_time' => 'required|date_format:H:i|after:time_slots.*.start_time',
            'time_slots.*.capacity' => 'required|integer|min:1|max:100',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $daysMap = [
                'sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3,
                'thu' => 4, 'fri' => 5, 'sat' => 6,
            ];

            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            $targetDays = collect($request->days)->map(fn($d) => $daysMap[$d])->toArray();

            $dates = [];
            $totalSlots = 0;

            $current = $start->copy();
            while ($current->lte($end)) {
                if (in_array($current->dayOfWeek, $targetDays)) {
                    $dates[] = [
                        'date' => $current->format('Y-m-d'),
                        'day_name' => $current->format('Y年m月d日 (D)'),
                        'slots_count' => count($request->time_slots)
                    ];
                    $totalSlots += count($request->time_slots);
                }
                $current->addDay();
            }

            // 既存の予約枠との重複チェック
            $conflictDates = [];
            foreach ($dates as $date) {
                foreach ($request->time_slots as $slot) {
                    $existingSlot = TimeSlot::where('date', $date['date'])
                        ->where(function($query) use ($slot) {
                            $query->whereBetween('start_time', [$slot['start_time'], $slot['end_time']])
                                  ->orWhereBetween('end_time', [$slot['start_time'], $slot['end_time']])
                                  ->orWhere(function($q) use ($slot) {
                                      $q->where('start_time', '<=', $slot['start_time'])
                                        ->where('end_time', '>=', $slot['end_time']);
                                  });
                        })
                        ->exists();

                    if ($existingSlot) {
                        $conflictDates[] = $date['date'];
                        break;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'dates' => $dates,
                    'total_dates' => count($dates),
                    'total_slots' => $totalSlots,
                    'conflicts' => array_unique($conflictDates),
                    'time_slots' => $request->time_slots
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'バリデーションエラーが発生しました。',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'プレビューの生成に失敗しました。'
            ], 500);
        }
    }

    /**
     * Bulk create time slots
     */
    public function store(Request $request)
    {
        $request->validate([
            'days' => 'required|array|min:1',
            'days.*' => 'in:sun,mon,tue,wed,thu,fri,sat',
            'time_slots' => 'required|array|min:1',
            'time_slots.*.start_time' => 'required|date_format:H:i',
            'time_slots.*.end_time' => 'required|date_format:H:i|after:time_slots.*.start_time',
            'time_slots.*.capacity' => 'required|integer|min:1|max:100',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'overwrite_existing' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $daysMap = [
                'sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3,
                'thu' => 4, 'fri' => 5, 'sat' => 6,
            ];

            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            $targetDays = collect($request->days)->map(fn($d) => $daysMap[$d])->toArray();

            $createdSlots = 0;
            $skippedSlots = 0;
            $overwrittenSlots = 0;

            $current = $start->copy();
            while ($current->lte($end)) {
                if (in_array($current->dayOfWeek, $targetDays)) {
                    foreach ($request->time_slots as $slotData) {
                        // 既存の重複チェック
                        $existingSlot = TimeSlot::where('date', $current->format('Y-m-d'))
                            ->where(function($query) use ($slotData) {
                                $query->whereBetween('start_time', [$slotData['start_time'], $slotData['end_time']])
                                      ->orWhereBetween('end_time', [$slotData['start_time'], $slotData['end_time']])
                                      ->orWhere(function($q) use ($slotData) {
                                          $q->where('start_time', '<=', $slotData['start_time'])
                                            ->where('end_time', '>=', $slotData['end_time']);
                                      });
                            })
                            ->first();

                        if ($existingSlot) {
                            if ($request->boolean('overwrite_existing')) {
                                $existingSlot->update([
                                    'start_time' => $slotData['start_time'],
                                    'end_time' => $slotData['end_time'],
                                    'capacity' => $slotData['capacity'],
                                    'available' => true,
                                ]);
                                $overwrittenSlots++;
                            } else {
                                $skippedSlots++;
                            }
                        } else {
                            TimeSlot::create([
                                'date' => $current->format('Y-m-d'),
                                'start_time' => $slotData['start_time'],
                                'end_time' => $slotData['end_time'],
                                'capacity' => $slotData['capacity'],
                                'available' => true,
                                'service_id' => $slotData['service_id'] ?? null,
                            ]);
                            $createdSlots++;
                        }
                    }
                }
                $current->addDay();
            }

            DB::commit();

            $message = "時間枠の一括作成が完了しました。";
            if ($createdSlots > 0) $message .= " 新規作成: {$createdSlots}件";
            if ($overwrittenSlots > 0) $message .= " 上書き: {$overwrittenSlots}件";
            if ($skippedSlots > 0) $message .= " スキップ: {$skippedSlots}件";

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'created' => $createdSlots,
                    'overwritten' => $overwrittenSlots,
                    'skipped' => $skippedSlots,
                    'total' => $createdSlots + $overwrittenSlots + $skippedSlots
                ]
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'バリデーションエラーが発生しました。',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '時間枠の作成に失敗しました: ' . $e->getMessage()
            ], 500);
        }
    }
}
