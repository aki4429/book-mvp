<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    protected $fillable = [
       'customer_id',
       'time_slot_id',
       'status',
       'notes',
       'reminded_at',
       'created_by',
    ];

    // ── リレーション ──
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function creator()              // 作成者（管理者）
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
