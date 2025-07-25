<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:customer');
    }

    /**
     * 顧客ダッシュボード表示
     */
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $reservations = $customer->reservations()
            ->with('timeSlot')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('customer.dashboard', compact('customer', 'reservations'));
    }
}
