<?php

namespace App\Http\Controllers\Api\V1;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadProductImageRequest;
use App\Http\Resources\ProductsResource;
use App\Models\Products;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(UploadProductImageRequest $request, string $id)
    {
        $product = Products::find($id);
        if (! $product) {
            return ApiResponse::error(
                'Product Not Found',
                Response::HTTP_NOT_FOUND
            );
        }

        // Kalau kategori sudah punya gambar lama, hapus dulu.
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        // Upload gambar baru dan update kolom image.
        $path = $request->file('image')->store('products', 'public');
        $product->update(['image' => $path]);

        return ApiResponse::success(
            new ProductsResource($product),
            'Product Category Image Uploaded'
        );
    }

    public function destroy(string $id)
    {
        $product = Products::find($id);

        if (! $product) {
            return ApiResponse::error(
                'Product Not Found',
                Response::HTTP_NOT_FOUND
            );
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);

            $product->update([
                'image' => null,
            ]);
        }

        return ApiResponse::success(
            new ProductsResource($product),
            'Product Image Deleted'
        );
    }
}
