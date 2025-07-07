@extends('layouts.admin')
@section('page-title','予約編集')
@section('body')
<h2 class="text-xl font-semibold mb-6">予約 #{{ $reservation->id }} を編集</h2>
<form method="POST" action="{{ route('admin.reservations.update',$reservation) }}">
    @method('PUT')
    @include('admin.reservations._form', compact('reservation'))
</form>
@endsection
