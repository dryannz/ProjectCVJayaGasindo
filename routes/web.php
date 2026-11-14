<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\StaffDashboardController;
use Illuminate\Support\Facades\Route;

Route::controller(PageController::class)->group(function (): void {
    Route::get('/', 'home')->name('home');
    Route::get('/tentang-kami', 'about')->name('about');
    Route::get('/layanan', 'services')->name('services');
    Route::get('/galeri', 'gallery')->name('gallery');
    Route::get('/kontak', 'contact')->name('contact');
    Route::post('/kontak', 'submitContact')->name('contact.submit');
});

Route::controller(LoginController::class)->group(function (): void {
    Route::get('/login', 'create')->name('login');
    Route::post('/login', 'login')->name('login.attempt');
    Route::post('/logout', 'logout')->middleware('auth')->name('logout');
});

Route::controller(StaffDashboardController::class)->middleware('auth')->group(function (): void {
    Route::get('/admin/dashboard', 'admin')
        ->middleware('role:admin_sales')
        ->name('admin.dashboard');
    Route::get('/teknisi/work-order', 'workOrder')
        ->middleware('role:teknisi')
        ->name('teknisi.work-order');
    Route::get('/pimpinan/dashboard', 'pimpinan')
        ->middleware('role:pimpinan')
        ->name('pimpinan.dashboard');
});
