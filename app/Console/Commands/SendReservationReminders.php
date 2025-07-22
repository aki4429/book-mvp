<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use Carbon\Carbon;
use App\Mail\ReservationReminderMail;
use Illuminate\Support\Facades\Mail;

class SendReservationReminders extends Command
{
    protected $signature = 'reservation:remind';
    protected $description = '予約日前の顧客にリマインドメールを送る';

    public function handle()
    {
        $targetDate = Carbon::tomorrow()->format('Y-m-d');

        $reservations = Reservation::with('customer', 'timeSlot')
            ->whereHas('timeSlot', fn($q) => $q->where('date', $targetDate))
            ->where('status', 'confirmed') // 確定のみ送信
            ->get();

        foreach ($reservations as $reservation) {
            Mail::to($reservation->customer->email)
                ->send(new ReservationReminderMail($reservation));

            $this->info("Sent reminder to {$reservation->customer->email}");
        }
    }
}
