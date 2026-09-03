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
$edit_destination = null;

function destination_image_value(string $upload_field, string $current_value): string {
    if (!isset($_FILES[$upload_field]) || $_FILES[$upload_field]['error'] === UPLOAD_ERR_NO_FILE) {
        return trim($current_value);
    }

    $file = $_FILES[$upload_field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('One of the images could not be uploaded. Please try again.');
    }
    if ((int) $file['size'] > 8 * 1024 * 1024) {
        throw new RuntimeException('Each image must be 8 MB or smaller.');
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime]) || @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('Only valid JPG, PNG, WebP or GIF images are allowed.');
    }

    $upload_dir = dirname(__DIR__) . '/assets/uploads/destinations';
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0775, true) && !is_dir($upload_dir)) {
        throw new RuntimeException('The destination upload folder is not writable.');
    }

    $filename = date('Ymd-His') . '-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $upload_dir . '/' . $filename)) {
        throw new RuntimeException('The image could not be saved on the server.');
    }
    return 'assets/uploads/destinations/' . $filename;
}

// Handle form submission
if ($_POST) {
    $action = $_POST['action'];
    try {
        foreach (['image', 'gallery_image1', 'gallery_image2', 'gallery_image3', 'gallery_image4'] as $image_field) {
            $_POST[$image_field] = destination_image_value($image_field . '_upload', (string) ($_POST[$image_field] ?? ''));
        }
    } catch (RuntimeException $upload_exception) {
        $message = $upload_exception->getMessage();
        $message_type = 'error';
        $action = 'upload_error';
    }
    
    if ($action == 'add') {
        $name = trim($_POST['name']);
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description']);
        $image = trim($_POST['image']);
        $category_id = intval($_POST['category_id']);
        $highlights = trim($_POST['highlights']);
        $gallery_image1 = trim($_POST['gallery_image1']);
        $gallery_image2 = trim($_POST['gallery_image2']);
        $gallery_image3 = trim($_POST['gallery_image3']);
        $gallery_image4 = trim($_POST['gallery_image4']);
        
        if ($name && $description && $category_id) {
            // Verify category exists
            $cat_check = "SELECT category_name FROM categories WHERE category_id = ?";
            $cat_stmt = $db->prepare($cat_check);
            $cat_stmt->execute([$category_id]);
            
            if ($cat_stmt->fetch()) {
                $query = "INSERT INTO destinations (name, location, description, image, category_id, highlights, gallery_image1, gallery_image2, gallery_image3, gallery_image4) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$name, $location, $description, $image, $category_id, $highlights, $gallery_image1, $gallery_image2, $gallery_image3, $gallery_image4])) {
                    $message = "Destination added successfully!";
                    $message_type = "success";
                } else {
                    $message = "Error adding destination.";
                    $message_type = "error";
                }
            } else {
                $message = "Selected category does not exist.";
                $message_type = "error";
            }
        } else {
            $message = "Please fill in all required fields.";
            $message_type = "error";
        }
    }
    
    if ($action == 'update' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description']);
        $image = trim($_POST['image']);
        $category_id = intval($_POST['category_id']);
        $highlights = trim($_POST['highlights']);
        $gallery_image1 = trim($_POST['gallery_image1']);
        $gallery_image2 = trim($_POST['gallery_image2']);
        $gallery_image3 = trim($_POST['gallery_image3']);
        $gallery_image4 = trim($_POST['gallery_image4']);
        
        if ($name && $description && $category_id) {
            // Verify category exists
            $cat_check = "SELECT category_name FROM categories WHERE category_id = ?";
            $cat_stmt = $db->prepare($cat_check);
            $cat_stmt->execute([$category_id]);
            
            if ($cat_stmt->fetch()) {
                $query = "UPDATE destinations SET name = ?, location = ?, description = ?, image = ?, category_id = ?, highlights = ?, gallery_image1 = ?, gallery_image2 = ?, gallery_image3 = ?, gallery_image4 = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$name, $location, $description, $image, $category_id, $highlights, $gallery_image1, $gallery_image2, $gallery_image3, $gallery_image4, $id])) {
                    $message = "Destination updated successfully!";
                    $message_type = "success";
                } else {
                    $message = "Error updating destination.";
                    $message_type = "error";
                }
            } else {
                $message = "Selected category does not exist.";
                $message_type = "error";
            }
        } else {
            $message = "Please fill in all required fields.";
            $message_type = "error";
        }
    }
    
    if ($action == 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $query = "DELETE FROM destinations WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$id])) {
            $message = "Destination deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting destination.";
            $message_type = "error";
        }
    }
}

// Get all categories for dropdown
$categories_query = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
$categories_stmt = $db->prepare($categories_query);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if we're editing a destination
if (isset($_GET['edit']) && $_GET['edit']) {
    $edit_id = intval($_GET['edit']);
    $query = "SELECT * FROM destinations WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$edit_id]);
    $edit_destination = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all destinations with category names
$query = "SELECT d.*, c.category_name 
          FROM destinations d 
          LEFT JOIN categories c ON d.category_id = c.category_id 
          ORDER BY d.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/admin_header.php';
?>

<style>
/* Mobile-first responsive improvements */
@media (max-width: 768px) {
    .mobile-header {
        flex-direction: column !important;
        gap: 1rem;
        align-items: stretch !important;
    }
    
    .mobile-btn {
        width: 100%;
        text-align: center;
    }
    
    .mobile-form-grid {
        grid-template-columns: 1fr !important;
    }
    
    .mobile-gallery-grid {
        grid-template-columns: 1fr !important;
    }
    
    .mobile-actions {
        flex-direction: column !important;
        gap: 0.5rem;
    }
    
    .mobile-actions button,
    .mobile-actions a {
        width: 100%;
        text-align: center;
    }
    
    .mobile-table-hide {
        display: none !important;
    }
    
    .mobile-card {
        display: block !important;
        padding: 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        background: white;
    }
    
    .mobile-card-header {
        display: flex;
        align-items: start;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }
    
    .mobile-card-image {
        width: 60px;
        height: 60px;
        flex-shrink: 0;
        border-radius: 0.5rem;
        background-size: cover;
        background-position: center;
        background-color: #f3f4f6;
    }
    
    .mobile-card-info {
        flex: 1;
        min-width: 0;
    }
    
    .mobile-card-title {
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.25rem;
        word-break: break-word;
    }
    
    .mobile-card-desc {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.25rem;
    }
    
    .mobile-card-category {
        font-size: 0.875rem;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .mobile-card-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .mobile-card-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .mobile-card-actions a,
    .mobile-card-actions button {
        flex: 1;
        padding: 0.5rem;
        text-align: center;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .mobile-edit-btn {
        background-color: #3b82f6;
        color: white;
    }
    
    .mobile-edit-btn:hover {
        background-color: #2563eb;
    }
    
    .mobile-delete-btn {
        background-color: #ef4444;
        color: white;
    }
    
    .mobile-delete-btn:hover {
        background-color: #dc2626;
    }
}

.sticky-form-toggle {
    position: fixed;
    bottom: 1rem;
    right: 1rem;
    z-index: 50;
    background-color: #059669;
    color: white;
    padding: 1rem;
    border-radius: 50%;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    border: none;
    cursor: pointer;
    transition: all 0.3s;
}

.sticky-form-toggle:hover {
    background-color: #047857;
    transform: scale(1.1);
}

@media (min-width: 769px) {
    .sticky-form-toggle {
        display: none;
    }
}

.category-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.no-categories-warning {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: white;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
</style>

<div class="mb-6 flex justify-between items-center mobile-header">
    <div>
        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Manage Destinations</h2>
        <p class="text-gray-600 text-sm md:text-base">Add, edit, or remove travel destinations</p>
    </div>
    <button onclick="showAddDestinationForm()" class="hidden md:block bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold transition duration-300">
        <i class="fas fa-plus mr-2"></i>Add New Destination
    </button>
    <button onclick="showAddDestinationForm()" class="md:hidden mobile-btn bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition duration-300">
        <i class="fas fa-plus mr-2"></i>Add Destination
    </button>
</div>

<?php if (empty($categories)): ?>
    <div class="no-categories-warning">
        <i class="fas fa-exclamation-triangle text-xl"></i>
        <div>
            <strong>No categories available!</strong> Please <a href="categories.php" class="underline font-semibold">create categories</a> first before adding destinations.
        </div>
    </div>
<?php endif; ?>

<?php if ($message): ?>
    <div class="mb-6 p-4 rounded-lg <?php echo $message_type == 'success' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Sticky FAB for mobile -->
<button onclick="showAddDestinationForm()" class="sticky-form-toggle md:hidden" <?php echo empty($categories) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
    <i class="fas fa-plus text-xl"></i>
</button>

<!-- Add/Edit Destination Form -->
<div id="destination-form" class="<?php echo $edit_destination ? '' : 'hidden'; ?> bg-white rounded-lg shadow-lg p-4 md:p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg md:text-xl font-semibold">
            <?php echo $edit_destination ? 'Edit Destination' : 'Add New Destination'; ?>
        </h3>
        <button onclick="cancelEdit()" class="md:hidden text-gray-500 hover:text-gray-700">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>
    
    <?php if (empty($categories)): ?>
        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg mb-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                <p class="text-yellow-800">You need to create at least one category before adding destinations. <a href="categories.php" class="underline font-semibold">Go to Categories</a></p>
            </div>
        </div>
    <?php else: ?>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?php echo $edit_destination ? 'update' : 'add'; ?>">
        <?php if ($edit_destination): ?>
            <input type="hidden" name="id" value="<?php echo $edit_destination['id']; ?>">
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mobile-form-grid">
            <div>
                <label for="destination_name" class="block text-sm font-medium text-gray-700 mb-2">Destination Name *</label>
                <input type="text" id="destination_name" name="name" required 
                       value="<?php echo $edit_destination ? htmlspecialchars($edit_destination['name']) : ''; ?>"
                       class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label for="destination_category" class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                <select id="destination_category" name="category_id" required 
                        class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['category_id']; ?>" 
                            <?php echo ($edit_destination && $edit_destination['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    Don't see your category? <a href="categories.php" class="text-blue-600 hover:text-blue-800 underline">Manage categories</a>
                </p>
            </div>
        </div>

        <div class="mt-4">
            <label for="destination_location" class="block text-sm font-medium text-gray-700 mb-2">Location / Region</label>
            <input type="text" id="destination_location" name="location" placeholder="e.g. Badulla, Uva Province"
                   value="<?php echo $edit_destination ? htmlspecialchars($edit_destination['location'] ?? '') : ''; ?>"
                   class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
        
        <div class="mt-4">
            <label for="destination_description" class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
            <textarea id="destination_description" name="description" rows="4" required 
                      class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"><?php echo $edit_destination ? htmlspecialchars($edit_destination['description']) : ''; ?></textarea>
        </div>
        
        <div class="mt-4">
            <label for="destination_highlights" class="block text-sm font-medium text-gray-700 mb-2">Highlights</label>
            <textarea id="destination_highlights" name="highlights" rows="3" placeholder="Key attractions and activities (one per line)" 
                      class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"><?php echo $edit_destination ? htmlspecialchars($edit_destination['highlights']) : ''; ?></textarea>
        </div>
        
        <div class="mt-4">
            <label for="destination_image_upload" class="block text-sm font-medium text-gray-700 mb-2">Main destination photo</label>
            <input type="file" id="destination_image_upload" name="image_upload" accept="image/jpeg,image/png,image/webp,image/gif"
                   class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm file:mr-4 file:rounded-full file:border-0 file:bg-green-100 file:px-4 file:py-2 file:font-semibold file:text-green-800 hover:file:bg-green-200">
            <p class="mt-1 text-xs text-gray-500">JPG, PNG, WebP or GIF, up to 8 MB. A new upload replaces the current photo.</p>
            <label for="destination_image" class="block text-xs font-medium text-gray-600 mt-3 mb-1">Or use an image URL/path</label>
            <input type="text" id="destination_image" name="image" placeholder="assets/example.jpg or https://example.com/image.jpg" 
                   value="<?php echo $edit_destination ? htmlspecialchars($edit_destination['image']) : ''; ?>"
                   class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>

        <!-- Gallery Images Section -->
        <div class="mt-6">
            <h4 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Gallery Images (Optional)</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mobile-gallery-grid">
                <div>
                    <label for="gallery_image1_upload" class="block text-sm font-medium text-gray-700 mb-2">Gallery Image 1</label>
                    <input type="file" id="gallery_image1_upload" name="gallery_image1_upload" accept="image/jpeg,image/png,image/webp,image/gif" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                    <input type="text" id="gallery_image1" name="gallery_image1" placeholder="Optional URL or existing path" 
                           value="<?php echo $edit_destination ? htmlspecialchars($edit_destination['gallery_image1']) : ''; ?>"
                           class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label for="gallery_image2_upload" class="block text-sm font-medium text-gray-700 mb-2">Gallery Image 2</label>
                    <input type="file" id="gallery_image2_upload" name="gallery_image2_upload" accept="image/jpeg,image/png,image/webp,image/gif" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                    <input type="text" id="gallery_image2" name="gallery_image2" placeholder="Optional URL or existing path" 
                           value="<?php echo $edit_destination ? htmlspecialchars($edit_destination['gallery_image2']) : ''; ?>"
                           class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label for="gallery_image3_upload" class="block text-sm font-medium text-gray-700 mb-2">Gallery Image 3</label>
                    <input type="file" id="gallery_image3_upload" name="gallery_image3_upload" accept="image/jpeg,image/png,image/webp,image/gif" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                    <input type="text" id="gallery_image3" name="gallery_image3" placeholder="Optional URL or existing path" 
                           value="<?php echo $edit_destination ? htmlspecialchars($edit_destination['gallery_image3']) : ''; ?>"
                           class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label for="gallery_image4_upload" class="block text-sm font-medium text-gray-700 mb-2">Gallery Image 4</label>
                    <input type="file" id="gallery_image4_upload" name="gallery_image4_upload" accept="image/jpeg,image/png,image/webp,image/gif" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                    <input type="text" id="gallery_image4" name="gallery_image4" placeholder="Optional URL or existing path" 
                           value="<?php echo $edit_destination ? htmlspecialchars($edit_destination['gallery_image4']) : ''; ?>"
                           class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>
        </div>
        
        <div class="flex flex-col md:flex-row space-y-3 md:space-y-0 md:space-x-4 mt-6 mobile-actions">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 md:py-2 rounded-lg font-semibold transition duration-300">
                <i class="fas fa-save mr-2"></i><?php echo $edit_destination ? 'Update Destination' : 'Save Destination'; ?>
            </button>
            <button type="button" onclick="cancelEdit()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 md:py-2 rounded-lg font-semibold transition duration-300">
                Cancel
            </button>
        </div>
    </form>
    
    <?php endif; ?>
</div>

<!-- Destinations List -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <?php if (empty($destinations)): ?>
        <div class="p-8 text-center">
            <i class="fas fa-map-marker-alt text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500 text-base md:text-lg">No destinations found. Add your first destination to get started!</p>
            <?php if (!empty($categories)): ?>
                <button onclick="showAddDestinationForm()" class="mt-4 bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold transition duration-300">
                    <i class="fas fa-plus mr-2"></i>Add Your First Destination
                </button>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gallery</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($destinations as $destination): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-12 w-12 flex-shrink-0">
                                    <div class="h-12 w-12 rounded-lg bg-gradient-to-r from-green-400 to-blue-500" style="background-image: url('<?php echo $destination['image'] ?: ''; ?>'); background-size: cover; background-position: center;"></div>
                                </div>
                                <div class="ml-4">
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($destination['name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($destination['description'], 0, 60)) . '...'; ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($destination['category_name']): ?>
                                <span class="category-badge">
                                    <i class="fas fa-tag mr-1"></i>
                                    <?php echo htmlspecialchars($destination['category_name']); ?>
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    No Category
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex space-x-1">
                                <?php 
                                $gallery_images = [$destination['gallery_image1'], $destination['gallery_image2'], $destination['gallery_image3'], $destination['gallery_image4']];
                                $gallery_count = 0;
                                foreach ($gallery_images as $img) {
                                    if ($img) $gallery_count++;
                                }
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $gallery_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo $gallery_count; ?> images
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo date('M j, Y', strtotime($destination['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="?edit=<?php echo $destination['id']; ?>" class="text-blue-600 hover:text-blue-900 transition duration-300">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $destination['id']; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900 transition duration-300" onclick="return confirm('Are you sure you want to delete this destination?')">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Mobile Card View -->
        <div class="md:hidden p-4">
            <?php foreach ($destinations as $destination): ?>
                <div class="mobile-card">
                    <div class="mobile-card-header">
                        <div class="mobile-card-image" style="background-image: url('<?php echo $destination['image'] ?: ''; ?>');"></div>
                        <div class="mobile-card-info">
                            <div class="mobile-card-title"><?php echo htmlspecialchars($destination['name']); ?></div>
                            <div class="mobile-card-desc"><?php echo htmlspecialchars(substr($destination['description'], 0, 80)) . '...'; ?></div>
                            <div class="mobile-card-category">
                                <i class="fas fa-tag text-xs"></i>
                                <?php echo $destination['category_name'] ? htmlspecialchars($destination['category_name']) : 'No Category'; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mobile-card-meta">
                        <span>Created: <?php echo date('M j, Y', strtotime($destination['created_at'])); ?></span>
                        <?php 
                        $gallery_images = [$destination['gallery_image1'], $destination['gallery_image2'], $destination['gallery_image3'], $destination['gallery_image4']];
                        $gallery_count = 0;
                        foreach ($gallery_images as $img) {
                            if ($img) $gallery_count++;
                        }
                        ?>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $gallery_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                            <?php echo $gallery_count; ?> images
                        </span>
                    </div>
                    
                    <div class="mobile-card-actions">
                        <a href="?edit=<?php echo $destination['id']; ?>" class="mobile-edit-btn">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                        <form method="POST" action="" style="flex: 1;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $destination['id']; ?>">
                            <button type="submit" class="mobile-delete-btn w-full" onclick="return confirm('Are you sure you want to delete this destination?')">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Categories Statistics (Optional) -->
<?php if (!empty($destinations)): ?>
<div class="mt-6 bg-white rounded-lg shadow-lg p-4 sm:p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistics</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-green-50 p-4 rounded-lg">
            <div class="flex items-center">
                <div class="h-8 w-8 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-map-marker-alt text-white text-sm"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-green-600"><?php echo count($destinations); ?></div>
                    <div class="text-sm text-gray-600">Total Destinations</div>
                </div>
            </div>
        </div>
        
        <div class="bg-blue-50 p-4 rounded-lg">
            <div class="flex items-center">
                <div class="h-8 w-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-tags text-white text-sm"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-blue-600"><?php echo count($categories); ?></div>
                    <div class="text-sm text-gray-600">Categories Used</div>
                </div>
            </div>
        </div>
        
        <?php 
        $destinations_with_gallery = 0;
        foreach ($destinations as $dest) {
            $gallery_images = [$dest['gallery_image1'], $dest['gallery_image2'], $dest['gallery_image3'], $dest['gallery_image4']];
            $gallery_count = 0;
            foreach ($gallery_images as $img) {
                if ($img) $gallery_count++;
            }
            if ($gallery_count > 0) $destinations_with_gallery++;
        }
        ?>
        <div class="bg-purple-50 p-4 rounded-lg">
            <div class="flex items-center">
                <div class="h-8 w-8 bg-purple-500 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-images text-white text-sm"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-purple-600"><?php echo $destinations_with_gallery; ?></div>
                    <div class="text-sm text-gray-600">With Gallery</div>
                </div>
            </div>
        </div>
        
        <div class="bg-orange-50 p-4 rounded-lg">
            <div class="flex items-center">
                <div class="h-8 w-8 bg-orange-500 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-calendar text-white text-sm"></i>
                </div>
                <div>
                    <?php 
                    $recent_destinations = 0;
                    $thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
                    foreach ($destinations as $dest) {
                        if ($dest['created_at'] >= $thirty_days_ago) {
                            $recent_destinations++;
                        }
                    }
                    ?>
                    <div class="text-2xl font-bold text-orange-600"><?php echo $recent_destinations; ?></div>
                    <div class="text-sm text-gray-600">Added This Month</div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Destination form functions
function showAddDestinationForm() {
    // Check if categories exist
    const categorySelect = document.getElementById('destination_category');
    if (!categorySelect || categorySelect.options.length <= 1) {
        alert('Please create at least one category before adding destinations.');
        window.location.href = 'categories.php';
        return;
    }
    
    // Clear the form
    const form = document.querySelector('form');
    if (form) {
        form.reset();
        document.querySelector('input[name="action"]').value = 'add';
        document.querySelector('h3').textContent = 'Add New Destination';
        document.querySelector('button[type="submit"] i').className = 'fas fa-save mr-2';
        document.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-save mr-2"></i>Save Destination';
        
        // Remove any hidden id input
        const hiddenId = document.querySelector('input[name="id"]');
        if (hiddenId) {
            hiddenId.remove();
        }
    }
    
    document.getElementById('destination-form').classList.remove('hidden');
    document.getElementById('destination-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    // Focus on first input (desktop only)
    if (window.innerWidth >= 768) {
        setTimeout(() => {
            document.getElementById('destination_name').focus();
        }, 300);
    }
}

function hideDestinationForm() {
    document.getElementById('destination-form').classList.add('hidden');
}

function cancelEdit() {
    // Remove edit parameter from URL and reload
    const url = new URL(window.location);
    url.searchParams.delete('edit');
    window.location.href = url.toString();
}

// Auto-show form if editing
<?php if ($edit_destination): ?>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('destination-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
<?php endif; ?>

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const name = document.getElementById('destination_name').value.trim();
            const description = document.getElementById('destination_description').value.trim();
            const category = document.getElementById('destination_category').value;
            
            if (!name || !description || !category) {
                e.preventDefault();
                alert('Please fill in all required fields (Name, Description, and Category).');
                return;
            }
            
            // Accept either full image URLs or local assets/... paths.
            const urlFields = ['destination_image', 'gallery_image1', 'gallery_image2', 'gallery_image3', 'gallery_image4'];
            for (let fieldId of urlFields) {
                const field = document.getElementById(fieldId);
                if (field && field.value && !/^(https?:\/\/|assets\/)/i.test(field.value.trim())) {
                    e.preventDefault();
                    alert('Use a full https:// image URL or a local assets/... path.');
                    field.focus();
                    return false;
                }
            }
        });
    }
});

// Category management helper
function goToCategories() {
    window.location.href = 'categories.php';
}

// Mobile viewport handling
function handleViewportChange() {
    const viewport = document.querySelector('meta[name="viewport"]');
    if (!viewport) {
        const meta = document.createElement('meta');
        meta.name = 'viewport';
        meta.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no';
        document.head.appendChild(meta);
    }
}

// Initialize on load
document.addEventListener('DOMContentLoaded', handleViewportChange);

// Enhanced category selection
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('destination_category');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            // Add visual feedback when category is selected
            if (this.value) {
                this.style.borderColor = '#10b981';
                this.style.boxShadow = '0 0 0 1px #10b981';
            } else {
                this.style.borderColor = '#d1d5db';
                this.style.boxShadow = 'none';
            }
        });
    }
});
</script>

<?php include 'includes/admin_footer.php'; ?>
