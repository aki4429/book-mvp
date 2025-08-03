<?php $__env->startSection('page-title', 'プリセット編集'); ?>

<?php $__env->startSection('body'); ?>
  <div class="max-w-4xl mx-auto">
    <div class="flex items-center mb-6">
      <a href="<?php echo e(route('admin.presets.index')); ?>" class="text-blue-600 hover:text-blue-800 mr-4">
        ← 戻る
      </a>
      <h2 class="text-2xl font-bold">プリセット編集: <?php echo e($preset->name); ?></h2>
    </div>

    <form method="POST" action="<?php echo e(route('admin.presets.update', $preset)); ?>">
      <?php echo csrf_field(); ?>
      <?php echo method_field('PUT'); ?>

      <div class="mb-6 p-4 border rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="name" class="block text-sm font-medium mb-1">プリセット名 *</label>
            <input type="text" name="name" id="name" value="<?php echo e(old('name', $preset->name)); ?>"
              class="w-full border rounded px-3 py-2" required>
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
          <div>
            <label for="description" class="block text-sm font-medium mb-1">説明</label>
            <input type="text" name="description" id="description"
              value="<?php echo e(old('description', $preset->description)); ?>" class="w-full border rounded px-3 py-2">
            <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>
        </div>
        <div class="mt-4">
          <label class="flex items-center">
            <input type="checkbox" name="is_active" value="1"
              <?php echo e(old('is_active', $preset->is_active) ? 'checked' : ''); ?> class="mr-2">
            このプリセットを有効にする
          </label>
        </div>
      </div>

      <div class="mb-6 p-4 border rounded-lg">
        <div class="flex justify-between items-center mb-3">
          <label class="text-lg font-semibold">時間枠設定</label>
          <button type="button" id="add-time-slot" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
            + 時間枠を追加
          </button>
        </div>

        <div id="time-slots-container">
          <?php $__currentLoopData = old('time_slots', $preset->time_slots); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="time-slot-row mb-4 p-3 border rounded bg-gray-50">
              <div class="grid grid-cols-4 gap-4">
                <div>
                  <label class="block text-sm font-medium mb-1">開始時間</label>
                  <input type="time" name="time_slots[<?php echo e($index); ?>][start_time]"
                    value="<?php echo e($slot['start_time']); ?>" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">終了時間</label>
                  <input type="time" name="time_slots[<?php echo e($index); ?>][end_time]" value="<?php echo e($slot['end_time']); ?>"
                    class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">定員</label>
                  <input type="number" name="time_slots[<?php echo e($index); ?>][capacity]" value="<?php echo e($slot['capacity']); ?>"
                    min="1" class="w-full border rounded px-3 py-2" required>
                </div>
                <div class="flex items-end">
                  <button type="button"
                    class="remove-time-slot w-full bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">
                    削除
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>

      <div class="text-center">
        <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700">
          プリセットを更新
        </button>
      </div>
    </form>
  </div>

  <?php $__env->startPush('scripts'); ?>
    <script>
      let timeSlotIndex = <?php echo e(count(old('time_slots', $preset->time_slots))); ?>;

      document.getElementById('add-time-slot').addEventListener('click', function() {
        const container = document.getElementById('time-slots-container');
        const newRow = document.createElement('div');
        newRow.className = 'time-slot-row mb-4 p-3 border rounded bg-gray-50';
        newRow.innerHTML = `
        <div class="grid grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">開始時間</label>
            <input type="time" name="time_slots[${timeSlotIndex}][start_time]" class="w-full border rounded px-3 py-2" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">終了時間</label>
            <input type="time" name="time_slots[${timeSlotIndex}][end_time]" class="w-full border rounded px-3 py-2" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">定員</label>
            <input type="number" name="time_slots[${timeSlotIndex}][capacity]" value="1" min="1" class="w-full border rounded px-3 py-2" required>
          </div>
          <div class="flex items-end">
            <button type="button" class="remove-time-slot w-full bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">
              削除
            </button>
          </div>
        </div>
      `;
        container.appendChild(newRow);
        timeSlotIndex++;
      });

      document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-time-slot')) {
          const rows = document.querySelectorAll('.time-slot-row');
          if (rows.length > 1) {
            e.target.closest('.time-slot-row').remove();
          } else {
            alert('最低1つの時間枠は必要です');
          }
        }
      });
    </script>
  <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/app_3/resources/views/admin/presets/edit.blade.php ENDPATH**/ ?>