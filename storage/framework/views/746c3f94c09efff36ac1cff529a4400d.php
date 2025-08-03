<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?></title>
  <?php echo $__env->yieldPushContent('head'); ?>
  
  <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body class="h-screen flex bg-gray-100">
  
  <aside class="w-64 bg-white border-r shadow-sm flex flex-col">
    <div class="h-16 flex items-center justify-center text-xl font-semibold border-b">
      MyAdmin
    </div>
    <nav class="flex-1 px-2 py-4 space-y-2">
      <a href="<?php echo e(route('admin.dashboard')); ?>"
        class="flex items-center px-3 py-2 rounded-lg
                      hover:bg-gray-200 <?php echo e(request()->routeIs('admin.dashboard') ? 'bg-gray-200 font-semibold' : ''); ?>">
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor">
          <use href="#home-icon" />
        </svg>
        <span>ダッシュボード</span>
      </a>
      <a href="<?php echo e(route('admin.admin-dashboard.index')); ?>"
        class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-200
          <?php echo e(request()->routeIs('admin.admin-dashboard.*') ? 'bg-gray-200 font-semibold' : ''); ?>">
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        <span>管理者ダッシュボード (JS版)</span>
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
        class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-200 
          <?php echo e(request()->routeIs('customers.*') ? 'bg-gray-200 font-semibold' : ''); ?>">
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
      <a href="<?php echo e(route('admin.user-manager.index')); ?>"
        class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-200
          <?php echo e(request()->routeIs('admin.user-manager.*') ? 'bg-gray-200 font-semibold' : ''); ?>">
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor">
          <use href="#users-icon" />
        </svg>
        <span>ユーザー管理 (JS版)</span>
      </a>
      <a href="<?php echo e(route('admin.bulk-timeslots.index')); ?>"
        class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-200
          <?php echo e(request()->routeIs('admin.bulk-timeslots.*') ? 'bg-gray-200 font-semibold' : ''); ?>">
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>一括時間枠設定 (JS版)</span>
      </a>
      <a href="<?php echo e(route('admin.preset-manager.index')); ?>"
        class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-200
          <?php echo e(request()->routeIs('admin.preset-manager.*') ? 'bg-gray-200 font-semibold' : ''); ?>">
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <span>プリセット管理 (JS版)</span>
      </a>
      <a href="<?php echo e(route('admin.settings-manager.index')); ?>"
        class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-200
          <?php echo e(request()->routeIs('admin.settings-manager.*') ? 'bg-gray-200 font-semibold' : ''); ?>">
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        <span>システム設定 (JS版)</span>
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