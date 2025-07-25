{{-- @extends('layouts.admin') --}}
{{-- @section('page-title', '予約作成') --}}
{{-- @section('body') --}}
{{-- <h2 class="text-xl font-semibold mb-6">予約を新規作成</h2> --}}
{{-- <form method="POST" action="{{ route('admin.reservations.store') }}"> --}}
{{-- @include('admin.reservations._form', ['reservation' => new \App\Models\Reservation()]) --}}
{{-- </form> --}}
{{-- @endsection --}}

@extends('layouts.admin')

@section('page-title', '予約作成')

@section('body')
  @if ($selectedTimeSlot)
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
      <h3 class="font-medium text-blue-900 mb-2">選択された時間枠</h3>
      <p class="text-blue-800">
        {{ $selectedTimeSlot->date->format('Y年n月j日') }}
        {{ $selectedTimeSlot->start_time_as_object->format('H:i') }} -
        {{ $selectedTimeSlot->end_time_as_object->format('H:i') }}
      </p>
      <p class="text-sm text-blue-600 mt-1">
        残り {{ $selectedTimeSlot->capacity - $selectedTimeSlot->reservations->count() }} 名分の空きがあります
      </p>
    </div>
  @endif

  <h2 class="text-xl font-semibold mb-6">予約を新規作成</h2>

  <form method="POST" action="{{ route('admin.reservations.store') }}">
    @csrf
    @include('admin.reservations._form', [
        'reservation' => new \App\Models\Reservation(),
        'customers' => $customers,
        'timeSlots' => $timeSlots,
        'statuses' => $statuses,
        'selectedTimeSlot' => $selectedTimeSlot,
    ])
  </form>
@endsection
