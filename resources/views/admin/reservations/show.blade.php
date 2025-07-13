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
        <p>{{ $reservation->timeSlot->date }}</p>
        <p>{{ $reservation->timeSlot->start_time }} - {{ $reservation->timeSlot->end_time }}</p>
      @else
        <p class="text-gray-500 italic">予約枠情報がありません</p>
      @endif

      <p><b>作成:</b> {{ $reservation->created_at->format('Y-m-d H:i') }}</p>
    </div>
  </div>

  <a href="{{ route('admin.reservations.index') }}" class="inline-block mt-6 text-blue-600 hover:underline">← 一覧へ戻る</a>
@endsection
