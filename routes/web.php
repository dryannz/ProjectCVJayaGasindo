<?php

use App\Http\Controllers\Admin\BarangJasaController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RfqController;
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

Route::controller(KatalogController::class)->group(function (): void {
    Route::get('/katalog', 'index')->name('katalog.index');
    Route::get('/katalog/produk/{barangJasa}', 'show')->name('katalog.produk');
    Route::get('/katalog/{kategori:slug}', 'kategori')->name('katalog.kategori');
    Route::get('/sitemap.xml', 'sitemap')->name('sitemap');
});

Route::controller(RfqController::class)->prefix('rfq')->name('rfq.')->group(function (): void {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->middleware('throttle:8,1')->name('store');
    Route::get('/terkirim', 'terkirim')->name('terkirim');
    Route::post('/item/{barangJasa}', 'add')->name('add');
    Route::patch('/item/{barangJasa}', 'update')->name('update');
    Route::delete('/item/{barangJasa}', 'remove')->name('remove');
});

Route::controller(LoginController::class)->group(function (): void {
    Route::get('/login', 'create')->name('login');
    Route::post('/login', 'login')->name('login.attempt');
    Route::post('/logout', 'logout')->middleware('auth')->name('logout');
});

Route::middleware(['auth', 'role:admin_sales'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', [StaffDashboardController::class, 'admin'])->name('dashboard');

    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::patch('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');

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

    Route::get('/customer', [CustomerController::class, 'index'])->name('customer.index');
    Route::get('/customer/create', [CustomerController::class, 'create'])->name('customer.create');
    Route::post('/customer', [CustomerController::class, 'store'])->name('customer.store');
    Route::get('/customer/{customer}/edit', [CustomerController::class, 'edit'])->name('customer.edit');
    Route::put('/customer/{customer}', [CustomerController::class, 'update'])->name('customer.update');
    Route::delete('/customer/{customer}', [CustomerController::class, 'destroy'])->name('customer.destroy');

    Route::get('/quotation', [QuotationController::class, 'index'])->name('quotation.index');
    Route::get('/quotation/create', [QuotationController::class, 'create'])->name('quotation.create');
    Route::post('/quotation', [QuotationController::class, 'store'])->name('quotation.store');
    Route::get('/quotation/{quotation}', [QuotationController::class, 'show'])->name('quotation.show');
    Route::get('/quotation/{quotation}/edit', [QuotationController::class, 'edit'])->name('quotation.edit');
    Route::put('/quotation/{quotation}', [QuotationController::class, 'update'])->name('quotation.update');
    Route::patch('/quotation/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('quotation.status');
});

Route::controller(StaffDashboardController::class)->middleware('auth')->group(function (): void {
    Route::get('/teknisi/work-order', 'workOrder')
        ->middleware('role:teknisi')
        ->name('teknisi.work-order');
    Route::get('/pimpinan/dashboard', 'pimpinan')
        ->middleware('role:pimpinan')
        ->name('pimpinan.dashboard');
});
