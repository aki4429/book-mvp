<div x-data="{ showModal: false }" x-on:show-modal.window="showModal = true" x-on:close-modal.window="showModal = false"
  x-show="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">

  <div class="bg-white p-6 rounded shadow w-1/2">
    <h2 class="text-lg font-semibold mb-4">
      {{ $timeslotId ? '予約枠の編集' : '予約枠の作成' }}
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

      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">保存</button>
      <button type="button" x-on:click="showModal = false"
        class="ml-2 bg-gray-400 text-white px-4 py-2 rounded">キャンセル</button>
    </form>
  </div>
</div>
