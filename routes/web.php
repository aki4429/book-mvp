<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\AdminCalendarController;
use App\Http\Controllers\Admin\AdminDashboardController;
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

// テスト用：認証なしで管理者カレンダーにアクセス
Route::get('/admin/calendar/test', [AdminCalendarController::class, 'index'])->name('admin.calendar.test');
Route::get('/admin/calendar/debug', function() {
    $controller = new AdminCalendarController();
    $data = $controller->index()->getData();
    return view('admin.calendar.debug', $data);
})->name('admin.calendar.debug');
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

        // 顧客プロフィール管理
        Route::get('/profile', [App\Http\Controllers\Customer\ProfileController::class, 'show'])->name('profile.show');
        Route::get('/profile/edit', [App\Http\Controllers\Customer\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [App\Http\Controllers\Customer\ProfileController::class, 'update'])->name('profile.update');

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
    
    // 管理者カレンダー
    Route::get('/admin/calendar', [AdminCalendarController::class, 'index'])->name('admin.calendar.index');
    Route::get('/admin/calendar/change-month', [AdminCalendarController::class, 'changeMonth'])->name('admin.calendar.change-month');
    Route::get('/admin/calendar/day-slots', [AdminCalendarController::class, 'getDaySlots'])->name('admin.calendar.day-slots');
    Route::get('/admin/calendar/search-customers', [AdminCalendarController::class, 'searchCustomers'])->name('admin.calendar.search-customers');
    
    // 時間枠管理 (AdminCalendar用)
    Route::post('/admin/calendar/timeslots', [AdminCalendarController::class, 'createTimeSlot'])->name('admin.calendar.timeslots.create');
    Route::put('/admin/timeslots/{id}', [AdminCalendarController::class, 'updateTimeSlot'])->name('admin.timeslots.update');
    Route::delete('/admin/timeslots/{id}', [AdminCalendarController::class, 'deleteTimeSlot'])->name('admin.timeslots.delete');
    
    // 予約管理
    Route::post('/admin/calendar/reservations', [AdminCalendarController::class, 'createReservation'])->name('admin.calendar.reservations.create');
    Route::put('/admin/reservations/{id}', [AdminCalendarController::class, 'updateReservation'])->name('admin.reservations.update');
    Route::delete('/admin/reservations/{id}', [AdminCalendarController::class, 'deleteReservation'])->name('admin.reservations.delete');
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
    // 管理者ダッシュボード（通常版）
    Route::get('dashboard', function () {
        return view('admin.dashboard.simple');
    })->name('dashboard');
    
    // カレンダー管理
    Route::get('calendar', [\App\Http\Controllers\Admin\AdminCalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendar/change-month', [\App\Http\Controllers\Admin\AdminCalendarController::class, 'changeMonth'])->name('calendar.change-month');
    Route::get('calendar/day-slots', [\App\Http\Controllers\Admin\AdminCalendarController::class, 'getDaySlots'])->name('calendar.day-slots');
    
    // 時間枠管理
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

    // 管理者ユーザー管理（既存のLivewire版）
    Route::resource('users', \App\Http\Controllers\Admin\AdminUserController::class);
    
    // 管理者ダッシュボード（JS版）
    Route::prefix('admin-dashboard')->name('admin-dashboard.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('index');
        Route::get('/stats', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'getStats'])->name('stats');
        Route::get('/recent-activity', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'getRecentActivity'])->name('recent-activity');
        Route::get('/chart-data', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'getChartData'])->name('chart-data');
    });
    
    // JS版ユーザー管理
    Route::prefix('user-manager')->name('user-manager.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UserManagerController::class, 'index'])->name('index');
        Route::get('/users', [\App\Http\Controllers\Admin\UserManagerController::class, 'getUsers'])->name('users');
        Route::get('/users/{user}', [\App\Http\Controllers\Admin\UserManagerController::class, 'getUser'])->name('user');
        Route::post('/users', [\App\Http\Controllers\Admin\UserManagerController::class, 'store'])->name('store');
        Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserManagerController::class, 'update'])->name('update');
        Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserManagerController::class, 'destroy'])->name('destroy');
        Route::post('/users/bulk-delete', [\App\Http\Controllers\Admin\UserManagerController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/users/{user}/toggle-admin', [\App\Http\Controllers\Admin\UserManagerController::class, 'toggleAdmin'])->name('toggle-admin');
        
        // 予約管理機能
        Route::get('/users/{user}/reservations', [\App\Http\Controllers\Admin\UserManagerController::class, 'getUserReservations'])->name('user-reservations');
        Route::get('/reservations/{reservation}', [\App\Http\Controllers\Admin\UserManagerController::class, 'getReservation'])->name('reservation');
        Route::put('/reservations/{reservation}', [\App\Http\Controllers\Admin\UserManagerController::class, 'updateReservation'])->name('update-reservation');
        Route::delete('/reservations/{reservation}', [\App\Http\Controllers\Admin\UserManagerController::class, 'deleteReservation'])->name('delete-reservation');
        Route::post('/users/{user}/reservations', [\App\Http\Controllers\Admin\UserManagerController::class, 'createReservation'])->name('create-reservation');
        Route::get('/available-timeslots', [\App\Http\Controllers\Admin\UserManagerController::class, 'getAvailableTimeSlots'])->name('available-timeslots');
    });

    // JS版一括時間枠設定
    Route::prefix('bulk-timeslots')->name('bulk-timeslots.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\BulkTimeSlotController::class, 'index'])->name('index');
        Route::get('/presets', [\App\Http\Controllers\Admin\BulkTimeSlotController::class, 'getPresets'])->name('presets');
        Route::get('/presets/{preset}', [\App\Http\Controllers\Admin\BulkTimeSlotController::class, 'getPreset'])->name('preset');
        Route::post('/preview', [\App\Http\Controllers\Admin\BulkTimeSlotController::class, 'preview'])->name('preview');
        Route::post('/store', [\App\Http\Controllers\Admin\BulkTimeSlotController::class, 'store'])->name('store');
    });

    // JS版プリセット管理
    Route::prefix('preset-manager')->name('preset-manager.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PresetManagerController::class, 'index'])->name('index');
        Route::get('/presets', [\App\Http\Controllers\Admin\PresetManagerController::class, 'getPresets'])->name('presets');
        Route::get('/presets/{preset}', [\App\Http\Controllers\Admin\PresetManagerController::class, 'getPreset'])->name('preset');
        Route::post('/presets', [\App\Http\Controllers\Admin\PresetManagerController::class, 'store'])->name('store');
        Route::put('/presets/{preset}', [\App\Http\Controllers\Admin\PresetManagerController::class, 'update'])->name('update');
        Route::delete('/presets/{preset}', [\App\Http\Controllers\Admin\PresetManagerController::class, 'destroy'])->name('destroy');
        Route::post('/presets/bulk-delete', [\App\Http\Controllers\Admin\PresetManagerController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/presets/{preset}/toggle-status', [\App\Http\Controllers\Admin\PresetManagerController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/presets/{preset}/duplicate', [\App\Http\Controllers\Admin\PresetManagerController::class, 'duplicate'])->name('duplicate');
        Route::post('/update-order', [\App\Http\Controllers\Admin\PresetManagerController::class, 'updateOrder'])->name('update-order');
    });

    // JS版システム設定管理
    Route::prefix('settings-manager')->name('settings-manager.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SettingsManagerController::class, 'index'])->name('index');
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsManagerController::class, 'getSettings'])->name('settings');
        Route::get('/settings/{setting}', [\App\Http\Controllers\Admin\SettingsManagerController::class, 'getSetting'])->name('setting');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingsManagerController::class, 'store'])->name('store');
        Route::put('/settings/{setting}', [\App\Http\Controllers\Admin\SettingsManagerController::class, 'update'])->name('update');
        Route::delete('/settings/{setting}', [\App\Http\Controllers\Admin\SettingsManagerController::class, 'destroy'])->name('destroy');
        Route::post('/settings/bulk-delete', [\App\Http\Controllers\Admin\SettingsManagerController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/settings/{setting}/reset-default', [\App\Http\Controllers\Admin\SettingsManagerController::class, 'resetToDefault'])->name('reset-default');
        Route::get('/system-info', [\App\Http\Controllers\Admin\SettingsManagerController::class, 'getSystemInfo'])->name('system-info');
    });
});



require __DIR__.'/auth.php';
