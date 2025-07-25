<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlotPreset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'time_slots',
        'description',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'time_slots' => 'array',
        'is_active' => 'boolean'
    ];

    // アクティブなプリセットのみ取得
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // 表示順でソート
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
