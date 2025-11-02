<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - 403 Forbidden</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mb-6">
            <div class="mx-auto w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-ban text-red-500 text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">403 Forbidden</h1>
            <p class="text-gray-600">You don't have permission to access this resource.</p>
        </div>
        
        <div class="space-y-4">
            <p class="text-sm text-gray-500">
                <?php if (isset($_SESSION['user_id'])): ?>
                    You are logged in but don't have admin privileges.
                <?php else: ?>
                    Please log in to access this area.
                <?php endif; ?>
            </p>
            
            <div class="flex flex-col sm:flex-row gap-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-home mr-2"></i>Go Home
                    </a>
                <?php else: ?>
                    <a href="/login" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                <?php endif; ?>
                
                <button onclick="history.back()" class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Go Back
                </button>
            </div>
        </div>
        
        <div class="mt-6 pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-400">
                If you believe this is an error, please contact the administrator.
            </p>
        </div>
    </div>
</body>
</html>
