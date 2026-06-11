<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Http\Requests\GetCustomerRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\PaginatedResource;
use App\Models\Customer;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetCustomerRequest $request)
    {
        $customer = Customer::query()
            ->search($request->search)
            ->latest()
            ->paginate($request->limit ?? 10);

        // Kirim response sukses berisi daftar customer yang sudah dipaginasi. Untuk setiap customer di dalam daftar tersebut, format tampilannya menggunakan CustomerResource
        return ApiResponse::success(
            new PaginatedResource($customer, CustomerResource::class),
            'Customer List'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        $customer = Customer::orderBy('created_at', 'desc')->first();

        return ApiResponse::success(
            new CustomerResource($customer),
            'Customer Created Successfuly',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::find($id);

        if (! $customer) {
            return ApiResponse::error(
                'Customer Not Found',
                Response::HTTP_NOT_FOUND
            );
        }

        return ApiResponse::success(
            new CustomerResource($customer),
            'Customer Details',
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, string $id)
    {
        $customer = Customer::find($id);

        if (! $customer) {
            return ApiResponse::error(
                'Customer Not Found',
                Response::HTTP_NOT_FOUND
            );
        }

        $customer->update($request->validated());

        return ApiResponse::success(
            new CustomerResource($customer),
            'Customer Updated Successfuly',
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer = Customer::find($id);

        $customer->delete($id);

        return ApiResponse::success(
            new CustomerResource($customer),
            'Customer Deleted Successfuly',
        );
    }
}
