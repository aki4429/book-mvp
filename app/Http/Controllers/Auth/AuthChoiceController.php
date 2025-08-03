<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthChoiceController extends Controller
{
    /**
     * 認証選択画面を表示
     */
    public function show()
    {
        return view('auth.choice');
    }
}
