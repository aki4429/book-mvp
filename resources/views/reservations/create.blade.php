<h2>予約フォーム</h2>

{{-- <p>予約日時：{{ $slot->date->format('Y-m-d') }} {{ $slot->start_time }}〜{{ $slot->end_time }}</p> --}}
@if (isset($slot))
  <p>
    予約日時：
    {{ \Carbon\Carbon::parse($slot->date)->format('Y-m-d') }}
    {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} -
    {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
  </p>
@endif

<form method="POST" action="{{ route('reservations.store') }}">
  @csrf

  <input type="hidden" name="status" value="{{ $statuses[0] }}">

  <!-- 顧客選択 -->
  {{-- <div class="mb-4">
    <label for="customer_id" class="block text-sm font-medium text-gray-700">顧客名</label>
    <select name="customer_id" id="customer_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
      <option value="">選択してください</option>
      @foreach ($customers as $customer)
        <option value="{{ $customer->id }}"
          {{ old('customer_id', $reservation->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
          {{ $customer->name }}
        </option>
      @endforeach
    </select>
    @error('customer_id')
      <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
  </div> --}}

  <div class="mb-4">
    <label class="block mb-1">お名前</label>
    <input type="text" name="name" value="{{ old('name') }}" required class="border p-2 w-full">
  </div>

  <div class="mb-4">
    <label class="block mb-1">メールアドレス</label>
    <input type="email" name="email" value="{{ old('email') }}" required class="border p-2 w-full">
  </div>

  <div class="mb-4">
    <label class="block mb-1">電話番号</label>
    <input type="text" name="phone" value="{{ old('phone') }}" required class="border p-2 w-full">
  </div>


  {{-- 予約枠選択はそのまま --}}
  <input type="hidden" name="time_slot_id" value="{{ $slot->id }}">
  {{-- <div class="mb-4">
    <label class="block mb-1">予約枠</label>
    <select name="time_slot_id" required class="border p-2 w-full">
      @foreach ($timeSlots as $slot)
        <option value="{{ $slot->id }}">
          {{ $slot->date }} {{ $slot->start_time }}〜{{ $slot->end_time }}
        </option>
      @endforeach
    </select>
  </div> --}}


  {{-- <label>お名前</label>
  <input type="text" name="name" required class="border p-1 mb-2 w-full" value={{ $customer->name }}>

  <label>電話番号</label>
  <input type="text" name="phone" required class="border p-1 mb-2 w-full" value={{ $customer->phone }}> 
  --}}

  <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">予約する</button>
  @if ($errors->any())
    <div class="text-red-600">
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif


</form>
