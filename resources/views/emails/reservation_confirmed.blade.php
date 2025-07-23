<p>{{ $reservation->customer->name }} 様</p>
<p>ご予約ありがとうございます。</p>
<p>日時：{{ \Carbon\Carbon::parse($reservation->timeSlot->date)->format('Y年n月j日') }}
  {{ \Carbon\Carbon::parse($reservation->timeSlot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($reservation->timeSlot->end_time)->format('H:i') }}
</p>
