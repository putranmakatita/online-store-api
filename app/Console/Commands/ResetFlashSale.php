<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\DB;


#[Signature('app:reset-flash-sale')]
#[Description('Command description')]
class ResetFlashSale extends Command
{

    protected $signature = 'flash-sale:reset';
    protected $description = 'Reset orders dan inventory untuk pengujian flash sale';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::transaction(function () {

            DB::table('order_items')->delete();
            DB::table('orders')->delete();

            Product::where('id', 1)->update([
                'inventory' => 100,
            ]);
        });

        $this->info('Flash Sale berhasil di-reset');
        $this->line('Orders dihapus');
        $this->line('Order Items dihapus');
        $this->line('Inventory seluruh produk dikembalikan menjadi 100');
    }
}
