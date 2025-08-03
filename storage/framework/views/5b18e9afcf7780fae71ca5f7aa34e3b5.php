<?php $__env->startSection('title', '一括時間枠設定 (JS版)'); ?>
<?php $__env->startSection('page-title', '一括時間枠設定'); ?>

<?php $__env->startPush('head'); ?>
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('body'); ?>
<div id="bulk-timeslot-app">
    <!-- ヘッダー -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">一括時間枠設定</h2>
            <div class="flex space-x-3">
                <button id="reset-form-btn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    リセット
                </button>
            </div>
        </div>
    </div>

    <!-- アラート表示エリア -->
    <div id="alert-container" style="display: none;" class="mb-6">
        <div id="alert" class="rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0" id="alert-icon"></div>
                <div class="ml-3">
                    <p id="alert-message" class="text-sm font-medium"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- ローディング表示 -->
    <div id="loading" style="display: none;" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-600">処理中...</p>
    </div>

    <!-- メインフォーム -->
    <div class="bg-white rounded-lg shadow-sm">
        <form id="bulk-timeslot-form">
            <!-- 対象曜日選択 -->
            <div class="p-6 border-b">
                <label class="block text-lg font-semibold mb-4">対象曜日</label>
                <div class="grid grid-cols-7 gap-3">
                    <label class="flex items-center justify-center p-3 border-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors" data-day="mon">
                        <input type="checkbox" name="days[]" value="mon" class="sr-only">
                        <span class="font-medium">月</span>
                    </label>
                    <label class="flex items-center justify-center p-3 border-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors" data-day="tue">
                        <input type="checkbox" name="days[]" value="tue" class="sr-only">
                        <span class="font-medium">火</span>
                    </label>
                    <label class="flex items-center justify-center p-3 border-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors" data-day="wed">
                        <input type="checkbox" name="days[]" value="wed" class="sr-only">
                        <span class="font-medium">水</span>
                    </label>
                    <label class="flex items-center justify-center p-3 border-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors" data-day="thu">
                        <input type="checkbox" name="days[]" value="thu" class="sr-only">
                        <span class="font-medium">木</span>
                    </label>
                    <label class="flex items-center justify-center p-3 border-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors" data-day="fri">
                        <input type="checkbox" name="days[]" value="fri" class="sr-only">
                        <span class="font-medium">金</span>
                    </label>
                    <label class="flex items-center justify-center p-3 border-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors" data-day="sat">
                        <input type="checkbox" name="days[]" value="sat" class="sr-only">
                        <span class="font-medium">土</span>
                    </label>
                    <label class="flex items-center justify-center p-3 border-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors" data-day="sun">
                        <input type="checkbox" name="days[]" value="sun" class="sr-only">
                        <span class="font-medium">日</span>
                    </label>
                </div>
                <div class="mt-2">
                    <span id="error-days" class="text-red-500 text-sm" style="display: none;"></span>
                </div>
            </div>

            <!-- 対象期間 -->
            <div class="p-6 border-b">
                <label class="block text-lg font-semibold mb-4">対象期間</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium mb-2">開始日</label>
                        <input type="date" name="start_date" id="start_date" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <span id="error-start_date" class="text-red-500 text-sm" style="display: none;"></span>
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium mb-2">終了日</label>
                        <input type="date" name="end_date" id="end_date" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <span id="error-end_date" class="text-red-500 text-sm" style="display: none;"></span>
                    </div>
                </div>
            </div>

            <!-- プリセット選択 -->
            <div class="p-6 border-b">
                <div class="flex justify-between items-center mb-4">
                    <label class="text-lg font-semibold">プリセット選択</label>
                    <button type="button" id="clear-preset-btn" class="text-blue-600 hover:text-blue-800 text-sm">
                        プリセットをクリア
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="preset_select" class="block text-sm font-medium mb-2">利用可能なプリセット</label>
                        <select id="preset_select" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">プリセットを選択...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">選択されたプリセット</label>
                        <div id="selected-preset-info" class="p-3 bg-gray-50 rounded-lg text-sm text-gray-600">
                            プリセットが選択されていません
                        </div>
                    </div>
                </div>
            </div>

            <!-- 時間枠設定 -->
            <div class="p-6 border-b">
                <div class="flex justify-between items-center mb-4">
                    <label class="text-lg font-semibold">時間枠設定</label>
                    <button type="button" id="add-timeslot-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        時間枠を追加
                    </button>
                </div>

                <div id="timeslots-container">
                    <!-- 初期の時間枠 -->
                    <div class="timeslot-row border rounded-lg p-4 mb-3">
                        <div class="flex items-center space-x-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium mb-1">開始時間</label>
                                <input type="time" name="time_slots[0][start_time]" class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium mb-1">終了時間</label>
                                <input type="time" name="time_slots[0][end_time]" class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div class="w-24">
                                <label class="block text-sm font-medium mb-1">定員</label>
                                <input type="number" name="time_slots[0][capacity]" value="1" min="1" max="100" class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div class="w-32">
                                <label class="block text-sm font-medium mb-1">サービスID</label>
                                <input type="text" name="time_slots[0][service_id]" class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="任意">
                            </div>
                            <div class="flex-shrink-0">
                                <button type="button" class="remove-timeslot-btn mt-6 text-red-600 hover:text-red-800" disabled>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-2">
                    <span id="error-time_slots" class="text-red-500 text-sm" style="display: none;"></span>
                </div>
            </div>

            <!-- オプション設定 -->
            <div class="p-6 border-b">
                <label class="text-lg font-semibold mb-4 block">オプション設定</label>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" name="overwrite_existing" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-3 text-sm">既存の重複する時間枠を上書きする</span>
                    </label>
                </div>
            </div>

            <!-- アクションボタン -->
            <div class="p-6 bg-gray-50 flex justify-between">
                <button type="button" id="preview-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    プレビュー
                </button>
                <button type="submit" id="create-btn" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg inline-flex items-center" disabled>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    時間枠を作成
                </button>
            </div>
        </form>
    </div>

    <!-- プレビューモーダル -->
    <div id="preview-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">作成予定の時間枠プレビュー</h3>
                    <button id="close-preview-modal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div id="preview-content"></div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button id="cancel-preview" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                        キャンセル
                    </button>
                    <button id="confirm-create" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                        作成実行
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/bulk-timeslots.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/app_3/resources/views/admin/bulk-timeslots/index.blade.php ENDPATH**/ ?>