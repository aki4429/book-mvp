@extends('layouts.admin')

@section('title', 'システム設定')

@section('body')
  <div class="flex-1 p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">システム設定</h1>
    </div>

    @if (session('success'))
      <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
        {{ session('success') }}
      </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
      <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')

        <div class="space-y-6">
          <!-- 予約可能開始日設定 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              予約可能開始日 <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center space-x-3">
              <span class="text-sm text-gray-600">今日から</span>
              <input type="number" name="reservation_advance_days"
                value="{{ old('reservation_advance_days', $settings['reservation_advance_days']) }}" min="0"
                max="365"
                class="w-20 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
              <span class="text-sm text-gray-600">日後から予約可能</span>
            </div>
            <p class="mt-1 text-xs text-gray-500">
              0を設定すると今日から予約可能になります。1を設定すると明日から予約可能になります。
            </p>
            @error('reservation_advance_days')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- 設定例 -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-blue-800 mb-2">設定例</h3>
            <ul class="text-xs text-blue-700 space-y-1">
              <li>• <strong>0日:</strong> 今日から予約可能（当日予約OK）</li>
              <li>• <strong>1日:</strong> 明日から予約可能（前日までに予約）</li>
              <li>• <strong>3日:</strong> 3日後から予約可能（余裕を持った予約）</li>
              <li>• <strong>7日:</strong> 1週間後から予約可能（事前準備重視）</li>
            </ul>
          </div>
        </div>

        <div class="flex justify-end space-x-4 pt-6 mt-6 border-t">
          <button type="submit"
            class="px-6 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            設定を保存
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
