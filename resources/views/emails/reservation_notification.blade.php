<p>{{ config('mail.admin_address') }} 様</p>
<p>新しい予約がありました。</p>
<p>日時：{{ \Carbon\Carbon::parse($reservation->timeSlot->date)->format('Y年n月j日') }}
  {{ \Carbon\Carbon::parse($reservation->timeSlot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($reservation->timeSlot->end_time)->format('H:i') }}
</p>
