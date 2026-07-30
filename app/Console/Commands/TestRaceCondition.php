<?php

namespace App\Console\Commands;


use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Product;

#[Signature('app:test-race-condition')]
#[Description('Command description')]
class TestRaceCondition extends Command
{

    protected $signature = 'test:flash-sale';
    protected $description = 'Menguji race condition pada endpoint pembuatan pesanan saat flash sale';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Menyiapkan data produk untuk Flash Sale...');

        $product = Product::find(1);

        if (!$product) {
            $this->error('Produk dengan ID 1 tidak ditemukan di database.');
            return 1;
        }
        $initialStock = $product->inventory;

        $this->info("Produk flash sale: {$product->name} dengan stok {$product->inventory}");

        $endpoint = url('/api/v1/orders');

        $concurrentRequests = 200; // Mensimulasikan 200 user menekan tombol Beli bersamaan
        $this->info("Endpoint: {$endpoint} dengan request: {$concurrentRequests}");

        $this->info("Memulai pengujian: Mengirim {$concurrentRequests} request secara bersamaan...");

        // Mengirim request secara asinkron (concurrent) menggunakan Http::pool Laravel
        $responses = Http::pool(function (Pool $pool) use ($endpoint, $product, $concurrentRequests) {
            $requests = [];

            for ($i = 0; $i < $concurrentRequests; $i++) {
                $requests[] = $pool->post($endpoint, [
                    'items' => [
                        [
                            'product_id' => $product->id,
                            'quantity'   => 1 // Masing-masing user mencoba membeli 1 barang
                        ]
                    ]
                ]);
            }

            return $requests;
        });

        // Menganalisis hasil dari 200 request tersebut
        $successCount = 0;
        $failCount = 0;

        foreach ($responses as $index => $response) {
            if ($response instanceof \Exception) {
                $failCount++;
                continue;
            }

            if ($response->successful()) {
                $successCount++;
            } else {
                $failCount++;

                if ($failCount === 1) {
                    $this->error("Status: " . $response->status());
                    $this->line($response->body());
                }
            }
        }

        // Mengecek sisa stok di database
        $product->refresh();

        $this->newLine();
        $this->info('Hasil Pengujian: ');
        $this->line("Request Berhasil (Order dibuat): {$successCount}");
        $this->line("Request Gagal (Stok habis/Error): {$failCount}");
        $this->line("Sisa Stok di Database: {$product->inventory}");
        $this->newLine();

        // Verifikasi apakah sistem berhasil menangani race condition
        if (
            $successCount + $failCount === $concurrentRequests &&
            $successCount === min($initialStock, $concurrentRequests) &&
            $product->inventory === max(0, $initialStock - $successCount)
        ) {
            $this->info('(SUKSES) Race condition berhasil ditangani.');
            $this->info('Tidak terjadi inventory minus.');
            $this->info('Jumlah order sukses sesuai dengan stok yang tersedia.');
        } elseif ($product->inventory < 0) {
            $this->error('(GAGAL) Inventory menjadi minus.');
        } else {
            $this->warn('(PERINGATAN) Hasil tidak sesuai ekspektasi.');
        }
    }
}
