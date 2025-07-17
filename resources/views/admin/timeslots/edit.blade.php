@extends('layouts.admin')

@section('page-title', '予約枠の編集')

@section('body')
  <h2 class="text-xl font-semibold mb-6">予約枠の編集</h2>

  <form method="POST" action="{{ route('admin.timeslots.update', $timeslot) }}">
    @csrf
    @method('PUT')

    @include('admin.timeslots._form', ['timeslot' => $timeslot])

    <button type="submit" class="mt-4 bg-green-500 text-white px-4 py-2 rounded">更新</button>
  </form>
@endsection
