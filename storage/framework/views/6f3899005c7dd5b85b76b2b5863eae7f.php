<?php $__env->startSection('title', '予約管理'); ?>
<?php $__env->startSection('page-title', '予約管理 - カレンダー表示'); ?>

<?php $__env->startSection('body'); ?>
  <div class="bg-white rounded-lg shadow">
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">予約管理</h2>
          <p class="text-gray-600 mt-2">カレンダーから予約の確認・管理ができます</p>
        </div>
        <div class="flex space-x-3">
          <a href="<?php echo e(route('admin.reservations.create')); ?>"
            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
            ＋ 新規予約
          </a>
          <a href="<?php echo e(route('admin.reservations.list')); ?>"
            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
            📋 リスト表示
          </a>
        </div>
      </div>

      <!-- 使い方の説明 -->
      <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-start space-x-3">
          <div class="text-blue-500 text-xl">💡</div>
          <div>
            <h3 class="font-medium text-blue-900 mb-2">使い方</h3>
            <ul class="text-sm text-blue-800 space-y-1">
              <li>• カレンダーの日付にマウスを乗せると、その日の時間枠と予約一覧が表示されます</li>
              <li>• 日付をクリックすると表示を固定できます</li>
              <li>• 各時間枠の空き状況が色で表示されます（○: 空きあり、△: 残りわずか、満: 満席）</li>
              <li>• <strong>空きのある時間枠には「＋ 予約」ボタンが表示され、クリックでその時間枠に直接予約できます</strong></li>
              <li>• 既存の予約は「編集」「削除」ボタンで管理できます</li>
              <li>• 時間枠がある日は色付きで表示され、予約件数も確認できます</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- 予約カレンダーコンポーネント -->
      <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('reservation-calendar', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-3329613913-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    </div>
  </div>

  <?php if(session('message')): ?>
    <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
      <?php echo e(session('message')); ?>

    </div>
  <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
  <script>
    document.addEventListener('livewire:init', () => {
      Livewire.on('reservation-deleted', (event) => {
        // 削除後の処理（必要に応じて）
        console.log('予約が削除されました');
      });
    });

    // 成功メッセージの自動非表示
    document.addEventListener('DOMContentLoaded', function() {
      const successMessage = document.querySelector('.fixed.bottom-4.right-4');
      if (successMessage) {
        setTimeout(() => {
          successMessage.style.opacity = '0';
          setTimeout(() => {
            successMessage.remove();
          }, 300);
        }, 3000);
      }
    });
  </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/app_3/resources/views/admin/reservations/calendar.blade.php ENDPATH**/ ?>