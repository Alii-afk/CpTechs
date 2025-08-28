<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserRole;
use App\Models\PermissionType;
use App\Models\PermissionTab;
use App\Models\TabPermission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permission types
        $permissionTypes = [
            ['name' => 'View', 'slug' => 'view'],
            ['name' => 'Add', 'slug' => 'add'],
            ['name' => 'Update', 'slug' => 'update'],
            ['name' => 'Delete', 'slug' => 'delete'],
        ];

        foreach ($permissionTypes as $type) {
            PermissionType::create($type);
        }

        // Create permission tabs with their specific permissions
        $permissionTabs = [
            // Tabs with all 4 permissions (View, Add, Update, Delete)
            ['tab_name' => 'User', 'tab_slug' => 'user'],
            ['tab_name' => 'Roles', 'tab_slug' => 'roles'],
            ['tab_name' => 'Commission Agents', 'tab_slug' => 'commission_agents'],
            ['tab_name' => 'Supplier', 'tab_slug' => 'supplier'],
            ['tab_name' => 'Customer', 'tab_slug' => 'customer'],
            ['tab_name' => 'Add Product', 'tab_slug' => 'add_product'],
            ['tab_name' => 'Update Product Price and Quantity', 'tab_slug' => 'update_product_price_quantity'],
            ['tab_name' => 'Product Category', 'tab_slug' => 'product_category'],
            ['tab_name' => 'Brand', 'tab_slug' => 'brand'],
            ['tab_name' => 'Flavour', 'tab_slug' => 'flavour'],
            ['tab_name' => 'Units', 'tab_slug' => 'units'],
            ['tab_name' => 'Stock Transfer', 'tab_slug' => 'stock_transfer'],
            ['tab_name' => 'Stock Damage', 'tab_slug' => 'stock_damage'],
            ['tab_name' => 'Purchase', 'tab_slug' => 'purchase'],
            ['tab_name' => 'Shipments', 'tab_slug' => 'shipments'],
            ['tab_name' => 'Sale', 'tab_slug' => 'sale'],
            ['tab_name' => 'Sale Return', 'tab_slug' => 'sale_return'],
            ['tab_name' => 'Expense', 'tab_slug' => 'expense'],
            ['tab_name' => 'Expense Category', 'tab_slug' => 'expense_category'],
            ['tab_name' => 'Salary', 'tab_slug' => 'salary'],
            ['tab_name' => 'Accounts', 'tab_slug' => 'accounts'],
            ['tab_name' => 'Cash In Hand', 'tab_slug' => 'cash_in_hand'],
            ['tab_name' => 'Events', 'tab_slug' => 'events'],
            ['tab_name' => 'Social Account', 'tab_slug' => 'social_account'],
            ['tab_name' => 'Influencer', 'tab_slug' => 'influencer'],
            ['tab_name' => 'Shop', 'tab_slug' => 'shop'],
            ['tab_name' => 'Warehouse', 'tab_slug' => 'warehouse'],
            ['tab_name' => 'Business Location', 'tab_slug' => 'business_location'],
            ['tab_name' => 'Leads', 'tab_slug' => 'leads'],
            ['tab_name' => 'Chat', 'tab_slug' => 'chat'],
            ['tab_name' => 'Notifications', 'tab_slug' => 'notifications'],
            
            // Tabs with limited permissions
            ['tab_name' => 'Customer Import', 'tab_slug' => 'customer_import'], // Only ADD
            ['tab_name' => 'Balance Sheet', 'tab_slug' => 'balance_sheet'], // Only VIEW
            ['tab_name' => 'Trial Balance', 'tab_slug' => 'trial_balance'], // Only VIEW
            ['tab_name' => 'Website Query', 'tab_slug' => 'website_query'], // Only VIEW, UPDATE, DELETE
        ];

        foreach ($permissionTabs as $tab) {
            PermissionTab::create($tab);
        }

        // Get all tabs and permission types
        $tabs = PermissionTab::all();
        $types = PermissionType::all();

        // Create tab permissions based on specific requirements
        foreach ($tabs as $tab) {
            switch ($tab->tab_slug) {
                case 'customer_import':
                    // Only ADD permission
                    $addType = $types->where('slug', 'add')->first();
                    TabPermission::create([
                        'tab_id' => $tab->id,
                        'permission_type_id' => $addType->id
                    ]);
                    break;
                    
                case 'balance_sheet':
                case 'trial_balance':
                    // Only VIEW permission
                    $viewType = $types->where('slug', 'view')->first();
                    TabPermission::create([
                        'tab_id' => $tab->id,
                        'permission_type_id' => $viewType->id
                    ]);
                    break;
                    
                case 'website_query':
                    // Only VIEW, UPDATE, DELETE permissions
                    $allowedTypes = $types->whereIn('slug', ['view', 'update', 'delete']);
                    foreach ($allowedTypes as $type) {
                        TabPermission::create([
                            'tab_id' => $tab->id,
                            'permission_type_id' => $type->id
                        ]);
                    }
                    break;
                    
                default:
                    // All 4 permissions (View, Add, Update, Delete)
            foreach ($types as $type) {
                TabPermission::create([
                    'tab_id' => $tab->id,
                    'permission_type_id' => $type->id
                ]);
                    }
                    break;
            }
        }

        // Create user roles
        $adminRole = UserRole::create([
            'name' => 'Administrator',
            'status' => 'active'
        ]);

        $managerRole = UserRole::create([
            'name' => 'Manager',
            'status' => 'active'
        ]);

        $cashierRole = UserRole::create([
            'name' => 'Cashier',
            'status' => 'active'
        ]);

        // Assign all permissions to admin
        $adminRole->tabPermissions()->attach(TabPermission::all());

        // Assign limited permissions to manager (no user management)
        $managerPermissions = TabPermission::whereHas('permissionTab', function($query) {
            $query->whereNotIn('tab_slug', ['user', 'roles']);
        })->get();
        $managerRole->tabPermissions()->attach($managerPermissions);

        // Assign basic permissions to cashier (view and add only for most tabs)
        $cashierPermissions = TabPermission::where(function($query) {
            $query->whereHas('permissionType', function($q) {
                $q->whereIn('slug', ['view', 'add']);
            });
        })->whereHas('permissionTab', function($query) {
            $query->whereIn('tab_slug', ['sale', 'customer', 'add_product', 'purchase', 'expense']);
        })->get();
        $cashierRole->tabPermissions()->attach($cashierPermissions);

        // Create a default admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'user_role_id' => $adminRole->id
        ]);
    }
} 