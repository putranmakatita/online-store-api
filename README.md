# Online Store API

RESTful API sederhana untuk sistem Online Store yang dibangun menggunakan **Laravel 13**.

Project ini dibuat sebagai implementasi backend API dengan fitur utama:

* Product Management
* Order Management
* Inventory Management
* Flash Sale / Race Condition Handling
* Database Transaction
* API Documentation menggunakan Postman

---

# Tech Stack

* Laravel 13
* PHP >= 8.4
* MySQL
* Eloquent ORM
* REST API
* Postman

---

# Features

## Products

* Melihat seluruh produk
* Melihat detail produk
* Menambahkan produk baru

## Orders

* Membuat pesanan
* Melihat daftar pesanan
* Melihat detail pesanan

## Inventory

* Pengurangan stok otomatis ketika order berhasil dibuat
* Validasi stok
* Pencegahan stok minus

## Concurrency

API menggunakan:

* Database Transaction
* Row Lock (`lockForUpdate()`)
* Deadlock Prevention (sorting product_id)

untuk memastikan stok tetap konsisten ketika banyak user melakukan pembelian secara bersamaan (Flash Sale).

---

# Requirements

* PHP >= 8.4
* Composer
* MySQL
* Git

Disarankan menggunakan:

* Laragon
* MySQL 8+

---

# Installation

Clone repository

```bash
git clone <repository-url>
```

Masuk ke folder project

```bash
cd online-store-api
```

Install dependency

```bash
composer install
```

Copy file environment

```bash
cp .env.example .env
```

atau pada Windows

```bash
copy .env.example .env
```

Generate application key

```bash
php artisan key:generate
```

---

# Database Configuration

Edit file `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=online_store
DB_USERNAME=root
DB_PASSWORD=
```

Buat database baru

```sql
CREATE DATABASE online_store;
```

---

# Migration

Jalankan migration

```bash
php artisan migrate
```

---

# Seeder

Generate dummy products

```bash
php artisan db:seed
```

atau

```bash
php artisan db:seed --class=ProductSeeder
```

---

# Running Application

Menggunakan Laravel

```bash
php artisan serve
```

atau menggunakan Laragon

```
http://online-store-api.test
```

---

# API Documentation

Dokumentasi API lengkap tersedia di:

https://documenter.getpostman.com/view/21092059/2sBY4SNKdE

Dokumentasi mencakup:

* Endpoint
* Request Body
* Response
* Error Response
* Collection
* Example Request
* Example Response

---

# API Endpoints

## Products

### Get Products

```
GET /api/v1/products
```

### Get Product Detail

```
GET /api/v1/products/{id}
```

### Create Product

```
POST /api/v1/products
```

---

## Orders

### Get Orders

```
GET /api/v1/orders
```

### Get Order Detail

```
GET /api/v1/orders/{id}
```

### Create Order

```
POST /api/v1/orders
```

Contoh Request

```json
{
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        },
        {
            "product_id": 3,
            "quantity": 1
        }
    ]
}
```

---

# Flash Sale Test

Project ini menyediakan command untuk mensimulasikan ratusan user melakukan pembelian secara bersamaan.

Jalankan

```bash
php artisan test:flash-sale
```

Command tersebut akan:

* Mengirim ratusan request secara concurrent
* Menguji race condition
* Memastikan stok tidak menjadi negatif
* Menampilkan jumlah request berhasil dan gagal

Contoh output

```
Request Berhasil : 100
Request Gagal    : 100
Sisa Inventory   : 0
```

---

# Reset Flash Sale

Untuk menghapus seluruh order dan mengembalikan stok produk:

```bash
php artisan flash-sale:reset
```

---

# Project Structure

```
app/
 ├── Http/
 │    └── Controllers/
 │
 ├── Models/
 │
 └── Console/
      └── Commands/

database/
 ├── factories/
 ├── migrations/
 └── seeders/

routes/
 └── api.php
```

---

# Business Rules

## Product

* Inventory tidak boleh negatif.
* Harga produk disimpan sebagai harga satuan.

## Order

* Minimal memiliki satu item.
* Quantity minimal 1.
* Product harus tersedia.
* Inventory akan dikurangi setelah order berhasil dibuat.
* Total order merupakan jumlah seluruh subtotal item.

---

# Concurrency Strategy

Untuk menghindari race condition ketika Flash Sale:

* Menggunakan database transaction.
* Menggunakan `SELECT ... FOR UPDATE` (`lockForUpdate()`).
* Mengurutkan product berdasarkan ID untuk mengurangi potensi deadlock.
* Seluruh proses checkout dilakukan dalam satu transaksi database.

Dengan pendekatan tersebut, stok tetap konsisten walaupun terdapat banyak request yang berjalan secara bersamaan.

---

# Future Improvements

Beberapa pengembangan yang dapat dilakukan:

* Authentication (Laravel Sanctum/JWT)
* Update Product
* Delete Product
* Update Order Status
* Payment Gateway
* Unit Test
* Feature Test
* Docker Support
* Swagger / OpenAPI
* CI/CD Pipeline
* Redis Queue
* Redis Cache
* Pagination & Filtering
* Product Search
* Order Cancellation
* Inventory History

---

# Author

Developed by **Putra Nurhuda Makatita**
