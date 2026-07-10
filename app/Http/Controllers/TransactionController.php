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
use App\Service\WhatsappService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Override;
use Spatie\Permission\Middleware\PermissionMiddleware;

class TransactionController extends Controller implements HasMiddleware
{
    #[Override]
    public static function middleware()
    {
        return [
            new Middleware(PermissionMiddleware::using('view_transactions'), only: ['index', 'show', 'options']),
            new Middleware(PermissionMiddleware::using('create_transactions'), only: ['store']),
            new Middleware(PermissionMiddleware::using('view_refunds'), only: ['refunds']),
            new Middleware(PermissionMiddleware::using('create_refunds'), only: ['refund']),
        ];
    }

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

        $data = $request->validated();

        try {
            DB::beginTransaction();

            // Tempat menampung total harga semua barang
            $subtotal = 0;

            // untuk menyimpan item-item transaksi sementara.
            $itemData = [];

            $itemForNotification = [];

            // seluruh inputan user yang ada di dalam items diambil satu per satu lalu disimpan sementara ke $item.
            foreach ($data['items'] as $item) {

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

                $itemForNotification[] = [
                    'name' => $product->name,
                    'quantity' => $item['quantity'],
                    'subtotal' => $itemSubtotal,
                ];
            }

            // calculate Tax (Asumming 11%)
            $tax = $subtotal * 0.11;
            $total = $subtotal + $tax;

            // Generate Code (TRX-TIMESTAMP-RANDOM)
            $code = 'TRX-'.date('His-dmy').'-'.rand(1000, 9999);

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
            foreach ($itemData as $item) {
                // Simpan detail barang yang dibeli ke tabel transaction_items
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);

                // dapet data stock karena tadi disimpan pada key model yang di atas.
                // Kurangi stock sebanyak (...) dengan quantity yang tadi udah diinputkan sama user (kasir).
                $item['model']->decrement('stock', $item['quantity']);
            }

            DB::commit();

            $transaction->load([
                'customer',
                'transactionItems.product',
            ]);

            if (! empty($data['send_notification']) && $transaction->customer?->phone) {

                $whatsAppService = new WhatsappService;

                $whatsAppService->sendTransactionReceipt(
                    $transaction->customer->phone,
                    [
                        'code' => $transaction->code,
                        'date' => $transaction->created_at->format('d/m/Y H:i'),
                        'customer_name' => $transaction->customer->name,
                        'items' => $itemForNotification,
                        'subtotal' => $transaction->subtotal,
                        'tax' => $transaction->tax,
                        'total' => $transaction->total,
                        'paid' => $request->paid,
                        'change' => $request->paid - $transaction->total,
                    ]
                );
            }

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

            // kalau status refund sama dengan refunded artinya udah pernah refund sebelumnya dan qty nya udah abis. lanjut membuat alasan kenapa refund
            if ($transaction->status === 'refunded') {
                throw new Exception(
                    'Transaction Already Fully Refunded'
                );
            }

            // Simpan alasan refund
            TransactionRefund::create([
                'transaction_id' => $transaction->id,
                'reason' => $request->reason,
            ]);

            foreach ($request->items as $refundItem) {

                $item = TransactionItem::with('product')
                    ->find($refundItem['transaction_item_id']);

                if (! $item) {
                    throw new Exception(
                        'Transaction Item Not Found'
                    );
                }

                if ($item->transaction_id != $transaction->id) {
                    throw new Exception(
                        'Transaction Item does not belong to this Transaction'
                    );
                }

                $availableRefund = $item->quantity - $item->refunded_quantity;

                if ($refundItem['quantity'] > $availableRefund) {
                    throw new Exception(
                        "Refund quantity exceeds purchased quantity for product {$item->product->name}"
                    );
                }

                // Kembalikan stock hanya jika dicentang
                if ($refundItem['return_to_stock']) {
                    $item->product->increment(
                        'stock',
                        $refundItem['quantity']
                    );
                }

                // Tambah qty refund
                $item->increment(
                    'refunded_quantity',
                    $refundItem['quantity']
                );
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

    public function refunds(GetTransactionRequest $request)
    {
        $transactions = Transaction::with([
            'customer',
            'transactionItems.product',
            'refund',
        ])
            ->whereIn('status', [
                'refunded',
                'partially_refunded',
            ])
            ->whereHas('refund')
            ->join('transaction_refunds', 'transactions.id', '=', 'transaction_refunds.transaction_id')
            ->orderByDesc('transaction_refunds.created_at')
            ->select('transactions.*')
            ->paginate($request->limit ?? 10);

        return ApiResponse::success(
            new PaginatedResource(
                $transactions,
                TransactionResource::class
            ),
            'Refund List'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = Transaction::with(['customer', 'transactionItems.product', 'refund'])->find($id);

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
