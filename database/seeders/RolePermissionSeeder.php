<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard
            'view_dashboard_statistics',

            // Product Categories
            'view_product_categories',
            'create_product_categories',
            'edit_product_categories',
            'delete_product_categories',

            // Products
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',

            // Customers
            'view_customers',
            'create_customers',
            'edit_customers',
            'delete_customers',

            // Transactions
            'view_transactions',
            'create_transactions',

            // Refunds
            'view_refunds',
            'create_refunds',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $adminRole = Role::create(['name' => 'super-admin']);
        $adminRole->givePermissionTo(Permission::all());

        $cashierRole = Role::create(['name' => 'cashier']);
        $cashierRole->givePermissionTo([
            'view_dashboard_statistics',
            'view_product_categories',
            'view_products',
            'view_customers',
            'create_customers',
            'view_transactions',
            'create_transactions',
            'view_refunds',
            'create_refunds',
        ]);
    }
}
