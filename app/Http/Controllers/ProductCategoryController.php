<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Http\Requests\GetProductCategoryRequest;
use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Override;
use Spatie\Permission\Middleware\PermissionMiddleware;

class ProductCategoryController extends Controller implements HasMiddleware
{
    #[Override]
    public static function middleware()
    {
        return [
            new Middleware(PermissionMiddleware::using('view_product_categories'), only: ['index', 'show', 'options']),
            new Middleware(PermissionMiddleware::using('create_product_categories'), only: ['store']),
            new Middleware(PermissionMiddleware::using('edit_product_categories'), only: ['update']),
            new Middleware(PermissionMiddleware::using('delete_product_categories'), only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(GetProductCategoryRequest $request)
    {
        $categories = ProductCategory::search($request->search)->latest()->paginate($request->limit ?? 10);

        return ApiResponse::success(
            new PaginatedResource($categories, ProductCategoryResource::class),
            'Product Category List'
        );
    }

    public function options(GetProductCategoryRequest $request)
    {
        $categories = ProductCategory::select('id', 'name')->search($request->search)->orderBy('name')->get();

        return ApiResponse::success(
            ProductCategoryResource::collection($categories),
            'Product Category List'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductCategoryRequest $request)
    {
        $productCategory = ProductCategory::create($request->validated());

        return ApiResponse::success(
            new ProductCategoryResource($productCategory),
            'Product Category Created Successfuly',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = ProductCategory::find($id);

        if (! $category) {
            return ApiResponse::error(
                'Product Category Not Found',
                Response::HTTP_NOT_FOUND
            );
        }

        return ApiResponse::success(
            new ProductCategoryResource($category),
            'Product Category Details',
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductCategoryRequest $request, string $id)
    {
        $category = ProductCategory::find($id);

        if (! $category) {
            return ApiResponse::error(
                'Product Category Not Found',
                Response::HTTP_NOT_FOUND
            );
        }

        $category->update($request->validated());

        return ApiResponse::success(
            new ProductCategoryResource($category),
            'Product Category Updated Successfuly',
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = ProductCategory::find($id);

        $category->delete();

        return ApiResponse::success(
            new ProductCategoryResource($category),
            'Product Category Deleted Successfuly',
        );
    }
}
