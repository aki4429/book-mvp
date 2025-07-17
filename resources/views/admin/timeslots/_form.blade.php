@csrf

<div class="mb-4">
  <label for="date" class="block text-sm font-medium text-gray-700">日付</label>
  <input type="date" name="date" id="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
    value="{{ old('date', optional($timeslot->date)->format('Y-m-d') ?? '') }}">
</div>

<div class="mb-4">
  <label for="start_time" class="block text-sm font-medium text-gray-700">開始時間</label>
  <input type="time" name="start_time" id="start_time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
    value="{{ old('start_time', optional($timeslot->start_time)->format('H:i') ?? '') }}">
</div>

<div class="mb-4">
  <label for="end_time" class="block text-sm font-medium text-gray-700">終了時間</label>
  <input type="time" name="end_time" id="end_time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
    value="{{ old('end_time', optional($timeslot->end_time)->format('H:i') ?? '') }}">
</div>

<div class="mb-4">
  <label for="capacity" class="block text-sm font-medium text-gray-700">定員</label>
  <input type="number" name="capacity" id="capacity" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
    value="{{ old('capacity', $timeslot->capacity ?? 1) }}">
</div>

<div class="mb-4">
  <label class="inline-flex items-center">
    <input type="checkbox" name="available" value="1"
      {{ old('available', $timeslot->available ?? false) ? 'checked' : '' }}>
    <span class="ml-2">予約可能</span>
  </label>
</div>

{{-- <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">保存</button> --}}
