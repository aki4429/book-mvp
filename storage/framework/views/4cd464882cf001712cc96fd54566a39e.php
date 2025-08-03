<div class="prevent-scroll-jump">
  <div class="p-4">
    <!-- ヘッダー部分 -->
    <div class="flex justify-between items-center mb-4">
      <button wire:click="prevMonth" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded">←</button>
      <h2 class="text-lg font-bold"><?php echo e($currentMonth->format('Y年n月')); ?></h2>
      <button wire:click="nextMonth" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded">→</button>
    </div>

    <!-- 顧客ログイン/ダッシュボードリンク（管理者カレンダーでは非表示） -->
    <!--[if BLOCK]><![endif]--><?php if(!$isAdmin): ?>
      <div class="mb-4 text-right">
        <!--[if BLOCK]><![endif]--><?php if(auth()->guard('customer')->check()): ?>
          <div class="flex items-center justify-end space-x-4">
            <span class="text-sm text-gray-600"><?php echo e(Auth::guard('customer')->user()->name); ?>さん</span>
            <a href="<?php echo e(route('customer.dashboard')); ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
              マイページ
            </a>
            <form method="POST" action="<?php echo e(route('customer.logout')); ?>" class="inline">
              <?php echo csrf_field(); ?>
              <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                ログアウト
              </button>
            </form>
          </div>
        <?php else: ?>
          <a href="<?php echo e(route('customer.login')); ?>"
            class="inline-flex items-center px-4 py-2 border border-blue-300 rounded-md text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100">
            ログイン・新規登録
          </a>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
      </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <div class="grid grid-cols-7 gap-2 text-center" wire:key="calendar-grid-<?php echo e($year); ?>-<?php echo e($month); ?>">
      <!--[if BLOCK]><![endif]--><?php $__currentLoopData = ['日', '月', '火', '水', '木', '金', '土']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="font-semibold"><?php echo e($day); ?></div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

      <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $weeks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $week; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php
            $dateKey = $day->format('Y-m-d');
            $daySlots = $slots[$dateKey] ?? collect();
            $availableCount = $daySlots->where('available', true)->count();
            $totalSlots = $daySlots->count();

            // 予約可能日かどうかを判定
            $isReservationAllowed = $day->gte($reservationStartDate);

            // 満杯の時間枠数を計算（予約数 = 定員の時間枠）
            $fullSlots = $daySlots
                ->filter(function ($slot) {
                    return $slot->getCurrentReservationCount() >= $slot->capacity;
                })
                ->count();

            if ($isAdmin) {
                // 管理者向け詳細表示：満杯の時間枠数/総時間枠数
                if (!$isReservationAllowed) {
                    $mark = '×';
                    $detail = '予約不可';
                } elseif ($totalSlots === 0) {
                    $mark = '×';
                    $detail = '枠なし';
                } elseif ($availableCount === 0) {
                    $mark = '×';
                    $detail = $fullSlots . '/' . $totalSlots;
                } elseif ($availableCount <= 2) {
                    $mark = '△';
                    $detail = $fullSlots . '/' . $totalSlots;
                } else {
                    $mark = '○';
                    $detail = $fullSlots . '/' . $totalSlots;
                }
            } else {
                // 顧客向け表示：予約不可日は空白
                if (!$isReservationAllowed) {
                    $mark = '';
                } elseif ($totalSlots === 0) {
                    $mark = '';
                } elseif ($availableCount === 0) {
                    $mark = '満';
                } elseif ($availableCount <= 2) {
                    $mark = '△';
                } else {
                    $mark = '○';
                }
            }

            // セルの背景色とボーダー色を決定
            $cellClasses = 'border p-1 text-sm relative transition-colors duration-200';

            if ($pinnedDate === $dateKey) {
                $cellClasses .= ' bg-blue-100 border-blue-300';
            } else {
                // 予約可能日かどうかで表示を変更
                if (!$isReservationAllowed) {
                    // 予約不可日：グレー背景でクリック無効
                    $cellClasses .= ' bg-gray-200 border-gray-400 opacity-60';
                } elseif ($totalSlots === 0) {
                    // 枠なし：グレー背景
                    $cellClasses .= ' bg-gray-100 border-gray-300 hover:bg-gray-150';
                } elseif ($availableCount === 0) {
                    // 満員：薄い赤背景
                    $cellClasses .= ' bg-red-50 border-red-200 hover:bg-red-100';
                } elseif ($availableCount <= 2) {
                    // 残りわずか：薄いオレンジ背景
                    $cellClasses .= ' bg-orange-50 border-orange-200 hover:bg-orange-100';
                } else {
                    // 余裕あり：薄い青背景
                    $cellClasses .= ' bg-blue-50 border-blue-200 hover:bg-blue-100';
                }
            }
          ?>

          <div class="<?php echo e($cellClasses); ?>"
            <?php if(!$isReservationManagement): ?> wire:mouseenter="hoverDate('<?php echo e($dateKey); ?>')" wire:mouseleave="unhoverDate()" <?php endif; ?>
            wire:click="pinDate('<?php echo e($dateKey); ?>')">
            <div><?php echo e($day->format('j')); ?></div>

            <!--[if BLOCK]><![endif]--><?php if($isAdmin): ?>
              
              <!--[if BLOCK]><![endif]--><?php if($mark === '○' && $isReservationAllowed): ?>
                <span class="text-blue-500 block cursor-pointer"><?php echo e($mark); ?></span>
              <?php elseif($mark === '△' && $isReservationAllowed): ?>
                <span class="text-orange-500 block cursor-pointer"><?php echo e($mark); ?></span>
              <?php else: ?>
                <span class="text-gray-400 block"><?php echo e($mark); ?></span>
              <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
              <div class="text-xs text-gray-600 mt-1"><?php echo e($detail); ?></div>
            <?php else: ?>
              
              <!--[if BLOCK]><![endif]--><?php if($mark === '○' && $isReservationAllowed): ?>
                <span class="text-blue-500 block cursor-pointer"><?php echo e($mark); ?></span>
              <?php elseif($mark === '△' && $isReservationAllowed): ?>
                <span class="text-orange-500 block cursor-pointer"><?php echo e($mark); ?></span>
              <?php elseif($mark === '満' && $isReservationAllowed): ?>
                <span class="text-red-500 block"><?php echo e($mark); ?></span>
              <?php elseif($mark !== '' && $isReservationAllowed): ?>
                <span class="text-gray-400 block"><?php echo e($mark); ?></span>
              <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <!--[if BLOCK]><![endif]--><?php if($isAdmin): ?>
              <button wire:click.stop="openTimeSlotManager('<?php echo e($dateKey); ?>')"
                class="text-green-600 hover:text-green-800 underline text-xs block mt-1">
                時間枠管理
              </button>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    
    <div class="mt-6">
      
      <div class="min-h-[180px] smooth-layout">
        <!--[if BLOCK]><![endif]--><?php if($hoveredDate): ?>
          <?php
            $hoveredSlots = $slots[$hoveredDate] ?? collect();
            $isPinned = $pinnedDate === $hoveredDate;
          ?>
          <div
            class="p-4 border rounded-lg shadow-sm <?php echo e($isPinned ? 'bg-blue-100 border-blue-300' : 'bg-blue-50'); ?> transition-all duration-200"
            <?php if(!$isReservationManagement): ?> wire:mouseenter="hoverDate('<?php echo e($hoveredDate); ?>')" wire:mouseleave="unhoverDate()" <?php endif; ?>>
            <div class="flex justify-between items-center mb-3">
              <h3 class="font-semibold text-gray-900">
                <?php echo e(\Carbon\Carbon::parse($hoveredDate)->format('Y年n月j日')); ?>

                <!--[if BLOCK]><![endif]--><?php if($isAdmin): ?>
                  の時間枠詳細
                <?php else: ?>
                  の予約可能時間
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
              </h3>
              <!--[if BLOCK]><![endif]--><?php if($isPinned): ?>
                <div class="flex items-center space-x-2">
                  <span class="text-xs text-blue-600 bg-blue-200 px-2 py-1 rounded">固定表示中</span>
                  <button wire:click="clearPin()" class="text-xs text-gray-500 hover:text-gray-700 underline">
                    ✕ 閉じる
                  </button>
                </div>
              <?php else: ?>
                <span class="text-xs text-gray-500">
                  📌 クリックで固定表示
                </span>
              <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            
            <div class="max-h-28 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 pr-2">
              <!--[if BLOCK]><![endif]--><?php if($isAdmin): ?>
                
                <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $hoveredSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <?php
                    $reservedCount = $slot->getCurrentReservationCount();
                    $availableCount = $slot->capacity - $reservedCount;
                    $isAvailable = $availableCount > 0;
                  ?>
                  <div
                    class="inline-block mr-2 mb-2 px-4 py-2 bg-white border rounded-lg text-sm <?php echo e($isAvailable ? 'border-blue-200' : 'border-red-200'); ?>">
                    <div class="font-medium <?php echo e($isAvailable ? 'text-blue-700' : 'text-red-700'); ?>">
                      <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('H:i')); ?>-<?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('H:i')); ?>

                    </div>
                    <div class="text-xs text-gray-600">
                      予約: <?php echo e($reservedCount); ?>/<?php echo e($slot->capacity); ?>名
                    </div>
                    <!--[if BLOCK]><![endif]--><?php if($isAvailable): ?>
                      <div class="text-xs text-blue-600">
                        空き: <?php echo e($availableCount); ?>名
                      </div>
                    <?php else: ?>
                      <div class="text-xs text-red-600">
                        満員
                      </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                  </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <div class="text-gray-600 text-sm">この日は時間枠が設定されていません</div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
              <?php else: ?>
                
                <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $hoveredSlots->where('available', true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <!--[if BLOCK]><![endif]--><?php if(auth()->guard('customer')->check()): ?>
                    <a href="<?php echo e(route('customer.reservations.create', ['slot_id' => $slot->id])); ?>"
                      class="inline-block mr-2 mb-2 px-4 py-2 bg-white border border-blue-200 hover:bg-blue-100 rounded-lg text-sm transition-colors duration-200 shadow-sm">
                      <div class="font-medium text-blue-700">
                        <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('H:i')); ?>-<?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('H:i')); ?>

                      </div>
                      <div class="text-xs text-gray-600">
                        空き: <?php echo e($slot->capacity - $slot->getCurrentReservationCount()); ?>/<?php echo e($slot->capacity); ?>名
                      </div>
                    </a>
                  <?php else: ?>
                    <a href="<?php echo e(route('customer.login')); ?>"
                      class="inline-block mr-2 mb-2 px-4 py-2 bg-white border border-blue-200 hover:bg-blue-100 rounded-lg text-sm transition-colors duration-200 shadow-sm">
                      <div class="font-medium text-blue-700">
                        <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('H:i')); ?>-<?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('H:i')); ?>

                      </div>
                      <div class="text-xs text-gray-600">
                        空き: <?php echo e($slot->capacity - $slot->getCurrentReservationCount()); ?>/<?php echo e($slot->capacity); ?>名
                      </div>
                      <div class="text-xs text-orange-600 font-medium">
                        ログインが必要です
                      </div>
                    </a>
                  <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <div class="text-gray-600 text-sm">この日は予約可能な時間がありません</div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
              <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
          </div>
        <?php else: ?>
          
          <div class="p-4 border border-transparent rounded-lg opacity-0 pointer-events-none">
            <div class="h-6 mb-3"></div>
            <div class="h-16"></div>
          </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
      </div>
    </div>
  </div>

  <!--[if BLOCK]><![endif]--><?php if($isAdmin): ?>
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('time-slot-form', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-2261171407-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('time-slot-manager', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-2261171407-1', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
  <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH /var/www/html/app_3/resources/views/livewire/calendar.blade.php ENDPATH**/ ?>