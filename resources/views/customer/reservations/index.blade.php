@extends('layouts.customer')

@section('title', '予約一覧')
@section('page-title', '予約一覧')

@section('body')
  <div class="max-w-4xl mx-auto">
    <!-- ヘッダー部分 -->
    <div class="bg-white shadow rounded-lg mb-6">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold text-gray-900">予約一覧</h2>
          <a href="{{ route('customer.reservations.create') }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
            新しい予約を取る
          </a>
        </div>
      </div>

      @if ($reservations->count() > 0)
        <div class="divide-y divide-gray-200">
          @foreach ($reservations as $reservation)
            <div class="px-6 py-4 hover:bg-gray-50">
              <div class="flex items-center justify-between">
                <div class="flex-1">
                  <div class="flex items-center space-x-4">
                    <!-- 日付アイコン -->
                    <div class="flex-shrink-0">
                      <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <div class="text-center">
                          <div class="text-xs text-blue-600 font-medium">
                            {{ \Carbon\Carbon::parse($reservation->timeSlot->date)->format('n/j') }}
                          </div>
                          <div class="text-xs text-blue-500">
                            {{ \Carbon\Carbon::parse($reservation->timeSlot->date)->format('(D)') }}
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- 予約詳細 -->
                    <div class="flex-1">
                      <div class="flex items-center space-x-3">
                        <h3 class="text-lg font-medium text-gray-900">
                          {{ \Carbon\Carbon::parse($reservation->timeSlot->date)->format('Y年n月j日') }}
                        </h3>
                        <span
                          class="px-2 py-1 text-xs rounded-full {{ $reservation->status === 'confirmed'
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
                      <p class="text-sm text-gray-600 mt-1">
                        時間: {{ \Carbon\Carbon::parse($reservation->timeSlot->start_time)->format('H:i') }} -
                        {{ \Carbon\Carbon::parse($reservation->timeSlot->end_time)->format('H:i') }}
                      </p>
                      @if ($reservation->notes)
                        <p class="text-sm text-gray-500 mt-1">
                          備考: {{ $reservation->notes }}
                        </p>
                      @endif
                      <p class="text-xs text-gray-400 mt-1">
                        予約日時: {{ $reservation->created_at->format('Y年n月j日 H:i') }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- アクションボタン -->
                <div class="flex items-center space-x-2">
                  <a href="{{ route('customer.reservations.show', $reservation) }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    詳細
                  </a>
                  @if ($reservation->status !== 'canceled')
                    <form method="POST" action="{{ route('customer.reservations.cancel', $reservation) }}"
                      class="inline" onsubmit="return confirm('本当にこの予約をキャンセルしますか？')">
                      @csrf
                      <button type="submit"
                        class="inline-flex items-center px-3 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-white hover:bg-red-50">
                        キャンセル
                      </button>
                    </form>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>

        <!-- ページネーション -->
        <div class="px-6 py-4 border-t border-gray-200">
          {{ $reservations->links() }}
        </div>
      @else
        <!-- 予約がない場合 -->
        <div class="px-6 py-12 text-center">
          <div class="text-gray-400 text-6xl mb-4">📅</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">予約がありません</h3>
          <p class="text-gray-500 mb-6">まだ予約を取っていません。カレンダーから予約を取ってみましょう。</p>
          <div class="space-x-4">
            <a href="{{ route('customer.reservations.create') }}"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
              新しい予約を取る
            </a>
            <a href="{{ route('calendar.public') }}"
              class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
              カレンダーを見る
            </a>
          </div>
        </div>
      @endif
    </div>
  </div>
@endsection
