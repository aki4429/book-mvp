<?php $__env->startSection('page-title', 'プリセット管理'); ?>

<?php $__env->startSection('body'); ?>
  <div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">時間枠プリセット管理</h2>
      <a href="<?php echo e(route('admin.presets.create')); ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        新規プリセット作成
      </a>
    </div>

    <?php if($presets->isEmpty()): ?>
      <div class="text-center py-12 bg-gray-50 rounded-lg">
        <p class="text-gray-500 mb-4">プリセットがありません</p>
        <a href="<?php echo e(route('admin.presets.create')); ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
          最初のプリセットを作成
        </a>
      </div>
    <?php else: ?>
      <div class="grid gap-4">
        <?php $__currentLoopData = $presets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $preset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="border rounded-lg p-4 bg-white <?php echo e($preset->is_active ? '' : 'opacity-50'); ?>">
            <div class="flex justify-between items-start mb-3">
              <div>
                <h3 class="text-lg font-semibold"><?php echo e($preset->name); ?></h3>
                <?php if($preset->description): ?>
                  <p class="text-gray-600 text-sm"><?php echo e($preset->description); ?></p>
                <?php endif; ?>
                <span
                  class="text-xs px-2 py-1 rounded <?php echo e($preset->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'); ?>">
                  <?php echo e($preset->is_active ? '有効' : '無効'); ?>

                </span>
              </div>
              <div class="flex space-x-2">
                <a href="<?php echo e(route('admin.presets.edit', $preset)); ?>" class="text-blue-600 hover:text-blue-800">
                  編集
                </a>
                <form method="POST" action="<?php echo e(route('admin.presets.destroy', $preset)); ?>" class="inline"
                  onsubmit="return confirm('このプリセットを削除しますか？')">
                  <?php echo csrf_field(); ?>
                  <?php echo method_field('DELETE'); ?>
                  <button type="submit" class="text-red-600 hover:text-red-800">削除</button>
                </form>
              </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
              <?php $__currentLoopData = $preset->time_slots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="text-sm bg-gray-50 p-2 rounded">
                  <div class="font-medium"><?php echo e($slot['start_time']); ?> - <?php echo e($slot['end_time']); ?></div>
                  <div class="text-gray-600">定員: <?php echo e($slot['capacity']); ?>名</div>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    <?php endif; ?>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/app_3/resources/views/admin/presets/index.blade.php ENDPATH**/ ?>