<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'start_time', 'end_time', 'capacity', 'available'];


    protected $dates = ['date'];

    // app/Models/TimeSlot.php
    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'available' => 'boolean',
    ];

    /**
     * 予約とのリレーション
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'time_slot_id');
    }

    /**
     * 現在の予約数を取得
     */
    public function getCurrentReservationCount()
    {
        return $this->reservations()->count();
    }

    /**
     * 満席かどうかをチェック
     */
    public function isFull()
    {
        return $this->getCurrentReservationCount() >= $this->capacity;
    }

    /**
     * 予約可能かどうかをチェック（満席でない かつ available が true）
     */
    public function isBookable()
    {
        return $this->available && !$this->isFull();
    }

    /**
     * 満席になった場合に自動的に available を false にする
     */
    public function checkAndUpdateAvailability()
    {
        if ($this->isFull() && $this->available) {
            $this->update(['available' => false]);
            return true; // 状態が変更された
        }
        return false; // 状態は変更されなかった
    }

    /**
     * 予約がキャンセルされた場合に available を true に戻す
     */
    public function checkAndRestoreAvailability()
    {
        if (!$this->isFull() && !$this->available) {
            $this->update(['available' => true]);
            return true; // 状態が変更された
        }
        return false; // 状態は変更されなかった
    }

    /**
     * 時間枠を日本語フォーマットで表示
     * 例: 2025年7月11日 14:00-15:00
     */
    public function getFormattedDateTimeAttribute()
    {
        $date = \Carbon\Carbon::parse($this->date)->format('Y年n月j日');
        $startTime = \Carbon\Carbon::parse($this->start_time)->format('H:i');
        $endTime = \Carbon\Carbon::parse($this->end_time)->format('H:i');

        return "{$date} {$startTime}-{$endTime}";
    }

    /**
     * 時間帯のみを表示
     * 例: 14:00-15:00
     */
    public function getTimeRangeAttribute()
    {
        $startTime = \Carbon\Carbon::parse($this->start_time)->format('H:i');
        $endTime = \Carbon\Carbon::parse($this->end_time)->format('H:i');

        return "{$startTime}-{$endTime}";
    }

    /**
     * 開始時間をCarbonオブジェクトとして取得
     */
    public function getStartTimeAsObjectAttribute()
    {
        return is_string($this->start_time) ? \Carbon\Carbon::parse($this->start_time) : $this->start_time;
    }

    /**
     * 終了時間をCarbonオブジェクトとして取得
     */
    public function getEndTimeAsObjectAttribute()
    {
        return is_string($this->end_time) ? \Carbon\Carbon::parse($this->end_time) : $this->end_time;
    }

}
