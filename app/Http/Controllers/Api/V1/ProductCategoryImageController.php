<?php

namespace App\Http\Controllers\Api\V1;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadProductCategoryImageRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Override;
use Spatie\Permission\Middleware\PermissionMiddleware;

class ProductCategoryImageController extends Controller implements HasMiddleware
{
    #[Override]
    public static function middleware()
    {
        return [
            new Middleware(PermissionMiddleware::using('edit_product_categories')),
        ];
    }

    public function store(UploadProductCategoryImageRequest $request, string $id)
    {
        $category = ProductCategory::find($id);
        if (! $category) {
            return ApiResponse::error(
                'Product Category Not Found',
                Response::HTTP_NOT_FOUND
            );
        }

        // Kalau kategori sudah punya gambar lama, hapus dulu.
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        // Upload gambar baru dan update kolom image.
        $path = $request->file('image')->store('product_categories', 'public');
        $category->update(['image' => $path]);

        return ApiResponse::success(
            new ProductCategoryResource($category),
            'Product Category Image Uploaded'
        );
    }

    public function destroy(string $id)
    {
        $category = ProductCategory::find($id);

        if (! $category) {
            return ApiResponse::error(
                'Product Category Not Found',
                Response::HTTP_NOT_FOUND
            );
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);

            $category->update([
                'image' => null,
            ]);
        }

        return ApiResponse::success(
            new ProductCategoryResource($category),
            'Product Category Image Deleted'
        );
    }
}
