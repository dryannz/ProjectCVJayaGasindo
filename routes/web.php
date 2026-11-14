<?php

use App\Http\Controllers\Admin\BarangJasaController;
use App\Http\Controllers\Admin\KategoriController;
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

Route::middleware(['auth', 'role:admin_sales'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', [StaffDashboardController::class, 'admin'])->name('dashboard');

    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::get('/kategori/create', [KategoriController::class, 'create'])->name('kategori.create');
    Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
    Route::get('/kategori/{kategori}/edit', [KategoriController::class, 'edit'])->name('kategori.edit');
    Route::put('/kategori/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

    Route::get('/produk', [BarangJasaController::class, 'index'])->name('produk.index');
    Route::get('/produk/create', [BarangJasaController::class, 'create'])->name('produk.create');
    Route::post('/produk', [BarangJasaController::class, 'store'])->name('produk.store');
    Route::get('/produk/{barangJasa:idbarangjasa}/edit', [BarangJasaController::class, 'edit'])->name('produk.edit');
    Route::put('/produk/{barangJasa:idbarangjasa}', [BarangJasaController::class, 'update'])->name('produk.update');
    Route::patch('/produk/{barangJasa:idbarangjasa}/status', [BarangJasaController::class, 'toggleStatus'])->name('produk.status');
    Route::delete('/produk/{barangJasa:idbarangjasa}', [BarangJasaController::class, 'destroy'])->name('produk.destroy');
});

Route::controller(StaffDashboardController::class)->middleware('auth')->group(function (): void {
    Route::get('/teknisi/work-order', 'workOrder')
        ->middleware('role:teknisi')
        ->name('teknisi.work-order');
    Route::get('/pimpinan/dashboard', 'pimpinan')
        ->middleware('role:pimpinan')
        ->name('pimpinan.dashboard');
});
