<!DOCTYPE html>
<html lang="ja">

<head>
  <meta char <a href="<?php echo e(route('customers.index')); ?>"
    class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-200 
          <?php echo e(request()->routeIs('customers.*') ? 'bg-gray-200 font-semibold' : ''); ?>">
  <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?></title>
  
  <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body class="h-screen flex bg-gray-100">
  
  <aside class="w-64 bg-white border-r shadow-sm flex flex-col">
    <div class="h-16 flex items-center justify-center text-xl font-semibold border-b">
      MyAdmin
    </div>
    <nav class="flex-1 px-2 py-4 space-y-2">
      <a href="<?php echo e(route('dashboard')); ?>"
        class="flex items-center px-3 py-2 rounded-lg
                      hover:bg-gray-200 <?php echo e(request()->routeIs('dashboard') ? 'bg-gray-200 font-semibold' : ''); ?>">
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor">
          <use href="#home-icon" />
        </svg>
        <span>ダッシュボード</span>
      </a>
      <a href="<?php echo e(route('admin.timeslots.index')); ?>"
        class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-200
          <?php echo e(request()->routeIs('admin.timeslots.index') ? 'bg-gray-200 font-semibold' : ''); ?>">
        <svg class="h-5 w-5 mr-2"><!-- plus icon --></svg>
        <span>予約枠管理</span>
      </a>
      <a href="<?php echo e(route('admin.timeslots.bulkCreate')); ?>"
        class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-200
          <?php echo e(request()->routeIs('admin.timeslots.bulkCreate') ? 'bg-gray-200 font-semibold' : ''); ?>">
        <svg class="h-5 w-5 mr-2"><!-- preset icon --></svg>
        <span>曜日指定予約</span>
      </a>
      <a href="<?php echo e(route('admin.presets.index')); ?>"
        class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-200
          <?php echo e(request()->routeIs('admin.presets.*') ? 'bg-gray-200 font-semibold' : ''); ?>">
        <svg class="h-5 w-5 mr-2"><!-- preset icon --></svg>
        <span>プリセット管理</span>
      </a>
      <a href="<?php echo e(route('admin.calendar')); ?>"
        class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-200
          <?php echo e(request()->routeIs('admin.calendar') ? 'bg-gray-200 font-semibold' : ''); ?>">
        <svg class="h-5 w-5 mr-2"><!-- plus icon --></svg>
        <span>予約カレンダー</span>
      </a>
      <a href="<?php echo e(route('admin.reservations.index')); ?>"
        class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-200 
          <?php echo e(request()->routeIs('admin.reservations.*') ? 'bg-gray-200 font-semibold' : ''); ?>">
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor">
          <use href="#calendar-icon" />
        </svg>
        <span>予約管理</span>
      </a>
      <a href="<?php echo e(route('customers.index')); ?>"
        class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-200 <?php echo e(request()->routeIs('customers.*') ? 'bg-gray-200 font-semibold' : ''); ?>">
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor">
          <use href="#users-icon" />
        </svg>
        <span>顧客管理</span>
      </a>
      <a href="<?php echo e(route('admin.reservations.create')); ?>"
        class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-200
          <?php echo e(request()->routeIs('admin.reservations.create') ? 'bg-gray-200 font-semibold' : ''); ?>">
        <svg class="h-5 w-5 mr-2"><!-- plus icon --></svg>
        <span>予約 新規作成</span>
      </a>
      <a href="<?php echo e(route('admin.settings.index')); ?>"
        class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-200
          <?php echo e(request()->routeIs('admin.settings.*') ? 'bg-gray-200 font-semibold' : ''); ?>">
        <svg class="h-5 w-5 mr-2"><!-- settings icon --></svg>
        <span>システム設定</span>
      </a>
      <a href="<?php echo e(route('admin.users.index')); ?>"
        class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-200
          <?php echo e(request()->routeIs('admin.users.*') ? 'bg-gray-200 font-semibold' : ''); ?>">
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor">
          <use href="#users-icon" />
        </svg>
        <span>管理者ユーザー管理</span>
      </a>


    </nav>
    <form method="POST" action="<?php echo e(route('logout')); ?>" class="p-4 border-t">
      <?php echo csrf_field(); ?>
      <button class="w-full text-left text-sm text-gray-600 hover:text-red-600">ログアウト</button>
    </form>
  </aside>

  
  <div class="flex-1 flex flex-col">
    <!-- Top bar -->
    <header class="h-16 bg-white shadow flex items-center px-6">
      <h1 class="text-lg font-semibold"><?php echo $__env->yieldContent('page-title', 'Dashboard'); ?></h1>
    </header>

    <!-- Page content -->
    <main class="flex-1 overflow-y-auto p-6">
      <?php echo $__env->yieldContent('body'); ?>
    </main>
  </div>

  
  <svg class="hidden">
    <symbol id="home-icon" viewBox="0 0 24 24" stroke-width="1.5">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M3 9.75L12 3l9 6.75M4.5 10.5v9.75A1.5 1.5 0 006 21.75h4.5v-6h3v6H18a1.5 1.5 0 001.5-1.5V10.5" />
    </symbol>
    <symbol id="calendar-icon" viewBox="0 0 24 24" stroke-width="1.5">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M8 2.75v2.5M16 2.75v2.5M3.25 7.75h17.5M4.75 6V18A2 2 0 006.75 20h10.5A2 2 0 0019.25 18V6" />
    </symbol>
    <symbol id="users-icon" viewBox="0 0 24 24" stroke-width="1.5">
      <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
      <circle cx="9" cy="7" r="4" />
      <path stroke-linecap="round" stroke-linejoin="round" d="M23 20v-2a4 4 0 00-3-3.87" />
      <path stroke-linecap="round" stroke-linejoin="round" d="M16 3.13a4 4 0 010 7.75" />
    </symbol>
  </svg>

  
  <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH /var/www/html/app_3/resources/views/layouts/admin.blade.php ENDPATH**/ ?>