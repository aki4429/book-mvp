<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>顧客ダッシュボード</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50">
  <!-- ヘッダー -->
  <header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-6">
        <div class="flex-shrink-0">
          <h1 class="text-xl font-bold text-gray-900">予約システム</h1>
        </div>
        <div class="flex items-center space-x-4">
          <!-- カレンダーへ戻る -->
          <a href="{{ route('calendar.public') }}" 
             class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span>カレンダー</span>
          </a>
          <span class="text-gray-700">{{ $customer->name }}さん</span>
          <form method="POST" action="{{ route('customer.logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
              ログアウト
            </button>
          </form>
        </div>
      </div>
    </div>
  </header>

  <!-- メインコンテンツ -->
  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
      <!-- ウェルカムメッセージ -->
      <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-2">
            ようこそ、{{ $customer->name }}さん
          </h2>
          <p class="text-gray-600">
            こちらから予約の確認や新しい予約を取ることができます。
          </p>
        </div>
      </div>

      <!-- アクションボタン -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <a href="{{ route('customer.reservations.create') }}"
          class="bg-blue-500 hover:bg-blue-600 text-white p-6 rounded-lg text-center transition-colors">
          <div class="text-2xl mb-2">📅</div>
          <h3 class="text-lg font-semibold mb-1">新しい予約を取る</h3>
          <p class="text-blue-100 text-sm">カレンダーから空き時間を選んで予約</p>
        </a>

        <a href="{{ route('customer.reservations.index') }}"
          class="bg-green-500 hover:bg-green-600 text-white p-6 rounded-lg text-center transition-colors">
          <div class="text-2xl mb-2">📋</div>
          <h3 class="text-lg font-semibold mb-1">予約一覧</h3>
          <p class="text-green-100 text-sm">予約を管理</p>
        </a>

        <a href="{{ route('calendar.public') }}"
          class="bg-purple-500 hover:bg-purple-600 text-white p-6 rounded-lg text-center transition-colors">
          <div class="text-2xl mb-2">🗓️</div>
          <h3 class="text-lg font-semibold mb-1">カレンダー表示</h3>
          <p class="text-purple-100 text-sm">空き状況を確認</p>
        </a>
      </div>

      <!-- 予約履歴 -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900">
            予約履歴
          </h3>
          <p class="mt-1 max-w-2xl text-sm text-gray-500">
            あなたの予約一覧です
          </p>
        </div>

        @if ($reservations->count() > 0)
          <ul class="divide-y divide-gray-200">
            @foreach ($reservations as $reservation)
              <li class="px-4 py-4 sm:px-6">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                          <span class="text-blue-600 font-medium text-sm">
                            {{ \Carbon\Carbon::parse($reservation->timeSlot->date)->format('j') }}
                          </span>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="flex items-center">
                          <p class="text-sm font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($reservation->timeSlot->date)->format('Y年n月j日') }}
                          </p>
                          <span
                            class="ml-2 px-2 py-1 text-xs rounded-full {{ $reservation->status === 'confirmed'
                                ? 'bg-green-100 text-green-800'
                                : ($reservation->status === 'pending'
                                    ? 'bg-yellow-100 text-yellow-800'
                                    : ($reservation->status === 'canceled'
                                        ? 'bg-red-100 text-red-800'
                                        : 'bg-gray-100 text-gray-800')) }}">
                            {{ $reservation->status === 'confirmed'
                                ? '確定'
                                : ($reservation->status === 'pending'
                                    ? '保留中'
                                    : ($reservation->status === 'canceled'
                                        ? 'キャンセル'
                                        : $reservation->status)) }}
                          </span>
                        </div>
                        <p class="text-sm text-gray-600">
                          {{ \Carbon\Carbon::parse($reservation->timeSlot->start_time)->format('H:i') }} -
                          {{ \Carbon\Carbon::parse($reservation->timeSlot->end_time)->format('H:i') }}
                        </p>
                        @if ($reservation->notes)
                          <p class="text-xs text-gray-500 mt-1">
                            備考: {{ $reservation->notes }}
                          </p>
                        @endif
                      </div>
                    </div>
                  </div>
                  <div class="text-right text-xs text-gray-400">
                    予約日: {{ $reservation->created_at->format('Y/m/d') }}
                  </div>
                </div>
              </li>
            @endforeach
          </ul>

          <!-- ページネーション -->
          <div class="px-4 py-3 border-t border-gray-200">
            {{ $reservations->links() }}
          </div>
        @else
          <div class="px-4 py-8 text-center">
            <div class="text-gray-400 text-4xl mb-4">📋</div>
            <p class="text-gray-500 mb-4">まだ予約がありません</p>
            <a href="{{ route('calendar.public') }}"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
              初回予約を取る
            </a>
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- 成功メッセージ -->
  @if (session('success'))
    <div
      class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg z-50">
      {{ session('success') }}
    </div>
  @endif
</body>

</html>
