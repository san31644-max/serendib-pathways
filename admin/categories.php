<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$message_type = '';
$edit_category = null;

// Handle form submission
if ($_POST) {
    $action = $_POST['action'];
    
    if ($action == 'add') {
        $category_name = trim($_POST['category_name']);
        
        if ($category_name) {
            // Check if category already exists
            $check_query = "SELECT COUNT(*) FROM categories WHERE category_name = ?";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->execute([$category_name]);
            
            if ($check_stmt->fetchColumn() > 0) {
                $message = "Category with this name already exists.";
                $message_type = "error";
            } else {
                $query = "INSERT INTO categories (category_name) VALUES (?)";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$category_name])) {
                    $message = "Category added successfully!";
                    $message_type = "success";
                } else {
                    $message = "Error adding category.";
                    $message_type = "error";
                }
            }
        } else {
            $message = "Please enter a category name.";
            $message_type = "error";
        }
    }
    
    if ($action == 'edit' && isset($_POST['category_id'])) {
        $category_id = intval($_POST['category_id']);
        $category_name = trim($_POST['category_name']);
        
        if ($category_name) {
            // Check if category name already exists (excluding current category)
            $check_query = "SELECT COUNT(*) FROM categories WHERE category_name = ? AND category_id != ?";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->execute([$category_name, $category_id]);
            
            if ($check_stmt->fetchColumn() > 0) {
                $message = "Category with this name already exists.";
                $message_type = "error";
            } else {
                $query = "UPDATE categories SET category_name = ? WHERE category_id = ?";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$category_name, $category_id])) {
                    $message = "Category updated successfully!";
                    $message_type = "success";
                } else {
                    $message = "Error updating category.";
                    $message_type = "error";
                }
            }
        } else {
            $message = "Please enter a category name.";
            $message_type = "error";
        }
    }
    
    if ($action == 'delete' && isset($_POST['category_id'])) {
        $category_id = intval($_POST['category_id']);
        
        // Optional: Check if category is being used in other tables
        // $usage_check = "SELECT COUNT(*) FROM packages WHERE category_id = ?";
        // $usage_stmt = $db->prepare($usage_check);
        // $usage_stmt->execute([$category_id]);
        // 
        // if ($usage_stmt->fetchColumn() > 0) {
        //     $message = "Cannot delete category. It is being used by existing packages.";
        //     $message_type = "error";
        // } else {
            $query = "DELETE FROM categories WHERE category_id = ?";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$category_id])) {
                $message = "Category deleted successfully!";
                $message_type = "success";
            } else {
                $message = "Error deleting category.";
                $message_type = "error";
            }
        // }
    }
}

// Handle edit request
if (isset($_GET['edit']) && $_GET['edit']) {
    $edit_id = intval($_GET['edit']);
    $query = "SELECT * FROM categories WHERE category_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$edit_id]);
    $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all categories
$query = "SELECT * FROM categories ORDER BY category_name ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/admin_header.php';
?>

<!-- Mobile-optimized header -->
<div class="mb-4 sm:mb-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-1 sm:mb-2">Manage Categories</h2>
            <p class="text-sm sm:text-base text-gray-600">Add, edit, or remove tour categories</p>
        </div>
        <button onclick="showAddCategoryForm()" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white px-4 sm:px-6 py-3 sm:py-2 rounded-lg font-semibold transition duration-300 touch-manipulation">
            <i class="fas fa-plus mr-2"></i>Add New Category
        </button>
    </div>
</div>

<?php if ($message): ?>
    <div class="mb-4 sm:mb-6 p-3 sm:p-4 rounded-lg text-sm sm:text-base <?php echo $message_type == 'success' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Mobile-optimized Add/Edit Category Form -->
<div id="category-form" class="<?php echo ($edit_category || isset($_GET['add'])) ? '' : 'hidden'; ?> bg-white rounded-lg shadow-lg p-4 sm:p-6 mb-4 sm:mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg sm:text-xl font-semibold"><?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?></h3>
        <button type="button" onclick="hideCategoryForm()" class="sm:hidden p-2 text-gray-500 hover:text-gray-700">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="action" value="<?php echo $edit_category ? 'edit' : 'add'; ?>">
        <?php if ($edit_category): ?>
            <input type="hidden" name="category_id" value="<?php echo $edit_category['category_id']; ?>">
        <?php endif; ?>
        
        <!-- Mobile-first form layout -->
        <div class="space-y-4">
            <div>
                <label for="category_name" class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
                <input type="text" id="category_name" name="category_name" value="<?php echo $edit_category ? htmlspecialchars($edit_category['category_name']) : ''; ?>" required 
                       placeholder="Enter category name" 
                       class="w-full px-3 sm:px-4 py-3 sm:py-2 text-base sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 touch-manipulation"
                       maxlength="100">
                <p class="text-xs text-gray-500 mt-1">Maximum 100 characters</p>
            </div>
        </div>
        
        <!-- Mobile-optimized action buttons -->
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mt-6">
            <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white px-6 py-3 sm:py-2 rounded-lg font-semibold transition duration-300 touch-manipulation order-1">
                <i class="fas fa-save mr-2"></i><?php echo $edit_category ? 'Update Category' : 'Save Category'; ?>
            </button>
            <button type="button" onclick="hideCategoryForm()" class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 active:bg-gray-700 text-white px-6 py-3 sm:py-2 rounded-lg font-semibold transition duration-300 touch-manipulation order-2 sm:order-1">
                Cancel
            </button>
        </div>
    </form>
</div>

<!-- Mobile-optimized Categories List -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <?php if (empty($categories)): ?>
        <div class="p-6 sm:p-8 text-center">
            <i class="fas fa-tags text-3xl sm:text-4xl text-gray-400 mb-3 sm:mb-4"></i>
            <p class="text-gray-500 text-base sm:text-lg">No categories found. Add your first category to get started!</p>
        </div>
    <?php else: ?>
        <!-- Mobile card view -->
        <div class="block sm:hidden">
            <div class="divide-y divide-gray-200">
                <?php foreach ($categories as $category): ?>
                <div class="p-4 hover:bg-gray-50 transition duration-150">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 flex-shrink-0 bg-gradient-to-r from-blue-400 to-purple-500 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tag text-white text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($category['category_name']); ?></div>
                                <div class="text-sm text-gray-600">ID: <?php echo $category['category_id']; ?></div>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="?edit=<?php echo $category['category_id']; ?>" class="p-2 text-blue-600 hover:text-blue-900 active:text-blue-700 transition duration-150 touch-manipulation">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                <button type="submit" class="p-2 text-red-600 hover:text-red-900 active:text-red-700 transition duration-150 touch-manipulation">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Desktop table view -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($categories as $category): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo $category['category_id']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 flex-shrink-0 bg-gradient-to-r from-blue-400 to-purple-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-tag text-white text-xs"></i>
                                </div>
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($category['category_name']); ?></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-4">
                            <a href="?edit=<?php echo $category['category_id']; ?>" class="text-blue-600 hover:text-blue-900 transition duration-300">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900 transition duration-300" onclick="return confirm('Are you sure you want to delete this category?')">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Category Statistics (Optional) -->
<div class="mt-6 bg-white rounded-lg shadow-lg p-4 sm:p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistics</h3>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg">
            <div class="flex items-center">
                <div class="h-8 w-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-tags text-white text-sm"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-blue-600"><?php echo count($categories); ?></div>
                    <div class="text-sm text-gray-600">Total Categories</div>
                </div>
            </div>
        </div>
        <!-- Add more statistics as needed -->
    </div>
</div>

<script>
    // Enhanced mobile-friendly JavaScript
    function showAddCategoryForm() {
        // Clear the URL parameters and show form
        window.history.replaceState({}, document.title, window.location.pathname);
        
        const form = document.getElementById('category-form');
        form.classList.remove('hidden');
        
        // Change form title and action
        const title = form.querySelector('h3');
        title.textContent = 'Add New Category';
        
        const actionInput = form.querySelector('input[name="action"]');
        actionInput.value = 'add';
        
        // Remove edit ID input if exists
        const idInput = form.querySelector('input[name="category_id"]');
        if (idInput) {
            idInput.remove();
        }
        
        // Clear all form fields
        form.querySelectorAll('input[type="text"]').forEach(field => {
            field.value = '';
        });
        
        // Update button text
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Category';
        
        // Smooth scroll with mobile-friendly offset
        setTimeout(() => {
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
        
        // Focus first input on desktop only
        if (window.innerWidth >= 640) {
            setTimeout(() => {
                document.getElementById('category_name').focus();
            }, 300);
        }
    }

    function hideCategoryForm() {
        // Clear URL parameters
        window.history.replaceState({}, document.title, window.location.pathname);
        
        const form = document.getElementById('category-form');
        form.classList.add('hidden');
    }

    // Auto-focus and scroll handling
    document.addEventListener('DOMContentLoaded', function() {
        // If editing, scroll to form
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('edit')) {
            setTimeout(() => {
                document.getElementById('category-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    });

    // Enhanced touch interactions
    document.addEventListener('touchstart', function() {}, { passive: true });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const categoryName = document.getElementById('category_name').value.trim();
        
        if (!categoryName) {
            e.preventDefault();
            alert('Please enter a category name.');
            return;
        }
        
        if (categoryName.length > 100) {
            e.preventDefault();
            alert('Category name must be 100 characters or less.');
            return;
        }
    });
</script>

<?php include 'includes/admin_footer.php'; ?>