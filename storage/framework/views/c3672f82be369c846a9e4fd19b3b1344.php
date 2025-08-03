<?php if($slots->count() > 0): ?>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php $__currentLoopData = $slots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="time-slot border border-gray-200 rounded-lg p-4 transition-all duration-200 <?php echo e($slot->available ? 'hover:border-blue-300 hover:bg-blue-50' : 'bg-gray-50'); ?>">
        <div class="flex justify-between items-start mb-2">
          <div>
            <div class="font-medium text-gray-900">
              <?php echo e($slot->start_time->format('H:i')); ?> - <?php echo e($slot->end_time->format('H:i')); ?>

            </div>
            <?php if($slot->service_id): ?>
              <div class="text-sm text-gray-600">サービスID: <?php echo e($slot->service_id); ?></div>
            <?php endif; ?>
          </div>
          <div class="text-sm">
            <?php if($slot->available): ?>
              <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                空きあり
              </span>
            <?php else: ?>
              <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                満席
              </span>
            <?php endif; ?>
          </div>
        </div>
        
        <?php if($slot->capacity): ?>
          <div class="text-sm text-gray-500 mb-3">
            定員: <?php echo e($slot->capacity); ?>名
          </div>
        <?php endif; ?>

        <?php if($slot->available): ?>
          <button onclick="window.redirectToReservation(<?php echo e($slot->id); ?>)" 
                  class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors cursor-pointer">
            この時間で予約する
          </button>
        <?php else: ?>
          <div class="w-full bg-gray-300 text-gray-500 py-2 px-4 rounded-lg text-sm font-medium text-center cursor-not-allowed">
            予約不可
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
<?php else: ?>
  <div class="text-center py-8 text-gray-500">
    <p>この日は予約枠がありません。</p>
  </div>
<?php endif; ?>
<?php /**PATH /var/www/html/app_3/resources/views/calendar/partials/day-slots.blade.php ENDPATH**/ ?>