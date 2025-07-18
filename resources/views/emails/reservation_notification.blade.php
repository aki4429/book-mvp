<p>{{ config('mail.admin_address') }} 様</p>
<p>新しい予約がありました。</p>
<p>日時：{{ $reservation->timeSlot->date }} {{ $reservation->timeSlot->start_time }}〜{{ $reservation->timeSlot->end_time }}</p>
