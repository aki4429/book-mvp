<div x-data="{ showModal: false }" x-on:show-modal.window="showModal = true" x-on:close-modal.window="showModal = false"
  x-show="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">

  <div class="bg-white p-6 rounded shadow w-1/2">
    <h2 class="text-lg font-semibold mb-4">
      @if($timeslotId)
        予約枠の編集 ({{ $date ? \Carbon\Carbon::parse($date)->format('Y年n月j日') : '' }})
      @else
        新規予約枠の作成 ({{ $date ? \Carbon\Carbon::parse($date)->format('Y年n月j日') : '' }})
      @endif
    </h2>

    <form wire:submit.prevent="save">
      <div class="mb-4">
        <label>日付</label>
        <input type="date" wire:model="date" class="w-full border rounded p-2">
      </div>

      <div class="mb-4">
        <label>開始時間</label>
        <input type="time" wire:model="start_time" class="w-full border rounded p-2">
      </div>

      <div class="mb-4">
        <label>終了時間</label>
        <input type="time" wire:model="end_time" class="w-full border rounded p-2">
      </div>

      <div class="mb-4">
        <label>定員</label>
        <input type="number" wire:model="capacity" class="w-full border rounded p-2">
      </div>

      <div class="mb-4">
        <label><input type="checkbox" wire:model="available"> 予約可能</label>
      </div>

      @if(count($existingSlots) > 0)
        <div class="mb-4 p-3 bg-gray-50 rounded">
          <h4 class="font-semibold mb-2">この日の予約枠一覧</h4>
          @foreach($existingSlots as $slot)
            <div class="flex justify-between items-center py-1 {{ $slot['id'] == $timeslotId ? 'bg-blue-100 px-2 rounded' : '' }}">
              <span>
                {{ \Carbon\Carbon::parse($slot['start_time'])->format('H:i') }} - 
                {{ \Carbon\Carbon::parse($slot['end_time'])->format('H:i') }} 
                (定員: {{ $slot['capacity'] }}人)
                @if(!$slot['available'])
                  <span class="text-red-500 text-xs">[利用不可]</span>
                @endif
              </span>
              @if($slot['id'] != $timeslotId)
                <button type="button" wire:click="editSlot({{ $slot['id'] }})" 
                        class="text-blue-600 text-xs underline hover:text-blue-800">この枠を編集</button>
              @else
                <span class="text-blue-600 text-xs font-semibold">編集中</span>
              @endif
            </div>
          @endforeach
        </div>
      @endif

      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">保存</button>
      <button type="button" x-on:click="showModal = false"
        class="ml-2 bg-gray-400 text-white px-4 py-2 rounded">キャンセル</button>
    </form>
  </div>
</div>
