@extends('layouts.admin')

@section('page-title', '予約枠の作成')

@section('body')
  <h2 class="text-xl font-semibold mb-6">予約枠の作成</h2>

  <form method="POST" action="{{ route('admin.timeslots.store') }}">
    @csrf

    @include('admin.timeslots._form', ['timeslot' => new \App\Models\TimeSlot()])

    <button type="submit" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">作成</button>
  </form>
@endsection
