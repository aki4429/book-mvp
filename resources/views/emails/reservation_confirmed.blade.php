<p>{{ $reservation->customer->name }} 様</p>
<p>ご予約ありがとうございます。</p>
<p>日時：{{ $reservation->timeSlot->date }} {{ $reservation->timeSlot->start_time }}〜{{ $reservation->timeSlot->end_time }}</p>
