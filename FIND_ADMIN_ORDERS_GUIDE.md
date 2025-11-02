# How to Access Admin Orders Management Section

## Location of Orders Page

The **Orders Management** section is located in the **Admin Panel** sidebar menu.

### Step-by-Step Access:

1. **Log in as Admin**
   - Go to: `http://localhost/login` (or your site URL)
   - Login with admin credentials

2. **Navigate to Admin Panel**
   - After login, go to: `http://localhost/admin` or `http://localhost/admin/dashboard`

3. **Find Orders in Sidebar**
   - Look at the **left sidebar menu**
   - You'll see a menu item labeled **"Orders"** with a shopping bag icon (🛍️)
   - It's located between:
     - **Inventory** (above)
     - **Products** (below)

4. **Click on "Orders"**
   - Click the "Orders" menu item
   - URL: `http://localhost/admin/orders`

### Visual Guide:

```
Admin Sidebar Menu:
┌─────────────────────┐
│ Dashboard           │
│ Sales Analytics     │
│ Inventory           │
│ ▶ Orders  ← HERE!   │ ← Click this!
│ Products            │
│ Categories          │
│ Coupons             │
│ Users               │
│ Subscriptions       │
│ Surprise Gifts      │
└─────────────────────┘
```

## Direct URL Access

You can also access the Orders page directly:
- **URL**: `http://localhost/admin/orders`

## If You Don't See Orders

If you visit the Orders page but see "No orders found", this could mean:

1. **No orders exist in database**
   - No customers have placed orders yet
   - Check the database to verify

2. **Filters are hiding orders**
   - Look at the filter section at the top
   - Click "Clear Filters" or set Status to "All Orders"
   - Check date range filters

3. **Orders are on another page**
   - Check pagination at the bottom
   - Use page numbers or Next/Previous buttons

## Quick Troubleshooting

### Check if Orders Exist:
1. Go to: `http://localhost/admin/orders`
2. Look at the statistics cards at the top
   - **Total Orders** - shows total count
   - **Pending** - shows pending orders
   - **Delivered** - shows delivered orders
3. If all show 0, there are no orders in the database

### Clear All Filters:
1. On the Orders page, find the filter section
2. Click "Clear Filters" button (or manually reset):
   - Status: "All Orders"
   - Search: (empty)
   - From Date: (empty)
   - To Date: (empty)
   - Driver: "All Drivers"
3. Click "Apply Filters"

## Mobile/Tablet Access

On mobile devices, the sidebar may be hidden:
1. Look for the **menu icon (☰)** at the top-left
2. Click it to open the sidebar
3. Scroll to find "Orders" menu item
4. Click "Orders"

## Need Help?

If you still can't find the Orders section:
1. Make sure you're logged in as an admin user
2. Check the URL: it should be `/admin/orders`
3. Verify admin access in the database
4. Check browser console for JavaScript errors

