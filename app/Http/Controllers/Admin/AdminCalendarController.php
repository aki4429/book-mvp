<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCalendarController extends Controller
{
    public function index(Request $request)
    {
        \Log::info('AdminCalendar index method called', [
            'user' => auth()->user() ? auth()->user()->toArray() : 'not authenticated',
            'request_data' => $request->all()
        ]);
        
        try {
            $currentDate = Carbon::now();
            $year = $request->get('year', $currentDate->year);
            $month = $request->get('month', $currentDate->month);
            
            $currentMonth = Carbon::create($year, $month, 1);
            
            // 月の開始日と終了日
            $startOfMonth = $currentMonth->copy()->startOfMonth();
            $endOfMonth = $currentMonth->copy()->endOfMonth();
            
            // カレンダー表示用の日付配列を生成
            $calendar = $this->generateCalendarData($startOfMonth, $endOfMonth);
            
            $calendarData = [
                'currentMonth' => $currentMonth,
                'calendar' => $calendar,
                'startOfMonth' => $startOfMonth,
                'endOfMonth' => $endOfMonth,
            ];
            
            // ビューに渡す追加の変数
            $currentYear = $currentMonth->year;
            $currentMonthNum = $currentMonth->month;
            
            \Log::info('AdminCalendar calendar data prepared', [
                'current_month' => $currentMonth->format('Y-m'),
                'calendar_weeks' => count($calendar),
                'start_month' => $startOfMonth->format('Y-m-d'),
                'end_month' => $endOfMonth->format('Y-m-d')
            ]);
            
            return view('admin.calendar.index', compact('calendarData', 'currentYear', 'currentMonthNum'));
        } catch (\Exception $e) {
            \Log::error('AdminCalendar index error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    public function changeMonth(Request $request)
    {
        $year = $request->get('year');
        $month = $request->get('month');
        
        $currentMonth = Carbon::create($year, $month, 1);
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();
        
        $calendar = $this->generateCalendarData($startOfMonth, $endOfMonth);
        
        $calendarData = [
            'currentMonth' => $currentMonth,
            'calendar' => $calendar,
            'startOfMonth' => $startOfMonth,
            'endOfMonth' => $endOfMonth,
        ];
        
        $html = view('admin.calendar.partials.calendar-grid-simple', $calendarData)->render();
        
        return response()->json([
            'success' => true,
            'html' => $html,
            'year' => $year,
            'month' => $month
        ]);
    }
    
    public function getDaySlots(Request $request)
    {
        $date = $request->get('date');
        $targetDate = Carbon::parse($date);
        
        // その日の時間枠を取得（予約情報も含む）
        $timeSlots = TimeSlot::where('date', $targetDate->format('Y-m-d'))
            ->with(['reservations.customer'])
            ->orderBy('start_time')
            ->get();
        
        $html = view('admin.calendar.partials.day-slots', compact('timeSlots', 'targetDate'))->render();
        
        return response()->json([
            'success' => true,
            'html' => $html,
            'date' => $date,
            'slots_count' => $timeSlots->count()
        ]);
    }
    
    public function updateTimeSlot(Request $request, $id)
    {
        try {
            \Log::info('UpdateTimeSlot request received', [
                'id' => $id,
                'request_data' => $request->all(),
                'raw_content' => $request->getContent(),
                'content_type' => $request->header('Content-Type'),
                'method' => $request->method()
            ]);
            
            $request->validate([
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'capacity' => 'required|integer|min:1',
                'service_id' => 'nullable|string|max:50',
                'available' => 'boolean'
            ]);
            
            $timeSlot = TimeSlot::findOrFail($id);
            $timeSlot->update([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'capacity' => $request->capacity,
                'service_id' => $request->service_id,
                'available' => $request->boolean('available', true)
            ]);
            
            \Log::info('TimeSlot updated successfully', ['id' => $id]);
            
            return response()->json([
                'success' => true,
                'message' => '時間枠を更新しました。'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('TimeSlot validation error', [
                'id' => $id,
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'バリデーションエラー: ' . collect($e->errors())->flatten()->implode(', ')
            ], 422);
        } catch (\Exception $e) {
            \Log::error('TimeSlot update error', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'エラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function deleteTimeSlot($id)
    {
        $timeSlot = TimeSlot::findOrFail($id);
        
        // 予約がある場合は削除を拒否
        if ($timeSlot->reservations()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'この時間枠には予約があるため削除できません。'
            ], 400);
        }
        
        $timeSlot->delete();
        
        return response()->json([
            'success' => true,
            'message' => '時間枠を削除しました。'
        ]);
    }
    
    public function createTimeSlot(Request $request)
    {
        try {
            \Log::info('CreateTimeSlot request received', [
                'request_data' => $request->all()
            ]);
            
            $request->validate([
                'date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'capacity' => 'required|integer|min:1',
                'service_id' => 'nullable|string|max:50',
                'available' => 'boolean'
            ]);
            
            $timeSlot = TimeSlot::create([
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'capacity' => $request->capacity,
                'service_id' => $request->service_id,
                'available' => $request->boolean('available', true)
            ]);
            
            \Log::info('TimeSlot created successfully', ['id' => $timeSlot->id]);
            
            return response()->json([
                'success' => true,
                'message' => '時間枠を作成しました。',
                'slot' => $timeSlot
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('TimeSlot creation validation error', [
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'バリデーションエラー: ' . collect($e->errors())->flatten()->implode(', ')
            ], 422);
        } catch (\Exception $e) {
            \Log::error('TimeSlot creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'エラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function updateReservation(Request $request, $id)
    {
        try {
            \Log::info('UpdateReservation request received', [
                'id' => $id,
                'request_data' => $request->all(),
                'raw_content' => $request->getContent(),
                'content_type' => $request->header('Content-Type'),
                'method' => $request->method()
            ]);
            
            $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'status' => 'required|in:pending,confirmed,cancelled'
            ]);
            
            $reservation = Reservation::findOrFail($id);
            
            // 顧客情報を更新または作成
            $customer = $reservation->customer;
            if (!$customer) {
                // 顧客が存在しない場合は新規作成
                $customer = \App\Models\Customer::create([
                    'name' => $request->customer_name,
                    'email' => $request->customer_email,
                    'phone' => $request->customer_phone,
                    'password' => bcrypt('temporary_password') // 一時的なパスワード
                ]);
                
                // 予約の顧客IDを更新
                $reservation->customer_id = $customer->id;
                $reservation->save();
            } else {
                // 既存の顧客情報を更新
                $customer->update([
                    'name' => $request->customer_name,
                    'email' => $request->customer_email,
                    'phone' => $request->customer_phone
                ]);
            }
            
            $reservation->update([
                'status' => $request->status
            ]);
            
            \Log::info('Reservation updated successfully', ['id' => $id]);
            
            return response()->json([
                'success' => true,
                'message' => '予約を更新しました。'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Reservation validation error', [
                'id' => $id,
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'バリデーションエラー: ' . collect($e->errors())->flatten()->implode(', ')
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Reservation update error', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'エラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function deleteReservation($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();
        
        return response()->json([
            'success' => true,
            'message' => '予約を削除しました。'
        ]);
    }
    
    public function createReservation(Request $request)
    {
        try {
            \Log::info('CreateReservation request received', [
                'request_data' => $request->all()
            ]);
            
            // 既存顧客を使用する場合
            if ($request->boolean('use_existing_customer', false)) {
                $request->validate([
                    'time_slot_id' => 'required|exists:time_slots,id',
                    'customer_id' => 'required|exists:customers,id',
                    'status' => 'required|in:pending,confirmed,cancelled'
                ]);
                
                $customer = \App\Models\Customer::findOrFail($request->customer_id);
            } else {
                // 新規顧客の場合
                $request->validate([
                    'time_slot_id' => 'required|exists:time_slots,id',
                    'customer_name' => 'required|string|max:255',
                    'customer_email' => 'required|email|max:255',
                    'customer_phone' => 'nullable|string|max:20',
                    'customer_password' => 'required|string|min:6|confirmed',
                    'status' => 'required|in:pending,confirmed,cancelled'
                ]);
                
                // 顧客を検索または作成
                $customer = \App\Models\Customer::where('email', $request->customer_email)->first();
                
                if (!$customer) {
                    $customer = \App\Models\Customer::create([
                        'name' => $request->customer_name,
                        'email' => $request->customer_email,
                        'phone' => $request->customer_phone,
                        'password' => bcrypt($request->customer_password)
                    ]);
                } else {
                    // 既存顧客の情報を更新（パスワードは既存顧客の場合は更新しない）
                    $customer->update([
                        'name' => $request->customer_name,
                        'phone' => $request->customer_phone
                    ]);
                }
            }
            
            // 時間枠の容量をチェック
            $timeSlot = TimeSlot::with('reservations')->findOrFail($request->time_slot_id);
            $currentReservations = $timeSlot->reservations()->where('status', '!=', 'cancelled')->count();
            
            if ($currentReservations >= $timeSlot->capacity) {
                return response()->json([
                    'success' => false,
                    'message' => 'この時間枠は満席です。'
                ], 400);
            }
            
            // 予約を作成
            $reservation = Reservation::create([
                'time_slot_id' => $request->time_slot_id,
                'customer_id' => $customer->id,
                'status' => $request->status,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // 関連データと一緒に予約を再取得
            $reservation = Reservation::with(['customer', 'timeSlot'])->find($reservation->id);
            
            \Log::info('Reservation created successfully', ['id' => $reservation->id]);
            
            return response()->json([
                'success' => true,
                'message' => '予約を作成しました。',
                'reservation' => $reservation
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Reservation creation validation error', [
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'バリデーションエラー: ' . collect($e->errors())->flatten()->implode(', ')
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Reservation creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'エラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function generateCalendarData($startOfMonth, $endOfMonth)
    {
        $calendar = [];
        
        // 月の最初の週の開始日（日曜日）
        $startOfCalendar = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        // 月の最後の週の終了日（土曜日）
        $endOfCalendar = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);
        
        // その期間の時間枠データを取得
        $timeSlots = TimeSlot::whereBetween('date', [$startOfCalendar->format('Y-m-d'), $endOfCalendar->format('Y-m-d')])
            ->with(['reservations'])
            ->get()
            ->groupBy(function($slot) {
                return $slot->date->format('Y-m-d');
            });
        
        $current = $startOfCalendar->copy();
        while ($current->lte($endOfCalendar)) {
            $dateString = $current->format('Y-m-d');
            $slots = $timeSlots->get($dateString, collect());
            
            // 予約状況の統計
            $totalSlots = $slots->count();
            $totalReservations = $slots->sum(function($slot) {
                return $slot->reservations->count();
            });
            $availableSlots = $slots->where('available', true)->count();
            
            $calendar[] = [
                'date' => $current->copy(),
                'isCurrentMonth' => $current->month == $startOfMonth->month,
                'isToday' => $current->isToday(),
                'slots' => $slots,
                'totalSlots' => $totalSlots,
                'totalReservations' => $totalReservations,
                'availableSlots' => $availableSlots,
                'hasSlots' => $totalSlots > 0
            ];
            
            $current->addDay();
        }
        
        return array_chunk($calendar, 7);
    }
    
    public function searchCustomers(Request $request)
    {
        try {
            $query = $request->get('query', '');
            
            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'customers' => []
                ]);
            }
            
            $customers = \App\Models\Customer::where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone']);
            
            return response()->json([
                'success' => true,
                'customers' => $customers
            ]);
        } catch (\Exception $e) {
            \Log::error('Customer search error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'エラーが発生しました: ' . $e->getMessage()
            ], 500);
        }
    }
}
