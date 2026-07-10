<?php

namespace App\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected string $apiUrl = 'https://api.fonnte.com/send';

    protected string $token = '';

    public function __construct()
    {

        $this->token = config('services.fonnte.token');

        Log::info('Fonnte Token', [
            'token' => $this->token,
        ]);

    }

    public function send(string $target, string $message, string $countryCode = '62')
    {
        try {

            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post($this->apiUrl, [
                'target' => $target,
                'message' => $message,
                'countryCode' => $countryCode,
            ]);

            Log::info('Fonnte Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'json' => $response->json(),
            ]);

            return [
                'success' => true,
                'data' => $response->json(),
            ];

        } catch (\Exception $e) {

            Log::error($e->getMessage());

            throw $e;
        }
    }

    public function sendTransactionReceipt(string $phoneNumber, array $transactionData)
    {
        $message = $this->formatTransactionReceipt($transactionData);

        return $this->send($phoneNumber, $message);
    }

    public function formatTransactionReceipt(array $data): string
    {
        $storeName = config('app.name');
        $items = '';

        foreach ($data['items'] as $item) {
            $items .= "- {$item['name']} x{$item['quantity']} = Rp ".
                number_format($item['subtotal'], 0, ',', '.')."\n";
        }

        return <<<MESSAGE
🧾 *STRUK PEMBELIAN*

{$storeName}

🧾 *Kode Transaksi:* {$data['code']}
📅 *Tanggal:* {$data['date']}
👤 *Pelanggan:* {$data['customer_name']}

*Daftar Belanja:*
{$items}
----------------------------

💰 *Subtotal:* Rp {$data['subtotal']}
🧾 *Pajak:* Rp {$data['tax']}
----------------------------
💵 *TOTAL:* Rp {$data['total']}

💳 *Bayar:* Rp {$data['paid']}
💸 *Kembalian:* Rp {$data['change']}

🙏 Terima kasih atas pembelian Anda.
MESSAGE;
    }
}
