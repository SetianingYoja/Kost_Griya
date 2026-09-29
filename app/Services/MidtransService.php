<?php

namespace App\Services;

use Exception;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        $this->configureMidtrans();
    }

    /**
     * Konfigurasi SDK Midtrans menggunakan nilai dari config/midtrans.php
     */
    protected function configureMidtrans(): void
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = config('midtrans.is_sanitized', true);
        Config::$is3ds = config('midtrans.is_3ds', true);

        // Override notification URL per transaksi menggunakan fitur resmi SDK.
        // Midtrans akan mengirim Payment Notification ke URL ini via header X-Override-Notification.
        // Kosongkan MIDTRANS_NOTIFICATION_URL di .env untuk mode Production.
        $notifUrl = config('midtrans.notification_url');
        Config::$overrideNotifUrl = $notifUrl ?: null;
    }

    /**
     * Membuat Snap Token transaksi Midtrans khusus metode pembayaran QRIS.
     *
     * @param  string  $orderId  Unique order ID untuk transaksi
     * @param  int|float  $grossAmount  Nominal total transaksi (dalam Rupiah)
     * @param  array  $customerDetails  Detail data diri penghuni/pelanggan (opsional)
     * @param  array  $itemDetails  Detail rincian item/tagihan (opsional)
     * @return string Snap Token dari Midtrans
     *
     * @throws Exception
     */
    public function createSnapToken(
        string $orderId,
        int|float $grossAmount,
        array $customerDetails = [],
        array $itemDetails = []
    ): string {
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) round($grossAmount),
            ],
            'enabled_payments' => ['other_qris'],
        ];

        if (! empty($customerDetails)) {
            $params['customer_details'] = $customerDetails;
        }

        if (! empty($itemDetails)) {
            $params['item_details'] = $itemDetails;
        }

        return Snap::getSnapToken($params);
    }

    /**
     * Verifikasi signature key dari webhook Payment Notification Midtrans.
     * Formula: SHA512(order_id + status_code + gross_amount + server_key)
     */
    public function isValidSignature(
        string $orderId,
        string $statusCode,
        string $grossAmount,
        string $inputSignature
    ): bool {
        $serverKey = config('midtrans.server_key');
        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expectedSignature, $inputSignature);
    }
}
