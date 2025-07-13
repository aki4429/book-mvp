@extends('layouts.admin')

@section('title', '予約カレンダー')

@section('body')
  <div class="flex-1 p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">予約カレンダー</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
      @livewire('calendar')
    </div>
  </div>
@endsection
