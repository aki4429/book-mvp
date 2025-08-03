<?php echo csrf_field(); ?>
<div class="space-y-6">
  <!-- 顧客選択・作成セクション -->
  <div class="bg-gray-50 p-4 rounded-lg">
    <h3 class="text-lg font-medium text-gray-900 mb-4">顧客情報</h3>

    <!-- 顧客選択タブ -->
    <div class="flex border-b mb-4">
      <button type="button" onclick="showCustomerTab('existing')" id="existing-tab"
        class="px-4 py-2 text-sm font-medium text-blue-600 border-b-2 border-blue-500">
        既存顧客を選択
      </button>
      <button type="button" onclick="showCustomerTab('new')" id="new-tab"
        class="px-4 py-2 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700">
        新規顧客を作成
      </button>
    </div>

    <!-- 既存顧客選択 -->
    <div id="existing-customer" class="">
      <label class="block text-sm font-medium text-gray-700 mb-2">顧客を選択</label>
      <select name="customer_id" id="customer_select"
        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        <option value="">顧客を選択してください</option>
        <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($c->id); ?>" <?php if(old('customer_id', $reservation->customer_id ?? '') == $c->id): echo 'selected'; endif; ?>>
            <?php echo e($c->name); ?> (<?php echo e($c->email); ?>)
          </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select>
      <?php $__errorArgs = ['customer_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
      <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <!-- 新規顧客作成 -->
    <div id="new-customer" class="hidden space-y-4">
      <input type="hidden" name="new_customer" id="new_customer_flag" value="">

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">お名前 <span class="text-red-500">*</span></label>
        <input type="text" name="customer_name" id="customer_name"
          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          value="<?php echo e(old('customer_name')); ?>">
        <?php $__errorArgs = ['customer_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">メールアドレス <span class="text-red-500">*</span></label>
        <input type="email" name="customer_email" id="customer_email"
          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          value="<?php echo e(old('customer_email')); ?>">
        <?php $__errorArgs = ['customer_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">電話番号</label>
        <input type="tel" name="customer_phone" id="customer_phone"
          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          value="<?php echo e(old('customer_phone')); ?>">
        <?php $__errorArgs = ['customer_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>
    </div>
  </div>

  <!-- 予約詳細セクション -->
  <div class="grid md:grid-cols-2 gap-6">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">時間枠 <span class="text-red-500">*</span></label>
      <select name="time_slot_id"
        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        <?php
          $selectedSlotId = $selectedTimeSlot->id ?? old('time_slot_id', $reservation->time_slot_id ?? '');
        ?>
        <?php $__currentLoopData = $timeSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($slot->id); ?>" <?php if($selectedSlotId == $slot->id): echo 'selected'; endif; ?>>
            <?php echo e($slot->formatted_date_time); ?>

          </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select>
      <?php if(isset($selectedTimeSlot)): ?>
        <p class="text-xs text-gray-600 mt-1">
          ※ カレンダーから選択された時間枠です
        </p>
      <?php endif; ?>
      <?php $__errorArgs = ['time_slot_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
      <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">ステータス <span class="text-red-500">*</span></label>
      <select name="status"
        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($s); ?>" <?php if(old('status', $reservation->status ?? 'confirmed') == $s): echo 'selected'; endif; ?>>
            <?php echo e($s === 'pending' ? '保留' : ($s === 'confirmed' ? '確定' : ($s === 'canceled' ? 'キャンセル' : '完了'))); ?>

          </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select>
      <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
      <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="md:col-span-2">
      <label class="block text-sm font-medium text-gray-700 mb-2">備考</label>
      <textarea name="notes" rows="3"
        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"><?php echo e(old('notes', $reservation->notes ?? '')); ?></textarea>
      <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
      <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
  </div>
</div>

<div class="flex justify-end space-x-4 pt-6">
  <a href="<?php echo e(route('admin.reservations.index')); ?>"
    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
    キャンセル
  </a>
  <button type="submit"
    class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">
    予約を保存
  </button>
</div>

<script>
  function showCustomerTab(tab) {
    const existingTab = document.getElementById('existing-tab');
    const newTab = document.getElementById('new-tab');
    const existingCustomer = document.getElementById('existing-customer');
    const newCustomer = document.getElementById('new-customer');
    const newCustomerFlag = document.getElementById('new_customer_flag');
    const customerSelect = document.getElementById('customer_select');

    if (tab === 'existing') {
      existingTab.className = 'px-4 py-2 text-sm font-medium text-blue-600 border-b-2 border-blue-500';
      newTab.className =
      'px-4 py-2 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700';
      existingCustomer.classList.remove('hidden');
      newCustomer.classList.add('hidden');
      newCustomerFlag.value = '';
      customerSelect.required = true;

      // 新規顧客フィールドをクリア
      document.getElementById('customer_name').required = false;
      document.getElementById('customer_email').required = false;
    } else {
      newTab.className = 'px-4 py-2 text-sm font-medium text-blue-600 border-b-2 border-blue-500';
      existingTab.className =
        'px-4 py-2 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700';
      newCustomer.classList.remove('hidden');
      existingCustomer.classList.add('hidden');
      newCustomerFlag.value = '1';
      customerSelect.required = false;
      customerSelect.value = '';

      // 新規顧客フィールドを必須に
      document.getElementById('customer_name').required = true;
      document.getElementById('customer_email').required = true;
    }
  }

  // エラーがある場合は適切なタブを表示
  <?php if($errors->has('customer_name') || $errors->has('customer_email') || $errors->has('customer_phone')): ?>
    showCustomerTab('new');
  <?php endif; ?>
</script>
<?php /**PATH /var/www/html/app_3/resources/views/admin/reservations/_form.blade.php ENDPATH**/ ?>