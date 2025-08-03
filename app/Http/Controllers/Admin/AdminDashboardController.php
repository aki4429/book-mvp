<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\TimeSlot;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index()
    {
        // 基本統計データを取得
        $stats = $this->getBasicStats();
        
        // 最近の予約データを取得
        $recentReservations = $this->getRecentReservations();
        
        // 今日の予約統計
        $todayStats = $this->getTodayStats();
        
        // 月別統計データ
        $monthlyStats = $this->getMonthlyStats();
        
        // システム情報
        $systemInfo = $this->getSystemInfo();

        return view('admin.dashboard.index', compact(
            'stats',
            'recentReservations',
            'todayStats',
            'monthlyStats',
            'systemInfo'
        ));
    }

    public function getStats(Request $request)
    {
        try {
            $period = $request->get('period', 'today'); // today, week, month, year
            
            $stats = [];
            
            switch ($period) {
                case 'today':
                    $stats = $this->getTodayStats();
                    break;
                case 'week':
                    $stats = $this->getWeekStats();
                    break;
                case 'month':
                    $stats = $this->getMonthStats();
                    break;
                case 'year':
                    $stats = $this->getYearStats();
                    break;
                default:
                    $stats = $this->getTodayStats();
            }

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '統計データの取得に失敗しました。'
            ], 500);
        }
    }

    public function getRecentActivity(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            
            $recentReservations = Reservation::with(['customer', 'timeSlot'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($reservation) {
                    return [
                        'id' => $reservation->id,
                        'customer_name' => $reservation->customer->name,
                        'customer_email' => $reservation->customer->email,
                        'time_slot_date' => $reservation->timeSlot->date,
                        'time_slot_start' => $reservation->timeSlot->start_time,
                        'time_slot_end' => $reservation->timeSlot->end_time,
                        'status' => $reservation->status,
                        'created_at' => $reservation->created_at->format('Y-m-d H:i'),
                        'formatted_date' => $reservation->created_at->format('m月d日 H:i')
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $recentReservations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '最近のアクティビティの取得に失敗しました。'
            ], 500);
        }
    }

    public function getChartData(Request $request)
    {
        try {
            $type = $request->get('type', 'reservations'); // reservations, revenue, customers
            $period = $request->get('period', 'month'); // week, month, year
            
            $data = [];
            
            switch ($type) {
                case 'reservations':
                    $data = $this->getReservationChartData($period);
                    break;
                case 'customers':
                    $data = $this->getCustomerChartData($period);
                    break;
                case 'timeslots':
                    $data = $this->getTimeSlotChartData($period);
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'チャートデータの取得に失敗しました。'
            ], 500);
        }
    }

    private function getBasicStats()
    {
        return [
            'total_users' => User::count(),
            'total_customers' => Customer::count(),
            'total_reservations' => Reservation::count(),
            'total_timeslots' => TimeSlot::count(),
            'active_timeslots' => TimeSlot::where('available', true)->count(),
        ];
    }

    private function getRecentReservations()
    {
        return Reservation::with(['customer', 'timeSlot'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    private function getTodayStats()
    {
        $today = Carbon::today();
        
        return [
            'today_reservations' => Reservation::whereDate('created_at', $today)->count(),
            'today_timeslots' => TimeSlot::whereDate('date', $today)->count(),
            'today_available_slots' => TimeSlot::whereDate('date', $today)->where('available', true)->count(),
            'today_customers' => Customer::whereDate('created_at', $today)->count(),
        ];
    }

    private function getWeekStats()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        return [
            'week_reservations' => Reservation::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),
            'week_timeslots' => TimeSlot::whereBetween('date', [$startOfWeek, $endOfWeek])->count(),
            'week_available_slots' => TimeSlot::whereBetween('date', [$startOfWeek, $endOfWeek])->where('available', true)->count(),
            'week_customers' => Customer::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),
        ];
    }

    private function getMonthStats()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        return [
            'month_reservations' => Reservation::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            'month_timeslots' => TimeSlot::whereBetween('date', [$startOfMonth, $endOfMonth])->count(),
            'month_available_slots' => TimeSlot::whereBetween('date', [$startOfMonth, $endOfMonth])->where('available', true)->count(),
            'month_customers' => Customer::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
        ];
    }

    private function getYearStats()
    {
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();
        
        return [
            'year_reservations' => Reservation::whereBetween('created_at', [$startOfYear, $endOfYear])->count(),
            'year_timeslots' => TimeSlot::whereBetween('date', [$startOfYear, $endOfYear])->count(),
            'year_available_slots' => TimeSlot::whereBetween('date', [$startOfYear, $endOfYear])->where('available', true)->count(),
            'year_customers' => Customer::whereBetween('created_at', [$startOfYear, $endOfYear])->count(),
        ];
    }

    private function getMonthlyStats()
    {
        $monthlyData = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            
            $monthlyData[] = [
                'month' => $date->format('Y-m'),
                'month_label' => $date->format('Y年m月'),
                'reservations' => Reservation::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                'customers' => Customer::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                'timeslots' => TimeSlot::whereBetween('date', [$startOfMonth, $endOfMonth])->count(),
            ];
        }
        
        return $monthlyData;
    }

    private function getReservationChartData($period)
    {
        $data = [];
        
        switch ($period) {
            case 'week':
                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i);
                    $data[] = [
                        'label' => $date->format('m/d'),
                        'value' => Reservation::whereDate('created_at', $date)->count()
                    ];
                }
                break;
            case 'month':
                for ($i = 29; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i);
                    $data[] = [
                        'label' => $date->format('m/d'),
                        'value' => Reservation::whereDate('created_at', $date)->count()
                    ];
                }
                break;
            case 'year':
                for ($i = 11; $i >= 0; $i--) {
                    $date = Carbon::now()->subMonths($i);
                    $startOfMonth = $date->copy()->startOfMonth();
                    $endOfMonth = $date->copy()->endOfMonth();
                    $data[] = [
                        'label' => $date->format('Y/m'),
                        'value' => Reservation::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count()
                    ];
                }
                break;
        }
        
        return $data;
    }

    private function getCustomerChartData($period)
    {
        $data = [];
        
        switch ($period) {
            case 'week':
                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i);
                    $data[] = [
                        'label' => $date->format('m/d'),
                        'value' => Customer::whereDate('created_at', $date)->count()
                    ];
                }
                break;
            case 'month':
                for ($i = 29; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i);
                    $data[] = [
                        'label' => $date->format('m/d'),
                        'value' => Customer::whereDate('created_at', $date)->count()
                    ];
                }
                break;
            case 'year':
                for ($i = 11; $i >= 0; $i--) {
                    $date = Carbon::now()->subMonths($i);
                    $startOfMonth = $date->copy()->startOfMonth();
                    $endOfMonth = $date->copy()->endOfMonth();
                    $data[] = [
                        'label' => $date->format('Y/m'),
                        'value' => Customer::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count()
                    ];
                }
                break;
        }
        
        return $data;
    }

    private function getTimeSlotChartData($period)
    {
        $data = [];
        
        switch ($period) {
            case 'week':
                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i);
                    $data[] = [
                        'label' => $date->format('m/d'),
                        'value' => TimeSlot::whereDate('date', $date)->count()
                    ];
                }
                break;
            case 'month':
                for ($i = 29; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i);
                    $data[] = [
                        'label' => $date->format('m/d'),
                        'value' => TimeSlot::whereDate('date', $date)->count()
                    ];
                }
                break;
            case 'year':
                for ($i = 11; $i >= 0; $i--) {
                    $date = Carbon::now()->subMonths($i);
                    $startOfMonth = $date->copy()->startOfMonth();
                    $endOfMonth = $date->copy()->endOfMonth();
                    $data[] = [
                        'label' => $date->format('Y/m'),
                        'value' => TimeSlot::whereBetween('date', [$startOfMonth, $endOfMonth])->count()
                    ];
                }
                break;
        }
        
        return $data;
    }

    private function getSystemInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connection' => config('database.default'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'timezone' => config('app.timezone'),
        ];
    }
}
