@csrf
<div class="grid md:grid-cols-2 gap-6">
  <div>
      <label class="block text-sm font-medium mb-1">顧客</label>
      <select name="customer_id" class="w-full border rounded p-2">
          @foreach($customers as $c)
              <option value="{{ $c->id }}" @selected(old('customer_id', $reservation->customer_id ?? '')==$c->id)>
                  {{ $c->name }}
              </option>
          @endforeach
      </select>
  </div>

  <div>
      <label class="block text-sm font-medium mb-1">時間枠</label>
      <select name="time_slot_id" class="w-full border rounded p-2">
          @foreach($timeSlots as $ts)
              <option value="{{ $ts->id }}" @selected(old('time_slot_id', $reservation->time_slot_id ?? '')==$ts->id)>
                  {{ $ts->slot_date }} {{ $ts->start_time }}-{{ $ts->end_time }}
              </option>
          @endforeach
      </select>
  </div>

  <div>
      <label class="block text-sm font-medium mb-1">ステータス</label>
      <select name="status" class="w-full border rounded p-2">
          @foreach($statuses as $s)
              <option value="{{ $s }}" @selected(old('status', $reservation->status ?? '')==$s)>{{ $s }}</option>
          @endforeach
      </select>
  </div>

  <div class="md:col-span-2">
      <label class="block text-sm font-medium mb-1">メモ</label>
      <textarea name="notes" rows="3" class="w-full border rounded p-2">{{ old('notes', $reservation->notes ?? '') }}</textarea>
  </div>
</div>

<button class="mt-6 px-4 py-2 bg-blue-600 text-white rounded">保存</button>
