<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Reservation;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class UserManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display the main user management page (JS version)
     */
    public function index()
    {
        return view('admin.user-manager.index');
    }

    /**
     * Get paginated users list via AJAX
     */
    public function getUsers(Request $request)
    {
        $query = User::withCount('reservations');

        // 検索機能
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // フィルター機能
        if ($request->filled('role')) {
            if ($request->role === 'admin') {
                $query->where('is_admin', true);
            } elseif ($request->role === 'user') {
                $query->where('is_admin', false);
            }
        }

        // ソート機能
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSorts = ['name', 'email', 'created_at', 'is_admin'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $users = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ]
        ]);
    }

    /**
     * Get single user details
     */
    public function getUser(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Store a new user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'is_admin' => 'boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => $request->boolean('is_admin'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ユーザーが正常に作成されました。',
            'data' => $user
        ]);
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'is_admin' => 'boolean',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'is_admin' => $request->boolean('is_admin'),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'ユーザー情報が正常に更新されました。',
            'data' => $user
        ]);
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        // 自分自身は削除できないようにする
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => '自分自身を削除することはできません。'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'ユーザーが正常に削除されました。'
        ]);
    }

    /**
     * Bulk actions (delete multiple users)
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $userIds = $request->user_ids;
        
        // 自分自身が含まれていないかチェック
        if (in_array(auth()->id(), $userIds)) {
            return response()->json([
                'success' => false,
                'message' => '自分自身を削除することはできません。'
            ], 400);
        }

        $deletedCount = User::whereIn('id', $userIds)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount}人のユーザーが削除されました。"
        ]);
    }

    /**
     * Toggle admin status
     */
    public function toggleAdmin(User $user)
    {
        // 自分自身の管理者権限は変更できない
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => '自分自身の管理者権限は変更できません。'
            ], 400);
        }

        $user->update(['is_admin' => !$user->is_admin]);

        $status = $user->is_admin ? '管理者' : '一般ユーザー';

        return response()->json([
            'success' => true,
            'message' => "ユーザーの権限を{$status}に変更しました。",
            'data' => $user
        ]);
    }

    /**
     * Get user's reservations
     */
    public function getUserReservations(User $user, Request $request)
    {
        $query = $user->reservations()->with(['timeSlot']);

        // 日付範囲フィルター
        if ($request->filled('date_from')) {
            $query->whereHas('timeSlot', function($q) use ($request) {
                $q->where('date', '>=', $request->date_from);
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('timeSlot', function($q) use ($request) {
                $q->where('date', '<=', $request->date_to);
            });
        }

        // ステータスフィルター
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ソート
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if ($sortField === 'date') {
            $query->join('time_slots', 'reservations.time_slot_id', '=', 'time_slots.id')
                  ->orderBy('time_slots.date', $sortDirection)
                  ->orderBy('time_slots.start_time', $sortDirection)
                  ->select('reservations.*');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        $perPage = $request->get('per_page', 10);
        $reservations = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $reservations->items(),
            'pagination' => [
                'current_page' => $reservations->currentPage(),
                'last_page' => $reservations->lastPage(),
                'per_page' => $reservations->perPage(),
                'total' => $reservations->total(),
                'from' => $reservations->firstItem(),
                'to' => $reservations->lastItem()
            ]
        ]);
    }

    /**
     * Get single reservation details
     */
    public function getReservation(Reservation $reservation)
    {
        $reservation->load(['user', 'timeSlot']);
        
        return response()->json([
            'success' => true,
            'data' => $reservation
        ]);
    }

    /**
     * Update reservation
     */
    public function updateReservation(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'time_slot_id' => 'sometimes|exists:time_slots,id',
            'status' => 'sometimes|in:confirmed,cancelled,completed',
            'notes' => 'nullable|string|max:1000'
        ]);

        // 時間枠を変更する場合は空きをチェック
        if (isset($validated['time_slot_id']) && $validated['time_slot_id'] != $reservation->time_slot_id) {
            $newTimeSlot = TimeSlot::find($validated['time_slot_id']);
            
            if (!$newTimeSlot->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'message' => '選択された時間枠は満席です。'
                ], 400);
            }
        }

        $reservation->update($validated);
        $reservation->load(['user', 'timeSlot']);

        return response()->json([
            'success' => true,
            'message' => '予約が更新されました。',
            'data' => $reservation
        ]);
    }

    /**
     * Delete reservation
     */
    public function deleteReservation(Reservation $reservation)
    {
        $reservation->delete();

        return response()->json([
            'success' => true,
            'message' => '予約が削除されました。'
        ]);
    }

    /**
     * Create new reservation for user
     */
    public function createReservation(Request $request, User $user)
    {
        $validated = $request->validate([
            'time_slot_id' => 'required|exists:time_slots,id',
            'status' => 'sometimes|in:confirmed,cancelled,completed',
            'notes' => 'nullable|string|max:1000'
        ]);

        $timeSlot = TimeSlot::find($validated['time_slot_id']);
        
        if (!$timeSlot->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => '選択された時間枠は満席です。'
            ], 400);
        }

        $validated['user_id'] = $user->id;
        $validated['status'] = $validated['status'] ?? 'confirmed';

        $reservation = Reservation::create($validated);
        $reservation->load(['user', 'timeSlot']);

        return response()->json([
            'success' => true,
            'message' => '予約が作成されました。',
            'data' => $reservation
        ]);
    }

    /**
     * Get available time slots for reservation creation
     */
    public function getAvailableTimeSlots(Request $request)
    {
        $query = TimeSlot::where('available', true)
                         ->where('date', '>=', Carbon::today());

        // 日付範囲フィルター
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $timeSlots = $query->orderBy('date')
                          ->orderBy('start_time')
                          ->get();

        return response()->json([
            'success' => true,
            'data' => $timeSlots
        ]);
    }
}
