@extends('layouts.admin')

@section('page-title', 'プリセット管理')

@section('body')
  <div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">時間枠プリセット管理</h2>
      <a href="{{ route('admin.presets.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        新規プリセット作成
      </a>
    </div>

    @if ($presets->isEmpty())
      <div class="text-center py-12 bg-gray-50 rounded-lg">
        <p class="text-gray-500 mb-4">プリセットがありません</p>
        <a href="{{ route('admin.presets.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
          最初のプリセットを作成
        </a>
      </div>
    @else
      <div class="grid gap-4">
        @foreach ($presets as $preset)
          <div class="border rounded-lg p-4 bg-white {{ $preset->is_active ? '' : 'opacity-50' }}">
            <div class="flex justify-between items-start mb-3">
              <div>
                <h3 class="text-lg font-semibold">{{ $preset->name }}</h3>
                @if ($preset->description)
                  <p class="text-gray-600 text-sm">{{ $preset->description }}</p>
                @endif
                <span
                  class="text-xs px-2 py-1 rounded {{ $preset->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                  {{ $preset->is_active ? '有効' : '無効' }}
                </span>
              </div>
              <div class="flex space-x-2">
                <a href="{{ route('admin.presets.edit', $preset) }}" class="text-blue-600 hover:text-blue-800">
                  編集
                </a>
                <form method="POST" action="{{ route('admin.presets.destroy', $preset) }}" class="inline"
                  onsubmit="return confirm('このプリセットを削除しますか？')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="text-red-600 hover:text-red-800">削除</button>
                </form>
              </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
              @foreach ($preset->time_slots as $slot)
                <div class="text-sm bg-gray-50 p-2 rounded">
                  <div class="font-medium">{{ $slot['start_time'] }} - {{ $slot['end_time'] }}</div>
                  <div class="text-gray-600">定員: {{ $slot['capacity'] }}名</div>
                </div>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
@endsection
