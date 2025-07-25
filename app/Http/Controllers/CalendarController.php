<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * 顧客用カレンダーを表示
     */
    public function index()
    {
        return view('calendar.public');
    }
}
