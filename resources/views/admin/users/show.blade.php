@extends('layouts.admin')

@section('title', 'ユーザー詳細')
@section('page-title', 'ユーザー詳細')

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
                    <span class="ml-1 text-gray-500">{{ $user->name }}</span>
                </div>
            </li>
        </ol>
    </nav>
</div>

<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0 h-12 w-12">
                    <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center text-gray-700 font-medium text-lg">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">{{ $user->name }}</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ $user->email }}</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.users.edit', $user) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    編集
                </a>
                @if($user->id !== auth()->id())
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                                onclick="return confirm('本当にこのユーザーを削除しますか？')">
                            削除
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    
    <div class="px-6 py-4">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500">名前</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
            </div>
            
            <div>
                <dt class="text-sm font-medium text-gray-500">メールアドレス</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
            </div>
            
            <div>
                <dt class="text-sm font-medium text-gray-500">権限</dt>
                <dd class="mt-1">
                    @if($user->is_admin)
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                            管理者
                        </span>
                    @else
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                            一般ユーザー
                        </span>
                    @endif
                </dd>
            </div>
            
            <div>
                <dt class="text-sm font-medium text-gray-500">メール認証状態</dt>
                <dd class="mt-1">
                    @if($user->email_verified_at)
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            認証済み
                        </span>
                        <span class="text-sm text-gray-500 ml-2">
                            ({{ $user->email_verified_at->format('Y年m月d日 H:i') }})
                        </span>
                    @else
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            未認証
                        </span>
                    @endif
                </dd>
            </div>
            
            <div>
                <dt class="text-sm font-medium text-gray-500">作成日</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('Y年m月d日 H:i') }}</dd>
            </div>
            
            <div>
                <dt class="text-sm font-medium text-gray-500">最終更新日</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('Y年m月d日 H:i') }}</dd>
            </div>
        </dl>
    </div>
</div>

@if($user->id === auth()->id())
    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    これは現在ログイン中のユーザーです。このユーザーは削除できません。
                </p>
            </div>
        </div>
    </div>
@endif
@endsection
