<?php $__env->startSection('title', '予約一覧'); ?>
<?php $__env->startSection('page-title', '予約一覧'); ?>

<?php $__env->startSection('body'); ?>
  <div class="max-w-4xl mx-auto">
    <!-- ヘッダー部分 -->
    <div class="bg-white shadow rounded-lg mb-6">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold text-gray-900">予約一覧</h2>
          <a href="<?php echo e(route('customer.reservations.create')); ?>"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
            新しい予約を取る
          </a>
        </div>
      </div>

      <?php if($reservations->count() > 0): ?>
        <div class="divide-y divide-gray-200">
          <?php $__currentLoopData = $reservations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reservation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="px-6 py-4 hover:bg-gray-50">
              <div class="flex items-center justify-between">
                <div class="flex-1">
                  <div class="flex items-center space-x-4">
                    <!-- 日付アイコン -->
                    <div class="flex-shrink-0">
                      <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <div class="text-center">
                          <div class="text-xs text-blue-600 font-medium">
                            <?php echo e(\Carbon\Carbon::parse($reservation->timeSlot->date)->format('n/j')); ?>

                          </div>
                          <div class="text-xs text-blue-500">
                            <?php echo e(\Carbon\Carbon::parse($reservation->timeSlot->date)->format('(D)')); ?>

                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- 予約詳細 -->
                    <div class="flex-1">
                      <div class="flex items-center space-x-3">
                        <h3 class="text-lg font-medium text-gray-900">
                          <?php echo e(\Carbon\Carbon::parse($reservation->timeSlot->date)->format('Y年n月j日')); ?>

                        </h3>
                        <span
                          class="px-2 py-1 text-xs rounded-full <?php echo e($reservation->status === 'confirmed'
                              ? 'bg-green-100 text-green-800'
                              : ($reservation->status === 'pending'
                                  ? 'bg-yellow-100 text-yellow-800'
                                  : ($reservation->status === 'canceled'
                                      ? 'bg-red-100 text-red-800'
                                      : 'bg-gray-100 text-gray-800'))); ?>">
                          <?php echo e($reservation->status === 'confirmed'
                              ? '確定'
                              : ($reservation->status === 'pending'
                                  ? '保留中'
                                  : ($reservation->status === 'canceled'
                                      ? 'キャンセル済み'
                                      : $reservation->status))); ?>

                        </span>
                      </div>
                      <p class="text-sm text-gray-600 mt-1">
                        時間: <?php echo e(\Carbon\Carbon::parse($reservation->timeSlot->start_time)->format('H:i')); ?> -
                        <?php echo e(\Carbon\Carbon::parse($reservation->timeSlot->end_time)->format('H:i')); ?>

                      </p>
                      <?php if($reservation->notes): ?>
                        <p class="text-sm text-gray-500 mt-1">
                          備考: <?php echo e($reservation->notes); ?>

                        </p>
                      <?php endif; ?>
                      <p class="text-xs text-gray-400 mt-1">
                        予約日時: <?php echo e($reservation->created_at->format('Y年n月j日 H:i')); ?>

                      </p>
                    </div>
                  </div>
                </div>

                <!-- アクションボタン -->
                <div class="flex items-center space-x-2">
                  <a href="<?php echo e(route('customer.reservations.show', $reservation)); ?>"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    詳細
                  </a>
                  <?php if($reservation->status !== 'canceled'): ?>
                    <form method="POST" action="<?php echo e(route('customer.reservations.cancel', $reservation)); ?>"
                      class="inline" onsubmit="return confirm('本当にこの予約をキャンセルしますか？')">
                      <?php echo csrf_field(); ?>
                      <button type="submit"
                        class="inline-flex items-center px-3 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-white hover:bg-red-50">
                        キャンセル
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- ページネーション -->
        <div class="px-6 py-4 border-t border-gray-200">
          <?php echo e($reservations->links()); ?>

        </div>
      <?php else: ?>
        <!-- 予約がない場合 -->
        <div class="px-6 py-12 text-center">
          <div class="text-gray-400 text-6xl mb-4">📅</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">予約がありません</h3>
          <p class="text-gray-500 mb-6">まだ予約を取っていません。カレンダーから予約を取ってみましょう。</p>
          <div class="space-x-4">
            <a href="<?php echo e(route('customer.reservations.create')); ?>"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
              新しい予約を取る
            </a>
            <a href="<?php echo e(route('calendar.public')); ?>"
              class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
              カレンダーを見る
            </a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.customer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/app_3/resources/views/customer/reservations/index.blade.php ENDPATH**/ ?>