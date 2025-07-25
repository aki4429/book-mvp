@extends('layouts.admin')

@section('page-title', 'プリセット作成')

@section('body')
  <div class="max-w-4xl mx-auto">
    <div class="flex items-center mb-6">
      <a href="{{ route('admin.presets.index') }}" class="text-blue-600 hover:text-blue-800 mr-4">
        ← 戻る
      </a>
      <h2 class="text-2xl font-bold">新規プリセット作成</h2>
    </div>

    <form method="POST" action="{{ route('admin.presets.store') }}">
      @csrf

      <div class="mb-6 p-4 border rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="name" class="block text-sm font-medium mb-1">プリセット名 *</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}"
              class="w-full border rounded px-3 py-2" required>
            @error('name')
              <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
          </div>
          <div>
            <label for="description" class="block text-sm font-medium mb-1">説明</label>
            <input type="text" name="description" id="description" value="{{ old('description') }}"
              class="w-full border rounded px-3 py-2">
            @error('description')
              <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>
      </div>

      <div class="mb-6 p-4 border rounded-lg">
        <div class="flex justify-between items-center mb-3">
          <label class="text-lg font-semibold">時間枠設定</label>
          <button type="button" id="add-time-slot" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
            + 時間枠を追加
          </button>
        </div>

        <div id="time-slots-container">
          <div class="time-slot-row mb-4 p-3 border rounded bg-gray-50">
            <div class="grid grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">開始時間</label>
                <input type="time" name="time_slots[0][start_time]" class="w-full border rounded px-3 py-2" required>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">終了時間</label>
                <input type="time" name="time_slots[0][end_time]" class="w-full border rounded px-3 py-2" required>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">定員</label>
                <input type="number" name="time_slots[0][capacity]" value="1" min="1"
                  class="w-full border rounded px-3 py-2" required>
              </div>
              <div class="flex items-end">
                <button type="button"
                  class="remove-time-slot w-full bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">
                  削除
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="text-center">
        <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700">
          プリセットを作成
        </button>
      </div>
    </form>
  </div>

  @push('scripts')
    <script>
      let timeSlotIndex = 1;

      document.getElementById('add-time-slot').addEventListener('click', function() {
        const container = document.getElementById('time-slots-container');
        const newRow = document.createElement('div');
        newRow.className = 'time-slot-row mb-4 p-3 border rounded bg-gray-50';
        newRow.innerHTML = `
        <div class="grid grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">開始時間</label>
            <input type="time" name="time_slots[${timeSlotIndex}][start_time]" class="w-full border rounded px-3 py-2" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">終了時間</label>
            <input type="time" name="time_slots[${timeSlotIndex}][end_time]" class="w-full border rounded px-3 py-2" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">定員</label>
            <input type="number" name="time_slots[${timeSlotIndex}][capacity]" value="1" min="1" class="w-full border rounded px-3 py-2" required>
          </div>
          <div class="flex items-end">
            <button type="button" class="remove-time-slot w-full bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">
              削除
            </button>
          </div>
        </div>
      `;
        container.appendChild(newRow);
        timeSlotIndex++;
      });

      document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-time-slot')) {
          const rows = document.querySelectorAll('.time-slot-row');
          if (rows.length > 1) {
            e.target.closest('.time-slot-row').remove();
          } else {
            alert('最低1つの時間枠は必要です');
          }
        }
      });
    </script>
  @endpush
@endsection
