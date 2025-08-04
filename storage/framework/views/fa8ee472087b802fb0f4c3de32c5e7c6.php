<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $__env->yieldContent('title', '予約システム'); ?></title>
  <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body class="bg-gray-50">
  <!-- ヘッダー -->
  <header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-6">
        <div class="flex items-center">
          <h1 class="text-xl font-bold text-gray-900">予約システム</h1>
          <nav class="ml-8 hidden md:flex space-x-8">
            <a href="<?php echo e(route('customer.dashboard')); ?>"
              class="text-gray-500 hover:text-gray-700 <?php echo e(request()->routeIs('customer.dashboard') ? 'text-blue-600 font-medium' : ''); ?>">
              ダッシュボード
            </a>
            <a href="<?php echo e(route('customer.reservations.index')); ?>"
              class="text-gray-500 hover:text-gray-700 <?php echo e(request()->routeIs('customer.reservations.*') ? 'text-blue-600 font-medium' : ''); ?>">
              予約一覧
            </a>
            <a href="<?php echo e(route('calendar.public')); ?>" class="text-gray-500 hover:text-gray-700">
              カレンダー
            </a>
          </nav>
        </div>
        <div class="flex items-center space-x-4">
          <span class="text-gray-700"><?php echo e(Auth::guard('customer')->user()->name); ?>さん</span>
          <form method="POST" action="<?php echo e(route('customer.logout')); ?>" class="inline">
            <?php echo csrf_field(); ?>
            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
              ログアウト
            </button>
          </form>
        </div>
      </div>
    </div>
  </header>

  <!-- ページタイトル -->
  <div class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="py-4">
        <h2 class="text-2xl font-bold leading-7 text-gray-900">
          <?php echo $__env->yieldContent('page-title', 'ページ'); ?>
        </h2>
      </div>
    </div>
  </div>

  <!-- メインコンテンツ -->
  <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <?php echo $__env->yieldContent('body'); ?>
    </div>
  </main>

  <!-- 成功・エラーメッセージ -->
  <?php if(session('success')): ?>
    <div
      class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg z-50">
      <?php echo e(session('success')); ?>

    </div>
  <?php endif; ?>

  <?php if(session('error')): ?>
    <div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg z-50">
      <?php echo e(session('error')); ?>

    </div>
  <?php endif; ?>

  <?php if($errors->any()): ?>
    <div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg z-50">
      <ul class="list-disc list-inside text-sm">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- モバイルメニュー（必要に応じて） -->
  <div class="md:hidden fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 z-40">
    <div class="flex">
      <a href="<?php echo e(route('customer.dashboard')); ?>"
        class="flex-1 flex flex-col items-center py-2 px-3 text-center <?php echo e(request()->routeIs('customer.dashboard') ? 'text-blue-600' : 'text-gray-500'); ?>">
        <div class="text-xs">ダッシュボード</div>
      </a>
      <a href="<?php echo e(route('customer.reservations.index')); ?>"
        class="flex-1 flex flex-col items-center py-2 px-3 text-center <?php echo e(request()->routeIs('customer.reservations.*') ? 'text-blue-600' : 'text-gray-500'); ?>">
        <div class="text-xs">予約一覧</div>
      </a>
      <a href="<?php echo e(route('calendar.public')); ?>"
        class="flex-1 flex flex-col items-center py-2 px-3 text-center text-gray-500">
        <div class="text-xs">カレンダー</div>
      </a>
    </div>
  </div>
</body>

</html>
<?php /**PATH /var/www/html/app_3/resources/views/layouts/customer.blade.php ENDPATH**/ ?>