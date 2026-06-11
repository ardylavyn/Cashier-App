<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Http\Requests\GetProductsRequest;
use App\Http\Requests\StoreProductsRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\ProductsResource;
use App\Models\Products;
use Illuminate\Http\Response;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetProductsRequest $request)
    {
        $products = Products::query()
        // pakai with biar yang diresource ikut tampil
            ->with('productCategory')
            ->search($request->search)
            ->byCategory($request->byCategory)
            ->latest()
            ->paginate($request->limit ?? 10);

        // Kirim response sukses berisi daftar produk yang sudah dipaginasi. Untuk setiap produk di dalam daftar tersebut, format tampilannya menggunakan ProductsResource
        return ApiResponse::success(
            new PaginatedResource($products, ProductsResource::class),
            'Product List'
        );
    }

    public function options(GetProductsRequest $request)
    {
        $product = Products::query()
            ->with('productCategory')
            ->search($request->search)
            ->byCategory($request->byCategory)
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            ProductsResource::collection($product),
            'Product List'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductsRequest $request)
    {
        $products = Products::create($request->validated());

        $products = Products::orderBy('created_at', 'desc')->first();

        return ApiResponse::success(
            new ProductsResource($products->load('productCategory')),
            'Product Created Successfuly',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Products::find($id);

        if (! $product) {
            return ApiResponse::error(
                'Product Not Found',
                Response::HTTP_NOT_FOUND
            );
        }

        return ApiResponse::success(
            new ProductsResource($product->load('productCategory')),
            'Product Details',
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $product = Products::find($id);

        if (! $product) {
            return ApiResponse::error(
                'Product Not Found',
                Response::HTTP_NOT_FOUND
            );
        }

        $product->update($request->validated());

        return ApiResponse::success(
            new ProductsResource($product->load('productCategory')),
            'Product Updated Successfuly',
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Products::find($id);

        $product->delete();

        return ApiResponse::success(
            new ProductsResource($product),
            'Product Deleted Successfuly',
        );
    }
}
