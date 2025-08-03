<?php $__env->startSection('page-title', '予約作成'); ?>

<?php $__env->startSection('body'); ?>
  <?php if($selectedTimeSlot): ?>
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
      <h3 class="font-medium text-blue-900 mb-2">選択された時間枠</h3>
      <p class="text-blue-800">
        <?php echo e($selectedTimeSlot->date->format('Y年n月j日')); ?>

        <?php echo e($selectedTimeSlot->start_time_as_object->format('H:i')); ?> -
        <?php echo e($selectedTimeSlot->end_time_as_object->format('H:i')); ?>

      </p>
      <p class="text-sm text-blue-600 mt-1">
        残り <?php echo e($selectedTimeSlot->capacity - $selectedTimeSlot->reservations->count()); ?> 名分の空きがあります
      </p>
    </div>
  <?php endif; ?>

  <h2 class="text-xl font-semibold mb-6">予約を新規作成</h2>

  <form method="POST" action="<?php echo e(route('admin.reservations.store')); ?>">
    <?php echo csrf_field(); ?>
    <?php echo $__env->make('admin.reservations._form', [
        'reservation' => new \App\Models\Reservation(),
        'customers' => $customers,
        'timeSlots' => $timeSlots,
        'statuses' => $statuses,
        'selectedTimeSlot' => $selectedTimeSlot,
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/app_3/resources/views/admin/reservations/create.blade.php ENDPATH**/ ?>