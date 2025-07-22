<p>{{ $reservation->customer->name }}様</p>

<p>ご予約いただいた日時が近づいてきましたのでお知らせいたします。</p>

<p>
    日時：{{ \Carbon\Carbon::parse($reservation->timeSlot->date)->format('Y年n月j日(D)') }}
    {{ \Carbon\Carbon::parse($reservation->timeSlot->start_time)->format('H:i') }}〜
    {{ \Carbon\Carbon::parse($reservation->timeSlot->end_time)->format('H:i') }}
</p>

<p>ご来店お待ちしております。</p>
