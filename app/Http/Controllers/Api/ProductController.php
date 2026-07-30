<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        // Mengambil data produk, diurutkan dari yang terbaru, dengan paginasi 15 item per halaman
        $products = Product::latest()->paginate(15);

        return response()->json($products, 200);
    }

    public function store(Request $request)
    {
        // Memvalidasi input dari request pengguna
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'price'     => ['required', 'numeric', 'min:0'],
            'inventory' => ['required', 'integer', 'min:0'],
        ]);

        // Menyimpan data produk ke database
        $product = Product::create($validated);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'product' => $product
        ], 201);
    }

    public function show(Product $product)
    {
        return response()->json($product, 200);
    }
}
