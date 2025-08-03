<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\ReservationController as PublicReservationController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\PublicCalendarController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// 顧客用パブリックカレンダー（認証不要）- Livewireなし版
Route::get('/calendar', [PublicCalendarController::class, 'index'])->name('calendar.public');
Route::get('/calendar/change-month', [PublicCalendarController::class, 'changeMonth'])->name('calendar.change-month');
Route::get('/calendar/day-slots', [PublicCalendarController::class, 'getDaySlots'])->name('calendar.day-slots');

// 旧Livewire版（バックアップ）
Route::get('/calendar-livewire', [CalendarController::class, 'index'])->name('calendar.livewire');

// 顧客認証ルート
Route::prefix('customer')->name('customer.')->group(function () {
    // 未認証でアクセス可能
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [App\Http\Controllers\Customer\AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/register', [App\Http\Controllers\Customer\AuthController::class, 'register'])->name('register');
        Route::post('/login', [App\Http\Controllers\Customer\AuthController::class, 'login'])->name('login.post');
    });

    // 顧客認証が必要
    Route::middleware('auth:customer')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Customer\DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [App\Http\Controllers\Customer\AuthController::class, 'logout'])->name('logout');

        // 顧客の予約管理（自分の予約のみ）
        Route::get('/reservations', [App\Http\Controllers\Customer\ReservationController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/create', [App\Http\Controllers\Customer\ReservationController::class, 'create'])->name('reservations.create');
        Route::post('/reservations', [App\Http\Controllers\Customer\ReservationController::class, 'store'])->name('reservations.store');
        Route::get('/reservations/{reservation}', [App\Http\Controllers\Customer\ReservationController::class, 'show'])->name('reservations.show');
        Route::post('/reservations/{reservation}/cancel', [App\Http\Controllers\Customer\ReservationController::class, 'cancel'])->name('reservations.cancel');
    });
});

// 管理者用ダッシュボード（管理者のみアクセス可能）
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['admin', 'verified'])->name('dashboard');

// 管理者専用ルート（admin ミドルウェアで管理者のみアクセス可能）
Route::get('/admin-calendar', function () {
    return view('calendar');
})->middleware(['admin'])->name('admin.calendar');

Route::get('/test-modal', function () {
    return view('test-modal');
})->middleware(['admin'])->name('test-modal');

Route::middleware('admin')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/reservations', [AdminReservationController::class, 'index'])->name('reservations.index');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
});

// 管理者ダッシュボード
Route::middleware(['admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// 管理者の予約管理
Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('reservations', AdminReservationController::class);
    Route::get('reservations-list', [AdminReservationController::class, 'list'])->name('reservations.list');
});

// パブリック予約作成
Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');

// 管理者の時間枠管理
Route::prefix('admin')->middleware(['admin'])->name('admin.')->group(function () {
    Route::resource('timeslots', \App\Http\Controllers\Admin\TimeSlotController::class)->except(['show']);

    // 一括登録画面表示
    Route::get('timeslots/bulk-create', [\App\Http\Controllers\Admin\TimeSlotController::class, 'bulkCreate'])->name('timeslots.bulkCreate');

    // 一括登録処理
    Route::post('timeslots/bulk-store', [\App\Http\Controllers\Admin\TimeSlotController::class, 'bulkStore'])->name('timeslots.bulkStore');

    // プリセット管理
    Route::resource('presets', \App\Http\Controllers\Admin\TimeSlotPresetController::class)->except(['show']);
    Route::post('presets/update-order', [\App\Http\Controllers\Admin\TimeSlotPresetController::class, 'updateOrder'])->name('presets.updateOrder');

    // システム設定
    Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

    // 管理者ユーザー管理
    Route::resource('users', \App\Http\Controllers\Admin\AdminUserController::class);
});



require __DIR__.'/auth.php';
