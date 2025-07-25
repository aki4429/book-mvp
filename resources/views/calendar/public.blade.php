<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>予約カレンダー</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
</head>

<body class="bg-gray-50">
  <div class="min-h-screen">
    <!-- ヘッダー -->
    <header class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">予約カレンダー</h1>
            <p class="text-sm text-gray-600">ご希望の日時を選択してください</p>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-500">○: 空きあり</span>
            <span class="text-sm text-gray-500">△: 残りわずか</span>
            <span class="text-sm text-gray-500">×: 満席</span>
          </div>
        </div>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white rounded-lg shadow-sm">
        <!-- カレンダーコンポーネント（顧客用: isAdmin = false） -->
        <livewire:calendar :is-admin="false" />
      </div>
    </main>

    <!-- フッター -->
    <footer class="bg-white border-t mt-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="text-center text-sm text-gray-500">
          <p>予約に関するお問い合わせは、お電話またはメールにてお気軽にご連絡ください。</p>
        </div>
      </div>
    </footer>
  </div>

  @livewireScripts
</body>

</html>
