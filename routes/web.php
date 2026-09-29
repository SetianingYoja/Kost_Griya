<?php

use App\Http\Controllers\AuthController;
// Controllers
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VisitorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Midtrans Webhook Payment Notification
|--------------------------------------------------------------------------
*/
Route::post('/midtrans/webhook', [MidtransWebhookController::class, 'handleNotification'])
    ->name('midtrans.webhook');

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LaporanSistemController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Pemilik\BookingController as PemilikBookingController;
use App\Http\Controllers\Pemilik\DashboardController as PemilikDashboardController;
use App\Http\Controllers\Pemilik\KamarController as PemilikKamarController;
use App\Http\Controllers\Pemilik\KeluhanController as PemilikKeluhanController;
use App\Http\Controllers\Pemilik\LaporanController as PemilikLaporanController;
use App\Http\Controllers\Pemilik\PembayaranController as PemilikPembayaranController;
use App\Http\Controllers\Pemilik\PenghuniController as PemilikPenghuniController;
use App\Http\Controllers\Pemilik\PerpanjanganController as PemilikPerpanjanganController;
use App\Http\Controllers\Pemilik\RatingController as PemilikRatingController;
use App\Http\Controllers\Pemilik\TagihanController as PemilikTagihanController;
use App\Http\Controllers\Penghuni\BookingController as PenghuniBookingController;
use App\Http\Controllers\Penghuni\DashboardController as PenghuniDashboardController;
use App\Http\Controllers\Penghuni\KeluhanController as PenghuniKeluhanController;
use App\Http\Controllers\Penghuni\PembayaranController as PenghuniPembayaranController;
use App\Http\Controllers\Penghuni\PerpanjanganController as PenghuniPerpanjanganController;
use App\Http\Controllers\Penghuni\RatingController as PenghuniRatingController;
use App\Http\Controllers\Penghuni\RiwayatController as PenghuniRiwayatController;
use App\Http\Controllers\Penghuni\TagihanController as PenghuniTagihanController;

/*
|--------------------------------------------------------------------------
| Visitor
|--------------------------------------------------------------------------
*/

Route::get('/', [VisitorController::class, 'home'])->name('home');

Route::get('/kamar', [VisitorController::class, 'kamarIndex'])
    ->name('kamar.index');

Route::get('/kamar/{id}', [VisitorController::class, 'kamarDetail'])
    ->name('kamar.detail');

Route::get('/tentang', [VisitorController::class, 'tentang'])
    ->name('tentang');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.post');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::get('/notifications/{id}', [NotificationController::class, 'open'])
        ->name('notifications.open');

    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');

    Route::get('/profile', [ProfileController::class, 'show'])
        ->name('profile');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password');
});

/*
|--------------------------------------------------------------------------
| Admin / Super Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:super-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // Users
        Route::middleware('permission:manage-users')->group(function () {
            Route::get('/users', [AdminUserController::class, 'index'])
                ->name('users.index');

            Route::get('/users/create', [AdminUserController::class, 'create'])
                ->name('users.create');

            Route::post('/users', [AdminUserController::class, 'store'])
                ->name('users.store');

            Route::get('/users/{id}/edit', [AdminUserController::class, 'edit'])
                ->name('users.edit');

            Route::put('/users/{id}', [AdminUserController::class, 'update'])
                ->name('users.update');

            Route::patch('/users/{id}/status', [AdminUserController::class, 'toggleStatus'])
                ->name('users.toggle');

            Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])
                ->name('users.destroy');
        });

        // Role & Permission
        // Role & Permission
        Route::middleware('permission:manage-roles')->group(function () {
            Route::get('/roles-permissions', [RolePermissionController::class, 'index'])
                ->name('roles.index');

            Route::put('/roles-permissions/{id}', [RolePermissionController::class, 'updatePermissions'])
                ->name('roles.update');
        });

        // Laporan Sistem
        Route::middleware('permission:view-laporan-sistem')->group(function () {
            Route::get('/laporan', [LaporanSistemController::class, 'index'])
                ->name('laporan');
        });
    });

/*
|--------------------------------------------------------------------------
| Pemilik
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:pemilik-kost'])
    ->prefix('pemilik')
    ->name('pemilik.')
    ->group(function () {

        Route::get('/dashboard', [PemilikDashboardController::class, 'index'])
            ->name('dashboard');

        // Kamar
        Route::middleware('permission:manage-kamar')->group(function () {
            Route::get('/kamar/create', [PemilikKamarController::class, 'create'])->name('kamar.create');
            Route::post('/kamar', [PemilikKamarController::class, 'store'])->name('kamar.store');
            Route::get('/kamar/{id}/edit', [PemilikKamarController::class, 'edit'])->name('kamar.edit');
            Route::put('/kamar/{id}', [PemilikKamarController::class, 'update'])->name('kamar.update');
            Route::delete('/kamar/{id}', [PemilikKamarController::class, 'destroy'])->name('kamar.destroy');
        });

        Route::middleware('permission:view-kamar')->group(function () {
            Route::get('/kamar', [PemilikKamarController::class, 'index'])->name('kamar.index');
            Route::get('/kamar/{id}', [PemilikKamarController::class, 'show'])->name('kamar.show');
        });

        // Booking
        Route::middleware('permission:validate-booking')->group(function () {
            Route::get('/booking', [PemilikBookingController::class, 'index'])->name('booking.index');
            Route::get('/booking/{id}', [PemilikBookingController::class, 'show'])->name('booking.show');
            Route::patch('/booking/{id}/approve', [PemilikBookingController::class, 'approve'])->name('booking.approve');
            Route::patch('/booking/{id}/reject', [PemilikBookingController::class, 'reject'])->name('booking.reject');
        });

        // Penghuni
        Route::middleware('permission:manage-penghuni')->group(function () {
            Route::get('/penghuni', [PemilikPenghuniController::class, 'index'])
                ->name('penghuni.index');

            Route::get('/penghuni/{id}', [PemilikPenghuniController::class, 'show'])
                ->name('penghuni.show');
        });

        // Tagihan
        Route::middleware('permission:manage-tagihan')->group(function () {
            Route::get('/tagihan', [PemilikTagihanController::class, 'index'])
                ->name('tagihan.index');

            Route::get('/tagihan/create', [PemilikTagihanController::class, 'create'])
                ->name('tagihan.create');

            Route::post('/tagihan', [PemilikTagihanController::class, 'store'])
                ->name('tagihan.store');

            Route::get('/tagihan/{id}', [PemilikTagihanController::class, 'show'])
                ->name('tagihan.show');
        });

        // Pembayaran
        Route::middleware('permission:validate-pembayaran')->group(function () {
            Route::get('/pembayaran', [PemilikPembayaranController::class, 'index'])
                ->name('pembayaran.index');

            Route::get('/pembayaran/{id}', [PemilikPembayaranController::class, 'show'])
                ->name('pembayaran.show');

            Route::patch('/pembayaran/{id}/approve', [PemilikPembayaranController::class, 'approve'])
                ->name('pembayaran.approve');

            Route::patch('/pembayaran/{id}/reject', [PemilikPembayaranController::class, 'reject'])
                ->name('pembayaran.reject');
        });

        // Keluhan
        Route::middleware('permission:manage-keluhan')->group(function () {
            Route::get('/keluhan', [PemilikKeluhanController::class, 'index'])
                ->name('keluhan.index');

            Route::get('/keluhan/{id}', [PemilikKeluhanController::class, 'show'])
                ->name('keluhan.show');

            Route::put('/keluhan/{id}', [PemilikKeluhanController::class, 'update'])
                ->name('keluhan.update');
        });

        // Perpanjangan
        Route::middleware('permission:validate-booking')->group(function () {
            Route::get('/perpanjangan', [PemilikPerpanjanganController::class, 'index'])
                ->name('perpanjangan.index');

            Route::get('/perpanjangan/{id}', [PemilikPerpanjanganController::class, 'show'])
                ->name('perpanjangan.show');

            Route::patch('/perpanjangan/{id}/approve', [PemilikPerpanjanganController::class, 'approve'])
                ->name('perpanjangan.approve');

            Route::patch('/perpanjangan/{id}/reject', [PemilikPerpanjanganController::class, 'reject'])
                ->name('perpanjangan.reject');
        });

        // Rating
        Route::middleware('permission:manage-rating')->group(function () {
            Route::get('/rating', [PemilikRatingController::class, 'index'])
                ->name('rating.index');

            Route::patch('/rating/{id}/approve', [PemilikRatingController::class, 'approve'])
                ->name('rating.approve');

            Route::patch('/rating/{id}/reject', [PemilikRatingController::class, 'reject'])
                ->name('rating.reject');

            Route::post('/rating/{id}/balas', [PemilikRatingController::class, 'balas'])
                ->name('rating.balas');

            Route::post('/rating/bulk-action', [PemilikRatingController::class, 'bulkAction'])
                ->name('rating.bulk');

            Route::post('/rating/approve-all', [PemilikRatingController::class, 'approveAll'])
                ->name('rating.approve-all');
        });

        // Laporan
        Route::middleware('permission:view-laporan-operasional')->group(function () {
            Route::get('/laporan', [PemilikLaporanController::class, 'index'])
                ->name('laporan.index');
        });
    });

/*
|--------------------------------------------------------------------------
| Penghuni
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:penghuni'])
    ->prefix('penghuni')
    ->name('penghuni.')
    ->group(function () {

        Route::get('/dashboard', [PenghuniDashboardController::class, 'index'])
            ->name('dashboard');

        // Booking
        Route::middleware('permission:create-booking')->group(function () {
            Route::get('/booking', [PenghuniBookingController::class, 'index'])
                ->name('booking.index');

            Route::get('/booking/create', [PenghuniBookingController::class, 'create'])
                ->name('booking.create');

            Route::post('/booking', [PenghuniBookingController::class, 'store'])
                ->name('booking.store');

            Route::get('/booking/{id}', [PenghuniBookingController::class, 'show'])
                ->name('booking.show');

            Route::get('/booking/{id}/bayar', [PenghuniBookingController::class, 'showBayar'])
                ->name('booking.bayar');

            Route::patch('/booking/{id}/cancel', [PenghuniBookingController::class, 'cancel'])
                ->name('booking.cancel');
        });

        // Tagihan
        Route::middleware('permission:view-self-tagihan')->group(function () {
            Route::get('/tagihan', [PenghuniTagihanController::class, 'index'])
                ->name('tagihan.index');

            Route::get('/tagihan/{id}', [PenghuniTagihanController::class, 'show'])
                ->name('tagihan.show');

            Route::get('/tagihan/{id}/bayar', [PenghuniTagihanController::class, 'showBayar'])
                ->name('tagihan.bayar');
        });

        // Pembayaran
        Route::middleware('permission:create-pembayaran')->group(function () {
            Route::get('/pembayaran', [PenghuniPembayaranController::class, 'index'])
                ->name('pembayaran.index');

            Route::get('/pembayaran/create', [PenghuniPembayaranController::class, 'create'])
                ->name('pembayaran.create');

            Route::post('/pembayaran', [PenghuniPembayaranController::class, 'store'])
                ->name('pembayaran.store');

            Route::get('/pembayaran/{id}', [PenghuniPembayaranController::class, 'show'])
                ->name('pembayaran.show');
        });

        // Keluhan
        Route::middleware('permission:create-keluhan')->group(function () {
            Route::get('/keluhan', [PenghuniKeluhanController::class, 'index'])
                ->name('keluhan.index');

            Route::get('/keluhan/create', [PenghuniKeluhanController::class, 'create'])
                ->name('keluhan.create');

            Route::post('/keluhan', [PenghuniKeluhanController::class, 'store'])
                ->name('keluhan.store');

            Route::get('/keluhan/{id}', [PenghuniKeluhanController::class, 'show'])
                ->name('keluhan.show');
        });

        // Perpanjangan
        Route::middleware('permission:create-booking')->group(function () {
            Route::get('/perpanjangan', [PenghuniPerpanjanganController::class, 'index'])
                ->name('perpanjangan.index');

            Route::get('/perpanjangan/create', [PenghuniPerpanjanganController::class, 'create'])
                ->name('perpanjangan.create');

            Route::post('/perpanjangan', [PenghuniPerpanjanganController::class, 'store'])
                ->name('perpanjangan.store');

            Route::get('/perpanjangan/{id}', [PenghuniPerpanjanganController::class, 'show'])
                ->name('perpanjangan.show');

            Route::get('/perpanjangan/{id}/bayar', [PenghuniPerpanjanganController::class, 'showBayar'])
                ->name('perpanjangan.bayar');
        });

        // Rating
        Route::middleware('permission:create-rating')->group(function () {
            Route::get('/rating', [PenghuniRatingController::class, 'index'])
                ->name('rating.index');

            Route::get('/rating/create', [PenghuniRatingController::class, 'create'])
                ->name('rating.create');

            Route::post('/rating', [PenghuniRatingController::class, 'store'])
                ->name('rating.store');
        });

        // Riwayat
        Route::middleware('permission:view-self-profile')->group(function () {
            Route::get('/riwayat', [PenghuniRiwayatController::class, 'index'])
                ->name('riwayat.index');
        });
    });
