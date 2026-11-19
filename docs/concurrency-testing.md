# Panduan Pengujian Concurrency Checkout & Row Locking (MySQL InnoDB)

Dokumen ini menjelaskan arsitektur pencegahan *race condition* pada fitur checkout serta panduan teknis pengujian *concurrent checkout* menggunakan dua koneksi independen pada database MySQL.

---

## 1. Arsitektur Pencegahan Race Condition & Deadlock

Pada aplikasi e-commerce berskala produksi, dua pelanggan dapat menekan tombol checkout pada detik yang sama untuk produk yang stoknya tersisa sedikit. Tanpa mekanisme penguncian (*locking*) yang benar, kedua transaksi akan membaca sisa stok yang sama, keduanya berhasil, dan stok akhir menjadi minus (*overselling*).

Untuk mengatasi masalah tersebut, [`app/Services/OrderService.php`](../app/Services/OrderService.php) menerapkan standar penguncian pesimistik (*pessimistic locking*):

```php
return DB::transaction(function () use ($user) {
    // 1. Ambil cart item user
    $cartItems = CartItem::where('user_id', $user->id)->get();

    // 2. Kunci produk berdasarkan urutan ID ascending (Mencegah Deadlock)
    $productIds = $cartItems->pluck('product_id')->unique()->sort()->values()->all();

    $products = Product::whereIn('id', $productIds)
        ->orderBy('id', 'asc')
        ->lockForUpdate()
        ->get()
        ->keyBy('id');

    // 3. Validasi stok di dalam lock
    foreach ($cartItems as $item) {
        $product = $products->get($item->product_id);
        if (!$product || $product->stock < $item->quantity) {
            throw new InsufficientStockException(...);
        }
    }

    // 4. Buat Order & Order Items
    ...
    // 5. Potong stok secara atomik
    $product->decrement('stock', $itemData['quantity']);

    // 6. Kosongkan keranjang
    CartItem::where('user_id', $user->id)->delete();
});
```

### Karakteristik Utama:
1. **`lockForUpdate()` (Pessimistic Locking)**:
   Mengeksekusi query SQL `SELECT ... FOR UPDATE` pada MySQL InnoDB. Transaksi pertama yang membaca baris produk langsung memegang lock eksklusif. Transaksi kedua yang mencoba membaca baris yang sama akan **dipaksa mengantre (wait)** sampai transaksi pertama selesai (`COMMIT` atau `ROLLBACK`).
2. **Deterministic Locking Order (`orderBy('id', 'asc')`)**:
   Jika User A membeli Produk 1 & 2, sedangkan User B membeli Produk 2 & 1 secara bersamaan, tanpa pengurutan akan terjadi *Deadlock* (User A memegang lock Produk 1 menunggu Produk 2, User B memegang lock Produk 2 menunggu Produk 1). Dengan mengurutkan ID secara menaik, urutan lock selalu sama (1 $\rightarrow$ 2), sehingga risiko deadlock dieliminasi.
3. **Catatan Teknis SQLite vs MySQL**:
   Automated unit test Laravel secara default menggunakan SQLite in-memory untuk kecepatan dan isolasi. Namun, **SQLite tidak mendukung row-level lock (`SELECT ... FOR UPDATE`)** melainkan file-level lock. Oleh karena itu, verifikasi concurrency locking yang sesungguhnya **wajib diuji langsung pada database MySQL**.

---

## 2. Prosedur Pengujian Concurrency Nyata pada MySQL

### Skenario Uji:
- **Produk Target**: Produk ID `1` memiliki **stok = 5**.
- **User A**: Membeli kuantitas **4**.
- **User B**: Membeli kuantitas **3**.
- **Hasil yang Diharapkan**:
  - Hanya 1 user yang berhasil (HTTP 201 Created).
  - 1 user lainnya gagal karena stok tersisa tidak cukup (HTTP 409 Conflict).
  - Sisa stok akhir di database: `5 - 4 = 1` (atau `5 - 3 = 2`), **TIDAK BOLEH MINUS**.
  - Tidak ada data order parsial atau cart yang rusak.

---

### Langkah Pengujian Manual via Terminal / PowerShell

#### Langkah 1: Siapkan Data Uji
Login ke MySQL atau database client Anda, dan pastikan stok produk ID 1 bernilai 5:
```sql
UPDATE products SET stock = 5 WHERE id = 1;
```

#### Langkah 2: Dapatkan Token untuk Dua User Berbeda
Jalankan request login di terminal untuk User A dan User B:

**User A (`test@gmail.com`):**
```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@gmail.com","password":"123"}'
```
*Simpan token User A.*

**User B (`customer@example.com` atau registrasi user baru):**
```bash
curl -X POST http://127.0.0.1:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"User B","email":"userb@example.com","password":"password123"}'
```
*Simpan token User B.*

#### Langkah 3: Isi Keranjang Masing-Masing User
**User A memasukkan 4 item:**
```bash
curl -X POST http://127.0.0.1:8000/api/cart/items \
  -H "Authorization: Bearer <TOKEN_USER_A>" \
  -H "Content-Type: application/json" \
  -d '{"product_id":1,"quantity":4}'
```

**User B memasukkan 3 item:**
```bash
curl -X POST http://127.0.0.1:8000/api/cart/items \
  -H "Authorization: Bearer <TOKEN_USER_B>" \
  -H "Content-Type: application/json" \
  -d '{"product_id":1,"quantity":3}'
```

#### Langkah 4: Jalankan Checkout Bersamaan (Concurrent Execution)
Jalankan script PowerShell berikut untuk menembakkan dua request checkout secara paralel:

```powershell
$tokenA = "<TOKEN_USER_A>"
$tokenB = "<TOKEN_USER_B>"

$jobA = Start-Job -ScriptBlock {
    param($t)
    Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/orders" -Method Post -Headers @{ Authorization = "Bearer $t"; Accept = "application/json" }
} -ArgumentList $tokenA

$jobB = Start-Job -ScriptBlock {
    param($t)
    Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/orders" -Method Post -Headers @{ Authorization = "Bearer $t"; Accept = "application/json" }
} -ArgumentList $tokenB

Wait-Job $jobA, $jobB
Receive-Job $jobA
Receive-Job $jobB
```

#### Langkah 5: Analisis Hasil
1. Salah satu response akan mengembalikan:
   ```json
   {
     "success": true,
     "message": "Checkout berhasil, order berhasil dibuat",
     "data": { ... }
   }
   ```
2. Response lainnya akan mengembalikan:
   ```json
   {
     "success": false,
     "message": "Checkout gagal karena terdapat produk yang tidak tersedia atau stok tidak mencukupi.",
     "errors": [
       {
         "product_id": 1,
         "title": "Wireless Noise-Canceling Headphones",
         "available_stock": 1,
         "requested_quantity": 3
       }
     ]
   }
   ```
3. Periksa sisa stok di database:
   ```sql
   SELECT id, title, stock FROM products WHERE id = 1;
   ```
   Stok akan bernilai `1` (jika User A yang lebih dulu masuk lock) atau `2` (jika User B yang lebih dulu masuk lock). **Stok tidak pernah bernilai -2.**
