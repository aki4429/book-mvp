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
        'available' => 'boolean',
    ];

}
