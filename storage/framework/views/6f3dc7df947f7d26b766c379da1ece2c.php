<?php if($timeSlots->count() > 0): ?>
  <div class="space-y-6">
    <?php $__currentLoopData = $timeSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
        <!-- 時間枠ヘッダー -->
        <div class="flex justify-between items-start mb-4">
          <div class="flex-1">
            <div class="flex items-center space-x-3 mb-2">
              <h4 class="text-lg font-medium text-gray-900">
                <?php echo e($slot->start_time ? date('H:i', strtotime($slot->start_time)) : ''); ?> - 
                <?php echo e($slot->end_time ? date('H:i', strtotime($slot->end_time)) : ''); ?>

              </h4>
              
              <!-- ステータスバッジ -->
              <?php if($slot->available): ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  受付中
                </span>
              <?php else: ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                  停止中
                </span>
              <?php endif; ?>
              
              <!-- 予約状況 -->
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                <?php echo e($slot->reservations->count()); ?>/<?php echo e($slot->capacity); ?> 予約
              </span>
            </div>
            
            <!-- 時間枠詳細 -->
            <div class="text-sm text-gray-600 space-y-1">
              <div>定員: <?php echo e($slot->capacity); ?>名</div>
              <?php if($slot->service_id): ?>
                <div>サービスID: <code class="bg-gray-200 px-1 rounded"><?php echo e($slot->service_id); ?></code></div>
              <?php endif; ?>
            </div>
          </div>
          
          <!-- 時間枠操作ボタン -->
          <div class="flex items-center space-x-2">
            <button 
              onclick="editTimeSlot(<?php echo e($slot->id); ?>, '<?php echo e(date('H:i', strtotime($slot->start_time))); ?>', '<?php echo e(date('H:i', strtotime($slot->end_time))); ?>', <?php echo e($slot->capacity); ?>, '<?php echo e($slot->service_id); ?>', <?php echo e($slot->available ? 'true' : 'false'); ?>)"
              class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors flex items-center space-x-1"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              <span>編集</span>
            </button>
            
            <button 
              onclick="deleteTimeSlot(<?php echo e($slot->id); ?>)"
              class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors flex items-center space-x-1"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
              <span>削除</span>
            </button>
          </div>
        </div>
        
        <!-- 予約一覧 -->
        <?php if($slot->reservations->count() > 0): ?>
          <div class="mt-4">
            <div class="flex justify-between items-center mb-3">
              <h5 class="text-sm font-medium text-gray-900">予約一覧 (<?php echo e($slot->reservations->count()); ?>件)</h5>
              
              <!-- 予約追加ボタン（容量チェック） -->
              <?php if($slot->reservations->where('status', '!=', 'cancelled')->count() < $slot->capacity): ?>
                <button 
                  onclick="addReservation(<?php echo e($slot->id); ?>)"
                  class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors flex items-center space-x-1"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  <span>予約追加</span>
                </button>
              <?php else: ?>
                <span class="text-sm text-red-600 font-medium">満席</span>
              <?php endif; ?>
            </div>
            <div class="space-y-2">
              <?php $__currentLoopData = $slot->reservations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reservation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white border border-gray-200 rounded-lg p-3">
                  <div class="flex justify-between items-start">
                    <div class="flex-1">
                      <div class="flex items-center space-x-3 mb-2">
                        <h6 class="font-medium text-gray-900"><?php echo e($reservation->customer->name ?? '名前不明'); ?></h6>
                        
                        <!-- 予約ステータス -->
                        <?php switch($reservation->status):
                          case ('confirmed'): ?>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                              確定
                            </span>
                            <?php break; ?>
                          <?php case ('pending'): ?>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                              待機中
                            </span>
                            <?php break; ?>
                          <?php case ('cancelled'): ?>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                              キャンセル
                            </span>
                            <?php break; ?>
                          <?php default: ?>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                              <?php echo e($reservation->status); ?>

                            </span>
                        <?php endswitch; ?>
                      </div>
                      
                      <div class="text-sm text-gray-600 space-y-1">
                        <?php if($reservation->customer->email): ?>
                          <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                            <span><?php echo e($reservation->customer->email); ?></span>
                          </div>
                        <?php endif; ?>
                        
                        <?php if($reservation->customer->phone): ?>
                          <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span><?php echo e($reservation->customer->phone); ?></span>
                          </div>
                        <?php endif; ?>
                        
                        <div class="flex items-center space-x-2">
                          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                          </svg>
                          <span>予約日時: <?php echo e($reservation->created_at ? $reservation->created_at->format('Y/m/d H:i') : '不明'); ?></span>
                        </div>
                      </div>
                    </div>
                    
                    <!-- 予約操作ボタン -->
                    <div class="flex items-center space-x-2">
                      <button 
                        onclick="editReservation(<?php echo e($reservation->id); ?>, '<?php echo e($reservation->customer->name ?? ''); ?>', '<?php echo e($reservation->customer->email ?? ''); ?>', '<?php echo e($reservation->customer->phone ?? ''); ?>', '<?php echo e($reservation->status); ?>')"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors flex items-center space-x-1"
                      >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span>編集</span>
                      </button>
                      
                      <button 
                        onclick="deleteReservation(<?php echo e($reservation->id); ?>)"
                        class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors flex items-center space-x-1"
                      >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        <span>削除</span>
                      </button>
                    </div>
                  </div>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
          </div>
        <?php else: ?>
          <div class="mt-4">
            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                  </svg>
                  <p class="text-sm text-yellow-800">この時間枠にはまだ予約がありません。</p>
                </div>
                
                <!-- 予約追加ボタン -->
                <button 
                  onclick="addReservation(<?php echo e($slot->id); ?>)"
                  class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors flex items-center space-x-1"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  <span>予約追加</span>
                </button>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
<?php else: ?>
  <div class="text-center py-12">
    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
    </svg>
    <h3 class="mt-2 text-sm font-medium text-gray-900">時間枠なし</h3>
    <p class="mt-1 text-sm text-gray-500">この日には時間枠が設定されていません。</p>
    <div class="mt-6">
      <button 
        onclick="addTimeSlot()"
        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
      >
        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        時間枠を追加
      </button>
    </div>
  </div>
<?php endif; ?>
<?php /**PATH /var/www/html/app_3/resources/views/admin/calendar/partials/day-slots.blade.php ENDPATH**/ ?>