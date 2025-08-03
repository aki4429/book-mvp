<?php $__env->startSection('title', 'ユーザー管理 (JS版)'); ?>
<?php $__env->startSection('page-title', 'ユーザー管理'); ?>

<?php $__env->startPush('head'); ?>
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('body'); ?>
<div id="user-manager-app">
    <!-- ヘッダー -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">ユーザー管理</h2>
            <button id="create-user-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                新規ユーザー作成
            </button>
        </div>
    </div>

    <!-- 検索・フィルターエリア -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">検索</label>
                <input type="text" id="search-input" placeholder="名前またはメールアドレスで検索"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">権限フィルター</label>
                <select id="role-filter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">すべて</option>
                    <option value="admin">管理者</option>
                    <option value="user">一般ユーザー</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ソート</label>
                <select id="sort-field" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="created_at">作成日</option>
                    <option value="name">名前</option>
                    <option value="email">メールアドレス</option>
                    <option value="is_admin">権限</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">順序</label>
                <select id="sort-direction" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="desc">降順</option>
                    <option value="asc">昇順</option>
                </select>
            </div>
        </div>
        
        <div class="mt-4 flex justify-between items-center">
            <button id="clear-filters-btn" class="text-gray-600 hover:text-gray-800 text-sm">フィルターをクリア</button>
            <div class="flex items-center space-x-4">
                <button id="bulk-delete-btn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    選択したユーザーを削除
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

    <!-- ユーザーテーブル -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">ユーザー一覧</h3>
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
                            ユーザー情報
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            権限
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            予約
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            作成日
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            操作
                        </th>
                    </tr>
                </thead>
                <tbody id="users-table-body" class="bg-white divide-y divide-gray-200">
                    <!-- ユーザーデータがここに動的に挿入されます -->
                </tbody>
            </table>
        </div>
        
        <!-- ページネーション -->
        <div id="pagination-container" class="px-6 py-4 border-t border-gray-200">
            <!-- ページネーションがここに動的に挿入されます -->
        </div>
    </div>
</div>

<!-- ユーザー作成/編集モーダル -->
<div id="user-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- モーダルヘッダー -->
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 id="modal-title" class="text-lg font-medium text-gray-900">ユーザー作成</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- モーダルボディ -->
            <form id="user-form" class="mt-6 space-y-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="user-name" class="block text-sm font-medium text-gray-700">
                            名前 <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="user-name" name="name" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div id="error-name" class="mt-1 text-sm text-red-600" style="display: none;"></div>
                    </div>
                    
                    <div>
                        <label for="user-email" class="block text-sm font-medium text-gray-700">
                            メールアドレス <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="user-email" name="email" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div id="error-email" class="mt-1 text-sm text-red-600" style="display: none;"></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="user-password" class="block text-sm font-medium text-gray-700">
                            <span id="password-label">パスワード <span class="text-red-500">*</span></span>
                        </label>
                        <input type="password" id="user-password" name="password"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div id="error-password" class="mt-1 text-sm text-red-600" style="display: none;"></div>
                        <p id="password-help" class="mt-1 text-sm text-gray-500">編集時は空欄で現在のパスワードを保持</p>
                    </div>
                    
                    <div>
                        <label for="user-password-confirmation" class="block text-sm font-medium text-gray-700">
                            パスワード確認
                        </label>
                        <input type="password" id="user-password-confirmation" name="password_confirmation"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <div id="error-password_confirmation" class="mt-1 text-sm text-red-600" style="display: none;"></div>
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center">
                        <input type="checkbox" id="user-is-admin" name="is_admin" value="1"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="user-is-admin" class="ml-2 block text-sm text-gray-900">
                            管理者権限を付与する
                        </label>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        チェックを入れると、このユーザーは管理者として全ての機能にアクセスできるようになります。
                    </p>
                </div>
                
                <!-- モーダルフッター -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <button type="button" id="cancel-btn" 
                            class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                        キャンセル
                    </button>
                    <button type="submit" id="save-btn"
                            class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium">
                        保存
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 削除確認モーダル -->
<div id="delete-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2">ユーザーを削除</h3>
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

<!-- 予約管理モーダル -->
<div id="reservation-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-6xl shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
        <div class="mt-3">
            <!-- モーダルヘッダー -->
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 id="reservation-modal-title" class="text-lg font-medium text-gray-900">予約管理</h3>
                <button id="close-reservation-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- 予約フィルター -->
            <div class="py-4 border-b">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">開始日</label>
                        <input type="date" id="reservation-date-from" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">終了日</label>
                        <input type="date" id="reservation-date-to" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ステータス</label>
                        <select id="reservation-status-filter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">すべて</option>
                            <option value="confirmed">確定</option>
                            <option value="cancelled">キャンセル</option>
                            <option value="completed">完了</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ソート</label>
                        <select id="reservation-sort" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="created_at">作成日</option>
                            <option value="date">予約日</option>
                            <option value="status">ステータス</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-between items-center mt-4">
                    <button id="clear-reservation-filters" class="text-gray-600 hover:text-gray-800 text-sm">フィルターをクリア</button>
                    <button id="create-reservation-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">新規予約作成</button>
                </div>
            </div>

            <!-- 予約一覧テーブル -->
            <div class="mt-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">予約日時</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">時間</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">メモ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">作成日</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                            </tr>
                        </thead>
                        <tbody id="reservations-table-body" class="bg-white divide-y divide-gray-200">
                            <!-- 予約データがここに動的に挿入されます -->
                        </tbody>
                    </table>
                </div>
                
                <!-- 予約ページネーション -->
                <div id="reservation-pagination-container" class="px-6 py-4 border-t border-gray-200">
                    <!-- ページネーションがここに動的に挿入されます -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 予約作成/編集モーダル -->
<div id="reservation-form-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- モーダルヘッダー -->
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 id="reservation-form-title" class="text-lg font-medium text-gray-900">予約作成</h3>
                <button id="close-reservation-form-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- 予約フォーム -->
            <form id="reservation-form" class="mt-6">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">時間枠</label>
                        <select id="reservation-timeslot" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">時間枠を選択してください</option>
                        </select>
                        <div id="error-timeslot" class="text-red-600 text-sm mt-1" style="display: none;"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ステータス</label>
                        <select id="reservation-status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="confirmed">確定</option>
                            <option value="cancelled">キャンセル</option>
                            <option value="completed">完了</option>
                        </select>
                        <div id="error-status" class="text-red-600 text-sm mt-1" style="display: none;"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">メモ</label>
                        <textarea id="reservation-notes" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="予約に関するメモ（任意）"></textarea>
                        <div id="error-notes" class="text-red-600 text-sm mt-1" style="display: none;"></div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" id="cancel-reservation-btn" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        キャンセル
                    </button>
                    <button type="submit" id="submit-reservation-btn" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">
                        作成
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?php echo e(asset('js/user-manager.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/user-manager.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/app_3/resources/views/admin/user-manager/index.blade.php ENDPATH**/ ?>