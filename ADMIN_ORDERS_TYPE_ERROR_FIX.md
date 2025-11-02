# Admin Orders Type Error Fix

## Error Description
```
Fatal error: Uncaught TypeError: Unsupported operand types: string - int 
in C:\xampp\htdocs\app\views\admin\orders.php:359
```

## Root Cause
The error occurred because PHP was trying to perform arithmetic operations (subtraction, multiplication) between variables that were of different types:
- `$currentPage` or `$total` was coming through as a string instead of an integer
- When doing `($currentPage - 1) * $limit`, PHP couldn't subtract an integer from a string

## Solution

### 1. Fixed in `app/views/admin/orders.php`
**Before:**
```php
$startOrder = (($currentPage - 1) * $limit) + 1;
$endOrder = min($currentPage * $limit, $total ?? 0);
$totalOrders = $total ?? 0;
```

**After:**
```php
// Ensure all variables are properly typed (integers)
$limit = 20;
$currentPage = isset($currentPage) ? (int)$currentPage : 1;
$totalPages = isset($totalPages) ? (int)$totalPages : 1;
$total = isset($total) ? (int)$total : 0;

$startOrder = (($currentPage - 1) * $limit) + 1;
$endOrder = min($currentPage * $limit, $total);
$totalOrders = $total;
```

### 2. Fixed in `app/controllers/AdminController.php`

**Before:**
```php
$total = $stmt->fetch()['total'];
$totalPages = ceil($total / $limit);
```

**After:**
```php
$countResult = $stmt->fetch();
$total = (int)($countResult['total'] ?? 0); // Ensure integer type
$totalPages = $total > 0 ? (int)ceil($total / $limit) : 1; // Ensure integer type and minimum 1
```

**Also fixed the render call:**
```php
$this->render('admin/orders', [
    'currentPage' => (int)$page, // Ensure integer type
    'totalPages' => (int)$totalPages, // Ensure integer type
    'total' => (int)$total, // Ensure integer type
    // ... other params
]);
```

## Changes Made

### File: `app/views/admin/orders.php`
- Added explicit type casting for `$currentPage`, `$totalPages`, and `$total` before arithmetic operations
- Added `isset()` checks to prevent undefined variable errors
- Ensured all pagination calculations use integers

### File: `app/controllers/AdminController.php`
- Cast database count result to integer: `(int)($countResult['total'] ?? 0)`
- Cast `$totalPages` calculation result to integer
- Cast all pagination variables when passing to view: `(int)$page`, `(int)$totalPages`, `(int)$total`
- Added safety check for minimum 1 page

## Testing

The fix ensures:
✅ All variables are properly typed as integers before arithmetic
✅ No type mismatch errors when calculating pagination
✅ Safe fallback values if variables are undefined
✅ Consistent integer types throughout pagination logic

## Why This Happened

1. **Database Return Types**: PDO's `fetch()` can return values as strings, especially with `COUNT(*)` queries
2. **GET Parameters**: URL parameters (`?page=3`) come through as strings
3. **PHP Type Coercion**: PHP 8+ is stricter about type mismatches than PHP 7

## Prevention

- Always cast database numeric values to integers: `(int)$value`
- Always cast URL parameters to integers: `intval($_GET['page'])`
- Validate and cast variables before arithmetic operations
- Use strict type checking in calculations

## Result

✅ **Error Fixed**: No more type mismatch errors
✅ **Pagination Works**: All pagination calculations work correctly
✅ **Type Safety**: All numeric values are properly typed
✅ **No Issues**: The page loads and displays orders correctly

