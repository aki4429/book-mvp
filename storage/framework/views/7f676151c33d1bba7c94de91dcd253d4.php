<?php $__env->startSection('page-title', '曜日ベース予約枠 一括登録'); ?>

<?php $__env->startSection('body'); ?>
  <div class="max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">曜日ベース予約枠 一括登録</h2>

    <form method="POST" action="<?php echo e(route('admin.timeslots.bulkStore')); ?>" id="bulk-form">
      <?php echo csrf_field(); ?>

      <!-- 対象曜日選択 -->
      <div class="mb-6 p-4 border rounded-lg">
        <label class="block text-lg font-semibold mb-3">対象曜日</label>
        <div class="grid grid-cols-7 gap-2">
          <?php $__currentLoopData = ['mon' => '月', 'tue' => '火', 'wed' => '水', 'thu' => '木', 'fri' => '金', 'sat' => '土', 'sun' => '日']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <label class="flex items-center justify-center p-2 border rounded hover:bg-gray-50 cursor-pointer">
              <input type="checkbox" name="days[]" value="<?php echo e($key); ?>" class="mr-2">
              <span class="font-medium"><?php echo e($label); ?></span>
            </label>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>

      <!-- 対象期間 -->
      <div class="mb-6 p-4 border rounded-lg">
        <label class="block text-lg font-semibold mb-3">対象期間</label>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="start_date" class="block text-sm font-medium mb-1">開始日</label>
            <input type="date" name="start_date" id="start_date" class="w-full border rounded px-3 py-2" required>
          </div>
          <div>
            <label for="end_date" class="block text-sm font-medium mb-1">終了日</label>
            <input type="date" name="end_date" id="end_date" class="w-full border rounded px-3 py-2" required>
          </div>
        </div>
      </div>

      <!-- 時間枠設定 -->
      <div class="mb-6 p-4 border rounded-lg">
        <div class="flex justify-between items-center mb-3">
          <label class="text-lg font-semibold">時間枠設定</label>
          <button type="button" id="add-time-slot" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
            + 時間枠を追加
          </button>
        </div>

        <div id="time-slots-container">
          <!-- 初期の時間枠 -->
          <div class="time-slot-row mb-4 p-3 border rounded bg-gray-50">
            <div class="grid grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">開始時間</label>
                <input type="time" name="time_slots[0][start_time]" class="w-full border rounded px-3 py-2" required>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">終了時間</label>
                <input type="time" name="time_slots[0][end_time]" class="w-full border rounded px-3 py-2" required>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">定員</label>
                <input type="number" name="time_slots[0][capacity]" value="1" min="1"
                  class="w-full border rounded px-3 py-2" required>
              </div>
              <div class="flex items-end">
                <button type="button"
                  class="remove-time-slot w-full bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">
                  削除
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- よく使う時間枠プリセット -->
      <div class="mb-6 p-4 border rounded-lg bg-blue-50">
        <div class="flex justify-between items-center mb-3">
          <label class="text-lg font-semibold">よく使う時間枠プリセット</label>
          <a href="<?php echo e(route('admin.presets.index')); ?>" class="text-sm text-blue-600 hover:text-blue-800">
            プリセット管理 →
          </a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
          <?php $__empty_1 = true; $__currentLoopData = $presets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $preset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <button type="button" class="preset-btn bg-blue-500 text-white px-3 py-2 rounded hover:bg-blue-600"
              data-preset='<?php echo json_encode($preset->time_slots, 15, 512) ?>' title="<?php echo e($preset->description); ?>">
              <?php echo e($preset->name); ?>

            </button>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-span-full text-gray-500 text-center">
              プリセットがありません。
              <a href="<?php echo e(route('admin.presets.create')); ?>" class="text-blue-600 hover:text-blue-800">新規作成</a>
            </div>
          <?php endif; ?>
        </div>
      </div> <!-- 送信ボタン -->
      <div class="text-center">
        <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700">
          予約枠を一括登録
        </button>
      </div>
    </form>
  </div>

  <?php $__env->startPush('scripts'); ?>
    <script>
      let timeSlotIndex = 1;

      // 時間枠追加
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

      // 時間枠削除
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

      // プリセット適用
      document.addEventListener('click', function(e) {
        if (e.target.classList.contains('preset-btn')) {
          const preset = JSON.parse(e.target.dataset.preset);
          const container = document.getElementById('time-slots-container');

          // 既存の時間枠をクリア
          container.innerHTML = '';
          timeSlotIndex = 0;

          // プリセットの時間枠を追加
          preset.forEach((slot, index) => {
            const newRow = document.createElement('div');
            newRow.className = 'time-slot-row mb-4 p-3 border rounded bg-gray-50';
            newRow.innerHTML = `
            <div class="grid grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">開始時間</label>
                <input type="time" name="time_slots[${index}][start_time]" value="${slot.start_time}" class="w-full border rounded px-3 py-2" required>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">終了時間</label>
                <input type="time" name="time_slots[${index}][end_time]" value="${slot.end_time}" class="w-full border rounded px-3 py-2" required>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">定員</label>
                <input type="number" name="time_slots[${index}][capacity]" value="${slot.capacity}" min="1" class="w-full border rounded px-3 py-2" required>
              </div>
              <div class="flex items-end">
                <button type="button" class="remove-time-slot w-full bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">
                  削除
                </button>
              </div>
            </div>
          `;
            container.appendChild(newRow);
          });
          timeSlotIndex = preset.length;
        }
      });

      // 今日の日付をデフォルトに設定
      document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_date').value = today;

        const nextMonth = new Date();
        nextMonth.setMonth(nextMonth.getMonth() + 1);
        document.getElementById('end_date').value = nextMonth.toISOString().split('T')[0];
      });
    </script>
  <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/app_3/resources/views/admin/timeslots/bulk-create.blade.php ENDPATH**/ ?>