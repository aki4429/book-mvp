<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン・新規登録</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- ヘッダー -->
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">予約システム</h1>
                <p class="text-gray-600">予約をするにはログインまたは新規登録をしてください</p>
            </div>

            <!-- メッセージ表示 -->
            @if (session('info'))
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="text-blue-400 mr-3">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <p class="text-blue-700">{{ session('info') }}</p>
                    </div>
                </div>
            @endif

            <!-- 選択ボタン -->
            <div class="space-y-4">
                <!-- ログインボタン -->
                <a href="{{ route('login') }}" 
                   class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-lg font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        既にアカウントをお持ちの方はログイン
                    </div>
                </a>

                <!-- 新規登録ボタン -->
                <a href="{{ route('register') }}" 
                   class="group relative w-full flex justify-center py-4 px-4 border border-gray-300 text-lg font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        初めての方は新規登録
                    </div>
                </a>
            </div>

            <!-- 戻るリンク -->
            <div class="text-center">
                <a href="{{ route('calendar.public') }}" 
                   class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                    ← カレンダーに戻る
                </a>
            </div>

            <!-- 説明文 -->
            <div class="bg-gray-100 rounded-lg p-4 text-sm text-gray-600">
                <h3 class="font-semibold mb-2">新規登録について</h3>
                <ul class="space-y-1 text-xs">
                    <li>• メールアドレスとパスワードで簡単に登録できます</li>
                    <li>• 予約の管理や履歴の確認ができるようになります</li>
                    <li>• 個人情報は適切に保護されます</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
