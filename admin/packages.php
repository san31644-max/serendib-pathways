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
$edit_package = null;

// Handle form submission
if ($_POST) {
    $action = $_POST['action'];
    
    if ($action == 'add') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $image = trim($_POST['image']);
        $duration = trim($_POST['duration']);
        $destinations = trim($_POST['destinations']);
        $inclusions = trim($_POST['inclusions']);
        $exclusions = trim($_POST['exclusions']);
        $gallery_image1 = trim($_POST['gallery_image1']);
        $gallery_image2 = trim($_POST['gallery_image2']);
        $gallery_image3 = trim($_POST['gallery_image3']);
        $gallery_image4 = trim($_POST['gallery_image4']);
        
        if ($name && $description && $duration) {
            $query = "INSERT INTO packages (name, description, image, duration, destinations, inclusions, exclusions, gallery_image1, gallery_image2, gallery_image3, gallery_image4) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$name, $description, $image, $duration, $destinations, $inclusions, $exclusions, $gallery_image1, $gallery_image2, $gallery_image3, $gallery_image4])) {
                $message = "Package added successfully!";
                $message_type = "success";
            } else {
                $message = "Error adding package.";
                $message_type = "error";
            }
        } else {
            $message = "Please fill in all required fields.";
            $message_type = "error";
        }
    }
    
    if ($action == 'edit' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $image = trim($_POST['image']);
        $duration = trim($_POST['duration']);
        $destinations = trim($_POST['destinations']);
        $inclusions = trim($_POST['inclusions']);
        $exclusions = trim($_POST['exclusions']);
        $gallery_image1 = trim($_POST['gallery_image1']);
        $gallery_image2 = trim($_POST['gallery_image2']);
        $gallery_image3 = trim($_POST['gallery_image3']);
        $gallery_image4 = trim($_POST['gallery_image4']);
        
        if ($name && $description && $duration) {
            $query = "UPDATE packages SET name = ?, description = ?, image = ?, duration = ?, destinations = ?, inclusions = ?, exclusions = ?, gallery_image1 = ?, gallery_image2 = ?, gallery_image3 = ?, gallery_image4 = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$name, $description, $image, $duration, $destinations, $inclusions, $exclusions, $gallery_image1, $gallery_image2, $gallery_image3, $gallery_image4, $id])) {
                $message = "Package updated successfully!";
                $message_type = "success";
            } else {
                $message = "Error updating package.";
                $message_type = "error";
            }
        } else {
            $message = "Please fill in all required fields.";
            $message_type = "error";
        }
    }
    
    if ($action == 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $query = "DELETE FROM packages WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$id])) {
            $message = "Package deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting package.";
            $message_type = "error";
        }
    }
}

// Handle edit request
if (isset($_GET['edit']) && $_GET['edit']) {
    $edit_id = intval($_GET['edit']);
    $query = "SELECT * FROM packages WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$edit_id]);
    $edit_package = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all packages
$query = "SELECT * FROM packages ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/admin_header.php';
?>

<!-- Mobile-optimized header -->
<div class="mb-4 sm:mb-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-1 sm:mb-2">Manage Packages</h2>
            <p class="text-sm sm:text-base text-gray-600">Add, edit, or remove tour packages</p>
        </div>
        <button onclick="showAddPackageForm()" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 active:bg-green-800 text-white px-4 sm:px-6 py-3 sm:py-2 rounded-lg font-semibold transition duration-300 touch-manipulation">
            <i class="fas fa-plus mr-2"></i>Add New Package
        </button>
    </div>
</div>

<?php if ($message): ?>
    <div class="mb-4 sm:mb-6 p-3 sm:p-4 rounded-lg text-sm sm:text-base <?php echo $message_type == 'success' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Mobile-optimized Add/Edit Package Form -->
<div id="package-form" class="<?php echo ($edit_package || isset($_GET['add'])) ? '' : 'hidden'; ?> bg-white rounded-lg shadow-lg p-4 sm:p-6 mb-4 sm:mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg sm:text-xl font-semibold"><?php echo $edit_package ? 'Edit Package' : 'Add New Package'; ?></h3>
        <button type="button" onclick="hidePackageForm()" class="sm:hidden p-2 text-gray-500 hover:text-gray-700">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="action" value="<?php echo $edit_package ? 'edit' : 'add'; ?>">
        <?php if ($edit_package): ?>
            <input type="hidden" name="id" value="<?php echo $edit_package['id']; ?>">
        <?php endif; ?>
        
        <!-- Mobile-first form layout -->
        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="package_name" class="block text-sm font-medium text-gray-700 mb-2">Package Name *</label>
                    <input type="text" id="package_name" name="name" value="<?php echo $edit_package ? htmlspecialchars($edit_package['name']) : ''; ?>" required class="w-full px-3 sm:px-4 py-3 sm:py-2 text-base sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 touch-manipulation">
                </div>
                <div>
                    <label for="package_duration" class="block text-sm font-medium text-gray-700 mb-2">Duration *</label>
                    <input type="text" id="package_duration" name="duration" value="<?php echo $edit_package ? htmlspecialchars($edit_package['duration']) : ''; ?>" placeholder="e.g., 7 days / 6 nights" required class="w-full px-3 sm:px-4 py-3 sm:py-2 text-base sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 touch-manipulation">
                </div>
            </div>
            
            <div>
                <label for="package_description" class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                <textarea id="package_description" name="description" rows="4" required class="w-full px-3 sm:px-4 py-3 sm:py-2 text-base sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 touch-manipulation resize-y"><?php echo $edit_package ? htmlspecialchars($edit_package['description']) : ''; ?></textarea>
            </div>
            
            <div>
                <label for="package_destinations" class="block text-sm font-medium text-gray-700 mb-2">Destinations Included</label>
                <textarea id="package_destinations" name="destinations" rows="3" placeholder="List of destinations included in this package" class="w-full px-3 sm:px-4 py-3 sm:py-2 text-base sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 touch-manipulation resize-y"><?php echo $edit_package ? htmlspecialchars($edit_package['destinations']) : ''; ?></textarea>
            </div>
            
            <!-- Collapsible sections for mobile -->
            <div class="border border-gray-200 rounded-lg">
                <button type="button" onclick="toggleSection('inclusions')" class="w-full px-4 py-3 text-left font-medium text-gray-700 bg-gray-50 rounded-t-lg flex justify-between items-center sm:hidden">
                    <span>Inclusions & Exclusions</span>
                    <i class="fas fa-chevron-down transition-transform" id="inclusions-icon"></i>
                </button>
                <div id="inclusions-section" class="p-4 space-y-4 sm:block">
                    <div>
                        <label for="package_inclusions" class="block text-sm font-medium text-gray-700 mb-2">Inclusions</label>
                        <textarea id="package_inclusions" name="inclusions" rows="4" placeholder="What's included in the package (one per line)" class="w-full px-3 sm:px-4 py-3 sm:py-2 text-base sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 touch-manipulation resize-y"><?php echo $edit_package ? htmlspecialchars($edit_package['inclusions']) : ''; ?></textarea>
                    </div>
                    
                    <div>
                        <label for="package_exclusions" class="block text-sm font-medium text-gray-700 mb-2">Exclusions</label>
                        <textarea id="package_exclusions" name="exclusions" rows="3" placeholder="What's not included in the package (one per line)" class="w-full px-3 sm:px-4 py-3 sm:py-2 text-base sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 touch-manipulation resize-y"><?php echo $edit_package ? htmlspecialchars($edit_package['exclusions']) : ''; ?></textarea>
                    </div>
                </div>
            </div>
            
            <div>
                <label for="package_image" class="block text-sm font-medium text-gray-700 mb-2">Main Image URL</label>
                <input type="url" id="package_image" name="image" value="<?php echo $edit_package ? htmlspecialchars($edit_package['image']) : ''; ?>" placeholder="https://example.com/image.jpg" class="w-full px-3 sm:px-4 py-3 sm:py-2 text-base sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 touch-manipulation">
            </div>

            <!-- Gallery Images Section - Collapsible on mobile -->
            <div class="border border-gray-200 rounded-lg">
                <button type="button" onclick="toggleSection('gallery')" class="w-full px-4 py-3 text-left font-medium text-gray-700 bg-gray-50 rounded-t-lg flex justify-between items-center sm:hidden">
                    <span>Gallery Images (Optional)</span>
                    <i class="fas fa-chevron-down transition-transform" id="gallery-icon"></i>
                </button>
                <div class="sm:px-0 sm:py-0 sm:bg-transparent">
                    <h4 class="hidden sm:block text-base sm:text-lg font-semibold text-gray-800 mb-4">Gallery Images (Optional)</h4>
                </div>
                <div id="gallery-section" class="p-4 space-y-4 sm:p-0 sm:space-y-4 sm:block">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="gallery_image1" class="block text-sm font-medium text-gray-700 mb-2">Gallery Image 1</label>
                            <input type="url" id="gallery_image1" name="gallery_image1" value="<?php echo $edit_package ? htmlspecialchars($edit_package['gallery_image1']) : ''; ?>" placeholder="https://example.com/gallery1.jpg" class="w-full px-3 sm:px-4 py-3 sm:py-2 text-base sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 touch-manipulation">
                        </div>
                        <div>
                            <label for="gallery_image2" class="block text-sm font-medium text-gray-700 mb-2">Gallery Image 2</label>
                            <input type="url" id="gallery_image2" name="gallery_image2" value="<?php echo $edit_package ? htmlspecialchars($edit_package['gallery_image2']) : ''; ?>" placeholder="https://example.com/gallery2.jpg" class="w-full px-3 sm:px-4 py-3 sm:py-2 text-base sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 touch-manipulation">
                        </div>
                        <div>
                            <label for="gallery_image3" class="block text-sm font-medium text-gray-700 mb-2">Gallery Image 3</label>
                            <input type="url" id="gallery_image3" name="gallery_image3" value="<?php echo $edit_package ? htmlspecialchars($edit_package['gallery_image3']) : ''; ?>" placeholder="https://example.com/gallery3.jpg" class="w-full px-3 sm:px-4 py-3 sm:py-2 text-base sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 touch-manipulation">
                        </div>
                        <div>
                            <label for="gallery_image4" class="block text-sm font-medium text-gray-700 mb-2">Gallery Image 4</label>
                            <input type="url" id="gallery_image4" name="gallery_image4" value="<?php echo $edit_package ? htmlspecialchars($edit_package['gallery_image4']) : ''; ?>" placeholder="https://example.com/gallery4.jpg" class="w-full px-3 sm:px-4 py-3 sm:py-2 text-base sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 touch-manipulation">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile-optimized action buttons -->
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mt-6">
            <button type="submit" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 active:bg-green-800 text-white px-6 py-3 sm:py-2 rounded-lg font-semibold transition duration-300 touch-manipulation order-1">
                <i class="fas fa-save mr-2"></i><?php echo $edit_package ? 'Update Package' : 'Save Package'; ?>
            </button>
            <button type="button" onclick="hidePackageForm()" class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 active:bg-gray-700 text-white px-6 py-3 sm:py-2 rounded-lg font-semibold transition duration-300 touch-manipulation order-2 sm:order-1">
                Cancel
            </button>
        </div>
    </form>
</div>

<!-- Mobile-optimized Packages List -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <?php if (empty($packages)): ?>
        <div class="p-6 sm:p-8 text-center">
            <i class="fas fa-box text-3xl sm:text-4xl text-gray-400 mb-3 sm:mb-4"></i>
            <p class="text-gray-500 text-base sm:text-lg">No packages found. Add your first package to get started!</p>
        </div>
    <?php else: ?>
        <!-- Mobile card view -->
        <div class="block sm:hidden">
            <div class="divide-y divide-gray-200">
                <?php foreach ($packages as $package): ?>
                <div class="p-4 hover:bg-gray-50 transition duration-150">
                    <div class="flex items-start space-x-3">
                        <div class="h-16 w-16 flex-shrink-0">
                            <div class="h-16 w-16 rounded-lg bg-gradient-to-r from-blue-400 to-purple-500" style="background-image: url('<?php echo $package['image'] ?: ''; ?>'); background-size: cover; background-position: center;"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($package['name']); ?></div>
                            <div class="text-sm text-gray-600 mb-2 line-clamp-2"><?php echo htmlspecialchars(substr($package['description'], 0, 100)) . '...'; ?></div>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <?php echo htmlspecialchars($package['duration']); ?>
                                </span>
                                <?php 
                                $gallery_images = [$package['gallery_image1'], $package['gallery_image2'], $package['gallery_image3'], $package['gallery_image4']];
                                $gallery_count = 0;
                                foreach ($gallery_images as $img) {
                                    if ($img) $gallery_count++;
                                }
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $gallery_count > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo $gallery_count; ?> images
                                </span>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <?php echo date('M j, Y', strtotime($package['created_at'])); ?>
                                </span>
                            </div>
                            <div class="flex space-x-4">
                                <a href="?edit=<?php echo $package['id']; ?>" class="text-blue-600 hover:text-blue-900 active:text-blue-700 text-sm font-medium transition duration-150 touch-manipulation">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this package?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $package['id']; ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900 active:text-red-700 text-sm font-medium transition duration-150 touch-manipulation">
                                        <i class="fas fa-trash mr-1"></i>Delete
                                    </button>
                                </form>
                            </div>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gallery</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($packages as $package): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-12 w-12 flex-shrink-0">
                                    <div class="h-12 w-12 rounded-lg bg-gradient-to-r from-blue-400 to-purple-500" style="background-image: url('<?php echo $package['image'] ?: ''; ?>'); background-size: cover; background-position: center;"></div>
                                </div>
                                <div class="ml-4">
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($package['name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($package['description'], 0, 60)) . '...'; ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                <?php echo htmlspecialchars($package['duration']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex space-x-1">
                                <?php 
                                $gallery_images = [$package['gallery_image1'], $package['gallery_image2'], $package['gallery_image3'], $package['gallery_image4']];
                                $gallery_count = 0;
                                foreach ($gallery_images as $img) {
                                    if ($img) $gallery_count++;
                                }
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $gallery_count > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo $gallery_count; ?> images
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo date('M j, Y', strtotime($package['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="?edit=<?php echo $package['id']; ?>" class="text-blue-600 hover:text-blue-900 transition duration-300">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $package['id']; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900 transition duration-300" onclick="return confirm('Are you sure you want to delete this package?')">
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

<script>
    // Enhanced mobile-friendly JavaScript
    function showAddPackageForm() {
        // Clear the URL parameters and show form
        window.history.replaceState({}, document.title, window.location.pathname);
        
        const form = document.getElementById('package-form');
        form.classList.remove('hidden');
        
        // Change form title and action
        const title = form.querySelector('h3');
        title.textContent = 'Add New Package';
        
        const actionInput = form.querySelector('input[name="action"]');
        actionInput.value = 'add';
        
        // Remove edit ID input if exists
        const idInput = form.querySelector('input[name="id"]');
        if (idInput) {
            idInput.remove();
        }
        
        // Clear all form fields
        form.querySelectorAll('input[type="text"], input[type="url"], textarea').forEach(field => {
            field.value = '';
        });
        
        // Update button text
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Package';
        
        // Smooth scroll with mobile-friendly offset
        setTimeout(() => {
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
        
        // Focus first input on desktop only
        if (window.innerWidth >= 640) {
            setTimeout(() => {
                document.getElementById('package_name').focus();
            }, 300);
        }
    }

    function hidePackageForm() {
        // Clear URL parameters
        window.history.replaceState({}, document.title, window.location.pathname);
        
        const form = document.getElementById('package-form');
        form.classList.add('hidden');
        
        // Close any open collapsible sections
        const sections = ['inclusions', 'gallery'];
        sections.forEach(section => {
            const sectionEl = document.getElementById(`${section}-section`);
            const iconEl = document.getElementById(`${section}-icon`);
            if (sectionEl && window.innerWidth < 640) {
                sectionEl.classList.add('hidden');
                if (iconEl) iconEl.classList.remove('rotate-180');
            }
        });
    }

    function toggleSection(sectionName) {
        const section = document.getElementById(`${sectionName}-section`);
        const icon = document.getElementById(`${sectionName}-icon`);
        
        if (section.classList.contains('hidden')) {
            section.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            section.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    }

    // Auto-hide collapsible sections on mobile load
    document.addEventListener('DOMContentLoaded', function() {
        if (window.innerWidth < 640) {
            const sections = ['inclusions-section', 'gallery-section'];
            sections.forEach(sectionId => {
                const section = document.getElementById(sectionId);
                if (section) section.classList.add('hidden');
            });
        }
        
        // If editing, scroll to form
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('edit')) {
            setTimeout(() => {
                document.getElementById('package-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        const sections = ['inclusions-section', 'gallery-section'];
        if (window.innerWidth >= 640) {
            // Show all sections on desktop
            sections.forEach(sectionId => {
                const section = document.getElementById(sectionId);
                if (section) section.classList.remove('hidden');
            });
        }
    });

    // Enhanced touch interactions
    document.addEventListener('touchstart', function() {}, { passive: true });
</script>

<?php include 'includes/admin_footer.php'; ?>