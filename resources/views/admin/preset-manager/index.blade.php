@extends('layouts.admin')

@section('title', 'プリセット管理 (JS版)')
@section('page-title', 'プリセット管理')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('body')
<div id="preset-manager-app">
    <!-- ヘッダー -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">プリセット管理</h2>
            <button id="create-preset-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                プリセット作成
            </button>
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

    <!-- 検索・フィルター欄 -->
    <div class="bg-white rounded-lg shadow-sm mb-6 p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search-input" class="block text-sm font-medium text-gray-700 mb-1">検索</label>
                <input type="text" id="search-input" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="名前・説明文で検索...">
            </div>
            <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">ステータス</label>
                <select id="status-filter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">すべて</option>
                    <option value="active">アクティブ</option>
                    <option value="inactive">非アクティブ</option>
                </select>
            </div>
            <div>
                <label for="sort-field" class="block text-sm font-medium text-gray-700 mb-1">ソート</label>
                <select id="sort-field" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="sort_order">表示順</option>
                    <option value="name">名前</option>
                    <option value="created_at">作成日</option>
                    <option value="is_active">ステータス</option>
                </select>
            </div>
            <div>
                <label for="sort-direction" class="block text-sm font-medium text-gray-700 mb-1">順序</label>
                <select id="sort-direction" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="asc">昇順</option>
                    <option value="desc">降順</option>
                </select>
            </div>
        </div>
        <div class="flex justify-between items-center mt-4">
            <button id="clear-filters-btn" class="text-gray-600 hover:text-gray-800 text-sm">フィルターをクリア</button>
            <div class="flex items-center space-x-4">
                <label for="per-page" class="text-sm text-gray-700">表示件数:</label>
                <select id="per-page" class="border border-gray-300 rounded px-2 py-1 text-sm">
                    <option value="10">10件</option>
                    <option value="25">25件</option>
                    <option value="50">50件</option>
                </select>
            </div>
        </div>
    </div>

    <!-- 一括操作バー -->
    <div class="bg-white rounded-lg shadow-sm mb-6 p-4">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <label class="flex items-center">
                    <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">すべて選択</span>
                </label>
                <button id="bulk-delete-btn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm" disabled>
                    選択したプリセットを削除
                </button>
            </div>
        </div>
    </div>

    <!-- プリセット一覧テーブル -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            プリセット情報
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            時間枠数
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ステータス
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            表示順
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            作成日
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            操作
                        </th>
                    </tr>
                </thead>
                <tbody id="presets-table-body" class="bg-white divide-y divide-gray-200">
                    <!-- プリセット一覧がここに動的に挿入される -->
                </tbody>
            </table>
        </div>

        <!-- ページネーション -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div id="pagination-container">
                <!-- ページネーションがここに動的に挿入される -->
            </div>
        </div>
    </div>

    <!-- プリセット作成/編集モーダル -->
    <div id="preset-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="modal-title" class="text-lg font-medium text-gray-900">プリセット作成</h3>
                    <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="preset-form">
                    <!-- 基本情報 -->
                    <div class="mb-6">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="preset-name" class="block text-sm font-medium text-gray-700 mb-1">
                                    プリセット名 <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="preset-name" name="name" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                       required>
                                <span id="error-name" class="text-red-500 text-sm" style="display: none;"></span>
                            </div>
                            <div>
                                <label for="preset-description" class="block text-sm font-medium text-gray-700 mb-1">
                                    説明
                                </label>
                                <textarea id="preset-description" name="description" rows="3"
                                         class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                         placeholder="プリセットの説明を入力してください..."></textarea>
                                <span id="error-description" class="text-red-500 text-sm" style="display: none;"></span>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="preset-is-active" name="is_active" value="1" checked
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <label for="preset-is-active" class="ml-2 text-sm text-gray-700">
                                    このプリセットをアクティブにする
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 時間枠設定 -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <label class="text-lg font-semibold">時間枠設定</label>
                            <button type="button" id="add-timeslot-modal-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm inline-flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                時間枠を追加
                            </button>
                        </div>

                        <div id="modal-timeslots-container">
                            <!-- 初期の時間枠 -->
                            <div class="modal-timeslot-row border rounded-lg p-4 mb-3">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium mb-1">開始時間</label>
                                        <input type="time" name="time_slots[0][start_time]" 
                                               class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium mb-1">終了時間</label>
                                        <input type="time" name="time_slots[0][end_time]" 
                                               class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    </div>
                                    <div class="w-24">
                                        <label class="block text-sm font-medium mb-1">定員</label>
                                        <input type="number" name="time_slots[0][capacity]" value="1" min="1" max="100"
                                               class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    </div>
                                    <div class="w-32">
                                        <label class="block text-sm font-medium mb-1">サービスID</label>
                                        <input type="text" name="time_slots[0][service_id]" 
                                               class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                               placeholder="任意">
                                    </div>
                                    <div class="flex-shrink-0">
                                        <button type="button" class="remove-modal-timeslot-btn mt-6 text-red-600 hover:text-red-800" disabled>
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

                    <!-- フォームボタン -->
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancel-btn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                            キャンセル
                        </button>
                        <button type="submit" id="submit-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                            作成
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 削除確認モーダル -->
    <div id="delete-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-2">プリセットを削除</h3>
                <div class="mt-2 px-7 py-3">
                    <p id="delete-message" class="text-sm text-gray-500"></p>
                </div>
                <div class="flex justify-center space-x-3 mt-4">
                    <button id="cancel-delete" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                        キャンセル
                    </button>
                    <button id="confirm-delete" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                        削除
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/preset-manager.js') }}"></script>
@endpush
