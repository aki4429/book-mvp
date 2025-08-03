<div>
  <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
    <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
      <?php echo e(session('message')); ?>

    </div>
  <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

  <!--[if BLOCK]><![endif]--><?php if($selectedDate): ?>
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="time-slot-modal"
      wire:click="close">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white"
        wire:click.stop>
        <div class="mt-3">
          <!-- ヘッダー -->
          <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-medium text-gray-900">
              <?php echo e(\Carbon\Carbon::parse($selectedDate ?? now())->format('Y年n月j日')); ?> の時間枠管理
            </h3>
            <div class="flex items-center space-x-2">
              <button type="button" wire:click="removeDuplicateTimeSlots"
                onclick="return confirm('重複した時間枠を削除しますか？（より新しい時間枠が残されます）')"
                class="bg-orange-500 text-white px-3 py-1 rounded text-sm hover:bg-orange-600">
                重複削除
              </button>
              <button type="button" wire:click="close" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>

          <!-- フラッシュメッセージ -->
          <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
            <div class="mt-3 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
              <?php echo e(session('message')); ?>

            </div>
          <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

          <!-- 時間枠リスト -->
          <div class="mt-4 max-h-96 overflow-y-auto">
            <!--[if BLOCK]><![endif]--><?php if(count($timeSlots) > 0): ?>
              <div class="space-y-3">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $timeSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <div class="border rounded-lg p-4 <?php echo e($slot['available'] ? 'bg-white' : 'bg-gray-100'); ?>">
                    <!--[if BLOCK]><![endif]--><?php if($isEditing[$index] ?? false): ?>
                      <!-- 編集モード -->
                      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                          <label class="block text-sm font-medium text-gray-700">開始時間</label>
                          <input type="time" wire:model.defer="editingData.<?php echo e($index); ?>.start_time"
                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                          <!--[if BLOCK]><![endif]--><?php $__errorArgs = ["editingData.$index.start_time"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="text-red-500 text-xs"><?php echo e($message); ?></span>
                          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-700">終了時間</label>
                          <input type="time" wire:model.defer="editingData.<?php echo e($index); ?>.end_time"
                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                          <!--[if BLOCK]><![endif]--><?php $__errorArgs = ["editingData.$index.end_time"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="text-red-500 text-xs"><?php echo e($message); ?></span>
                          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-700">定員</label>
                          <input type="number" wire:model.defer="editingData.<?php echo e($index); ?>.capacity"
                            min="1" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                          <!--[if BLOCK]><![endif]--><?php $__errorArgs = ["editingData.$index.capacity"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="text-red-500 text-xs"><?php echo e($message); ?></span>
                          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        <div class="flex items-end space-x-2">
                          <button type="button" wire:click="saveEdit(<?php echo e($index); ?>)"
                            class="bg-green-500 text-white px-3 py-2 rounded text-sm hover:bg-green-600">
                            保存
                          </button>
                          <button type="button" wire:click="cancelEdit(<?php echo e($index); ?>)"
                            class="bg-gray-500 text-white px-3 py-2 rounded text-sm hover:bg-gray-600">
                            キャンセル
                          </button>
                        </div>
                      </div>
                      <div class="mt-2">
                        <label class="flex items-center">
                          <input type="checkbox" wire:model.defer="editingData.<?php echo e($index); ?>.available"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                          <span class="ml-2 text-sm text-gray-600">予約受付中</span>
                        </label>
                      </div>
                    <?php else: ?>
                      <!-- 表示モード -->
                      <div class="flex justify-between items-center">
                        <div class="flex-1">
                          <div class="flex items-center space-x-4">
                            <span class="font-medium text-lg">
                              <?php echo e(\Carbon\Carbon::parse($slot['start_time'])->format('H:i')); ?> -
                              <?php echo e(\Carbon\Carbon::parse($slot['end_time'])->format('H:i')); ?>

                            </span>
                            <span class="text-sm text-gray-600">
                              予約: <?php echo e($slot['current_reservations'] ?? 0); ?>/<?php echo e($slot['capacity']); ?>名
                            </span>
                            <!--[if BLOCK]><![endif]--><?php if($slot['is_full'] ?? false): ?>
                              <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                満席
                              </span>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <span
                              class="px-2 py-1 text-xs rounded-full <?php echo e($slot['available'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                              <?php echo e($slot['available'] ? '受付中' : '停止中'); ?>

                            </span>
                          </div>
                        </div>
                        <div class="flex space-x-2">
                          <button type="button" wire:click="startEdit(<?php echo e($index); ?>)"
                            class="text-blue-600 hover:text-blue-800 text-sm">
                            編集
                          </button>
                          <button type="button" wire:click="toggleAvailable(<?php echo e($index); ?>)"
                            class="text-yellow-600 hover:text-yellow-800 text-sm">
                            <?php echo e($slot['available'] ? '停止' : '再開'); ?>

                          </button>
                          <button type="button" wire:click="deleteTimeSlot(<?php echo e($index); ?>)"
                            onclick="return confirm('この時間枠を削除しますか？')" class="text-red-600 hover:text-red-800 text-sm">
                            削除
                          </button>
                        </div>
                      </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                  </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
              </div>
            <?php else: ?>
              <div class="text-center py-8 text-gray-500">
                この日付には時間枠がありません
              </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
          </div>

          <!-- 新規追加フォーム -->
          <!--[if BLOCK]><![endif]--><?php if($showAddForm): ?>
            <div class="mt-4 p-4 border-t bg-blue-50 rounded-lg">
              <h4 class="font-medium text-gray-900 mb-3">新しい時間枠を追加</h4>
              <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700">開始時間</label>
                  <input type="time" wire:model.defer="newTimeSlot.start_time"
                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                  <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['newTimeSlot.start_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="text-red-500 text-xs"><?php echo e($message); ?></span>
                  <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">終了時間</label>
                  <input type="time" wire:model.defer="newTimeSlot.end_time"
                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                  <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['newTimeSlot.end_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="text-red-500 text-xs"><?php echo e($message); ?></span>
                  <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">定員</label>
                  <input type="number" wire:model.defer="newTimeSlot.capacity" min="1"
                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                  <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['newTimeSlot.capacity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="text-red-500 text-xs"><?php echo e($message); ?></span>
                  <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                <div class="flex items-end space-x-2">
                  <button type="button" wire:click="addTimeSlot"
                    class="bg-blue-500 text-white px-4 py-2 rounded text-sm hover:bg-blue-600">
                    追加
                  </button>
                  <button type="button" wire:click="hideAddForm"
                    class="bg-gray-500 text-white px-3 py-2 rounded text-sm hover:bg-gray-600">
                    キャンセル
                  </button>
                </div>
              </div>
              <div class="mt-2">
                <label class="flex items-center">
                  <input type="checkbox" wire:model.defer="newTimeSlot.available"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                  <span class="ml-2 text-sm text-gray-600">予約受付中</span>
                </label>
              </div>
            </div>
          <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

          <!-- フッター -->
          <div class="mt-6 flex justify-between border-t pt-4">
            <button type="button" wire:click="toggleAddForm"
              class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 <?php echo e($showAddForm ? 'opacity-50 cursor-not-allowed' : ''); ?>"
              <?php echo e($showAddForm ? 'disabled' : ''); ?>>
              + 時間枠を追加
            </button>
            <button type="button" wire:click="close"
              class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
              閉じる
            </button>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH /var/www/html/app_3/resources/views/livewire/time-slot-manager.blade.php ENDPATH**/ ?>