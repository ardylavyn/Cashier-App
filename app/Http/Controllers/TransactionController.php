<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Http\Requests\GetTransactionRequest;
use App\Http\Requests\RefundTransactionRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\TransactionResource;
use App\Models\Products;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionRefund;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetTransactionRequest $request)
    {
        // Selain transaksi, sekalian ambilin data customer dan produk yang ada di transaksi tersebut.
        // transactionItems itu nama relasi
        $transaction = Transaction::with(['customer', 'transactionItems.product'])
            ->search($request->search)
            ->latest()
            ->paginate($request->limit ?? 10);

        // Kirim response sukses berisi daftar produk yang sudah dipaginasi. Untuk setiap produk di dalam daftar tersebut, format tampilannya menggunakan ProductsResource
        return ApiResponse::success(
            new PaginatedResource($transaction, TransactionResource::class),
            'Transaction Pagination List'
        );
    }

    public function options(GetTransactionRequest $request)
    {
        $transaction = Transaction::query()
            ->with('customer')
            ->search($request->search)
            ->latest()
            ->get();

        return ApiResponse::success(
            TransactionResource::collection($transaction),
            'Transaction List'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request)
    {
        try {
            DB::beginTransaction();

            // Tempat menampung total harga semua barang
            $subtotal = 0;

            // untuk menyimpan item-item transaksi sementara.
            $itemData = [];

            // seluruh inputan user yang ada di dalam items diambil satu per satu lalu disimpan sementara ke $item.
            foreach ($request->items as $item) {

                // Ambil nilai dari key product_id yang ada di dalam variabel $item, yang awalnya berasal dari data request user yang sudah divalidasi oleh StoreTransactionRequest.
                // Misalnya $item['product_id'] -> 9 maka laravel menjalankan Products::find(9);
                // Hasil query nya seperti : 'id' => 9, 'name' => 'Indomie Goreng', 'price' => 3000, 'stock' => 100
                // Hasil itu lah yang dimasukkan ke $product
                $product = Products::find($item['product_id']);

                // cek apakah product ada atau ngga
                if (! $product) {
                    throw new Exception("Product with ID {$item['product_id']} not found");
                }

                // cek apakah stock masih atau ngga
                // makanya ketika tadi ambil data product, sekarang bisa ambil kolom stock buat ngecek ketersediaannya.
                if ($product->stock < $item['quantity']) {
                    throw new Exception("Insufficient stock for product {$product->name}");
                }

                // Hitung Subtotal Per Barang
                $itemSubtotal = $product->price * $item['quantity'];

                // Tambah ke Subtotal Besar
                $subtotal += $itemSubtotal;

                // Karena ini masih di dalam loop, dan ingin menghitung biaya pajaknya dulu, maka data tiap barang ditaruh sementara ke $itemData[]
                $itemData[] = [
                    'product_id' => $product->id,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $itemSubtotal,
                    // stock dan name yang ada di $product akan disimpan di key model utk suatu saat dibutuhkan kembali
                    'model' => $product,
                ];
            }

            // calculate Tax (Asumming 11%)
            $tax = $subtotal * 0.11;
            $total = $subtotal + $tax;

            // Generate Code (TRX-TIMESTAMP-RANDOM)
            $code = 'TRX - '.time().'-'.rand(1000, 9999);

            // Setelah berhasil insert ke tabel transaction, database membuat ID baru untuk transaksi tersebut, lalu Laravel mengembalikan data transaksi yang baru dibuat (termasuk ID-nya) ke variabel $transaction. ID tersebut kemudian digunakan untuk membuat transaction_items.
            $transaction = Transaction::create([
                'code' => $code,
                'customer_id' => $request->customer_id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'status' => 'completed',
            ]);

            // Ambil setiap barang yang ada di keranjang sementara (($itemData) yang di atas tadi) satu per satu lalu simpan ke variabel $data
            foreach ($itemData as $data) {
                // Simpan detail barang yang dibeli ke tabel transaction_items
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $data['product_id'],
                    'price' => $data['price'],
                    'quantity' => $data['quantity'],
                    'subtotal' => $data['subtotal'],
                ]);

                // dapet data stock karena tadi disimpan pada key model yang di atas.
                // Kurangi stock sebanyak (...) dengan quantity yang tadi udah diinputkan sama user (kasir).
                $data['model']->decrement('stock', $data['quantity']);
            }

            DB::commit();

            return ApiResponse::success(
                new TransactionResource($transaction->load(['customer', 'transactionItems.product'])),
                'Transaction Created Successfuly',
                Response::HTTP_CREATED,
            );
        } catch (Exception $e) {
            DB::rollback();

            return ApiResponse::error(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function refund(RefundTransactionRequest $request, string $id)
    {
        try {

            DB::beginTransaction();

            // Cari data transaksi berdasarkan id yang dikirim pada URL. Selain data transaksi, sekalian ambil relasi transactionItems dan dari setiap transactionItem sekalian ambil product-nya. Hasilnya disimpan ke dalam variabel $transaction.
            $transaction = Transaction::with('transactionItems.product')->find($id);

            // kalau transaction gaada maka tampilkan pesan Transaction Not Found, kalau ada lanjut pemeriksaan status refund
            if (! $transaction) {
                return ApiResponse::error(
                    'Transaction Not Found',
                    Response::HTTP_NOT_FOUND
                );
            }

            // kalau status refund tidak sama dengan completed artinya udah pernah refund sebelumnya, kalau belum lanjut membuat alasan kenapa refund
            if ($transaction->status !== 'completed') {
                return ApiResponse::error(
                    'Transaction Already Refunded',
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Simpan alasan refund
            TransactionRefund::create([
                'transaction_id' => $transaction->id,
                'reason' => $request->reason,
            ]);

            foreach ($request->items as $refundItem) {
                // awalnya cari transaction_item_id di $refundItem yang diambil dari inputan request user (RefundTransactionRequest), kalau udah ketemu terus karena ada with productnya maka product juga ikut diambil.
                $item = TransactionItem::with('product')
                    ->find($refundItem['transaction_item_id']);

                if (! $item) {
                    throw new Exception(
                        'Transaction Item Not Found'
                    );
                }

                // Pastikan item milik transaksi yang direfund
                if ($item->transaction_id != $transaction->id) {
                    throw new Exception(
                        'Transaction Item does not belong to this Transaction'
                    );
                }

                $availableRefund = $item->quantity - $item->refunded_quantity;

                // Tidak boleh refund melebihi qty yang dibeli
                if ($refundItem['quantity'] > $availableRefund) {
                    throw new Exception(
                        "Refund quantity exceeds purchased quantity for product {$item->product->name}"
                    );
                }

                // Kembalikan stock
                $item->product->increment('stock', $refundItem['quantity']);

                // Tambah refunded_quantity
                $item->increment('refunded_quantity', $refundItem['quantity']);
            }

            // Reload data terbaru
            $transaction->refresh();

            $fullyRefunded = true;

            foreach ($transaction->transactionItems as $item) {

                if ($item->quantity != $item->refunded_quantity) {
                    $fullyRefunded = false;
                    break;
                }
            }

            $transaction->update(['status' => $fullyRefunded ? 'refunded' : 'partially_refunded']);

            DB::commit();

            return ApiResponse::success(
                new TransactionResource(
                    $transaction->fresh()->load([
                        'customer',
                        'transactionItems.product',
                        'refund',
                    ])
                ),
                'Transaction Refunded Successfully'
            );

        } catch (Exception $e) {

            DB::rollBack();

            return ApiResponse::error(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = Transaction::with(['customer', 'transactionItems.product'])->find($id);

        if (! $transaction) {
            return ApiResponse::error(
                'Transaction Not Found',
                Response::HTTP_NOT_FOUND
            );
        }

        return ApiResponse::success(
            new TransactionResource($transaction),
            'Transaction Details',
        );
    }
}
