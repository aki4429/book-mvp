<?php $__env->startSection('title', '管理者ダッシュボード'); ?>
<?php $__env->startSection('page-title', '管理者ダッシュボード'); ?>

<?php $__env->startSection('body'); ?>
<div id="admin-dashboard">
    <!-- アラート表示エリア -->
    <div id="alert-container" style="display: none;">
        <div id="alert" class="rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <div id="alert-icon" class="mr-3"></div>
                <p id="alert-message" class="text-sm font-medium"></p>
            </div>
        </div>
    </div>

    <!-- ローディング表示 -->
    <div id="loading" class="text-center py-8" style="display: none;">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
        <p class="mt-2 text-gray-600">データを読み込み中...</p>
    </div>

    <!-- ナビゲーションカード -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <a href="<?php echo e(route('admin.calendar.index')); ?>" 
           class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">管理者カレンダー</h3>
                    <p class="text-blue-100 text-sm mt-1">予約カレンダー管理</p>
                </div>
                <svg class="w-8 h-8 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </a>

        <a href="<?php echo e(route('admin.user-manager.index')); ?>" 
           class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">ユーザー管理</h3>
                    <p class="text-green-100 text-sm mt-1">ユーザー・予約管理</p>
                </div>
                <svg class="w-8 h-8 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
            </div>
        </a>

        <a href="<?php echo e(route('admin.bulk-timeslots.index')); ?>" 
           class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">一括時間枠設定</h3>
                    <p class="text-purple-100 text-sm mt-1">時間枠の一括作成</p>
                </div>
                <svg class="w-8 h-8 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </a>

        <a href="<?php echo e(route('admin.preset-manager.index')); ?>" 
           class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">プリセット管理</h3>
                    <p class="text-orange-100 text-sm mt-1">時間枠プリセット</p>
                </div>
                <svg class="w-8 h-8 text-orange-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </a>

        <a href="<?php echo e(route('admin.settings-manager.index')); ?>" 
           class="bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-xl p-6 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">システム設定</h3>
                    <p class="text-gray-100 text-sm mt-1">システム設定管理</p>
                </div>
                <svg class="w-8 h-8 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
        </a>
    </div>

    <!-- 統計サマリー -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">総ユーザー数</p>
                    <p id="total-users" class="text-2xl font-bold text-gray-900"><?php echo e($stats['total_users']); ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">総顧客数</p>
                    <p id="total-customers" class="text-2xl font-bold text-gray-900"><?php echo e($stats['total_customers']); ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="m22 21-3-3m0 0-3-3m3 3-3 3m3-3-3-3"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">総予約数</p>
                    <p id="total-reservations" class="text-2xl font-bold text-gray-900"><?php echo e($stats['total_reservations']); ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">利用可能な時間枠</p>
                    <p id="active-timeslots" class="text-2xl font-bold text-gray-900"><?php echo e($stats['active_timeslots']); ?></p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- 期間別統計とチャート -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- 期間別統計 -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">期間別統計</h3>
                <select id="stats-period" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="today">今日</option>
                    <option value="week">今週</option>
                    <option value="month" selected>今月</option>
                    <option value="year">今年</option>
                </select>
            </div>
            <div id="period-stats" class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">予約数</span>
                    <span id="period-reservations" class="font-semibold text-gray-900"><?php echo e($todayStats['today_reservations']); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">時間枠数</span>
                    <span id="period-timeslots" class="font-semibold text-gray-900"><?php echo e($todayStats['today_timeslots']); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">利用可能枠数</span>
                    <span id="period-available-slots" class="font-semibold text-gray-900"><?php echo e($todayStats['today_available_slots']); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">新規顧客数</span>
                    <span id="period-customers" class="font-semibold text-gray-900"><?php echo e($todayStats['today_customers']); ?></span>
                </div>
            </div>
        </div>

        <!-- チャート -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">データチャート</h3>
                <div class="flex space-x-2">
                    <select id="chart-type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="reservations" selected>予約数</option>
                        <option value="customers">顧客数</option>
                        <option value="timeslots">時間枠数</option>
                    </select>
                    <select id="chart-period" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="week">週間</option>
                        <option value="month" selected>月間</option>
                        <option value="year">年間</option>
                    </select>
                </div>
            </div>
            <div id="chart-container" class="h-64">
                <canvas id="dashboard-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- 最近のアクティビティと月別統計 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- 最近のアクティビティ -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">最近のアクティビティ</h3>
                <button onclick="adminDashboard.loadRecentActivity()" class="text-blue-500 hover:text-blue-700 text-sm">
                    更新
                </button>
            </div>
            <div id="recent-activity" class="space-y-3">
                <?php $__currentLoopData = $recentReservations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reservation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900"><?php echo e($reservation->customer->name); ?></p>
                        <p class="text-xs text-gray-500"><?php echo e($reservation->timeSlot->date); ?> <?php echo e(substr($reservation->timeSlot->start_time, 0, 5)); ?>-<?php echo e(substr($reservation->timeSlot->end_time, 0, 5)); ?></p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            <?php echo e($reservation->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                            <?php echo e($reservation->status === 'confirmed' ? '確定' : '保留'); ?>

                        </span>
                        <p class="text-xs text-gray-500 mt-1"><?php echo e($reservation->created_at->format('m/d H:i')); ?></p>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <!-- システム情報 -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">システム情報</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">PHP バージョン</span>
                    <span class="font-semibold text-gray-900"><?php echo e($systemInfo['php_version']); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Laravel バージョン</span>
                    <span class="font-semibold text-gray-900"><?php echo e($systemInfo['laravel_version']); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">データベース</span>
                    <span class="font-semibold text-gray-900"><?php echo e($systemInfo['database_connection']); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">環境</span>
                    <span class="font-semibold text-gray-900"><?php echo e($systemInfo['app_env']); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">タイムゾーン</span>
                    <span class="font-semibold text-gray-900"><?php echo e($systemInfo['timezone']); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?php echo e(asset('js/admin-dashboard.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/app_3/resources/views/admin/dashboard/index.blade.php ENDPATH**/ ?>