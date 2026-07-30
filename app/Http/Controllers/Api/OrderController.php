<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('items.product')->latest()->paginate(15);

        return response()->json($orders, 200);
    }

    public function show(Order $order)
    {
        $order->load('items.product');

        return response()->json($order, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            // transaction akan otomatis melakukan commit jika sukses, dan rollback jika terjadi error/exception
            $order = DB::transaction(function () use ($validated) {
                $totalAmount = 0;
                $orderItemsData = [];

                // Urutkan item berdasarkan product_id untuk mencegah deadlock database.
                // Ini penting jika ada dua transaksi bersamaan yang mencoba mengunci produk yang sama dengan urutan berbeda.
                $items = collect($validated['items'])->sortBy('product_id')->values()->all();

                // Mengekstrak ID produk ke dalam array
                $productIds = array_column($items, 'product_id');

                // Mengambil dan mengunci (lock) secara eksklusif semua produk yang dibutuhkan pada transaksi ini
                $products = Product::whereIn('id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($items as $item) {
                    $product = $products->get($item['product_id']);

                    // Memvalidasi ketersediaan stok di bawah perlindungan row-lock database
                    if ($product->inventory < $item['quantity']) {
                        abort(400, "Stok tidak mencukupi untuk produk: {$product->name}");
                    }

                    // Mengurangi stok dan menyimpan pembaruan produk
                    $product->inventory -= $item['quantity'];
                    $product->save();

                    // Menghitung sub-total harga
                    $lineTotal = $product->price * $item['quantity'];
                    $totalAmount += $lineTotal;

                    // Menyiapkan data item pesanan untuk disimpan sekaligus (bulk insertion)
                    $orderItemsData[] = [
                        'product_id' => $product->id,
                        'quantity'   => $item['quantity'],
                        'price'      => $product->price,
                    ];
                }

                // Membuat data Pesanan (Order)
                $order = Order::create(['total' => $totalAmount]);

                // Menyimpan semua Item Pesanan sekaligus dengan fitur createMany
                $order->items()->createMany($orderItemsData);

                return $order->load('items');
            });

            return response()->json([
                'message' => 'Pesanan berhasil dibuat',
                'order'   => $order
            ], 201);
        } catch (\Exception $e) {
            // Menentukan kode status HTTP yang sesuai
            $statusCode = 500;
            if ($e instanceof HttpException) {
                $statusCode = $e->getStatusCode();
            }

            return response()->json([
                'error' => 'Gagal membuat pesanan',
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
