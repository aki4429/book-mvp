<?php $__env->startSection('title', '予約カレンダー'); ?>

<?php $__env->startSection('body'); ?>
  <div class="flex-1 p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">予約カレンダー</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
      <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('calendar', ['isAdmin' => true, 'isReservationManagement' => false]);

$__html = app('livewire')->mount($__name, $__params, 'lw-464082022-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/app_3/resources/views/calendar.blade.php ENDPATH**/ ?>