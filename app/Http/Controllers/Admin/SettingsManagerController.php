<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display the main settings management page (JS version)
     */
    public function index()
    {
        return view('admin.settings-manager.index');
    }

    /**
     * Get all settings via AJAX
     */
    public function getSettings(Request $request)
    {
        $query = Setting::query();

        // 検索機能
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // タイプフィルター
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // ソート機能
        $sortField = $request->get('sort', 'key');
        $sortDirection = $request->get('direction', 'asc');
        
        $allowedSorts = ['key', 'type', 'description', 'created_at', 'updated_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $perPage = $request->get('per_page', 10);
        $settings = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $settings->items(),
            'pagination' => [
                'current_page' => $settings->currentPage(),
                'last_page' => $settings->lastPage(),
                'per_page' => $settings->perPage(),
                'total' => $settings->total(),
                'from' => $settings->firstItem(),
                'to' => $settings->lastItem(),
            ]
        ]);
    }

    /**
     * Get single setting details
     */
    public function getSetting(Setting $setting)
    {
        return response()->json([
            'success' => true,
            'data' => $setting
        ]);
    }

    /**
     * Store new setting
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:settings,key',
            'value' => 'required|string',
            'type' => 'required|in:string,integer,boolean,json',
            'description' => 'nullable|string|max:500'
        ]);

        // 値の型チェック
        $this->validateValueByType($validated['value'], $validated['type']);

        $setting = Setting::create($validated);

        return response()->json([
            'success' => true,
            'message' => '設定が作成されました。',
            'data' => $setting
        ]);
    }

    /**
     * Update setting
     */
    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:settings,key,' . $setting->id,
            'value' => 'required|string',
            'type' => 'required|in:string,integer,boolean,json',
            'description' => 'nullable|string|max:500'
        ]);

        // 値の型チェック
        $this->validateValueByType($validated['value'], $validated['type']);

        $setting->update($validated);

        return response()->json([
            'success' => true,
            'message' => '設定が更新されました。',
            'data' => $setting
        ]);
    }

    /**
     * Delete setting
     */
    public function destroy(Setting $setting)
    {
        // 重要な設定は削除を防ぐ
        $protectedKeys = ['reservation_advance_days'];
        
        if (in_array($setting->key, $protectedKeys)) {
            return response()->json([
                'success' => false,
                'message' => 'この設定は削除できません。'
            ], 400);
        }

        $setting->delete();

        return response()->json([
            'success' => true,
            'message' => '設定が削除されました。'
        ]);
    }

    /**
     * Bulk delete settings
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'setting_ids' => 'required|array',
            'setting_ids.*' => 'integer|exists:settings,id'
        ]);

        $settingIds = $validated['setting_ids'];
        
        // 保護された設定をチェック
        $protectedKeys = ['reservation_advance_days'];
        $protectedSettings = Setting::whereIn('id', $settingIds)
                                   ->whereIn('key', $protectedKeys)
                                   ->count();

        if ($protectedSettings > 0) {
            return response()->json([
                'success' => false,
                'message' => '削除できない重要な設定が含まれています。'
            ], 400);
        }

        $deletedCount = Setting::whereIn('id', $settingIds)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount}個の設定が削除されました。"
        ]);
    }

    /**
     * Reset setting to default value
     */
    public function resetToDefault(Setting $setting)
    {
        $defaults = $this->getDefaultValues();
        
        if (!isset($defaults[$setting->key])) {
            return response()->json([
                'success' => false,
                'message' => 'デフォルト値が定義されていません。'
            ], 400);
        }

        $default = $defaults[$setting->key];
        $setting->update([
            'value' => $default['value'],
            'type' => $default['type']
        ]);

        return response()->json([
            'success' => true,
            'message' => '設定をデフォルト値にリセットしました。',
            'data' => $setting
        ]);
    }

    /**
     * Get system information
     */
    public function getSystemInfo()
    {
        $info = [
            'total_settings' => Setting::count(),
            'setting_types' => Setting::select('type')->distinct()->pluck('type'),
            'last_updated' => Setting::latest('updated_at')->first()?->updated_at,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connection' => config('database.default'),
        ];

        return response()->json([
            'success' => true,
            'data' => $info
        ]);
    }

    /**
     * Validate value by type
     */
    private function validateValueByType($value, $type)
    {
        switch ($type) {
            case 'integer':
                if (!is_numeric($value)) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], [])->errors()->add('value', '整数値を入力してください。')
                    );
                }
                break;
            case 'boolean':
                if (!in_array(strtolower($value), ['true', 'false', '1', '0'])) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], [])->errors()->add('value', 'true/false または 1/0 を入力してください。')
                    );
                }
                break;
            case 'json':
                json_decode($value);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], [])->errors()->add('value', '有効なJSON形式で入力してください。')
                    );
                }
                break;
        }
    }

    /**
     * Get default values for settings
     */
    private function getDefaultValues()
    {
        return [
            'reservation_advance_days' => [
                'value' => '0',
                'type' => 'integer',
                'description' => '予約可能開始日（今日から何日後から予約可能にするか）'
            ],
        ];
    }
}
