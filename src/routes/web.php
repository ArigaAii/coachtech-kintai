<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampCorrectionRequestController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::post('/login/custom', [LoginController::class, 'store'])->name('login.store');
Route::post('/admin/login/custom', [AdminLoginController::class, 'store'])->name('admin.login.store');
Route::post('/register/custom', [RegisterController::class, 'store'])->name('register.store');

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/admin/login', function () {
    return view('admin.auth.login');
})->name('admin.login');

Route::get('/attendance/staff/{user}/csv', [AdminAttendanceController::class, 'exportStaffCsv'])
    ->name('admin.attendance.staff.csv');

// 一般ユーザー
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock_in');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock_out');
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.break_start');
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.break_end');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/detail/{attendance}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/stamp_correction_request/{attendance}', [StampCorrectionRequestController::class, 'store'])->name('stamp_correction_request.store');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])->name('stamp_correction_request.index');
});

// 管理者
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
    Route::get('/attendance/{attendance}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');

    Route::get('/staff/list', [StaffController::class, 'index'])->name('admin.staff.index');
    Route::get('/attendance/staff/{user}', [AdminAttendanceController::class, 'staffAttendanceList'])->name('admin.attendance.staff');
    Route::post('/attendance/{attendance}/update', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');
    Route::get('/stamp_correction_request/approve/{attendanceCorrectionRequest}', [AdminStampCorrectionRequestController::class, 'show'])->name('admin.stamp_correction_request.show');
    Route::post('/stamp_correction_request/approve/{attendanceCorrectionRequest}/approve', [AdminStampCorrectionRequestController::class, 'approve'])->name('admin.stamp_correction_request.approve');
    Route::post('/stamp_correction_request/approve/{attendanceCorrectionRequest}/reject', [AdminStampCorrectionRequestController::class, 'reject'])->name('admin.stamp_correction_request.reject');
});