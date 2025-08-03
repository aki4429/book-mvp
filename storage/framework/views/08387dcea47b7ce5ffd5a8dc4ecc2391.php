<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>マイページ - 予約システム</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <!-- ヘッダー -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-gray-800">マイページ</h1>
                    <span class="text-gray-600">こんにちは、<?php echo e($customer->name); ?>さん</span>
                </div>
                
                <div class="flex items-center space-x-3">
                    <!-- カレンダーへ戻る -->
                    <a href="<?php echo e(route('calendar.public')); ?>" 
                       class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>カレンダー</span>
                    </a>
                    
                    <!-- プロフィール編集 -->
                    <a href="<?php echo e(route('customer.profile.edit')); ?>" 
                       class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span>プロフィール編集</span>
                    </a>
                    
                    <!-- ログアウト -->
                    <form method="POST" action="<?php echo e(route('customer.logout')); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" 
                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-2"
                                onclick="return confirm('ログアウトしますか？')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            <span>ログアウト</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 成功メッセージ -->
        <?php if(session('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?php echo e(session('success')); ?>

        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- プロフィール情報 -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">プロフィール情報</h2>
                    <a href="<?php echo e(route('customer.profile.edit')); ?>" 
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        編集
                    </a>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">お名前</label>
                        <p class="mt-1 text-gray-900"><?php echo e($customer->name); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">メールアドレス</label>
                        <p class="mt-1 text-gray-900"><?php echo e($customer->email); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">電話番号</label>
                        <p class="mt-1 text-gray-900"><?php echo e($customer->phone ?: '未設定'); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">会員登録日</label>
                        <p class="mt-1 text-gray-900"><?php echo e($customer->created_at->format('Y年m月d日')); ?></p>
                    </div>
                </div>
            </div>

            <!-- 今後の予約 -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">今後の予約</h2>
                    <a href="<?php echo e(route('customer.reservations.index')); ?>" 
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        すべて見る
                    </a>
                </div>
                
                <?php if($upcomingReservations->count() > 0): ?>
                    <div class="space-y-3">
                        <?php $__currentLoopData = $upcomingReservations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reservation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium text-gray-900">
                                        <?php echo e($reservation->timeSlot->date->format('m月d日(D)')); ?>

                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <?php echo e($reservation->timeSlot->start_time->format('H:i')); ?> - 
                                        <?php echo e($reservation->timeSlot->end_time->format('H:i')); ?>

                                    </p>
                                    <?php if($reservation->notes): ?>
                                    <p class="text-sm text-gray-500 mt-1"><?php echo e($reservation->notes); ?></p>
                                    <?php endif; ?>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    <?php echo e($reservation->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'); ?>">
                                    <?php echo e($reservation->status === 'confirmed' ? '確定' : $reservation->status); ?>

                                </span>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-8">今後の予約はありません</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 過去の予約履歴 -->
        <div class="bg-white shadow-sm rounded-lg p-6 mt-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">最近の予約履歴</h2>
                <a href="<?php echo e(route('customer.reservations.index')); ?>" 
                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    すべて見る
                </a>
            </div>
            
            <?php if($pastReservations->count() > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日時</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">メモ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $pastReservations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reservation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo e($reservation->timeSlot->date->format('Y年m月d日(D)')); ?>

                                        </p>
                                        <p class="text-sm text-gray-500">
                                            <?php echo e($reservation->timeSlot->start_time->format('H:i')); ?> - 
                                            <?php echo e($reservation->timeSlot->end_time->format('H:i')); ?>

                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        <?php echo e($reservation->status === 'completed' ? 'bg-blue-100 text-blue-800' : 
                                           ($reservation->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')); ?>">
                                        <?php echo e($reservation->status === 'completed' ? '完了' : 
                                           ($reservation->status === 'confirmed' ? '確定' : $reservation->status)); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php echo e($reservation->notes ?: '-'); ?>

                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-8">予約履歴はありません</p>
            <?php endif; ?>
        </div>

        <!-- クイックアクション -->
        <div class="bg-white shadow-sm rounded-lg p-6 mt-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">クイックアクション</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="<?php echo e(route('calendar.public')); ?>" 
                   class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">新しい予約</h3>
                            <p class="text-sm text-gray-500">カレンダーから予約する</p>
                        </div>
                    </div>
                </a>
                
                <a href="<?php echo e(route('customer.reservations.index')); ?>" 
                   class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">予約管理</h3>
                            <p class="text-sm text-gray-500">予約の確認・キャンセル</p>
                        </div>
                    </div>
                </a>
                
                <a href="<?php echo e(route('customer.profile.edit')); ?>" 
                   class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">プロフィール</h3>
                            <p class="text-sm text-gray-500">個人情報の編集</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH /var/www/html/app_3/resources/views/customer/dashboard.blade.php ENDPATH**/ ?>