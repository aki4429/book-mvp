<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>顧客ログイン・新規登録</title>
  <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body class="bg-gray-50">
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- ヘッダー -->
      <div class="text-center">
        <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
          予約システム
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          ログインまたは新規会員登録をしてください
        </p>
      </div>

      <!-- タブ切り替え -->
      <div class="bg-white shadow rounded-lg">
        <div class="flex border-b">
          <button onclick="showTab('login')" id="login-tab"
            class="flex-1 py-3 px-4 text-center font-medium text-blue-600 border-b-2 border-blue-500">
            ログイン
          </button>
          <button onclick="showTab('register')" id="register-tab"
            class="flex-1 py-3 px-4 text-center font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700">
            新規登録
          </button>
        </div>

        <!-- ログインフォーム -->
        <div id="login-form" class="p-6">
          <form method="POST" action="<?php echo e(route('customer.login.post')); ?>">
            <?php echo csrf_field(); ?>
            <div class="space-y-4">
              <div>
                <label for="login_email" class="block text-sm font-medium text-gray-700">
                  メールアドレス
                </label>
                <input id="login_email" name="email" type="email" required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  value="<?php echo e(old('email')); ?>">
                <?php $__errorArgs = ['email'];
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
                <label for="login_password" class="block text-sm font-medium text-gray-700">
                  パスワード
                </label>
                <input id="login_password" name="password" type="password" required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <?php $__errorArgs = ['password'];
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

              <div class="flex items-center">
                <input id="remember" name="remember" type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="remember" class="ml-2 block text-sm text-gray-700">
                  ログイン状態を保持する
                </label>
              </div>

              <button type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                ログイン
              </button>
            </div>
          </form>
        </div>

        <!-- 新規登録フォーム -->
        <div id="register-form" class="p-6 hidden">
          <form method="POST" action="<?php echo e(route('customer.register')); ?>">
            <?php echo csrf_field(); ?>
            <div class="space-y-4">
              <div>
                <label for="register_name" class="block text-sm font-medium text-gray-700">
                  お名前 <span class="text-red-500">*</span>
                </label>
                <input id="register_name" name="name" type="text" required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  value="<?php echo e(old('name')); ?>">
                <?php $__errorArgs = ['name'];
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
                <label for="register_email" class="block text-sm font-medium text-gray-700">
                  メールアドレス <span class="text-red-500">*</span>
                </label>
                <input id="register_email" name="email" type="email" required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  value="<?php echo e(old('email')); ?>">
                <?php $__errorArgs = ['email'];
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
                <label for="register_phone" class="block text-sm font-medium text-gray-700">
                  電話番号
                </label>
                <input id="register_phone" name="phone" type="tel"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  value="<?php echo e(old('phone')); ?>">
                <?php $__errorArgs = ['phone'];
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
                <label for="register_password" class="block text-sm font-medium text-gray-700">
                  パスワード <span class="text-red-500">*</span>
                </label>
                <input id="register_password" name="password" type="password" required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-xs text-gray-500">6文字以上で入力してください</p>
                <?php $__errorArgs = ['password'];
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
                <label for="register_password_confirmation" class="block text-sm font-medium text-gray-700">
                  パスワード確認 <span class="text-red-500">*</span>
                </label>
                <input id="register_password_confirmation" name="password_confirmation" type="password" required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
              </div>

              <button type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                新規登録
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- パブリックカレンダーへのリンク -->
      <div class="text-center">
        <a href="<?php echo e(route('calendar.public')); ?>" class="text-blue-600 hover:text-blue-500 text-sm">
          ← カレンダーに戻る
        </a>
      </div>
    </div>
  </div>

  <!-- エラーメッセージ表示 -->
  <?php if(session('success')): ?>
    <div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg">
      <?php echo e(session('success')); ?>

    </div>
  <?php endif; ?>

  <?php if(session('error')): ?>
    <div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg">
      <?php echo e(session('error')); ?>

    </div>
  <?php endif; ?>

  <script>
    function showTab(tab) {
      const loginTab = document.getElementById('login-tab');
      const registerTab = document.getElementById('register-tab');
      const loginForm = document.getElementById('login-form');
      const registerForm = document.getElementById('register-form');

      if (tab === 'login') {
        loginTab.className = 'flex-1 py-3 px-4 text-center font-medium text-blue-600 border-b-2 border-blue-500';
        registerTab.className =
          'flex-1 py-3 px-4 text-center font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700';
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');
      } else {
        registerTab.className = 'flex-1 py-3 px-4 text-center font-medium text-blue-600 border-b-2 border-blue-500';
        loginTab.className =
          'flex-1 py-3 px-4 text-center font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700';
        registerForm.classList.remove('hidden');
        loginForm.classList.add('hidden');
      }
    }

    // エラーがある場合は適切なタブを表示
    <?php if($errors->has('name') || $errors->has('phone') || $errors->has('password_confirmation')): ?>
      showTab('register');
    <?php endif; ?>
  </script>
</body>

</html>
<?php /**PATH /var/www/html/app_3/resources/views/customer/auth/login.blade.php ENDPATH**/ ?>