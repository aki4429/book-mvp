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
        return $this->belongsTo(User::class, 'customer_id');
    }

    // 別名でアクセスしやすくするためのリレーション
    public function user()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    /**
     * モデルイベント
     */
    protected static function booted()
    {
        // 予約作成時に時間枠の受付状況をチェック
        static::created(function ($reservation) {
            if ($reservation->timeSlot) {
                $reservation->timeSlot->checkAndUpdateAvailability();
            }
        });

        // 予約削除時に時間枠の受付状況を復元
        static::deleted(function ($reservation) {
            if ($reservation->timeSlot) {
                $reservation->timeSlot->checkAndRestoreAvailability();
            }
        });

        // 予約状況変更時（キャンセルなど）に時間枠の受付状況をチェック
        static::updated(function ($reservation) {
            if ($reservation->timeSlot) {
                // キャンセルされた場合は受付を復元
                if ($reservation->status === 'cancelled') {
                    $reservation->timeSlot->checkAndRestoreAvailability();
                } else {
                    $reservation->timeSlot->checkAndUpdateAvailability();
                }
            }
        });
    }

    public function creator()              // 作成者（管理者）
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 予約日時を日本語フォーマットで取得
     * 例: 2025年7月11日 14:00-15:00
     */
    public function getFormattedDateTimeAttribute()
    {
        if (!$this->timeSlot) {
            return '時間枠未設定';
        }

        $date = \Carbon\Carbon::parse($this->timeSlot->date)->format('Y年n月j日');
        $startTime = \Carbon\Carbon::parse($this->timeSlot->start_time)->format('H:i');
        $endTime = \Carbon\Carbon::parse($this->timeSlot->end_time)->format('H:i');

        return "{$date} {$startTime}-{$endTime}";
    }

    /**
     * 予約日時を短縮フォーマットで取得
     * 例: 7/11 14:00-15:00
     */
    public function getShortDateTimeAttribute()
    {
        if (!$this->timeSlot) {
            return '時間枠未設定';
        }

        $date = \Carbon\Carbon::parse($this->timeSlot->date)->format('n/j');
        $startTime = \Carbon\Carbon::parse($this->timeSlot->start_time)->format('H:i');
        $endTime = \Carbon\Carbon::parse($this->timeSlot->end_time)->format('H:i');

        return "{$date} {$startTime}-{$endTime}";
    }
}
