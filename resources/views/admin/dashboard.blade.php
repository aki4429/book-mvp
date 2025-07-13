{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('page-title', 'ダッシュボード')

@section('body')
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <x-stat-card title="顧客数" :value="\App\Models\Customer::count()" />

    <x-stat-card title="今週の予約数" :value="\App\Models\Reservation::whereHas(
        'timeSlot',
        // fn($q) => $q->whereBetween('slot_date', [now()->startOfWeek(), now()->endOfWeek()]),
        fn($q) => $q->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]),
    )->count()" />
    <livewire:calendar />


  </div>
@endsection
