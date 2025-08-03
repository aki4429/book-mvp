<div x-data="{ showModal: false }" x-on:show-modal.window="showModal = true" x-on:close-modal.window="showModal = false"
  x-show="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">

  <div class="bg-white p-6 rounded shadow w-1/2">
    <h2 class="text-lg font-semibold mb-4">
      <!--[if BLOCK]><![endif]--><?php if($timeslotId): ?>
        予約枠の編集 (<?php echo e($date ? \Carbon\Carbon::parse($date)->format('Y年n月j日') : ''); ?>)
      <?php else: ?>
        新規予約枠の作成 (<?php echo e($date ? \Carbon\Carbon::parse($date)->format('Y年n月j日') : ''); ?>)
      <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </h2>

    <form wire:submit.prevent="save">
      <div class="mb-4">
        <label>日付</label>
        <input type="date" wire:model="date" class="w-full border rounded p-2">
      </div>

      <div class="mb-4">
        <label>開始時間</label>
        <input type="time" wire:model="start_time" class="w-full border rounded p-2">
      </div>

      <div class="mb-4">
        <label>終了時間</label>
        <input type="time" wire:model="end_time" class="w-full border rounded p-2">
      </div>

      <div class="mb-4">
        <label>定員</label>
        <input type="number" wire:model="capacity" class="w-full border rounded p-2">
      </div>

      <div class="mb-4">
        <label><input type="checkbox" wire:model="available"> 予約可能</label>
      </div>

      <!--[if BLOCK]><![endif]--><?php if(count($existingSlots) > 0): ?>
        <div class="mb-4 p-3 bg-gray-50 rounded">
          <h4 class="font-semibold mb-2">この日の予約枠一覧</h4>
          <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $existingSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex justify-between items-center py-1 <?php echo e($slot['id'] == $timeslotId ? 'bg-blue-100 px-2 rounded' : ''); ?>">
              <span>
                <?php echo e(\Carbon\Carbon::parse($slot['start_time'])->format('H:i')); ?> - 
                <?php echo e(\Carbon\Carbon::parse($slot['end_time'])->format('H:i')); ?> 
                (定員: <?php echo e($slot['capacity']); ?>人)
                <!--[if BLOCK]><![endif]--><?php if(!$slot['available']): ?>
                  <span class="text-red-500 text-xs">[利用不可]</span>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
              </span>
              <!--[if BLOCK]><![endif]--><?php if($slot['id'] != $timeslotId): ?>
                <button type="button" wire:click="editSlot(<?php echo e($slot['id']); ?>)" 
                        class="text-blue-600 text-xs underline hover:text-blue-800">この枠を編集</button>
              <?php else: ?>
                <span class="text-blue-600 text-xs font-semibold">編集中</span>
              <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </div>
      <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">保存</button>
      <button type="button" x-on:click="showModal = false"
        class="ml-2 bg-gray-400 text-white px-4 py-2 rounded">キャンセル</button>
    </form>
  </div>
</div>
<?php /**PATH /var/www/html/app_3/resources/views/livewire/time-slot-form.blade.php ENDPATH**/ ?>