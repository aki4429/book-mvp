@extends('layouts.admin')

@section('title', 'ユーザー編集')
@section('page-title', 'ユーザー編集')

@section('body')
<div class="mb-6">
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.users.index') }}" class="text-blue-700 hover:text-blue-900">
                    ユーザー管理
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-1 text-gray-500">{{ $user->name }} の編集</span>
                </div>
            </li>
        </ol>
    </nav>
</div>

<div class="bg-white shadow-sm rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium leading-6 text-gray-900">ユーザー情報編集</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">
            ユーザーの基本情報と権限を編集します。
        </p>
    </div>
    
    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="px-6 py-4 space-y-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    名前 <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $user->name) }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-300 @enderror"
                       required>
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                    メールアドレス <span class="text-red-500">*</span>
                </label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       value="{{ old('email', $user->email) }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-300 @enderror"
                       required>
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    新しいパスワード
                </label>
                <input type="password" 
                       name="password" 
                       id="password"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-300 @enderror">
                <p class="mt-1 text-sm text-gray-500">
                    パスワードを変更しない場合は空欄のままにしてください。
                </p>
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    パスワード確認
                </label>
                <input type="password" 
                       name="password_confirmation" 
                       id="password_confirmation"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        
        <div>
            <div class="flex items-center">
                <input type="checkbox" 
                       name="is_admin" 
                       id="is_admin"
                       value="1"
                       {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="is_admin" class="ml-2 block text-sm text-gray-900">
                    管理者権限を付与する
                </label>
            </div>
            <p class="mt-1 text-sm text-gray-500">
                チェックを入れると、このユーザーは管理者として全ての機能にアクセスできるようになります。
            </p>
        </div>
        
        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.users.index') }}" 
               class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                キャンセル
            </a>
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                更新する
            </button>
        </div>
    </form>
</div>
@endsection
