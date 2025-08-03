<?php $__env->startSection('page-title', '予約枠一覧'); ?>

<?php $__env->startSection('body'); ?>
  <h2 class="text-xl font-semibold mb-6">予約枠一覧</h2>

  <a href="<?php echo e(route('admin.timeslots.create')); ?>" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">
    ＋ 新規作成
  </a>

  <?php if(session('success')): ?>
    <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
      <?php echo e(session('success')); ?>

    </div>
  <?php endif; ?>

  <table class="w-full border-collapse border border-gray-300 text-sm">
    <thead class="bg-gray-100">
      <tr>
        <th class="border px-2 py-1">ID</th>
        <th class="border px-2 py-1">日付</th>
        <th class="border px-2 py-1">時間</th>
        <th class="border px-2 py-1">定員</th>
        <th class="border px-2 py-1">予約可能</th>
        <th class="border px-2 py-1">操作</th>
      </tr>
    </thead>
    <tbody>
      <?php $__empty_1 = true; $__currentLoopData = $timeSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
          <td class="border px-2 py-1"><?php echo e($slot->id); ?></td>
          <td class="border px-2 py-1"><?php echo e(\Carbon\Carbon::parse($slot->date)->format('Y-m-d')); ?> </td>
          <td class="border px-2 py-1"><?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('H:i')); ?> -
            <?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('H:i')); ?> </td>
          <td class="border px-2 py-1"><?php echo e($slot->capacity); ?></td>
          <td class="border px-2 py-1"><?php echo e($slot->available ? '○' : '×'); ?></td>
          <td class="border px-2 py-1">
            <a href="<?php echo e(route('admin.timeslots.edit', $slot)); ?>" class="text-blue-600 mr-2">編集</a>

            <form action="<?php echo e(route('admin.timeslots.destroy', $slot)); ?>" method="POST" class="inline"
              onsubmit="return confirm('削除してもよろしいですか？')">
              <?php echo csrf_field(); ?>
              <?php echo method_field('DELETE'); ?>
              <button type="submit" class="text-red-600">削除</button>
            </form>
          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
          <td colspan="6" class="text-center py-2">予約枠がありません。</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="mt-4">
    <?php echo e($timeSlots->links()); ?>

  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/app_3/resources/views/admin/timeslots/index.blade.php ENDPATH**/ ?>