@extends('layouts.customer')

@section('title', '予約詳細')
@section('page-title', '予約詳細')

@section('body')
  <div class="max-w-2xl mx-auto">
    <div class="bg-white shadow rounded-lg">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold text-gray-900">予約詳細</h2>
          <span
            class="px-3 py-1 text-sm rounded-full {{ $reservation->status === 'confirmed'
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
                        ? 'キャンセル済み'
                        : $reservation->status)) }}
          </span>
        </div>
      </div>

      <div class="px-6 py-6">
        <!-- 予約情報 -->
        <div class="space-y-6">
          <div class="bg-blue-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-blue-900 mb-3">予約日時</h3>
            <div class="space-y-2">
              <div class="flex items-center">
                <span class="text-sm font-medium text-blue-700 w-16">日付:</span>
                <span
                  class="text-blue-900">{{ \Carbon\Carbon::parse($reservation->timeSlot->date)->format('Y年n月j日 (D)') }}</span>
              </div>
              <div class="flex items-center">
                <span class="text-sm font-medium text-blue-700 w-16">時間:</span>
                <span class="text-blue-900">
                  {{ \Carbon\Carbon::parse($reservation->timeSlot->start_time)->format('H:i') }} -
                  {{ \Carbon\Carbon::parse($reservation->timeSlot->end_time)->format('H:i') }}
                </span>
              </div>
            </div>
          </div>

          <!-- 顧客情報 -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-3">予約者情報</h3>
            <div class="bg-gray-50 p-4 rounded-lg space-y-2">
              <div class="flex items-center">
                <span class="text-sm font-medium text-gray-700 w-20">お名前:</span>
                <span class="text-gray-900">{{ $reservation->customer->name }}</span>
              </div>
              <div class="flex items-center">
                <span class="text-sm font-medium text-gray-700 w-20">メール:</span>
                <span class="text-gray-900">{{ $reservation->customer->email }}</span>
              </div>
              @if ($reservation->customer->phone)
                <div class="flex items-center">
                  <span class="text-sm font-medium text-gray-700 w-20">電話:</span>
                  <span class="text-gray-900">{{ $reservation->customer->phone }}</span>
                </div>
              @endif
            </div>
          </div>

          <!-- 備考 -->
          @if ($reservation->notes)
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-3">備考</h3>
              <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900">{{ $reservation->notes }}</p>
              </div>
            </div>
          @endif

          <!-- 予約履歴 -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-3">予約履歴</h3>
            <div class="bg-gray-50 p-4 rounded-lg space-y-2">
              <div class="flex items-center">
                <span class="text-sm font-medium text-gray-700 w-20">予約日:</span>
                <span class="text-gray-900">{{ $reservation->created_at->format('Y年n月j日 H:i') }}</span>
              </div>
              @if ($reservation->updated_at != $reservation->created_at)
                <div class="flex items-center">
                  <span class="text-sm font-medium text-gray-700 w-20">更新日:</span>
                  <span class="text-gray-900">{{ $reservation->updated_at->format('Y年n月j日 H:i') }}</span>
                </div>
              @endif
            </div>
          </div>
        </div>

        <!-- アクションボタン -->
        <div class="flex items-center justify-between pt-6 border-t border-gray-200 mt-6">
          <a href="{{ route('customer.reservations.index') }}"
            class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
            ← 予約一覧に戻る
          </a>

          @if ($reservation->status !== 'canceled')
            <form method="POST" action="{{ route('customer.reservations.cancel', $reservation) }}" class="inline"
              onsubmit="return confirm('本当にこの予約をキャンセルしますか？\n\nキャンセル後は元に戻せません。')">
              @csrf
              <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                予約をキャンセル
              </button>
            </form>
          @else
            <span class="text-red-600 text-sm font-medium">この予約はキャンセル済みです</span>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection
