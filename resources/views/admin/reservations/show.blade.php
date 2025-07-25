@extends('layouts.admin')
@section('page-title', '予約詳細')

@section('body')
  <h2 class="text-xl font-semibold mb-6">予約 #{{ $reservation->id }}</h2>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded shadow">
      <h3 class="font-semibold mb-2">基本情報</h3>
      <p><b>顧客:</b> {{ $reservation->customer->name }}</p>
      <p><b>ステータス:</b> {{ $reservation->status }}</p>
      <p><b>メモ:</b> {{ $reservation->notes ?? '-' }}</p>
    </div>
    <div class="bg-white p-6 rounded shadow">
      <h3 class="font-semibold mb-2">時間枠</h3>
      @if ($reservation->timeSlot)
        <p><b>予約日時:</b> {{ $reservation->formatted_date_time }}</p>
        <p><b>定員:</b> {{ $reservation->timeSlot->capacity }}名</p>
        <p><b>現在の予約数:</b> {{ $reservation->timeSlot->getCurrentReservationCount() }}名</p>
        <p><b>受付状況:</b>
          <span
            class="px-2 py-1 text-xs rounded {{ $reservation->timeSlot->available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
            {{ $reservation->timeSlot->available ? '受付中' : '受付停止' }}
          </span>
        </p>
      @else
        <p class="text-gray-500 italic">予約枠情報がありません</p>
      @endif

      <p><b>作成:</b> {{ $reservation->created_at->format('Y年n月j日 H:i') }}</p>
    </div>
  </div>

  <a href="{{ route('admin.reservations.index') }}" class="inline-block mt-6 text-blue-600 hover:underline">← 一覧へ戻る</a>
@endsection
