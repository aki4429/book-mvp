@extends('layouts.admin')

@section('page-title', '予約枠一覧')

@section('body')
  <h2 class="text-xl font-semibold mb-6">予約枠一覧</h2>

  <a href="{{ route('admin.timeslots.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">
    ＋ 新規作成
  </a>

  @if (session('success'))
    <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
      {{ session('success') }}
    </div>
  @endif

  <table class="w-full border-collapse border border-gray-300 text-sm">
    <thead class="bg-gray-100">
      <tr>
        <th class="border px-2 py-1">ID</th>
        <th class="border px-2 py-1">日付</th>
        <th class="border px-2 py-1">時間</th>
        <th class="border px-2 py-1">定員</th>
        <th class="border px-2 py-1">予約可能</th>
        <th class="border px-2 py-1">操作</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($timeSlots as $slot)
        <tr>
          <td class="border px-2 py-1">{{ $slot->id }}</td>
          <td class="border px-2 py-1">{{ \Carbon\Carbon::parse($slot->date)->format('Y-m-d') }} </td>
          <td class="border px-2 py-1">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} -
            {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }} </td>
          <td class="border px-2 py-1">{{ $slot->capacity }}</td>
          <td class="border px-2 py-1">{{ $slot->available ? '○' : '×' }}</td>
          <td class="border px-2 py-1">
            <a href="{{ route('admin.timeslots.edit', $slot) }}" class="text-blue-600 mr-2">編集</a>

            <form action="{{ route('admin.timeslots.destroy', $slot) }}" method="POST" class="inline"
              onsubmit="return confirm('削除してもよろしいですか？')">
              @csrf
              @method('DELETE')
              <button type="submit" class="text-red-600">削除</button>
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-center py-2">予約枠がありません。</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <div class="mt-4">
    {{ $timeSlots->links() }}
  </div>
@endsection
