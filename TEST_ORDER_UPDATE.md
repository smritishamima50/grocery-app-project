# Order Update Testing Guide

## How to Test Order Status and Driver Assignment

### Prerequisites
1. Make sure you're logged in as an admin user
2. Navigate to `/admin/orders`
3. Open browser Developer Tools (F12) and go to Console tab

### Test 1: Update Order Status (Confirmed → Packed)

**Steps:**
1. Find an order with status "Confirmed"
2. Click on the status dropdown for that order
3. Select "Packed" from the dropdown
4. Check the browser console for logs:
   - `📤 Sending PATCH request to /api/admin/orders/{orderId}`
   - `📥 Response status: 200 OK`
   - `📥 Response text: {"success":true,"new_status":"packed",...}`
5. Check the UI:
   - Dropdown should show "Packed"
   - Text color should change to purple
   - Success notification should appear: "Order status updated to 'Packed' successfully"
6. Check PHP error log (if enabled):
   - Should see: `✅ Database updated with fields: status = ?`
   - Should see: `✅ Transaction committed successfully`
   - Should see: `✅ Verification passed - database update confirmed`
7. Refresh the page - status should remain "Packed"

### Test 2: Assign Driver

**Steps:**
1. Find any order (any status)
2. Click on the driver dropdown for that order
3. Select a driver name (e.g., "John Doe")
4. Check the browser console for logs:
   - `📤 Sending PATCH request to /api/admin/orders/{orderId}`
   - `📥 Response status: 200 OK`
   - `📥 Response text: {"success":true,"assigned_driver":"John Doe",...}`
5. Check the UI:
   - Dropdown should show the selected driver name
   - Success notification should appear: "Driver 'John Doe' assigned successfully"
6. Check PHP error log:
   - Should see: `✅ Database updated with fields: assigned_driver = ?`
   - Should see: `✅ Transaction committed successfully`
   - Should see: `✅ Verification passed - database update confirmed`
7. Refresh the page - driver should remain assigned

### Test 3: Unassign Driver

**Steps:**
1. Find an order with a driver assigned
2. Click on the driver dropdown
3. Select "Unassigned" (empty option)
4. Check the UI:
   - Dropdown should show "Unassigned"
   - Success notification should appear: "Driver unassigned successfully"
5. Refresh the page - driver should remain unassigned

### Troubleshooting

**If status update fails:**
1. Check browser console for error messages
2. Check PHP error log (usually in `C:\xampp\apache\logs\error.log` or similar)
3. Verify you're logged in as admin (check `$_SESSION['role']`)
4. Verify the order ID exists in database
5. Check database connection

**If driver assignment fails:**
1. Check browser console for error messages
2. Verify driver name exists in `drivers` table
3. Check PHP error log
4. Verify admin authentication

**Common Issues:**
- **403 Forbidden**: Admin authentication failed - verify session role
- **404 Not Found**: Order ID doesn't exist
- **500 Internal Server Error**: Check PHP error log for details
- **No rows updated**: Order ID mismatch or database constraint issue

### Database Verification

To verify updates in database directly:

```sql
-- Check order status
SELECT id, status, assigned_driver, updated_at 
FROM orders 
WHERE id = {orderId};

-- Check status history
SELECT * 
FROM order_status_history 
WHERE order_id = {orderId} 
ORDER BY created_at DESC;

-- Check delivery updates
SELECT * 
FROM delivery_updates 
WHERE order_id = {orderId} 
ORDER BY updated_at DESC;
```

### Expected Behavior

✅ Status changes should:
- Update immediately in UI (no page reload)
- Save to database
- Show success message
- Update status color
- Create history entry
- Create delivery update entry

✅ Driver assignments should:
- Update immediately in UI (no page reload)
- Save to database
- Show success message with driver name
- Allow unassigning (setting to null)

