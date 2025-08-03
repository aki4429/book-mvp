<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>プロフィール - 予約システム</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <!-- ナビゲーション -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-gray-800">プロフィール</h1>
                </div>
                
                <div class="flex items-center space-x-3">
                    <a href="{{ route('customer.dashboard') }}" 
                       class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        マイページに戻る
                    </a>
                </div>
            </div>
        </div>

        <!-- プロフィール情報 -->
        <div class="bg-white shadow-sm rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800">プロフィール情報</h2>
                <a href="{{ route('customer.profile.edit') }}" 
                   class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    編集する
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">お名前</label>
                    <p class="text-gray-900 bg-gray-50 px-3 py-2 rounded-lg">{{ $customer->name }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">メールアドレス</label>
                    <p class="text-gray-900 bg-gray-50 px-3 py-2 rounded-lg">{{ $customer->email }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">電話番号</label>
                    <p class="text-gray-900 bg-gray-50 px-3 py-2 rounded-lg">{{ $customer->phone ?: '未設定' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">会員登録日</label>
                    <p class="text-gray-900 bg-gray-50 px-3 py-2 rounded-lg">{{ $customer->created_at->format('Y年m月d日') }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
