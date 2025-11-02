<?php
$title = 'Edit Product - Admin';
ob_start();
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Edit Product</h1>
        <a href="/admin/products" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
            Back to Products
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                    <input type="text" id="name" name="name" required
                           value="<?php echo htmlspecialchars($product['name']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                           placeholder="Enter product name">
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                    <select id="category_id" name="category_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                    <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                          placeholder="Enter product description"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price (৳) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required
                           value="<?php echo $product['price']; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                           placeholder="0.00">
                </div>

                <div>
                    <label for="stock_quantity" class="block text-sm font-medium text-gray-700 mb-2">Stock Quantity</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" min="0"
                           value="<?php echo $product['stock_quantity']; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                           placeholder="0">
                </div>

                <div>
                    <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
                    <select id="unit" name="unit"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                        <option value="">Select unit</option>
                        <option value="kg" <?php echo ($product['unit'] == 'kg') ? 'selected' : ''; ?>>Kilogram (kg)</option>
                        <option value="g" <?php echo ($product['unit'] == 'g') ? 'selected' : ''; ?>>Gram (g)</option>
                        <option value="liter" <?php echo ($product['unit'] == 'liter') ? 'selected' : ''; ?>>Liter</option>
                        <option value="ml" <?php echo ($product['unit'] == 'ml') ? 'selected' : ''; ?>>Milliliter (ml)</option>
                        <option value="pcs" <?php echo ($product['unit'] == 'pcs') ? 'selected' : ''; ?>>Pieces</option>
                        <option value="packs" <?php echo ($product['unit'] == 'packs') ? 'selected' : ''; ?>>Packs</option>
                        <option value="dozen" <?php echo ($product['unit'] == 'dozen') ? 'selected' : ''; ?>>Dozen</option>
                        <option value="bottles" <?php echo ($product['unit'] == 'bottles') ? 'selected' : ''; ?>>Bottles</option>
                        <option value="tubs" <?php echo ($product['unit'] == 'tubs') ? 'selected' : ''; ?>>Tubs</option>
                        <option value="boxes" <?php echo ($product['unit'] == 'boxes') ? 'selected' : ''; ?>>Boxes</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Image URL</label>
                <input type="url" id="image" name="image"
                       value="<?php echo htmlspecialchars($product['image']); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                       placeholder="https://example.com/image.jpg">
                <p class="text-sm text-gray-500 mt-1">Enter a URL for the product image</p>
            </div>

            <div>
                <label for="nutrition_info" class="block text-sm font-medium text-gray-700 mb-2">Nutrition Information</label>
                <textarea id="nutrition_info" name="nutrition_info" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                          placeholder="Enter nutrition information"><?php echo htmlspecialchars($product['nutrition_info']); ?></textarea>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                    <i class="fas fa-save mr-2"></i>Update Product
                </button>
                <a href="/admin/products" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'app/views/admin/layout.php';
?>
