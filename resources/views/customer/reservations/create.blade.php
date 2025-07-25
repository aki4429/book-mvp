@extends('layouts.customer')

@section('title', '新規予約')
@section('page-title', '新規予約')

@section('body')
  <div class="max-w-2xl mx-auto">
    <div class="bg-white shadow rounded-lg">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">新規予約</h2>
      </div>

      <form method="POST" action="{{ route('customer.reservations.store') }}">
        @csrf
        <div class="px-6 py-6 space-y-6">
          <!-- 時間枠選択 -->
          @if ($timeSlot)
            <!-- 事前選択された時間枠 -->
            <input type="hidden" name="time_slot_id" value="{{ $timeSlot->id }}">
            <div class="bg-blue-50 p-4 rounded-lg">
              <h3 class="text-lg font-medium text-blue-900 mb-3">選択された時間枠</h3>
              <div class="space-y-2">
                <div class="flex items-center">
                  <span class="text-sm font-medium text-blue-700 w-16">日付:</span>
                  <span class="text-blue-900">{{ $timeSlot->date->format('Y年n月j日 (D)') }}</span>
                </div>
                <div class="flex items-center">
                  <span class="text-sm font-medium text-blue-700 w-16">時間:</span>
                  <span class="text-blue-900">
                    {{ $timeSlot->start_time_as_object->format('H:i') }} -
                    {{ $timeSlot->end_time_as_object->format('H:i') }}
                  </span>
                </div>
                <div class="flex items-center">
                  <span class="text-sm font-medium text-blue-700 w-16">空き:</span>
                  <span class="text-blue-900">
                    {{ $timeSlot->capacity - $timeSlot->reservations->count() }}/{{ $timeSlot->capacity }}名
                  </span>
                </div>
              </div>
              <p class="text-xs text-blue-600 mt-2">
                他の時間枠を選択する場合は、
                <a href="{{ route('calendar.public') }}" class="underline">カレンダーページ</a>
                から選び直してください。
              </p>
            </div>
          @else
            <!-- 時間枠未選択の場合 -->
            <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
              <h3 class="text-lg font-medium text-yellow-800 mb-3">時間枠を選択してください</h3>
              <p class="text-yellow-700 mb-4">
                予約する時間枠が選択されていません。カレンダーから希望の時間枠を選択してください。
              </p>
              <a href="{{ route('calendar.public') }}"
                class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                カレンダーで時間枠を選択
              </a>
            </div>
          @endif

          <!-- 備考欄 -->
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
              備考（任意）
            </label>
            <textarea id="notes" name="notes" rows="4"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="ご要望やご質問がございましたらご記入ください">{{ old('notes') }}</textarea>
            @error('notes')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">500文字以内でご記入ください。</p>
          </div>

          <!-- 予約確認事項 -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-gray-900 mb-2">予約に関する注意事項</h3>
            <ul class="text-xs text-gray-600 space-y-1">
              <li>• 予約完了後、確認のメールをお送りします。</li>
              <li>• キャンセルは予約詳細ページから行うことができます。</li>
              <li>• 変更をご希望の場合は、一度キャンセルしてから新しい予約を取り直してください。</li>
            </ul>
          </div>
        </div>

        <!-- フッター -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <a href="{{ route('calendar.public') }}"
            class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
            ← カレンダーに戻る
          </a>

          @if ($timeSlot)
            <button type="submit"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
              予約を確定する
            </button>
          @endif
        </div>
      </form>
    </div>
  </div>
@endsection
