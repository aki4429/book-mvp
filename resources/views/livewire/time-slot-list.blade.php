  {{-- If you look to others for fulfillment, you will never truly be fulfilled. --}}
  <div class="mt-4">
    <h3 class="font-semibold mb-2">{{ $date }} の予約可能時間</h3>
    <ul class="space-y-2">
      @foreach ($slots as $slot)
        <li>
          <a href="{{ route('reservations.create', ['slot_id' => $slot->id]) }}" class="text-blue-500 hover:underline">
            {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}
          </a>
        </li>
      @endforeach
    </ul>
  </div>
