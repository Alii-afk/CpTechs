# CP Tech POS Migration Guide

## 🚀 Correct Migration Order

The migrations are now properly ordered to avoid foreign key constraint errors. Here's the correct sequence:

### 1. Core Laravel Tables
- `0001_01_01_000000_create_users_table.php`
- `0001_01_01_000001_create_cache_table.php`
- `0001_01_01_000002_create_jobs_table.php`

### 2. Permission System
- `2024_08_25_000001_create_user_roles_table.php`
- `2024_08_25_000002_create_permission_tabs_table.php`
- `2024_08_25_000003_create_permission_types_table.php`
- `2024_08_25_000004_create_tab_permissions_table.php`
- `2024_08_25_000005_create_user_roles_permissions_table.php`
- `2024_08_25_000006_add_user_role_id_to_users_table.php`

### 3. Core Business Tables
- `2025_08_25_101435_create_personal_access_tokens_table.php`
- `2025_08_25_144714_create_business_locations_table.php`
- `2025_08_25_145011_add_business_location_id_to_users_table.php`
- `2025_08_25_162155_enhance_users_table_add_registration_fields.php`
- `2025_08_25_162258_create_commission_agents_table.php`

### 4. Business Partners
- `2025_08_26_064931_create_suppliers_table.php`
- `2025_08_27_062811_remove_status_from_suppliers_table.php`
- `2025_08_27_063712_create_customers_table.php`

### 5. Product System
- `2025_08_27_100000_create_product_categories_table.php`
- `2025_08_27_090952_create_brands_table.php`
- `2025_08_27_091001_create_flavors_table.php`
- `2025_08_27_144052_create_units_table.php`
- `2025_08_27_140658_remove_image_and_sort_order_from_product_categories_table.php`

### 6. Main Application Tables
- `2025_08_28_202853_create_products_table_fixed.php`
- `2025_08_28_202947_create_purchases_table_fixed.php`
- `2025_08_28_202921_create_product_inventory_table_fixed.php`
- `2025_08_28_203015_create_purchase_audit_logs_table.php`

## 🛠️ How to Run Migrations

### Option 1: Use the Migration Script
```bash
cd /path/to/pos-backend
./migrate_fresh.sh
```

### Option 2: Manual Commands
```bash
cd /path/to/pos-backend

# Reset database (if needed)
php artisan migrate:reset --force

# Run all migrations
php artisan migrate --force

# Run seeders
php artisan db:seed --force
```

### Option 3: Fresh Installation
```bash
cd /path/to/pos-backend

# Fresh migration with seeders
php artisan migrate:fresh --seed --force
```

## ✅ What's Fixed

1. **Foreign Key Constraints**: All tables now have correct dependencies
2. **Migration Order**: Tables are created in the right sequence
3. **No Duplicate Migrations**: Removed redundant migration files
4. **Complete Schema**: All required fields and relationships are included

## 🎯 Key Dependencies

- **Products** → depends on: `product_categories`, `brands`, `flavors`, `units`, `users`
- **Purchases** → depends on: `suppliers`, `business_locations`, `users`
- **Product Inventory** → depends on: `products`, `purchases`, `suppliers`, `business_locations`
- **Purchase Audit Logs** → depends on: `purchases`, `users`

## 🚨 Troubleshooting

If you encounter any foreign key errors:

1. **Check Migration Status**: `php artisan migrate:status`
2. **Reset and Re-run**: `php artisan migrate:reset --force && php artisan migrate --force`
3. **Check Database**: Ensure no partial tables exist from failed migrations
4. **Clear Cache**: `php artisan config:clear && php artisan cache:clear`

## 🎉 Success Indicators

After successful migration, you should see:
- All tables created without errors
- Foreign key constraints working properly
- Seeders populated with initial data
- No migration errors in the console 