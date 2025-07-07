<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;
    protected $fillable = [
    'slot_date',
    'start_time',
    'end_time',
    'capacity',
    'service_id',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function service()   // 任意
    {
        return $this->belongsTo(Service::class);
    }

    // 予約済み人数を計算
    public function reservedCount(): int
    {
        return $this->reservations()->where('status', '!=', 'canceled')->count();
    }

    // 空きがあるか
    public function isAvailable(): bool
    {
        return $this->reservedCount() < $this->capacity;
    }
}
