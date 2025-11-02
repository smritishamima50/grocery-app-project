<?php
$title = 'Edit Category - Admin';
ob_start();
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Edit Category</h1>
        <a href="/admin/categories" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
            Back to Categories
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
                <input type="text" id="name" name="name" required
                       value="<?php echo htmlspecialchars($category['name']); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                       placeholder="Enter category name">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                          placeholder="Enter category description"><?php echo htmlspecialchars($category['description']); ?></textarea>
            </div>

            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Image URL</label>
                <input type="url" id="image" name="image"
                       value="<?php echo htmlspecialchars($category['image']); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                       placeholder="https://example.com/image.jpg">
                <p class="text-sm text-gray-500 mt-1">Enter a URL for the category image</p>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                    <i class="fas fa-save mr-2"></i>Update Category
                </button>
                <a href="/admin/categories" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
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
