<div class="p-6">
  <!-- 曜日ヘッダー -->
  <div class="grid grid-cols-7 gap-1 mb-2">
    <div class="text-center text-sm font-medium text-gray-700 py-2">日</div>
    <div class="text-center text-sm font-medium text-gray-700 py-2">月</div>
    <div class="text-center text-sm font-medium text-gray-700 py-2">火</div>
    <div class="text-center text-sm font-medium text-gray-700 py-2">水</div>
    <div class="text-center text-sm font-medium text-gray-700 py-2">木</div>
    <div class="text-center text-sm font-medium text-gray-700 py-2">金</div>
    <div class="text-center text-sm font-medium text-gray-700 py-2">土</div>
  </div>

  <!-- カレンダーグリッド -->
  <div class="grid grid-cols-7 gap-1">
    <?php $__currentLoopData = $calendar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php $__currentLoopData = $week; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
          $dateString = $day['date']->format('Y-m-d');
          $isCurrentMonth = $day['isCurrentMonth'];
          $isToday = $day['isToday'];
          $hasSlots = $day['hasSlots'];
          $totalSlots = $day['totalSlots'];
          $totalReservations = $day['totalReservations'];
          $availableSlots = $day['availableSlots'] ?? 0;
          
          // 管理者用のデータを準備
          $adminData = [
            'totalSlots' => $totalSlots,
            'totalReservations' => $totalReservations,
            'slots' => isset($day['slots']) ? $day['slots']->map(function($slot) {
              return [
                'id' => $slot->id,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'capacity' => $slot->capacity,
                'service_id' => $slot->service_id,
                'available' => $slot->available,
                'reservations' => isset($slot->reservations) ? $slot->reservations->toArray() : []
              ];
            })->toArray() : []
          ];
        ?>
        
        <div 
          class="calendar-day relative bg-white border border-gray-200 p-2 min-h-[100px] cursor-pointer <?php echo e(!$isCurrentMonth ? 'text-gray-400 bg-gray-50' : ''); ?> <?php echo e($isToday ? 'ring-2 ring-blue-500' : ''); ?> <?php echo e($hasSlots ? 'bg-purple-50 border-purple-200' : ''); ?>"
          data-date="<?php echo e($dateString); ?>"
          data-admin-slots="<?php echo e(json_encode($adminData)); ?>"
          onclick="selectDate('<?php echo e($dateString); ?>')"
        >
          <!-- 日付 -->
          <div class="flex justify-between items-start mb-2">
            <span class="text-sm font-medium <?php echo e($isCurrentMonth ? 'text-gray-900' : 'text-gray-400'); ?>">
              <?php echo e($day['date']->format('j')); ?>

            </span>
            
            <?php if($hasSlots): ?>
              <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                <?php echo e($totalSlots); ?>

              </span>
            <?php endif; ?>
          </div>
          
          <!-- 予約件数の表示 -->
          <?php if($hasSlots): ?>
            <div class="mb-2">
              <?php if($totalReservations > 0): ?>
                <div class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200 mb-1">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                  予約: <?php echo e($totalReservations); ?>件
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          
          <!-- 予約状況 -->
          <?php if($hasSlots): ?>
            <div class="space-y-1">
              <?php if($availableSlots > 0): ?>
                <div class="text-xs text-green-600 font-medium">
                  空き: <?php echo e($availableSlots); ?>枠
                </div>
              <?php elseif($totalReservations > 0): ?>
                <div class="text-xs text-red-600 font-medium">
                  満席
                </div>
              <?php else: ?>
                <div class="text-xs text-gray-500">
                  予約なし
                </div>
              <?php endif; ?>
              
              <!-- 時間枠の状態ドット -->
              <?php if(isset($day['slots']) && $day['slots']->count() > 0): ?>
                <div class="flex flex-wrap gap-1 mt-2">
                  <?php $__currentLoopData = $day['slots']->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                      $reservationCount = isset($slot->reservations) ? $slot->reservations->count() : 0;
                      $capacity = $slot->capacity;
                      $isFull = $reservationCount >= $capacity;
                      $isAvailable = $slot->available;
                      
                      $dotClass = 'w-2 h-2 rounded-full ';
                      if (!$isAvailable) {
                        $dotClass .= 'bg-gray-400'; // 停止中
                      } elseif ($isFull) {
                        $dotClass .= 'bg-red-400'; // 満席
                      } elseif ($reservationCount > 0) {
                        $dotClass .= 'bg-yellow-400'; // 一部予約
                      } else {
                        $dotClass .= 'bg-green-400'; // 空き
                      }
                    ?>
                    
                    <div class="<?php echo e($dotClass); ?>" title="<?php echo e($slot->start_time); ?> - <?php echo e($slot->end_time); ?> (<?php echo e($reservationCount); ?>/<?php echo e($capacity); ?>)"></div>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  
                  <?php if($day['slots']->count() > 6): ?>
                    <span class="text-xs text-gray-500">+<?php echo e($day['slots']->count() - 6); ?></span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>
<?php /**PATH /var/www/html/app_3/resources/views/admin/calendar/partials/calendar-grid-simple.blade.php ENDPATH**/ ?>