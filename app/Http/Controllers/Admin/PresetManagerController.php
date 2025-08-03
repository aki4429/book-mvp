<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeSlotPreset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PresetManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display the preset management page (JS version)
     */
    public function index()
    {
        return view('admin.preset-manager.index');
    }

    /**
     * Get all presets with pagination and search
     */
    public function getPresets(Request $request)
    {
        try {
            $query = TimeSlotPreset::query();

            // 検索機能
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // ステータスフィルター
            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            // ソート機能
            $sortField = $request->get('sort', 'sort_order');
            $sortDirection = $request->get('direction', 'asc');
            
            $allowedSorts = ['name', 'sort_order', 'created_at', 'is_active'];
            if (in_array($sortField, $allowedSorts)) {
                $query->orderBy($sortField, $sortDirection);
            }

            $presets = $query->paginate($request->get('per_page', 10));

            return response()->json([
                'success' => true,
                'data' => $presets->items(),
                'pagination' => [
                    'current_page' => $presets->currentPage(),
                    'last_page' => $presets->lastPage(),
                    'per_page' => $presets->perPage(),
                    'total' => $presets->total(),
                    'from' => $presets->firstItem(),
                    'to' => $presets->lastItem(),
                ]
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
     * Store a new preset
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'time_slots' => 'required|array|min:1',
                'time_slots.*.start_time' => 'required|date_format:H:i',
                'time_slots.*.end_time' => 'required|date_format:H:i|after:time_slots.*.start_time',
                'time_slots.*.capacity' => 'required|integer|min:1|max:100',
                'time_slots.*.service_id' => 'nullable|string|max:50',
                'is_active' => 'boolean',
            ]);

            DB::beginTransaction();

            $preset = TimeSlotPreset::create([
                'name' => $request->name,
                'description' => $request->description,
                'time_slots' => $request->time_slots,
                'is_active' => $request->boolean('is_active', true),
                'sort_order' => TimeSlotPreset::max('sort_order') + 1,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'プリセットを作成しました。',
                'data' => $preset
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
                'message' => 'プリセットの作成に失敗しました。'
            ], 500);
        }
    }

    /**
     * Update the specified preset
     */
    public function update(Request $request, TimeSlotPreset $preset)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'time_slots' => 'required|array|min:1',
                'time_slots.*.start_time' => 'required|date_format:H:i',
                'time_slots.*.end_time' => 'required|date_format:H:i|after:time_slots.*.start_time',
                'time_slots.*.capacity' => 'required|integer|min:1|max:100',
                'time_slots.*.service_id' => 'nullable|string|max:50',
                'is_active' => 'boolean',
            ]);

            DB::beginTransaction();

            $preset->update([
                'name' => $request->name,
                'description' => $request->description,
                'time_slots' => $request->time_slots,
                'is_active' => $request->boolean('is_active'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'プリセットを更新しました。',
                'data' => $preset->fresh()
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
                'message' => 'プリセットの更新に失敗しました。'
            ], 500);
        }
    }

    /**
     * Remove the specified preset
     */
    public function destroy(TimeSlotPreset $preset)
    {
        try {
            DB::beginTransaction();

            $preset->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'プリセットを削除しました。'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'プリセットの削除に失敗しました。'
            ], 500);
        }
    }

    /**
     * Bulk delete presets
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'preset_ids' => 'required|array|min:1',
            'preset_ids.*' => 'integer|exists:time_slot_presets,id',
        ]);

        try {
            DB::beginTransaction();

            $deletedCount = TimeSlotPreset::whereIn('id', $request->preset_ids)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount}件のプリセットを削除しました。"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'プリセットの一括削除に失敗しました。'
            ], 500);
        }
    }

    /**
     * Toggle preset active status
     */
    public function toggleStatus(TimeSlotPreset $preset)
    {
        try {
            DB::beginTransaction();

            $preset->update(['is_active' => !$preset->is_active]);

            DB::commit();

            $status = $preset->is_active ? 'アクティブ' : '非アクティブ';

            return response()->json([
                'success' => true,
                'message' => "プリセット「{$preset->name}」を{$status}に変更しました。",
                'data' => $preset->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'ステータスの変更に失敗しました。'
            ], 500);
        }
    }

    /**
     * Update preset sort order
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'presets' => 'required|array',
            'presets.*.id' => 'required|integer|exists:time_slot_presets,id',
            'presets.*.sort_order' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->presets as $presetData) {
                TimeSlotPreset::where('id', $presetData['id'])
                    ->update(['sort_order' => $presetData['sort_order']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'プリセットの表示順を更新しました。'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '表示順の更新に失敗しました。'
            ], 500);
        }
    }

    /**
     * Duplicate a preset
     */
    public function duplicate(TimeSlotPreset $preset)
    {
        try {
            DB::beginTransaction();

            $newPreset = TimeSlotPreset::create([
                'name' => $preset->name . ' (コピー)',
                'description' => $preset->description,
                'time_slots' => $preset->time_slots,
                'is_active' => false, // デフォルトで非アクティブ
                'sort_order' => TimeSlotPreset::max('sort_order') + 1,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'プリセットを複製しました。',
                'data' => $newPreset
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'プリセットの複製に失敗しました。'
            ], 500);
        }
    }
}
