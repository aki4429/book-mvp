@extends('layouts.admin')

@section('title', 'システム設定管理 (JS版)')

@section('page-title', 'システム設定管理 (JS版)')

@section('body')
<div class="container mx-auto px-4 py-6">
    <!-- 検索・フィルター -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">検索</label>
                <input type="text" id="search-input" placeholder="キーまたは説明で検索..." 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">データ型</label>
                <select id="type-filter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">すべて</option>
                    <option value="string">文字列</option>
                    <option value="integer">整数</option>
                    <option value="boolean">真偽値</option>
                    <option value="json">JSON</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ソート項目</label>
                <select id="sort-field" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="key">キー</option>
                    <option value="type">データ型</option>
                    <option value="description">説明</option>
                    <option value="created_at">作成日</option>
                    <option value="updated_at">更新日</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">順序</label>
                <select id="sort-direction" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="asc">昇順</option>
                    <option value="desc">降順</option>
                </select>
            </div>
        </div>
        
        <div class="mt-4 flex justify-between items-center">
            <button id="clear-filters-btn" class="text-gray-600 hover:text-gray-800 text-sm">フィルターをクリア</button>
            <div class="flex items-center space-x-4">
                <button id="system-info-btn" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm">
                    システム情報
                </button>
                <button id="bulk-delete-btn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    選択した設定を削除
                </button>
                <button id="create-setting-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                    新規設定作成
                </button>
                <span class="text-sm text-gray-600">表示件数:
                    <select id="per-page" class="border border-gray-300 rounded px-2 py-1 ml-1">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </span>
            </div>
        </div>
    </div>

    <!-- アラートエリア -->
    <div id="alert-container" class="mb-6" style="display: none;">
        <div id="alert" class="rounded-lg p-4">
            <div class="flex items-center">
                <div id="alert-icon" class="mr-3"></div>
                <p id="alert-message" class="font-medium"></p>
            </div>
        </div>
    </div>

    <!-- ローディング表示 -->
    <div id="loading" class="text-center py-8" style="display: none;">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-600">読み込み中...</p>
    </div>

    <!-- 設定テーブル -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">設定一覧</h3>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="select-all" class="rounded border-gray-300">
                    <label for="select-all" class="text-sm text-gray-600">すべて選択</label>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            選択
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            キー
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            値
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            データ型
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            説明
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            更新日
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            操作
                        </th>
                    </tr>
                </thead>
                <tbody id="settings-table-body" class="bg-white divide-y divide-gray-200">
                    <!-- 設定データがここに動的に挿入されます -->
                </tbody>
            </table>
        </div>
        
        <!-- ページネーション -->
        <div id="pagination-container" class="px-6 py-4 border-t border-gray-200">
            <!-- ページネーションがここに動的に挿入されます -->
        </div>
    </div>
</div>

<!-- 設定作成/編集モーダル -->
<div id="setting-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- モーダルヘッダー -->
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 id="modal-title" class="text-lg font-medium text-gray-900">設定作成</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- 設定フォーム -->
            <form id="setting-form" class="mt-6">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">キー <span class="text-red-500">*</span></label>
                        <input type="text" id="setting-key" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               placeholder="setting_key" required>
                        <p class="mt-1 text-xs text-gray-500">英数字とアンダースコアのみ使用可能</p>
                        <div id="error-key" class="text-red-600 text-sm mt-1" style="display: none;"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">データ型 <span class="text-red-500">*</span></label>
                        <select id="setting-type" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="string">文字列</option>
                            <option value="integer">整数</option>
                            <option value="boolean">真偽値</option>
                            <option value="json">JSON</option>
                        </select>
                        <div id="error-type" class="text-red-600 text-sm mt-1" style="display: none;"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">値 <span class="text-red-500">*</span></label>
                        <textarea id="setting-value" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                  placeholder="設定の値を入力" required></textarea>
                        <div id="value-help" class="mt-1 text-xs text-gray-500"></div>
                        <div id="error-value" class="text-red-600 text-sm mt-1" style="display: none;"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">説明</label>
                        <textarea id="setting-description" rows="2" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                  placeholder="この設定の説明（任意）"></textarea>
                        <div id="error-description" class="text-red-600 text-sm mt-1" style="display: none;"></div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" id="cancel-btn" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        キャンセル
                    </button>
                    <button type="submit" id="submit-btn" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2">設定を削除</h3>
            <div class="mt-2 px-7 py-3">
                <p id="delete-message" class="text-sm text-gray-500"></p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirm-delete" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700">
                    削除する
                </button>
                <button id="cancel-delete" class="mt-3 px-4 py-2 bg-white text-gray-700 text-base font-medium rounded-md w-full shadow-sm border border-gray-300 hover:bg-gray-50">
                    キャンセル
                </button>
            </div>
        </div>
    </div>
</div>

<!-- システム情報モーダル -->
<div id="system-info-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- モーダルヘッダー -->
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">システム情報</h3>
                <button id="close-system-info-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- システム情報コンテンツ -->
            <div id="system-info-content" class="mt-6">
                <!-- システム情報がここに動的に挿入されます -->
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/settings-manager.js') }}"></script>
@endsection

@push('scripts')
<script src="{{ asset('js/settings-manager.js') }}"></script>
@endpush

@push('scripts')
@endpush
