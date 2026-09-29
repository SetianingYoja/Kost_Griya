<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Merchant ID
    |--------------------------------------------------------------------------
    |
    | Merchant ID dari akun Midtrans (Sandbox / Production).
    |
    */
    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Client Key
    |--------------------------------------------------------------------------
    |
    | Client Key yang digunakan oleh Snap.js di sisi frontend.
    |
    */
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Server Key
    |--------------------------------------------------------------------------
    |
    | Server Key rahasia untuk otentikasi API backend dan verifikasi webhook.
    |
    */
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Environment Mode
    |--------------------------------------------------------------------------
    |
    | Status mode produksi. Set false untuk mode Sandbox (pengujian)
    | dan true jika aplikasi siap berjalan di lingkungan Production.
    |
    */
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | Fitur Tambahan SDK Midtrans
    |--------------------------------------------------------------------------
    |
    | is_sanitized: Otomatis membersihkan karakter khusus pada input request.
    | is_3ds: Mengaktifkan 3D Secure untuk transaksi kartu kredit.
    |
    */
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => env('MIDTRANS_IS_3DS', true),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Notification URL Override (Opsional)
    |--------------------------------------------------------------------------
    |
    | Jika diisi, setiap request Snap Token akan menyertakan header
    | X-Override-Notification sehingga Midtrans mengirim Payment Notification
    | ke URL ini, bukan ke URL yang terdaftar di dashboard Midtrans.
    |
    | Gunakan saat testing lokal via ngrok. Kosongkan di Production.
    |
    */
    'notification_url' => env('MIDTRANS_NOTIFICATION_URL', ''),

];
